<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Store;
use App\Services\ReturnService;
use Exception;

class ReturnCreate extends Component
{
    public $return_type = 'sales_return'; // sales_return, purchase_return
    public $customer_id;
    public $supplier_id;
    public $store_id;
    public $return_date;
    public $reason = '';
    public $searchQuery = '';
    public $items = [];

    public $errorMessage = '';

    protected $rules = [
        'store_id'         => 'required|exists:stores,id',
        'return_date'      => 'required|date',
        'items'            => 'required|array|min:1',
        'items.*.item_id'  => 'required|exists:items,id',
        'items.*.quantity' => 'required|numeric|min:0.001',
        'items.*.unit_price' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        abort_if(!auth()->user()?->can('returns.manage'), 403, 'غير مصرح لك بتسجيل مرتجعات');
        $this->return_date = now()->toDateString();
        
        $this->store_id = session('current_store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? Store::getMainStore()?->id;

        $firstCust = Customer::active()->first();
        if ($firstCust) $this->customer_id = $firstCust->id;

        $firstSupp = Supplier::active()->first();
        if ($firstSupp) $this->supplier_id = $firstSupp->id;
    }

    public function addItem($itemId)
    {
        $item = Item::active()->find($itemId);
        if (!$item) return;

        foreach ($this->items as $idx => $line) {
            if ($line['item_id'] == $item->id) {
                $this->items[$idx]['quantity'] = bcadd($line['quantity'], '1.000', 3);
                $this->calculateTotals();
                $this->searchQuery = '';
                return;
            }
        }

        $defaultPrice = $this->return_type === 'sales_return' ? $item->selling_price : $item->cost_price;

        $this->items[] = [
            'item_id'    => $item->id,
            'code'       => $item->code,
            'name'       => $item->name,
            'unit'       => $item->unit ?: 'كجم',
            'quantity'   => '1.000',
            'unit_price' => $defaultPrice,
            'total_price'=> $defaultPrice,
        ];

        $this->calculateTotals();
        $this->searchQuery = '';
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function calculateTotals()
    {
        foreach ($this->items as $idx => $line) {
            $qty = $line['quantity'] ?? '1.000';
            $price = $line['unit_price'] ?? '0.000';
            $this->items[$idx]['total_price'] = bcmul($qty, $price, 3);
        }
    }

    public function saveReturn(ReturnService $returnService)
    {
        abort_if(!auth()->user()?->can('returns.manage'), 403, 'غير مصرح لك بتسجيل المرتجعات');
        $this->errorMessage = '';
        $this->validate();

        try {
            if ($this->return_type === 'sales_return') {
                $returnDoc = $returnService->createSalesReturn([
                    'customer_id' => $this->customer_id,
                    'store_id'    => $this->store_id,
                    'return_date' => $this->return_date,
                    'reason'      => $this->reason ?: 'مرتجع مبيعات من العميل',
                    'items'       => $this->items,
                ]);
            } else {
                $returnDoc = $returnService->createPurchaseReturn([
                    'supplier_id' => $this->supplier_id,
                    'store_id'    => $this->store_id,
                    'return_date' => $this->return_date,
                    'reason'      => $this->reason ?: 'مرتجع مشتريات للمورد',
                    'items'       => $this->items,
                ]);
            }

            session()->flash('success', "تم تسجيل مستند المرتجع رقم {$returnDoc->return_number} وتحديث المخزون بنجاح.");
            $this->dispatch('swal:toast', ['icon' => 'success', 'title' => "تم تسجيل مستند المرتجع رقم {$returnDoc->return_number} بنجاح!"]);
            return redirect()->route('returns.index');
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $customers = Customer::active()->orderBy('name')->get();
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

        return view('livewire.return-create', [
            'customers'     => $customers,
            'suppliers'     => $suppliers,
            'stores'        => $stores,
            'searchResults' => $searchResults,
        ])->layout('components.layouts.app', ['title' => 'تسجيل مرتجع مبيعات / مشتريات']);
    }
}
