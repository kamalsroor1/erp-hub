<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Store;
use App\Models\Item;
use App\Services\StockTransferService;
use App\Livewire\Traits\RequiresAuth;
use Exception;

class StockTransferCreate extends Component
{
    use RequiresAuth;

    public $from_store_id;
    public $to_store_id;
    public $transfer_date;
    public $notes;
    public $items = [];

    public $searchQuery = '';
    public $errorMessage = '';

    protected $rules = [
        'from_store_id'     => 'required|different:to_store_id|exists:stores,id',
        'to_store_id'       => 'required|different:from_store_id|exists:stores,id',
        'transfer_date'     => 'required|date',
        'items'             => 'required|array|min:1',
        'items.*.item_id'   => 'required|exists:items,id',
        'items.*.quantity'  => 'required|numeric|min:0.001',
    ];

    public function mount()
    {
        abort_if(!auth()->user()?->can('transfers.create'), 403, 'غير مصرح لك بإنشاء أذونات تحويل وشحن البضاعة');
        $this->transfer_date = now()->toDateString();

        $mainStore = Store::getMainStore();
        $this->from_store_id = $mainStore ? $mainStore->id : Store::first()?->id;

        $targetStore = request()->query('to_store_id');
        if ($targetStore && (int)$targetStore !== (int)$this->from_store_id) {
            $this->to_store_id = (int)$targetStore;
        } else {
            $otherStore = Store::where('id', '!=', $this->from_store_id)->first();
            $this->to_store_id = $otherStore ? $otherStore->id : null;
        }
    }

    public function addItem($itemId, $quantity = '1.000')
    {
        $item = Item::active()->find($itemId);
        if (!$item) return;

        $qtyToAdd = (string)$quantity;

        // Check if item already exists in lines
        foreach ($this->items as $idx => $line) {
            if ($line['item_id'] == $item->id) {
                $this->items[$idx]['quantity'] = bcadd((string)$line['quantity'], $qtyToAdd, 3);
                $this->searchQuery = '';
                return;
            }
        }

        $availableStock = $item->getStockInStore($this->from_store_id);

        $this->items[] = [
            'item_id'         => $item->id,
            'code'            => $item->code,
            'name'            => $item->name,
            'unit'            => $item->unit ?: 'كجم',
            'available_stock' => $availableStock,
            'quantity'        => $qtyToAdd,
        ];

        $this->searchQuery = '';
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedFromStoreId()
    {
        // Refresh available stock indicators
        foreach ($this->items as $idx => $line) {
            $item = Item::find($line['item_id']);
            if ($item) {
                $this->items[$idx]['available_stock'] = $item->getStockInStore($this->from_store_id);
            }
        }
    }

    public function saveTransfer(StockTransferService $transferService)
    {
        abort_if(!auth()->user()?->can('transfers.create'), 403, 'غير مصرح لك بإنشاء أذونات تحويل وشحن البضاعة');
        $this->errorMessage = '';
        $this->validate();

        try {
            $transfer = $transferService->createTransfer([
                'from_store_id' => $this->from_store_id,
                'to_store_id'   => $this->to_store_id,
                'transfer_date' => $this->transfer_date,
                'notes'         => $this->notes,
                'items'         => $this->items,
            ]);

            session()->flash('success', "تم إنشاء واعتماد إذن التحويل والشحن بنجاح برقم: {$transfer->transfer_number}");
            return redirect()->route('stock-transfers');
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        $stores = Store::active()->get();

        $quickCatalog = Item::active()
            ->when(strlen($this->searchQuery) >= 1, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->searchQuery}%")
                        ->orWhere('code', 'like', "%{$this->searchQuery}%");
                });
            })
            ->take(12)
            ->get();

        return view('livewire.stock-transfer-create', [
            'stores'       => $stores,
            'quickCatalog' => $quickCatalog,
        ])->layout('components.layouts.app', ['title' => 'إنشاء إذن شحن وتحويل بضاعة']);
    }
}
