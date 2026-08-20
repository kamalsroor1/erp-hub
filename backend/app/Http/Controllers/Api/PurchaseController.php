<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Store;
use App\Services\PurchaseService;
use Exception;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService
    ) {}

    /**
     * List Purchases with filters and totals
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $supplierId = $request->input('supplier_id');
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
        $storeId = $request->input('store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? Store::getMainStore()?->id;

        $query = Purchase::with(['supplier', 'user', 'store'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('purchase_number', 'like', "%{$search}%")
                        ->orWhere('supplier_invoice_ref', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($supplierId && $supplierId !== 'all', fn($q) => $q->where('supplier_id', $supplierId))
            ->when($fromDate, fn($q) => $q->whereDate('purchase_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('purchase_date', '<=', $toDate));

        $totalPurchases = (clone $query)->where('status', 'confirmed')->sum('net_total') ?: 0;
        $totalRemaining = (clone $query)->where('status', 'confirmed')->sum('remaining_amount') ?: 0;
        $totalCount = (clone $query)->count();

        $purchases = $query->latest('purchase_date')->latest('id')->paginate(30);

        return response()->json([
            'success'         => true,
            'purchases'       => $purchases->items(),
            'total_purchases' => (string)$totalPurchases,
            'total_remaining' => (string)$totalRemaining,
            'total_count'     => $totalCount,
            'pagination'      => [
                'current_page' => $purchases->currentPage(),
                'last_page'    => $purchases->lastPage(),
                'total'        => $purchases->total(),
            ]
        ]);
    }

    /**
     * Show single purchase with items
     */
    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'user', 'store', 'items.item'])->findOrFail($id);

        return response()->json([
            'success'  => true,
            'purchase' => $purchase,
        ]);
    }

    /**
     * Store new purchase invoice
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'          => 'required|integer|exists:suppliers,id',
            'purchase_date'        => 'required|date',
            'paid_amount'          => 'nullable|numeric|min:0',
            'discount_amount'      => 'nullable|numeric|min:0',
            'supplier_invoice_ref' => 'nullable|string|max:100',
            'notes'                => 'nullable|string|max:1000',
            'items'                => 'required|array|min:1',
            'items.*.item_id'      => 'required|integer|exists:items,id',
            'items.*.quantity'     => 'required|numeric|min:0.001',
            'items.*.cost_price'   => 'required|numeric|min:0',
        ]);

        $storeId = $request->input('store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? Store::getMainStore()?->id;

        $validated['store_id'] = $storeId;

        try {
            $purchase = $this->purchaseService->createPurchase($validated);

            return response()->json([
                'success'  => true,
                'message'  => "تم تسجيل فاتورة المشتريات رقم {$purchase->purchase_number} وتوريد الخامات للمخزن بنجاح ✓",
                'purchase' => $purchase,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تسجيل فاتورة المشتريات: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel / Void purchase invoice
     */
    public function cancel(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);
        $reason = $request->input('reason', 'إلغاء من تطبيق الموبايل');

        try {
            $this->purchaseService->cancelPurchase($purchase, $reason);

            return response()->json([
                'success' => true,
                'message' => "تم إلغاء فاتورة المشتريات رقم {$purchase->purchase_number} وعكس المخزن والمديونية بنجاح ✓",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إلغاء فاتورة المشتريات: ' . $e->getMessage(),
            ], 422);
        }
    }
}
