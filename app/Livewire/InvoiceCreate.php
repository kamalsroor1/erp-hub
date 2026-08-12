<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Store;
use App\Services\InvoiceService;
use App\Services\CustomerPricingHelper;
use App\Livewire\Traits\RequiresAuth;
use Exception;

class InvoiceCreate extends Component
{
    use RequiresAuth;

    public $customer_id;
    public $store_id;
    public $invoice_date;
    public $payment_type = 'cash'; // cash, credit, partial
    public $discount_type = 'fixed'; // fixed, percentage
    public $discount_value = '0.000';
    public $paid_amount = '0.000';
    public $notes;

    // Search and Quick Add
    public $searchQuery = '';
    public $selectedCategory = 'all';
    public $items = [];

    // Summary calculations
    public $subtotal = '0.000';
    public $discount_amount = '0.000';
    public $net_total = '0.000';
    public $remaining_amount = '0.000';

    public $errorMessage = '';

    protected $rules = [
        'customer_id'    => 'required|exists:customers,id',
        'store_id'       => 'required|exists:stores,id',
        'invoice_date'   => 'required|date',
        'payment_type'   => 'required|in:cash,credit,partial',
        'items'          => 'required|array|min:1',
        'items.*.item_id'   => 'required|exists:items,id',
        'items.*.quantity'  => 'required|numeric|min:0.001',
        'items.*.unit_price'=> 'required|numeric|min:0',
    ];

    public function mount()
    {
        abort_if(!auth()->user()?->can('pos.access'), 403, 'غير مصرح لك بدخول شاشة نقطة البيع (POS)');

        $this->invoice_date = now()->toDateString();
        
        $this->store_id = session('current_store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? Store::getMainStore()?->id;

        $firstCustomer = Customer::active()->first();
        if ($firstCustomer) {
            $this->customer_id = $firstCustomer->id;
        }
    }

    public function updatedCustomerId()
    {
        $pricingHelper = app(CustomerPricingHelper::class);
        foreach ($this->items as $idx => $line) {
            $this->items[$idx]['last_customer_price'] = $this->customer_id
                ? $pricingHelper->getLastSoldPrice($this->customer_id, $line['item_id'], $this->store_id)
                : null;
        }
    }

    public function updatedStoreId()
    {
        // Re-evaluate stock and custom prices for the selected store
        foreach ($this->items as $idx => $line) {
            $item = Item::find($line['item_id']);
            if ($item) {
                $this->items[$idx]['current_stock'] = $item->getStockInStore($this->store_id);
            }
        }
        $this->updatedCustomerId();
    }

    public function addItem($itemId, $quantity = '1.000')
    {
        $item = Item::active()->find($itemId);
        if (!$item) return;

        $qtyToAdd = (string) $quantity;
        $currentStock = (string) $item->getStockInStore($this->store_id);

        // 🛑 Check 1: Stock is zero or negative
        if (bccomp($currentStock, '0.000', 3) <= 0) {
            $msg = "عفواً، الصنف ({$item->name}) غير متوفر حالياً بالمخزن/الفرع المحدد (الرصيد المتاح: 0).";
            $this->errorMessage = $msg;
            $this->dispatch('swal:toast', [
                'icon'  => 'error',
                'title' => $msg,
            ]);
            $this->dispatch('swal:alert', [
                'icon'    => 'error',
                'title'   => 'نفاد الكمية بالمخزن!',
                'message' => $msg,
            ]);
            return;
        }

        // Check if item already in lines
        foreach ($this->items as $index => $line) {
            if ($line['item_id'] == $item->id) {
                $newQty = bcadd($line['quantity'], $qtyToAdd, 3);
                // 🛑 Check 2: Total requested quantity exceeds available stock
                if (bccomp($newQty, $currentStock, 3) > 0) {
                    $msg = "عفواً، الكمية الإجمالية المطلوب بيعها ({$newQty}) تتجاوز الرصيد المتاح بالمخزن ({$currentStock}) للصنف: {$item->name}";
                    $this->errorMessage = $msg;
                    $this->dispatch('swal:toast', [
                        'icon'  => 'error',
                        'title' => $msg,
                    ]);
                    $this->dispatch('swal:alert', [
                        'icon'    => 'error',
                        'title'   => 'تجاوز رصيد المخزن!',
                        'message' => $msg,
                    ]);
                    return;
                }
                $this->items[$index]['quantity'] = $newQty;
                $this->items[$index]['total_price'] = bcmul($newQty, $this->items[$index]['unit_price'], 3);
                $this->calculateTotals();
                $this->searchQuery = '';
                return;
            }
        }

        // 🛑 Check 3: Initial requested quantity exceeds current stock
        if (bccomp($qtyToAdd, $currentStock, 3) > 0) {
            $msg = "عفواً، الكمية المطلوبة ({$qtyToAdd}) تتجاوز الرصيد المتاح بالمخزن ({$currentStock}) للصنف: {$item->name}";
            $this->errorMessage = $msg;
            $this->dispatch('swal:toast', [
                'icon'  => 'error',
                'title' => $msg,
            ]);
            $this->dispatch('swal:alert', [
                'icon'    => 'error',
                'title'   => 'تجاوز رصيد المخزن!',
                'message' => $msg,
            ]);
            return;
        }

        $effectivePrice = $item->getEffectivePriceForStore($this->store_id);
        $lastCustomerPrice = $this->customer_id 
            ? app(CustomerPricingHelper::class)->getLastSoldPrice($this->customer_id, $item->id, $this->store_id)
            : null;

        $this->items[] = [
            'item_id'             => $item->id,
            'code'                => $item->code,
            'name'                => $item->name,
            'category'            => $item->category,
            'unit'                => $item->unit ?: 'كجم',
            'current_stock'       => $currentStock,
            'quantity'            => $qtyToAdd,
            'unit_price'          => $effectivePrice,
            'discount_amount'     => '0.000',
            'total_price'         => bcmul($qtyToAdd, $effectivePrice, 3),
            'last_customer_price' => $lastCustomerPrice,
        ];

        $this->calculateTotals();
        $this->searchQuery = '';
    }

    public function applyCustomerLastPrice($index)
    {
        if (isset($this->items[$index]) && !empty($this->items[$index]['last_customer_price'])) {
            $this->items[$index]['unit_price'] = $this->items[$index]['last_customer_price']['unit_price'];
            $this->calculateTotals();
        }
    }

    public function setLineWeightPreset($index, $weight)
    {
        if (isset($this->items[$index])) {
            $line = $this->items[$index];
            $item = Item::find($line['item_id']);
            $currentStock = $item ? (string)$item->getStockInStore($this->store_id) : '0.000';
            $reqWeight = (string) $weight;

            if (bccomp($reqWeight, $currentStock, 3) > 0) {
                $msg = "عفواً، الوزن المطلوب ({$reqWeight}) يتجاوز الرصيد المتاح بالمخزن ({$currentStock}) للصنف: {$line['name']}";
                $this->errorMessage = $msg;
                $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $msg]);
                $this->dispatch('swal:alert', ['icon' => 'error', 'title' => 'تجاوز رصيد المخزن!', 'message' => $msg]);
                return;
            }

            $this->items[$index]['quantity'] = $reqWeight;
            $this->items[$index]['total_price'] = bcmul($reqWeight, $this->items[$index]['unit_price'], 3);
            $this->calculateTotals();
        }
    }

    public function setLineGrams($index, $grams)
    {
        if (isset($this->items[$index])) {
            $line = $this->items[$index];
            $item = Item::find($line['item_id']);
            $currentStock = $item ? (string)$item->getStockInStore($this->store_id) : '0.000';
            $kg = bcdiv((string)$grams, '1000', 4);

            if (bccomp($kg, $currentStock, 3) > 0) {
                $msg = "عفواً، الوزن المطلوب ({$kg} كجم) يتجاوز الرصيد المتاح بالمخزن ({$currentStock}) للصنف: {$line['name']}";
                $this->errorMessage = $msg;
                $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $msg]);
                $this->dispatch('swal:alert', ['icon' => 'error', 'title' => 'تجاوز رصيد المخزن!', 'message' => $msg]);
                return;
            }

            $this->items[$index]['quantity'] = $kg;
            $this->items[$index]['total_price'] = bcmul($kg, $this->items[$index]['unit_price'], 3);
            $this->calculateTotals();
        }
    }

    public function addLineWeightPreset($index, $weightToAdd)
    {
        if (isset($this->items[$index])) {
            $line = $this->items[$index];
            $item = Item::find($line['item_id']);
            $currentStock = $item ? (string)$item->getStockInStore($this->store_id) : '0.000';
            $newQty = bcadd($line['quantity'], (string)$weightToAdd, 3);

            if (bccomp($newQty, $currentStock, 3) > 0) {
                $msg = "عفواً، الوزن المطلوب ({$newQty}) يتجاوز الرصيد المتاح بالمخزن ({$currentStock}) للصنف: {$line['name']}";
                $this->errorMessage = $msg;
                $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $msg]);
                $this->dispatch('swal:alert', ['icon' => 'error', 'title' => 'تجاوز رصيد المخزن!', 'message' => $msg]);
                return;
            }

            $this->items[$index]['quantity'] = $newQty;
            $this->items[$index]['total_price'] = bcmul($newQty, $this->items[$index]['unit_price'], 3);
            $this->calculateTotals();
        }
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }

    public function updatedItems($value, $key)
    {
        if (str_contains($key, 'quantity')) {
            $parts = explode('.', $key);
            $idx = $parts[0] ?? null;
            if ($idx !== null && isset($this->items[$idx])) {
                $line = $this->items[$idx];
                $item = Item::find($line['item_id']);
                $currentStock = $item ? (string)$item->getStockInStore($this->store_id) : '0.000';
                $requestedQty = (string)($line['quantity'] ?: '0');

                if (bccomp($requestedQty, $currentStock, 3) > 0) {
                    $msg = "عفواً، الكمية المطلوبة ({$requestedQty}) تتجاوز الرصيد المتاح بالمخزن ({$currentStock}) للصنف: {$line['name']}";
                    $this->errorMessage = $msg;
                    $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $msg]);
                    $this->dispatch('swal:alert', ['icon' => 'error', 'title' => 'تجاوز رصيد المخزن!', 'message' => $msg]);
                    $this->items[$idx]['quantity'] = $currentStock;
                }
            }
        }
        $this->calculateTotals();
    }

    public function updatedDiscountType()
    {
        $this->calculateTotals();
    }

    public function updatedDiscountValue()
    {
        $this->calculateTotals();
    }

    public function updatedPaymentType()
    {
        if ($this->payment_type === 'cash') {
            $this->paid_amount = $this->net_total;
        } elseif ($this->payment_type === 'credit') {
            $this->paid_amount = '0.000';
        }
        $this->calculateTotals();
    }

    public function updatedPaidAmount()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $subtotal = '0.000';
        foreach ($this->items as $index => $line) {
            $qty = (string)($line['quantity'] ?: '0');
            $unitPrice = (string)($line['unit_price'] ?: '0');
            $lineDiscount = (string)($line['discount_amount'] ?? '0.000');

            $lineGross = bcmul($qty, $unitPrice, 3);
            $lineTotal = bcsub($lineGross, $lineDiscount, 3);
            $lineTotal = bccomp($lineTotal, '0.000', 3) > 0 ? $lineTotal : '0.000';

            $this->items[$index]['total_price'] = $lineTotal;
            $subtotal = bcadd($subtotal, $lineTotal, 3);
        }

        $this->subtotal = $subtotal;

        $discountVal = (string)($this->discount_value ?: '0.000');
        if ($this->discount_type === 'percentage') {
            $percAmount = bcmul($subtotal, bcdiv($discountVal, '100', 6), 3);
            $this->discount_amount = bccomp($percAmount, $subtotal, 3) > 0 ? $subtotal : $percAmount;
        } else {
            $this->discount_amount = bccomp($discountVal, $subtotal, 3) > 0 ? $subtotal : $discountVal;
        }

        $net = bcsub($subtotal, $this->discount_amount, 3);
        $this->net_total = bccomp($net, '0.000', 3) > 0 ? $net : '0.000';

        if ($this->payment_type === 'cash') {
            $this->paid_amount = $this->net_total;
            $this->remaining_amount = '0.000';
        } elseif ($this->payment_type === 'credit') {
            $this->paid_amount = '0.000';
            $this->remaining_amount = $this->net_total;
        } else {
            $paid = $this->paid_amount ?: '0.000';
            $rem = bcsub($this->net_total, $paid, 3);
            $this->remaining_amount = bccomp($rem, '0.000', 3) > 0 ? $rem : '0.000';
        }
    }

    public function saveInvoice(InvoiceService $invoiceService, $printMode = null)
    {
        abort_if(!auth()->user()?->can('invoices.create'), 403, 'غير مصرح لك بإنشاء واعتماد فواتير المبيعات');

        if (bccomp((string)($this->discount_value ?: '0.000'), '0.000', 3) > 0) {
            abort_if(!auth()->user()?->can('invoices.discount'), 403, 'غير مصرح لك بمنح خصومات على الفواتير');
        }

        $this->errorMessage = '';
        $this->validate();

        // 🛑 Strict validation before saving
        foreach ($this->items as $line) {
            $item = Item::find($line['item_id']);
            if ($item) {
                $avail = (string)$item->getStockInStore($this->store_id);
                if (bccomp($line['quantity'], $avail, 3) > 0) {
                    $msg = "عفواً، رصيد الصنف ({$item->name}) غير كافٍ لإنهاء الفاتورة (المطلوب: {$line['quantity']} - المتاح: {$avail}).";
                    $this->errorMessage = $msg;
                    $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $msg]);
                    $this->dispatch('swal:alert', ['icon' => 'error', 'title' => 'فشل حفظ الفاتورة!', 'message' => $msg]);
                    return;
                }
            }
        }

        try {
            $invoice = $invoiceService->confirmInvoice([
                'customer_id'    => $this->customer_id,
                'store_id'       => $this->store_id,
                'invoice_date'   => $this->invoice_date,
                'payment_type'   => $this->payment_type,
                'discount_type'  => $this->discount_type,
                'discount_value' => $this->discount_value,
                'paid_amount'    => $this->paid_amount,
                'notes'          => $this->notes,
                'items'          => $this->items,
            ]);

            if ($printMode === 'print' || $printMode === 'a4' || $printMode === 'thermal') {
                return redirect()->route('invoices.print.a4', $invoice->id);
            }

            session()->flash('success', "تم حفظ واعتماد فاتورة المبيعات بنجاح برقم: {$invoice->invoice_number}");
            return redirect()->route('invoices.show', $invoice->id);
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $customers = Customer::active()->orderBy('name')->get();
        $stores = Store::active()->orderBy('is_main', 'desc')->get();

        $quickCatalog = Item::active()
            ->when($this->selectedCategory !== 'all', fn($q) => $q->where('category', $this->selectedCategory))
            ->when(strlen($this->searchQuery) >= 1, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->searchQuery}%")
                        ->orWhere('code', 'like', "%{$this->searchQuery}%");
                });
            })
            ->take(12)
            ->get();

        return view('livewire.invoice-create', [
            'customers'    => $customers,
            'stores'       => $stores,
            'quickCatalog' => $quickCatalog,
            'currentStore' => Store::find($this->store_id),
        ])->layout('components.layouts.app', ['title' => 'نقطة البيع ومطحنة البن والشاي (POS)']);
    }
}
