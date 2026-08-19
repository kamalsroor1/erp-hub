<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Payment;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class SupplierController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string)$request->input('search', ''));
        $debtStatus = $request->input('debt_status', 'all');

        $query = Supplier::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($debtStatus === 'creditor') {
            $query->where('current_balance', '>', 0);
        } elseif ($debtStatus === 'zero') {
            $query->where('current_balance', '=', 0);
        }

        $suppliers = $query->latest('id')->paginate(20)->withQueryString();

        $totalPayable = (float)Supplier::where('current_balance', '>', 0)->sum('current_balance');
        $creditorsCount = Supplier::where('current_balance', '>', 0)->count();
        $totalSuppliersCount = Supplier::count();

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers->through(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'company_name' => $s->company_name,
                'phone' => $s->phone,
                'address' => $s->address,
                'current_balance' => (float)$s->current_balance,
                'is_active' => (bool)$s->is_active,
                'notes' => $s->notes,
                'can_be_deleted' => $s->canBeDeleted(),
                'deletion_blockers' => $s->getDeletionBlockers(),
            ]),
            'metrics' => [
                'total_payable' => $totalPayable,
                'creditors_count' => $creditorsCount,
                'total_suppliers' => $totalSuppliersCount,
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
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated) {
            Supplier::create([
                'name' => $validated['name'],
                'company_name' => $validated['company_name'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'current_balance' => $validated['opening_balance'] ?? '0.000',
                'is_active' => true,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->back()->with('success', 'تم إضافة المورد بنجاح');
    }

    public function update(Request $request, int $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($supplier, $validated) {
            $supplier->update($validated);
        });

        return redirect()->back()->with('success', 'تم تعديل بيانات المورد بنجاح');
    }

    public function pay(Request $request, int $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,instapay,wallet,bank',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($supplier, $validated) {
            Payment::create([
                'supplier_id' => $supplier->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'user_id' => auth()->id(),
                'notes' => $validated['notes'] ?? 'سداد دفعة نقدية للمورد',
            ]);

            // Deduct from supplier balance
            $supplier->current_balance = bcsub((string)$supplier->current_balance, (string)$validated['amount'], 3);
            $supplier->save();
        });

        return redirect()->back()->with('success', 'تم تسجيل سند الصرف وسداد الدفعة بنجاح');
    }

    public function destroy(int $id)
    {
        $supplier = Supplier::findOrFail($id);

        if (!$supplier->canBeDeleted()) {
            return redirect()->back()->with('error', 'لا يمكن حذف المورد لوجود فواتير أو مستحقات مالية مسجلة');
        }

        DB::transaction(function () use ($supplier) {
            $supplier->delete();
        });

        return redirect()->back()->with('success', 'تم حذف المورد بنجاح');
    }
}