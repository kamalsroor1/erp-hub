<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\ReturnDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string)$request->input('search', ''));
        $debtStatus = $request->input('debt_status', 'all');

        $query = Customer::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('tax_number', 'like', "%{$search}%");
            });
        }

        if ($debtStatus === 'debtor') {
            $query->where('current_balance', '>', 0);
        } elseif ($debtStatus === 'zero') {
            $query->where('current_balance', '=', 0);
        } elseif ($debtStatus === 'creditor') {
            $query->where('current_balance', '<', 0);
        }

        $customers = $query->latest('id')->paginate(20)->withQueryString();

        $totalDebt = (float)Customer::where('current_balance', '>', 0)->sum('current_balance');
        $debtorsCount = Customer::where('current_balance', '>', 0)->count();
        $totalCustomersCount = Customer::count();

        return Inertia::render('Customers/Index', [
            'customers' => $customers->through(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'address' => $c->address,
                'tax_number' => $c->tax_number,
                'current_balance' => (float)$c->current_balance,
                'is_active' => (bool)$c->is_active,
                'notes' => $c->notes,
                'can_be_deleted' => $c->canBeDeleted(),
                'deletion_blockers' => $c->getDeletionBlockers(),
            ]),
            'metrics' => [
                'total_debt' => $totalDebt,
                'debtors_count' => $debtorsCount,
                'total_customers' => $totalCustomersCount,
            ],
            'filters' => [
                'search' => $search,
                'debt_status' => $debtStatus,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:50',
            'opening_balance' => 'nullable|numeric',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated) {
            Customer::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'tax_number' => $validated['tax_number'] ?? null,
                'current_balance' => $validated['opening_balance'] ?? '0.000',
                'is_active' => true,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->back()->with('success', 'تم إضافة العميل بنجاح');
    }

    public function update(Request $request, int $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($customer, $validated) {
            $customer->update($validated);
        });

        return redirect()->back()->with('success', 'تم تعديل بيانات العميل بنجاح');
    }

    public function collectPayment(Request $request, int $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,instapay,wallet,bank',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($customer, $validated) {
            Payment::create([
                'customer_id' => $customer->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'user_id' => auth()->id(),
                'notes' => $validated['notes'] ?? 'تحصيل دفعة نقدية من الحساب',
            ]);

            // Deduct collected amount from current_balance
            $customer->current_balance = bcsub((string)$customer->current_balance, (string)$validated['amount'], 3);
            $customer->save();
        });

        return redirect()->back()->with('success', 'تم تسجيل سند التحصيل وقيد الدفعة بنجاح');
    }

    public function statement(Request $request, int $id): Response
    {
        $customer = Customer::findOrFail($id);
        $dateFrom = $request->input('from');
        $dateTo = $request->input('to');

        // Fetch Invoices
        $invoices = Invoice::where('customer_id', $customer->id)
            ->where('status', 'confirmed')
            ->when($dateFrom, fn($q) => $q->whereDate('invoice_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('invoice_date', '<=', $dateTo))
            ->get();

        // Fetch Payments
        $payments = Payment::where('customer_id', $customer->id)
            ->when($dateFrom, fn($q) => $q->whereDate('payment_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('payment_date', '<=', $dateTo))
            ->get();

        // Merge entries into chronological ledger
        $ledger = collect();

        foreach ($invoices as $inv) {
            $ledger->push([
                'id' => 'inv-' . $inv->id,
                'date' => $inv->invoice_date->toDateString(),
                'type' => 'invoice',
                'description' => "فاتورة مبيعات رقم #{$inv->invoice_number}",
                'debit' => (float)$inv->net_total,
                'credit' => (float)$inv->paid_amount,
                'reference_id' => $inv->id,
            ]);
        }

        foreach ($payments as $pay) {
            $ledger->push([
                'id' => 'pay-' . $pay->id,
                'date' => $pay->payment_date->toDateString(),
                'type' => 'payment',
                'description' => "سند قبض وتحصيل ({$pay->payment_method}) " . ($pay->notes ? " - {$pay->notes}" : ''),
                'debit' => 0.0,
                'credit' => (float)$pay->amount,
                'reference_id' => $pay->id,
            ]);
        }

        $sortedLedger = $ledger->sortBy('date')->values();

        // Compute running balance
        $runningBalance = 0.0;
        $processedLedger = $sortedLedger->map(function ($row) use (&$runningBalance) {
            $runningBalance += ($row['debit'] - $row['credit']);
            $row['balance'] = $runningBalance;
            return $row;
        });

        return Inertia::render('Customers/Statement', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'tax_number' => $customer->tax_number,
                'current_balance' => (float)$customer->current_balance,
            ],
            'ledger' => $processedLedger,
            'filters' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
        ]);
    }

    public function destroy(int $id)
    {
        $customer = Customer::findOrFail($id);

        if (!$customer->canBeDeleted()) {
            return redirect()->back()->with('error', 'لا يمكن حذف العميل لوجود رصيد أو فواتير مسجلة بحسابه');
        }

        DB::transaction(function () use ($customer) {
            $customer->delete();
        });

        return redirect()->back()->with('success', 'تم حذف العميل بنجاح');
    }
}
