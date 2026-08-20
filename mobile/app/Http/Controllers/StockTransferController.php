<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class StockTransferController extends Controller
{
    /**
     * Display Stock Transfers List
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'page']);
        $data = ApiService::getTransfers($filters);
        $itemsData = ApiService::getItems();
        $stores = session('accessible_stores', []);
        if (empty($stores)) {
            $settingsData = ApiService::getSettings();
            $stores = $settingsData['stores'] ?? [];
        }

        return Inertia::render('Transfers/Index', [
            'transfers'   => $data['transfers'] ?? [],
            'total_count' => $data['total_count'] ?? 0,
            'items'       => $itemsData['items'] ?? [],
            'stores'      => $stores,
            'pagination'  => $data['pagination'] ?? [],
            'filters'     => $filters,
        ]);
    }

    /**
     * Store new Stock Transfer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_store_id'     => 'required|integer',
            'to_store_id'       => 'required|integer|different:from_store_id',
            'transfer_date'     => 'required|date',
            'notes'             => 'nullable|string|max:1000',
            'items'             => 'required|array|min:1',
            'items.*.item_id'   => 'required|integer',
            'items.*.quantity'  => 'required|numeric|min:0.001',
        ]);

        $res = ApiService::createTransfer($validated);

        if (!empty($res['success'])) {
            return back()->with('success', $res['message'] ?? 'تم تسجيل التحويل المخزني بنجاح ✓');
        }

        return back()->with('error', $res['message'] ?? 'فشل إجراء التحويل المخزني');
    }

    /**
     * Cancel / Reverse Stock Transfer
     */
    public function cancel(Request $request, $id)
    {
        $reason = $request->input('reason', 'إلغاء من تطبيق الموبايل');
        $res = ApiService::cancelTransfer((int)$id, $reason);

        if (!empty($res['success'])) {
            return back()->with('success', $res['message'] ?? 'تم إلغاء التحويل المخزني بنجاح ✓');
        }

        return back()->with('error', $res['message'] ?? 'فشل إلغاء التحويل المخزني');
    }
}
