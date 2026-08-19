<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreStock;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class StoreController extends Controller
{
    public function index(): Response
    {
        $stores = Store::withCount('stocks')->latest('id')->get();

        return Inertia::render('Stores/Index', [
            'stores' => $stores->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->code,
                'type' => $s->type, // branch, warehouse, van
                'address' => $s->address,
                'phone' => $s->phone,
                'is_active' => (bool)$s->is_active,
                'is_main' => (bool)$s->is_main,
                'stocks_count' => $s->stocks_count,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:stores,code',
            'type' => 'required|string|in:branch,warehouse,van',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($validated) {
            Store::create([
                'name' => $validated['name'],
                'code' => $validated['code'] ?? strtoupper(substr($validated['type'], 0, 3)) . '-' . rand(100, 999),
                'type' => $validated['type'],
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'is_active' => true,
                'is_main' => false,
            ]);
        });

        return redirect()->back()->with('success', 'تم إضافة الفرع / المخزن بنجاح');
    }

    public function update(Request $request, int $id)
    {
        $store = Store::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:stores,code,' . $store->id,
            'type' => 'required|string|in:branch,warehouse,van',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($store, $validated) {
            $store->update($validated);
        });

        return redirect()->back()->with('success', 'تم تعديل بيانات المخزن بنجاح');
    }

    public function stocks(Request $request): Response
    {
        $storeId = $request->input('store_id');
        $search = trim((string)$request->input('search', ''));
        $stockStatus = $request->input('stock_status', 'all');

        $stores = Store::where('is_active', true)->select('id', 'name', 'type')->get();
        $selectedStoreId = $storeId ? (int)$storeId : ($stores->first()?->id ?? 1);

        $query = StoreStock::with('item')
            ->where('store_id', $selectedStoreId);

        if ($search !== '') {
            $query->whereHas('item', fn($iq) => $iq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
        }

        if ($stockStatus === 'low') {
            $query->whereHas('item', fn($iq) => $iq->whereColumn('store_stocks.quantity', '<=', 'items.min_stock_level'));
        } elseif ($stockStatus === 'out') {
            $query->where('quantity', '<=', 0);
        }

        $stocks = $query->paginate(20)->withQueryString();

        return Inertia::render('Stores/Stocks', [
            'stores' => $stores,
            'selected_store_id' => $selectedStoreId,
            'stocks' => $stocks->through(fn($st) => [
                'id' => $st->id,
                'item_name' => $st->item?->name,
                'item_code' => $st->item?->code,
                'unit' => $st->item?->unit,
                'quantity' => (float)$st->quantity,
                'min_stock_level' => (float)$st->item?->min_stock_level,
                'cost_price' => (float)$st->item?->cost_price,
                'total_valuation' => (float)($st->quantity * ($st->item?->cost_price ?? 0)),
            ]),
            'filters' => [
                'store_id' => $selectedStoreId,
                'search' => $search,
                'stock_status' => $stockStatus,
            ],
        ]);
    }
}