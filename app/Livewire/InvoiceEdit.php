<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Customer;
use App\Services\InvoiceService;
use App\Livewire\Traits\RequiresAuth;
use Exception;

class InvoiceEdit extends Component
{
    use RequiresAuth;

    public Invoice $invoice;
    public $invoice_id;
    public $invoice_number;
    public $customer_id;
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
        'customer_id'       => 'required|exists:customers,id',
        'invoice_date'      => 'required|date',
        'payment_type'      => 'required|in:cash,credit,partial',
        'items'             => 'required|array|min:1',
        'items.*.item_id'   => 'required|exists:items,id',
        'items.*.quantity'  => 'required|numeric|min:0.001',
        'items.*.unit_price'=> 'required|numeric|min:0',
    ];

    public function mount($id)
    {
        abort_if(!auth()->user()?->can('invoices.edit'), 403, 'غير مصرح لك بتعديل فواتير المبيعات المعتمدة');

        $this->invoice = Invoice::with(['items.item', 'customer'])->findOrFail($id);
        $this->invoice_id = $this->invoice->id;
        $this->invoice_number = $this->invoice->invoice_number;
        $this->customer_id = $this->invoice->customer_id;
        $this->invoice_date = $this->invoice->invoice_date->format('Y-m-d');
        $this->payment_type = $this->invoice->payment_type;
        $this->discount_type = $this->invoice->discount_type ?: 'fixed';
        $this->discount_value = (string)($this->invoice->discount_value ?: '0.000');
        $this->paid_amount = (string)($this->invoice->paid_amount ?: '0.000');
        $this->notes = $this->invoice->notes;

        $this->items = [];
        foreach ($this->invoice->items as $line) {
            $this->items[] = [
                'item_id'         => $line->item_id,
                'code'            => $line->item->code,
                'name'            => $line->item->name,
                'category'        => $line->item->category,
                'unit'            => $line->item->unit ?: 'كجم',
                'current_stock'   => $line->item->current_stock,
                'quantity'        => (string)$line->quantity,
                'unit_price'      => (string)$line->unit_price,
                'discount_amount' => (string)($line->discount_amount ?: '0.000'),
                'total_price'     => (string)$line->total_price,
            ];
        }

        $this->calculateTotals();
    }

    public function addItem($itemId, $quantity = '1.000')
    {
        $item = Item::active()->find($itemId);
        if (!$item) return;

        $qtyToAdd = (string) $quantity;

        // Check if item already in lines
        foreach ($this->items as $index => $line) {
            if ($line['item_id'] == $item->id) {
                $newQty = bcadd($line['quantity'], $qtyToAdd, 3);
                $this->items[$index]['quantity'] = $newQty;
                $this->calculateTotals();
                $this->searchQuery = '';
                return;
            }
        }

        $this->items[] = [
            'item_id'         => $item->id,
            'code'            => $item->code,
            'name'            => $item->name,
            'category'        => $item->category,
            'unit'            => $item->unit ?: 'كجم',
            'current_stock'   => $item->current_stock,
            'quantity'        => $qtyToAdd,
            'unit_price'      => $item->selling_price,
            'discount_amount' => '0.000',
            'total_price'     => bcmul($qtyToAdd, $item->selling_price, 3),
        ];

        $this->calculateTotals();
        $this->searchQuery = '';
    }

    public function setLineWeightPreset($index, $weight)
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['quantity'] = (string) $weight;
            $this->calculateTotals();
        }
    }

    public function setLineGrams($index, $grams)
    {
        if (isset($this->items[$index])) {
            $kg = bcdiv((string)$grams, '1000', 4);
            $this->items[$index]['quantity'] = $kg;
            $this->calculateTotals();
        }
    }

    public function addLineWeightPreset($index, $weightToAdd)
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['quantity'] = bcadd($this->items[$index]['quantity'], (string)$weightToAdd, 3);
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
        $sub = '0.000';

        foreach ($this->items as $idx => $line) {
            $qty = $line['quantity'] ?? '1.000';
            $price = $line['unit_price'] ?? '0.000';
            $disc = $line['discount_amount'] ?? '0.000';

            $lineTotal = bcsub(bcmul($qty, $price, 3), $disc, 3);
            if (bccomp($lineTotal, '0.000', 3) < 0) {
                $lineTotal = '0.000';
            }

            $this->items[$idx]['total_price'] = $lineTotal;
            $sub = bcadd($sub, $lineTotal, 3);
        }

        $this->subtotal = $sub;

        // Invoice Discount
        $discVal = $this->discount_value ?: '0.000';
        $invDisc = '0.000';

        if ($this->discount_type === 'percentage') {
            $invDisc = bcdiv(bcmul($this->subtotal, $discVal, 4), '100', 3);
        } else {
            $invDisc = $discVal;
        }

        if (bccomp($invDisc, $this->subtotal, 3) > 0) {
            $invDisc = $this->subtotal;
        }

        $this->discount_amount = $invDisc;
        $this->net_total = bcsub($this->subtotal, $invDisc, 3);

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

    public function updateInvoice(InvoiceService $invoiceService, $printMode = null)
    {
        abort_if(!auth()->user()?->can('invoices.edit'), 403, 'غير مصرح لك بتعديل فواتير المبيعات المعتمدة');

        if (bccomp((string)($this->discount_value ?: '0.000'), '0.000', 3) > 0) {
            abort_if(!auth()->user()?->can('invoices.discount'), 403, 'غير مصرح لك بمنح خصومات على الفواتير');
        }

        $this->errorMessage = '';
        $this->validate();

        try {
            $updated = $invoiceService->updateInvoice($this->invoice, [
                'customer_id'    => $this->customer_id,
                'invoice_date'   => $this->invoice_date,
                'payment_type'   => $this->payment_type,
                'discount_type'  => $this->discount_type,
                'discount_value' => $this->discount_value,
                'paid_amount'    => $this->paid_amount,
                'notes'          => $this->notes,
                'items'          => $this->items,
            ]);

            if ($printMode === 'print' || $printMode === 'a4') {
                return redirect()->route('invoices.print.a4', $updated->id);
            }

            session()->flash('success', "تم تحديث وتعديل الفاتورة رقم {$updated->invoice_number} بنجاح.");
            $this->dispatch('swal:toast', [
                'type'  => 'success',
                'title' => 'تم تعديل الفاتورة!',
                'text'  => "تم تحديث الفاتورة {$updated->invoice_number} وعكس أثر المخزون والحسابات بنجاح."
            ]);

            return redirect()->route('invoices.show', $updated->id);
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal:toast', ['type' => 'error', 'title' => 'خطأ أثناء التعديل', 'text' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $customers = Customer::active()->orderBy('name')->get();

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

        return view('livewire.invoice-edit', [
            'customers'    => $customers,
            'quickCatalog' => $quickCatalog,
        ])->layout('components.layouts.app', ['title' => "تعديل الفاتورة رقم {$this->invoice_number}"]);
    }
}
