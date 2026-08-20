<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Item;
use App\Models\Customer;
use App\Services\InvoiceService;
use Exception;

class CoffeeBlender extends Component
{
    public $blend_name = 'توليفة مخصوصة (خلطة خاصة)';
    public $customer_id;
    public $target_weight_grams = '250.000'; // Target weight in grams (e.g. 250g = 0.250 kg)
    public $roast_type = 'وسط'; // فاتح، وسط، غامق، محروق
    public $grind_level = 'تركي ناعم'; // حبوب بدون طحن، تركي ناعم، إسبريسو، فرينش بريس، فلتر

    public $components = []; // [{item_id, name, percentage, grams, kg, cost_price, selling_price, line_cost, line_price}]
    public $cardamom_grams = '0.000'; // إضافات حبهان بالجرام
    public $mastic_grams = '0.000'; // مستكة بالجرام

    // Calculated totals
    public $total_weight_grams = '0.000';
    public $total_weight_kg = '0.000';
    public $blended_cost_price = '0.000';
    public $blended_selling_price = '0.000';

    public $errorMessage = '';
    public $successMessage = '';

    public function mount()
    {
        abort_if(!auth()->user()?->can('pos.access'), 403, 'غير مصرح لك بدخول أداة خلط وتوليفات البن');
        $firstCust = Customer::active()->first();
        if ($firstCust) $this->customer_id = $firstCust->id;

        // Preload default popular items (e.g. Brazilian, Colombian, Ethiopian)
        $brazilian = Item::where('code', 'COF-001')->first();
        $colombian = Item::where('code', 'COF-002')->first();
        $ethiopian = Item::where('code', 'COF-003')->first();

        if ($brazilian) {
            $this->components[] = [
                'item_id'       => $brazilian->id,
                'name'          => $brazilian->name,
                'percentage'    => '60',
                'grams'         => '150.000',
                'cost_price'    => $brazilian->cost_price,
                'selling_price' => $brazilian->selling_price,
            ];
        }

        if ($colombian) {
            $this->components[] = [
                'item_id'       => $colombian->id,
                'name'          => $colombian->name,
                'percentage'    => '40',
                'grams'         => '100.000',
                'cost_price'    => $colombian->cost_price,
                'selling_price' => $colombian->selling_price,
            ];
        }

        $this->calculateBlend();
    }

    public function addComponent($itemId)
    {
        $item = Item::active()->find($itemId);
        if (!$item) return;

        foreach ($this->components as $c) {
            if ($c['item_id'] == $item->id) return;
        }

        $this->components[] = [
            'item_id'       => $item->id,
            'name'          => $item->name,
            'percentage'    => '0',
            'grams'         => '0.000',
            'cost_price'    => $item->cost_price,
            'selling_price' => $item->selling_price,
        ];

        $this->calculateBlend();
    }

    public function removeComponent($index)
    {
        unset($this->components[$index]);
        $this->components = array_values($this->components);
        $this->calculateBlend();
    }

    public function setPresetTargetWeight($grams)
    {
        $this->target_weight_grams = (string) $grams;
        $this->calculateBlend();
    }

    public function updatedTargetWeightGrams()
    {
        $this->calculateBlend();
    }

    public function updatedComponents()
    {
        $this->calculateBlend();
    }

    public function updatedCardamomGrams()
    {
        $this->calculateBlend();
    }

    public function updatedMasticGrams()
    {
        $this->calculateBlend();
    }

    public function calculateBlend()
    {
        $targetGrams = $this->target_weight_grams ?: '250.000';
        $totalCost = '0.000';
        $totalPrice = '0.000';
        $totalGrams = '0.000';

        foreach ($this->components as $idx => $comp) {
            $pct = $comp['percentage'] ?? '0';
            // Grams = Target * (Pct / 100)
            $grams = bcdiv(bcmul($targetGrams, (string)$pct, 4), '100', 3);
            $this->components[$idx]['grams'] = $grams;

            // Kg = Grams / 1000
            $kg = bcdiv($grams, '1000', 4);

            // Cost = Kg * cost_price_per_kg
            $lineCost = bcmul($kg, (string)$comp['cost_price'], 3);
            $linePrice = bcmul($kg, (string)$comp['selling_price'], 3);

            $totalCost = bcadd($totalCost, $lineCost, 3);
            $totalPrice = bcadd($totalPrice, $linePrice, 3);
            $totalGrams = bcadd($totalGrams, $grams, 3);
        }

        // Add Cardamom if selected
        if (bccomp((string)$this->cardamom_grams, '0.000', 3) > 0) {
            $cardItem = Item::where('code', 'SPICE-001')->first();
            if ($cardItem) {
                $cardKg = bcdiv((string)$this->cardamom_grams, '1000', 4);
                $totalCost = bcadd($totalCost, bcmul($cardKg, $cardItem->cost_price, 3), 3);
                $totalPrice = bcadd($totalPrice, bcmul($cardKg, $cardItem->selling_price, 3), 3);
                $totalGrams = bcadd($totalGrams, (string)$this->cardamom_grams, 3);
            }
        }

        // Add Mastic if selected
        if (bccomp((string)$this->mastic_grams, '0.000', 3) > 0) {
            $masItem = Item::where('code', 'SPICE-002')->first();
            if ($masItem) {
                $masKg = bcdiv((string)$this->mastic_grams, '1000', 4);
                $totalCost = bcadd($totalCost, bcmul($masKg, $masItem->cost_price, 3), 3);
                $totalPrice = bcadd($totalPrice, bcmul($masKg, $masItem->selling_price, 3), 3);
                $totalGrams = bcadd($totalGrams, (string)$this->mastic_grams, 3);
            }
        }

        $this->total_weight_grams = $totalGrams;
        $this->total_weight_kg = bcdiv($totalGrams, '1000', 3);
        $this->blended_cost_price = $totalCost;
        $this->blended_selling_price = $totalPrice;
    }

    public function createBlendInvoice(InvoiceService $invoiceService)
    {
        abort_if(!auth()->user()?->can('invoices.create'), 403, 'غير مصرح لك بإنشاء فواتير مبيعات من خلطات وتوليفات البن');
        $this->errorMessage = '';
        $this->successMessage = '';

        if (empty($this->components)) {
            $this->errorMessage = 'يجب اختيار صنف بن واحد على الأقل في التوليفة.';
            return;
        }

        try {
            $itemsForInvoice = [];

            // Add coffee components in Kg
            foreach ($this->components as $c) {
                $kg = bcdiv((string)$c['grams'], '1000', 4);
                if (bccomp($kg, '0.000', 4) > 0) {
                    $itemsForInvoice[] = [
                        'item_id'         => $c['item_id'],
                        'quantity'        => $kg,
                        'unit_price'      => $c['selling_price'],
                        'discount_amount' => '0.000',
                    ];
                }
            }

            // Add Cardamom if any
            if (bccomp((string)$this->cardamom_grams, '0.000', 3) > 0) {
                $cardItem = Item::where('code', 'SPICE-001')->first();
                if ($cardItem) {
                    $itemsForInvoice[] = [
                        'item_id'         => $cardItem->id,
                        'quantity'        => bcdiv((string)$this->cardamom_grams, '1000', 4),
                        'unit_price'      => $cardItem->selling_price,
                        'discount_amount' => '0.000',
                    ];
                }
            }

            // Add Mastic if any
            if (bccomp((string)$this->mastic_grams, '0.000', 3) > 0) {
                $masItem = Item::where('code', 'SPICE-002')->first();
                if ($masItem) {
                    $itemsForInvoice[] = [
                        'item_id'         => $masItem->id,
                        'quantity'        => bcdiv((string)$this->mastic_grams, '1000', 4),
                        'unit_price'      => $masItem->selling_price,
                        'discount_amount' => '0.000',
                    ];
                }
            }

            $storeId = session('current_store_id') 
                ?? auth()->user()?->getCurrentStore()?->id 
                ?? \App\Models\Store::getMainStore()?->id;

            $invoice = $invoiceService->confirmInvoice([
                'customer_id'    => $this->customer_id,
                'store_id'       => $storeId,
                'invoice_date'   => now()->toDateString(),
                'payment_type'   => 'cash',
                'discount_type'  => 'fixed',
                'discount_value' => '0.000',
                'paid_amount'    => $this->blended_selling_price,
                'notes'          => "توليفة بن مخصوصة: {$this->blend_name} (وزن: {$this->total_weight_grams} جم | درجة التحميص: {$this->roast_type} | الطحن: {$this->grind_level})",
                'items'          => $itemsForInvoice,
            ]);

            return redirect()->route('invoices.print.thermal', $invoice->id);
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        $customers = Customer::active()->orderBy('name')->get();
        $availableCoffees = Item::active()->where('category', 'بن وتوليفات')->get();

        return view('livewire.coffee-blender', [
            'customers'        => $customers,
            'availableCoffees' => $availableCoffees,
        ])->layout('components.layouts.app', ['title' => 'خلاط وتوليفات البن المخصوصة والمطحنة']);
    }
}
