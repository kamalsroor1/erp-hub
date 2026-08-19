<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    /**
     * List / Search Suppliers
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all');

        $query = Supplier::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status === 'with_balance') {
            $query->where('current_balance', '>', 0);
        }

        $perPage = (int)$request->input('per_page', 25);
        $suppliers = $query->latest('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $suppliers->items(),
            'pagination' => [
                'total'        => $suppliers->total(),
                'current_page' => $suppliers->currentPage(),
                'last_page'    => $suppliers->lastPage(),
                'per_page'     => $suppliers->perPage(),
            ],
            'summary' => [
                'total_suppliers' => Supplier::count(),
                'total_payable'   => (string)(Supplier::where('current_balance', '>', 0)->sum('current_balance') ?: '0.000'),
            ]
        ]);
    }

    /**
     * Get Supplier Profile
     */
    public function show($id)
    {
        $supplier = Supplier::withCount(['purchases', 'payments'])->findOrFail($id);

        $totalPurchases = (string)($supplier->purchases()->where('status', 'confirmed')->sum('net_total') ?: '0.000');
        $totalPaid = (string)($supplier->payments()->sum('amount') ?: '0.000');

        return response()->json([
            'success'  => true,
            'supplier' => $supplier,
            'stats'    => [
                'total_purchases'  => $totalPurchases,
                'total_paid'       => $totalPaid,
                'current_balance'  => (string)$supplier->current_balance,
                'purchases_count'  => $supplier->purchases_count,
                'payments_count'   => $supplier->payments_count,
            ]
        ]);
    }

    /**
     * Create New Supplier
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'company_name'    => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:50',
            'address'         => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric',
            'notes'           => 'nullable|string',
        ]);

        $openingBalance = (string)($validated['opening_balance'] ?? '0.000');

        $supplier = Supplier::create([
            'name'            => $validated['name'],
            'company_name'    => $validated['company_name'] ?? null,
            'phone'           => $validated['phone'] ?? null,
            'address'         => $validated['address'] ?? null,
            'current_balance' => $openingBalance,
            'is_active'       => true,
            'notes'           => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'تم إضافة المورد بنجاح',
            'supplier' => $supplier,
        ], 201);
    }

    /**
     * Update Supplier
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:255',
            'is_active'    => 'nullable|boolean',
            'notes'        => 'nullable|string',
        ]);

        $supplier->update($validated);

        return response()->json([
            'success'  => true,
            'message'  => 'تم تحديث بيانات المورد بنجاح',
            'supplier' => $supplier,
        ]);
    }

    /**
     * Statement of Account (كشف حساب مورد)
     */
    public function statement(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date', now()->toDateString());

        // 1. Purchases
        $purchasesQuery = Purchase::where('supplier_id', $id)
            ->where('status', 'confirmed');
        if ($fromDate) {
            $purchasesQuery->whereDate('purchase_date', '>=', $fromDate);
        }
        if ($toDate) {
            $purchasesQuery->whereDate('purchase_date', '<=', $toDate);
        }
        $purchases = $purchasesQuery->latest('purchase_date')->get();

        // 2. Payments to Supplier
        $paymentsQuery = Payment::where('supplier_id', $id);
        if ($fromDate) {
            $paymentsQuery->whereDate('payment_date', '>=', $fromDate);
        }
        if ($toDate) {
            $paymentsQuery->whereDate('payment_date', '<=', $toDate);
        }
        $payments = $paymentsQuery->latest('payment_date')->get();

        // 3. Ledger
        $ledger = collect();

        foreach ($purchases as $purch) {
            $ledger->push([
                'id'             => $purch->id,
                'type'           => 'purchase',
                'type_label'     => 'فاتورة مشتريات',
                'document_number'=> $purch->purchase_number,
                'date'           => $purch->purchase_date,
                'credit'         => (string)$purch->net_total,
                'debit'          => (string)($purch->paid_amount ?: '0.000'),
                'payment_type'   => $purch->payment_type ?? 'credit',
                'notes'          => $purch->notes,
                'items_count'    => $purch->items()->count(),
            ]);
        }

        foreach ($payments as $pay) {
            $ledger->push([
                'id'             => $pay->id,
                'type'           => 'payment',
                'type_label'     => 'سند صرف / سداد مورد',
                'document_number'=> $pay->payment_number ?? ('PAY-SUPP-' . $pay->id),
                'date'           => $pay->payment_date,
                'credit'         => '0.000',
                'debit'          => (string)$pay->amount,
                'payment_type'   => $pay->payment_type ?? 'cash',
                'notes'          => $pay->notes,
                'items_count'    => 0,
            ]);
        }

        $sortedLedger = $ledger->sortByDesc('date')->values();

        $totalPurchases = (string)($purchases->sum('net_total') ?: '0.000');
        $totalPaidDirect = (string)($payments->sum('amount') ?: '0.000');
        $totalPaidImmediate = (string)($purchases->sum('paid_amount') ?: '0.000');
        $totalSupplierPaid = bcadd($totalPaidDirect, $totalPaidImmediate, 3);

        return response()->json([
            'success'  => true,
            'supplier' => [
                'id'              => $supplier->id,
                'name'            => $supplier->name,
                'company_name'    => $supplier->company_name,
                'phone'           => $supplier->phone,
                'current_balance' => (string)$supplier->current_balance,
            ],
            'filter'   => [
                'from_date' => $fromDate,
                'to_date'   => $toDate,
            ],
            'summary'  => [
                'total_purchases_amount' => $totalPurchases,
                'total_paid_amount'      => $totalSupplierPaid,
                'net_balance'            => (string)$supplier->current_balance,
                'transactions_count'     => $sortedLedger->count(),
            ],
            'ledger'   => $sortedLedger,
        ]);
    }
}
