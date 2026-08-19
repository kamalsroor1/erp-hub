<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockTransfer;
use App\Models\Store;
use App\Services\StockTransferService;
use Exception;

class StockTransferController extends Controller
{
    public function __construct(
        protected StockTransferService $transferService
    ) {}

    /**
     * List Stock Transfers between stores
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $storeId = $request->input('store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? Store::getMainStore()?->id;

        $query = StockTransfer::with(['fromStore', 'toStore', 'user', 'items.item'])
            ->when($storeId, function ($q) use ($storeId) {
                $q->where(function ($sub) use ($storeId) {
                    $sub->where('from_store_id', $storeId)
                        ->orWhere('to_store_id', $storeId);
                });
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('transfer_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            });

        $totalCount = (clone $query)->count();
        $transfers = $query->latest('transfer_date')->latest('id')->paginate(30);

        return response()->json([
            'success'     => true,
            'transfers'   => $transfers->items(),
            'total_count' => $totalCount,
            'pagination'  => [
                'current_page' => $transfers->currentPage(),
                'last_page'    => $transfers->lastPage(),
                'total'        => $transfers->total(),
            ]
        ]);
    }

    /**
     * Show single Stock Transfer
     */
    public function show($id)
    {
        $transfer = StockTransfer::with(['fromStore', 'toStore', 'user', 'items.item'])->findOrFail($id);

        return response()->json([
            'success'  => true,
            'transfer' => $transfer,
        ]);
    }

    /**
     * Store new Stock Transfer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_store_id'     => 'required|integer|exists:stores,id',
            'to_store_id'       => 'required|integer|exists:stores,id|different:from_store_id',
            'transfer_date'     => 'required|date',
            'notes'             => 'nullable|string|max:1000',
            'items'             => 'required|array|min:1',
            'items.*.item_id'   => 'required|integer|exists:items,id',
            'items.*.quantity'  => 'required|numeric|min:0.001',
        ]);

        try {
            $transfer = $this->transferService->createTransfer($validated);

            return response()->json([
                'success'  => true,
                'message'  => "تم تسجيل إذن التحويل المخزني رقم {$transfer->transfer_number} ونقل البضاعة فوراً بنجاح ✓",
                'transfer' => $transfer,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل التحويل المخزني: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel / Reverse Stock Transfer
     */
    public function cancel(Request $request, $id)
    {
        $transfer = StockTransfer::findOrFail($id);
        $reason = $request->input('reason', 'إلغاء من تطبيق الموبايل');

        try {
            $this->transferService->cancelTransfer($transfer, $reason);

            return response()->json([
                'success' => true,
                'message' => "تم إلغاء إذن التحويل المخزني رقم {$transfer->transfer_number} وإعادة الرصيد للفرع المصدر بنجاح ✓",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إلغاء التحويل المخزني: ' . $e->getMessage(),
            ], 422);
        }
    }
}
