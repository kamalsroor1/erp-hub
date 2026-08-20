<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Store;

class ItemController extends Controller
{
    /**
     * List items with stock and price for the active store
     */
    public function index(Request $request)
    {
        $search   = trim((string)$request->input('search', ''));
        $category = $request->input('category', 'all');
        $storeId  = (int)($request->header('X-Store-Id') ?: $request->input('store_id') ?: session('current_store_id') ?: 1);

        $query = Item::query()->active()->with(['storeStocks' => function ($q) use ($storeId) {
            if ($storeId) {
                $q->where('store_id', $storeId);
            }
        }]);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($category !== 'all' && !empty($category)) {
            $query->where('category', $category);
        }

        $items = $query->orderBy('name')->get()->map(function (Item $item) use ($storeId) {
            $storeStock = $item->getStockInStore($storeId);
            $effectivePrice = $item->getEffectivePriceForStore($storeId);

            return [
                'id'                => $item->id,
                'code'              => $item->code,
                'name'              => $item->name,
                'category'          => $item->category,
                'unit'              => $item->unit,
                'selling_price'     => (string)$effectivePrice,
                'cost_price'        => (string)$item->cost_price,
                'current_stock'     => (string)$storeStock,
                'total_stock'       => (string)$item->current_stock,
                'min_stock_level'   => (string)$item->min_stock_level,
                'is_low_stock'      => bccomp($storeStock, (string)$item->min_stock_level, 3) <= 0,
            ];
        });

        // Unique categories list
        $categories = Item::query()->active()->whereNotNull('category')->distinct()->pluck('category')->filter()->values();

        return response()->json([
            'success'    => true,
            'store_id'   => $storeId,
            'categories' => $categories,
            'total'      => $items->count(),
            'data'       => $items,
        ]);
    }

    /**
     * Show single item details
     */
    public function show(Request $request, $id)
    {
        $storeId = (int)($request->header('X-Store-Id') ?: $request->input('store_id') ?: session('current_store_id') ?: 1);
        $item = Item::with(['storeStocks' => function ($q) use ($storeId) {
            if ($storeId) {
                $q->where('store_id', $storeId);
            }
        }])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'              => $item->id,
                'code'            => $item->code,
                'name'            => $item->name,
                'category'        => $item->category,
                'unit'            => $item->unit,
                'selling_price'   => (string)$item->getEffectivePriceForStore($storeId),
                'cost_price'      => (string)$item->cost_price,
                'current_stock'   => (string)$item->getStockInStore($storeId),
                'total_stock'     => (string)$item->current_stock,
                'min_stock_level' => (string)$item->min_stock_level,
            ],
        ]);
    }

    /**
     * Get Low Stock Radar Items
     */
    public function lowStock(Request $request)
    {
        $storeId = (int)($request->header('X-Store-Id') ?: $request->input('store_id') ?: session('current_store_id') ?: 1);

        $items = Item::query()
            ->active()
            ->lowStock()
            ->orderBy('current_stock', 'asc')
            ->get()
            ->map(function (Item $item) use ($storeId) {
                $stock = (string)$item->getStockInStore($storeId);
                $min = (string)$item->min_stock_level;
                $deficit = bcsub($min, $stock, 3);
                if (bccomp($deficit, '0.000', 3) < 0) {
                    $deficit = '0.000';
                }

                return [
                    'id'              => $item->id,
                    'code'            => $item->code,
                    'name'            => $item->name,
                    'category'        => $item->category,
                    'unit'            => $item->unit,
                    'cost_price'      => (string)$item->cost_price,
                    'selling_price'   => (string)$item->selling_price,
                    'current_stock'   => $stock,
                    'min_stock_level' => $min,
                    'deficit'         => $deficit,
                    'suggested_reorder_qty' => bccomp($deficit, '0.000', 3) > 0 ? $deficit : '10.000',
                ];
            });

        return response()->json([
            'success'   => true,
            'count'     => $items->count(),
            'low_items' => $items,
        ]);
    }
}
