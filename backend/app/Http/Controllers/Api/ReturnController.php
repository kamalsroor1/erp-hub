<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturnDocument;
use App\Models\Store;
use App\Services\ReturnService;
use Exception;

class ReturnController extends Controller
{
    public function __construct(
        protected ReturnService $returnService
    ) {}

    /**
     * List Return Documents (Sales & Purchases)
     */
    public function index(Request $request)
    {
        $type = $request->input('type'); // 'sales_return', 'purchase_return', 'all'
        $search = $request->input('search');
        $storeId = $request->input('store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? Store::getMainStore()?->id;

        $query = ReturnDocument::with(['customer', 'supplier', 'user', 'store', 'items.item'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->when($type && $type !== 'all', fn($q) => $q->where('return_type', $type))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('return_number', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
                });
            });

        $totalReturns = (clone $query)->sum('total_amount') ?: 0;
        $totalCount = (clone $query)->count();

        $returns = $query->latest('return_date')->latest('id')->paginate(30);

        return response()->json([
            'success'       => true,
            'returns'       => $returns->items(),
            'total_returns' => (string)$totalReturns,
            'total_count'   => $totalCount,
            'pagination'    => [
                'current_page' => $returns->currentPage(),
                'last_page'    => $returns->lastPage(),
                'total'        => $returns->total(),
            ]
        ]);
    }

    /**
     * Store new Sales Return from Customer
     */
    public function storeSalesReturn(Request $request)
    {
        $validated = $request->validate([
            'customer_id'        => 'required|integer|exists:customers,id',
            'invoice_id'         => 'nullable|integer|exists:invoices,id',
            'return_date'        => 'required|date',
            'reason'             => 'nullable|string|max:255',
            'items'              => 'required|array|min:1',
            'items.*.item_id'    => 'required|integer|exists:items,id',
            'items.*.quantity'   => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $storeId = $request->input('store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? Store::getMainStore()?->id;

        $validated['store_id'] = $storeId;

        try {
            $returnDoc = $this->returnService->createSalesReturn($validated);

            return response()->json([
                'success' => true,
                'message' => "تم تسجيل مرتجع المبيعات رقم {$returnDoc->return_number} وإعادة البضاعة للمخزن وتعديل الحساب بنجاح ✓",
                'return'  => $returnDoc,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تسجيل مرتجع المبيعات: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Store new Purchase Return to Supplier
     */
    public function storePurchaseReturn(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'        => 'required|integer|exists:suppliers,id',
            'purchase_id'        => 'nullable|integer|exists:purchases,id',
            'return_date'        => 'required|date',
            'reason'             => 'nullable|string|max:255',
            'items'              => 'required|array|min:1',
            'items.*.item_id'    => 'required|integer|exists:items,id',
            'items.*.quantity'   => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $storeId = $request->input('store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? Store::getMainStore()?->id;

        $validated['store_id'] = $storeId;

        try {
            $returnDoc = $this->returnService->createPurchaseReturn($validated);

            return response()->json([
                'success' => true,
                'message' => "تم تسجيل مرتجع المشتريات رقم {$returnDoc->return_number} وخصم البضاعة من المخزن بنجاح ✓",
                'return'  => $returnDoc,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تسجيل مرتجع المشتريات: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel Return Document
     */
    public function cancel(Request $request, $id)
    {
        $returnDoc = ReturnDocument::findOrFail($id);
        $reason = $request->input('reason', 'إلغاء من تطبيق الموبايل');

        try {
            $this->returnService->cancelReturn($returnDoc, $reason);

            return response()->json([
                'success' => true,
                'message' => "تم إلغاء مستند المرتجع رقم {$returnDoc->return_number} وعكس أثره بنجاح ✓",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إلغاء المرتجع: ' . $e->getMessage(),
            ], 422);
        }
    }
}
