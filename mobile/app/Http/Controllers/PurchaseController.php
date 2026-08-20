<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class PurchaseController extends Controller
{
    /**
     * Display Purchases List
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'supplier_id', 'from_date', 'to_date', 'page']);
        $data = ApiService::getPurchases($filters);
        $suppliersData = ApiService::getSuppliers();
        $itemsData = ApiService::getItems();

        return Inertia::render('Purchases/Index', [
            'purchases'       => $data['purchases'] ?? [],
            'total_purchases' => $data['total_purchases'] ?? '0.000',
            'total_remaining' => $data['total_remaining'] ?? '0.000',
            'total_count'     => $data['total_count'] ?? 0,
            'suppliers'       => $suppliersData['suppliers'] ?? [],
            'items'           => $itemsData['items'] ?? [],
            'pagination'      => $data['pagination'] ?? [],
            'filters'         => $filters,
        ]);
    }

    /**
     * Show Purchase Details
     */
    public function show($id)
    {
        $data = ApiService::getPurchase((int)$id);

        return Inertia::render('Purchases/Show', [
            'purchase' => $data['purchase'] ?? null,
        ]);
    }

    /**
     * Store new Purchase Invoice
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'          => 'required|integer',
            'purchase_date'        => 'required|date',
            'paid_amount'          => 'nullable|numeric|min:0',
            'discount_amount'      => 'nullable|numeric|min:0',
            'supplier_invoice_ref' => 'nullable|string|max:100',
            'notes'                => 'nullable|string|max:1000',
            'items'                => 'required|array|min:1',
            'items.*.item_id'      => 'required|integer',
            'items.*.quantity'     => 'required|numeric|min:0.001',
            'items.*.cost_price'   => 'required|numeric|min:0',
        ]);

        $res = ApiService::createPurchase($validated);

        if (!empty($res['success'])) {
            return back()->with('success', $res['message'] ?? 'تم تسجيل وتوريد فاتورة المشتريات بنجاح ✓');
        }

        return back()->with('error', $res['message'] ?? 'فشل تسجيل فاتورة المشتريات');
    }

    /**
     * Cancel Purchase Invoice
     */
    public function cancel(Request $request, $id)
    {
        $reason = $request->input('reason', 'إلغاء من تطبيق الموبايل');
        $res = ApiService::cancelPurchase((int)$id, $reason);

        if (!empty($res['success'])) {
            return back()->with('success', $res['message'] ?? 'تم إلغاء فاتورة المشتريات وعكس المخزن بنجاح ✓');
        }

        return back()->with('error', $res['message'] ?? 'فشل إلغاء فاتورة المشتريات');
    }
}
