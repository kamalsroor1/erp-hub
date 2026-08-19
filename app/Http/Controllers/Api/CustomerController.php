<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ReturnDocument;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * List / Search Customers
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all'); // all, active, inactive, with_debt

        $query = Customer::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('tax_number', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status === 'with_debt') {
            $query->where('current_balance', '>', 0);
        }

        $perPage = (int)$request->input('per_page', 25);
        $customers = $query->latest('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $customers->items(),
            'pagination' => [
                'total'        => $customers->total(),
                'current_page' => $customers->currentPage(),
                'last_page'    => $customers->lastPage(),
                'per_page'     => $customers->perPage(),
            ],
            'summary' => [
                'total_customers'  => Customer::count(),
                'total_receivable' => (string)(Customer::where('current_balance', '>', 0)->sum('current_balance') ?: '0.000'),
            ]
        ]);
    }

    /**
     * Get Customer Profile
     */
    public function show($id)
    {
        $customer = Customer::withCount(['invoices', 'payments'])->findOrFail($id);

        $totalSales = (string)($customer->invoices()->where('status', 'confirmed')->sum('net_total') ?: '0.000');
        $totalPaid = (string)($customer->payments()->sum('amount') ?: '0.000');

        return response()->json([
            'success'  => true,
            'customer' => $customer,
            'stats'    => [
                'total_sales'      => $totalSales,
                'total_paid'       => $totalPaid,
                'current_balance'  => (string)$customer->current_balance,
                'invoices_count'   => $customer->invoices_count,
                'payments_count'   => $customer->payments_count,
            ]
        ]);
    }

    /**
     * Create New Customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:50',
            'address'         => 'nullable|string|max:255',
            'tax_number'      => 'nullable|string|max:50',
            'credit_limit'    => 'nullable|numeric|min:0',
            'opening_balance' => 'nullable|numeric',
            'notes'           => 'nullable|string',
        ]);

        $openingBalance = (string)($validated['opening_balance'] ?? '0.000');

        $customer = Customer::create([
            'name'            => $validated['name'],
            'phone'           => $validated['phone'] ?? null,
            'address'         => $validated['address'] ?? null,
            'tax_number'      => $validated['tax_number'] ?? null,
            'current_balance' => $openingBalance,
            'is_active'       => true,
            'notes'           => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'تم إضافة العميل بنجاح',
            'customer' => $customer,
        ], 201);
    }

    /**
     * Update Customer
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:255',
            'tax_number'   => 'nullable|string|max:50',
            'is_active'    => 'nullable|boolean',
            'notes'        => 'nullable|string',
        ]);

        $customer->update($validated);

        return response()->json([
            'success'  => true,
            'message'  => 'تم تحديث بيانات العميل بنجاح',
            'customer' => $customer,
        ]);
    }

    /**
     * Statement of Account (كشف حساب عميل)
     */
    public function statement(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date', now()->toDateString());

        // 1. Invoices
        $invoicesQuery = Invoice::where('customer_id', $id)
            ->where('status', 'confirmed');
        if ($fromDate) {
            $invoicesQuery->whereDate('invoice_date', '>=', $fromDate);
        }
        if ($toDate) {
            $invoicesQuery->whereDate('invoice_date', '<=', $toDate);
        }
        $invoices = $invoicesQuery->latest('invoice_date')->get();

        // 2. Payments (Direct collections)
        $paymentsQuery = Payment::where('customer_id', $id);
        if ($fromDate) {
            $paymentsQuery->whereDate('payment_date', '>=', $fromDate);
        }
        if ($toDate) {
            $paymentsQuery->whereDate('payment_date', '<=', $toDate);
        }
        $payments = $paymentsQuery->latest('payment_date')->get();

        // 3. Combine chronological ledger
        $ledger = collect();

        foreach ($invoices as $inv) {
            $ledger->push([
                'id'             => $inv->id,
                'type'           => 'invoice',
                'type_label'     => 'فاتورة مبيعات',
                'document_number'=> $inv->invoice_number,
                'date'           => $inv->invoice_date,
                'debit'          => (string)$inv->net_total,
                'credit'         => (string)($inv->paid_amount ?: '0.000'),
                'payment_type'   => $inv->payment_type,
                'notes'          => $inv->notes,
                'items_count'    => $inv->items()->count(),
            ]);
        }

        foreach ($payments as $pay) {
            $ledger->push([
                'id'             => $pay->id,
                'type'           => 'payment',
                'type_label'     => 'سند قبض / تحصيل',
                'document_number'=> $pay->payment_number ?? ('PAY-' . $pay->id),
                'date'           => $pay->payment_date,
                'debit'          => '0.000',
                'credit'         => (string)$pay->amount,
                'payment_type'   => $pay->payment_type ?? 'cash',
                'notes'          => $pay->notes,
                'items_count'    => 0,
            ]);
        }

        // Sort descending by date
        $sortedLedger = $ledger->sortByDesc('date')->values();

        $totalSales = (string)($invoices->sum('net_total') ?: '0.000');
        $totalPaidDirect = (string)($payments->sum('amount') ?: '0.000');
        $totalPaidImmediate = (string)($invoices->sum('paid_amount') ?: '0.000');
        $totalCustomerPaid = bcadd($totalPaidDirect, $totalPaidImmediate, 3);

        return response()->json([
            'success'  => true,
            'customer' => [
                'id'              => $customer->id,
                'name'            => $customer->name,
                'phone'           => $customer->phone,
                'tax_number'      => $customer->tax_number,
                'current_balance' => (string)$customer->current_balance,
            ],
            'filter'   => [
                'from_date' => $fromDate,
                'to_date'   => $toDate,
            ],
            'summary'  => [
                'total_invoices_amount' => $totalSales,
                'total_paid_amount'     => $totalCustomerPaid,
                'net_balance'           => (string)$customer->current_balance,
                'transactions_count'    => $sortedLedger->count(),
            ],
            'ledger'   => $sortedLedger,
        ]);
    }

    /**
     * Delete Customer (Deactivate if has financial history, delete otherwise)
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer->invoices()->exists() || $customer->payments()->exists()) {
            $customer->update(['is_active' => false]);
            return response()->json([
                'success' => true,
                'message' => 'تم تعطيل حساب العميل نظراً لوجود حركات مالية مسجلة باسمه',
            ]);
        }

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف العميل بنجاح',
        ]);
    }
}
