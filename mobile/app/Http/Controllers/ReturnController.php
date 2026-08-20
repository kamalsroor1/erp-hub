<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class ReturnController extends Controller
{
    /**
     * Display Returns list (Sales & Purchases)
     */
    public function index(Request $request)
    {
        $filters = $request->only(['type', 'search', 'page']);
        $data = ApiService::getReturns($filters);
        $customersData = ApiService::getCustomers();
        $suppliersData = ApiService::getSuppliers();
        $itemsData = ApiService::getItems();

        return Inertia::render('Returns/Index', [
            'returns'       => $data['returns'] ?? [],
            'total_returns' => $data['total_returns'] ?? '0.000',
            'total_count'   => $data['total_count'] ?? 0,
            'customers'     => $customersData['customers'] ?? [],
            'suppliers'     => $suppliersData['suppliers'] ?? [],
            'items'         => $itemsData['items'] ?? [],
            'pagination'    => $data['pagination'] ?? [],
            'filters'       => $filters,
        ]);
    }

    /**
     * Store Sales Return from Customer
     */
    public function storeSales(Request $request)
    {
        $validated = $request->validate([
            'customer_id'        => 'required|integer',
            'invoice_id'         => 'nullable|integer',
            'return_date'        => 'required|date',
            'reason'             => 'nullable|string|max:255',
            'items'              => 'required|array|min:1',
            'items.*.item_id'    => 'required|integer',
            'items.*.quantity'   => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $res = ApiService::createSalesReturn($validated);

        if (!empty($res['success'])) {
            return back()->with('success', $res['message'] ?? 'تم تسجيل مرتجع المبيعات بنجاح ✓');
        }

        return back()->with('error', $res['message'] ?? 'فشل تسجيل مرتجع المبيعات');
    }

    /**
     * Store Purchase Return to Supplier
     */
    public function storePurchases(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'        => 'required|integer',
            'purchase_id'        => 'nullable|integer',
            'return_date'        => 'required|date',
            'reason'             => 'nullable|string|max:255',
            'items'              => 'required|array|min:1',
            'items.*.item_id'    => 'required|integer',
            'items.*.quantity'   => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $res = ApiService::createPurchaseReturn($validated);

        if (!empty($res['success'])) {
            return back()->with('success', $res['message'] ?? 'تم تسجيل مرتجع المشتريات بنجاح ✓');
        }

        return back()->with('error', $res['message'] ?? 'فشل تسجيل مرتجع المشتريات');
    }

    /**
     * Cancel Return Document
     */
    public function cancel(Request $request, $id)
    {
        $reason = $request->input('reason', 'إلغاء من تطبيق الموبايل');
        $res = ApiService::cancelReturn((int)$id, $reason);

        if (!empty($res['success'])) {
            return back()->with('success', $res['message'] ?? 'تم إلغاء المرتجع بنجاح ✓');
        }

        return back()->with('error', $res['message'] ?? 'فشل إلغاء المرتجع');
    }
}
