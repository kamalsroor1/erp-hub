<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Store;
use App\Services\PurchaseService;
use Exception;

class PurchaseCreate extends Component
{
    public $supplier_id;
    public $store_id;
    public $purchase_date;
    public $paid_amount = '0.000';
    public $discount_amount = '0.000';
    public $supplier_invoice_ref = '';
    public $notes = '';

    public $searchQuery = '';
    public $items = [];

    public $subtotal = '0.000';
    public $net_total = '0.000';
    public $remaining_amount = '0.000';

    public $errorMessage = '';

    protected $rules = [
        'supplier_id'   => 'required|exists:suppliers,id',
        'store_id'      => 'required|exists:stores,id',
        'purchase_date' => 'required|date',
        'items'         => 'required|array|min:1',
        'items.*.item_id'   => 'required|exists:items,id',
        'items.*.quantity'  => 'required|numeric|min:0.001',
        'items.*.cost_price'=> 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->purchase_date = now()->toDateString();
        
        $this->store_id = session('current_store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? Store::getMainStore()?->id;

        $firstSupplier = Supplier::active()->first();
        if ($firstSupplier) {
            $this->supplier_id = $firstSupplier->id;
        }
    }

    public function addItem($itemId, $quantity = '1.000')
    {
        $item = Item::active()->find($itemId);
        if (!$item) return;

        $qtyToAdd = (string) $quantity;

        foreach ($this->items as $idx => $line) {
            if ($line['item_id'] == $item->id) {
                $this->items[$idx]['quantity'] = bcadd($line['quantity'], $qtyToAdd, 3);
                $this->calculateTotals();
                $this->searchQuery = '';
                return;
            }
        }

        $this->items[] = [
            'item_id'    => $item->id,
            'code'       => $item->code,
            'name'       => $item->name,
            'unit'       => $item->unit ?: 'كجم',
            'quantity'   => $qtyToAdd,
            'cost_price' => $item->cost_price ?: '0.000',
            'total_price'=> bcmul($qtyToAdd, ($item->cost_price ?: '0.000'), 3),
        ];

        $this->calculateTotals();
        $this->searchQuery = '';
    }

    public function setLineQuantity($index, $qty)
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['quantity'] = (string) $qty;
            $this->calculateTotals();
        }
    }

    public function addLineQuantity($index, $qtyToAdd)
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['quantity'] = bcadd($this->items[$index]['quantity'], (string)$qtyToAdd, 3);
            $this->calculateTotals();
        }
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }

    public function updatedItems()
    {
        $this->calculateTotals();
    }

    public function updatedDiscountAmount()
    {
        $this->calculateTotals();
    }

    public function updatedPaidAmount()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $sub = '0.000';

        foreach ($this->items as $idx => $line) {
            $qty = $line['quantity'] ?? '1.000';
            $cost = $line['cost_price'] ?? '0.000';
            $lineTotal = bcmul($qty, $cost, 3);

            $this->items[$idx]['total_price'] = $lineTotal;
            $sub = bcadd($sub, $lineTotal, 3);
        }

        $this->subtotal = $sub;

        $disc = $this->discount_amount ?: '0.000';
        if (bccomp($disc, $this->subtotal, 3) > 0) {
            $disc = $this->subtotal;
        }

        $this->discount_amount = $disc;
        $this->net_total = bcsub($this->subtotal, $disc, 3);

        $paid = $this->paid_amount ?: '0.000';
        $rem = bcsub($this->net_total, $paid, 3);
        $this->remaining_amount = bccomp($rem, '0.000', 3) > 0 ? $rem : '0.000';
    }

    public function savePurchase(PurchaseService $purchaseService)
    {
        $this->errorMessage = '';
        $this->validate();

        try {
            $purchase = $purchaseService->createPurchase([
                'supplier_id'          => $this->supplier_id,
                'store_id'             => $this->store_id,
                'purchase_date'        => $this->purchase_date,
                'discount_amount'      => $this->discount_amount,
                'paid_amount'          => $this->paid_amount,
                'supplier_invoice_ref' => $this->supplier_invoice_ref,
                'notes'                => $this->notes,
                'items'                => $this->items,
            ]);

            session()->flash('success', "تم توريد وإضافة البضاعة للمخزن بنجاح برقم: {$purchase->purchase_number}");
            return redirect()->route('purchases.index');
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $stores = Store::active()->get();
        $searchResults = [];

        if (strlen($this->searchQuery) >= 1) {
            $searchResults = Item::active()
                ->where(function($q) {
                    $q->where('name', 'like', "%{$this->searchQuery}%")
                      ->orWhere('code', 'like', "%{$this->searchQuery}%");
                })
                ->take(8)
                ->get();
        }

        return view('livewire.purchase-create', [
            'suppliers'     => $suppliers,
            'stores'        => $stores,
            'searchResults' => $searchResults,
        ])->layout('components.layouts.app', ['title' => 'فاتورة مشتريات وتوريد مخزني']);
    }
}
