<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Store;
use App\Models\StoreStock;
use App\Models\Item;
use App\Livewire\Traits\RequiresAuth;

class StoreStockIndex extends Component
{
    use RequiresAuth;

    public $selectedStoreId;
    public $searchQuery = '';
    public $lowStockOnly = false;

    // Edit Modal State
    public $showEditModal = false;
    public $editingStockId = null;
    public $editingItemName = '';
    public $editingQuantity = '0.000';
    public $editingMinStock = '0.000';
    public $editingCustomPrice = '';

    public $successMessage = '';

    public function mount()
    {
        abort_if(!auth()->user()?->can('items.view'), 403, 'غير مصرح لك بعرض أرصدة المخزون');

        $this->selectedStoreId = request()->query('store_id') 
            ?? session('current_store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? Store::getMainStore()?->id;
    }

    public function openEditModal($stockId)
    {
        abort_if(!auth()->user()?->can('items.edit'), 403, 'غير مصرح لك بتعديل إعدادات وأسعار الصنف بالفرع');
        $stock = StoreStock::with('item')->findOrFail($stockId);
        $this->editingStockId     = $stock->id;
        $this->editingItemName    = $stock->item->name;
        $this->editingQuantity    = (string)$stock->quantity;
        $this->editingMinStock    = (string)$stock->min_stock;
        $this->editingCustomPrice = $stock->custom_selling_price ? (string)$stock->custom_selling_price : '';
        $this->showEditModal      = true;
    }

    public function saveStockSettings()
    {
        abort_if(!auth()->user()?->can('items.edit'), 403, 'غير مصرح لك بتعديل إعدادات وأسعار الصنف بالفرع');
        $this->validate([
            'editingMinStock'    => 'required|numeric|min:0',
            'editingCustomPrice' => 'nullable|numeric|min:0',
        ]);

        $stock = StoreStock::findOrFail($this->editingStockId);
        $stock->update([
            'min_stock'            => $this->editingMinStock,
            'custom_selling_price' => $this->editingCustomPrice !== '' ? $this->editingCustomPrice : null,
        ]);

        $this->successMessage = "تم تحديث إعدادات وسعر صنف [{$this->editingItemName}] في هذا الفرع بنجاح.";
        $this->showEditModal = false;
    }

    public function render()
    {
        $stores = Store::active()->orderBy('is_main', 'desc')->get();
        $currentStore = Store::find($this->selectedStoreId);

        // Ensure StoreStocks exist for all active items in this store
        if ($currentStore) {
            $existingItemIds = StoreStock::where('store_id', $currentStore->id)->pluck('item_id')->toArray();
            $missingItems = Item::active()->whereNotIn('id', $existingItemIds)->get();
            foreach ($missingItems as $missing) {
                StoreStock::create([
                    'store_id'             => $currentStore->id,
                    'item_id'              => $missing->id,
                    'quantity'             => '0.000',
                    'min_stock'            => $missing->min_stock_level,
                    'custom_selling_price' => null,
                ]);
            }
        }

        $stocks = StoreStock::with('item')
            ->where('store_id', $this->selectedStoreId)
            ->whereHas('item', function ($q) {
                $q->where('is_active', true);
                if (strlen($this->searchQuery) >= 1) {
                    $q->where(function ($sub) {
                        $sub->where('name', 'like', "%{$this->searchQuery}%")
                            ->orWhere('code', 'like', "%{$this->searchQuery}%");
                    });
                }
            })
            ->when($this->lowStockOnly, fn($q) => $q->whereColumn('quantity', '<=', 'min_stock'))
            ->get();

        return view('livewire.store-stock-index', [
            'stores'       => $stores,
            'currentStore' => $currentStore,
            'stocks'       => $stocks,
        ])->layout('components.layouts.app', ['title' => 'أرصدة وأسعار الفروع وعربات التوزيع']);
    }
}
