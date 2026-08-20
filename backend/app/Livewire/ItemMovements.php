<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\StoreStock;
use App\Livewire\Traits\RequiresAuth;

class ItemMovements extends Component
{
    use WithPagination, RequiresAuth;

    public Item $item;
    public string $datePreset = 'this_month'; // today, this_week, this_month, this_year, all, custom
    public ?string $fromDate = null;
    public ?string $toDate = null;
    public string $selectedStoreId = 'all';
    public string $filterType = 'all'; // all, in, out, adjustments

    public function mount($id)
    {
        abort_if(!auth()->user()?->can('items.view'), 403, 'غير مصرح لك بعرض حركة المخزون');
        $this->item = Item::withTrashed()->findOrFail($id);
        $this->applyDatePreset('this_month');
    }

    public function applyDatePreset(string $preset)
    {
        $this->datePreset = $preset;
        $now = now();

        match ($preset) {
            'today' => [
                $this->fromDate = $now->toDateString(),
                $this->toDate   = $now->toDateString(),
            ],
            'this_week' => [
                $this->fromDate = $now->startOfWeek()->toDateString(),
                $this->toDate   = $now->endOfWeek()->toDateString(),
            ],
            'this_month' => [
                $this->fromDate = $now->startOfMonth()->toDateString(),
                $this->toDate   = $now->endOfMonth()->toDateString(),
            ],
            'this_year' => [
                $this->fromDate = $now->startOfYear()->toDateString(),
                $this->toDate   = $now->endOfYear()->toDateString(),
            ],
            'all' => [
                $this->fromDate = null,
                $this->toDate   = null,
            ],
            default => null, // Keep custom dates
        };

        $this->resetPage();
    }

    public function updatedFromDate()
    {
        $this->datePreset = 'custom';
        $this->resetPage();
    }

    public function updatedToDate()
    {
        $this->datePreset = 'custom';
        $this->resetPage();
    }

    public function updatedSelectedStoreId()
    {
        $this->resetPage();
    }

    public function updatedFilterType()
    {
        $this->resetPage();
    }

    public function render()
    {
        $storeFilter = ($this->selectedStoreId !== 'all' && is_numeric($this->selectedStoreId))
            ? (int)$this->selectedStoreId
            : null;

        // Inbound/Outbound types depending on scope:
        // When viewing a specific store: incoming transfer is Inbound (+), outgoing transfer is Outbound (-).
        // When viewing ALL stores (Company level): transfers between branches are internal transfers that do not alter total company stock.
        if ($storeFilter) {
            $inTypes = [
                'purchase_in', 'stock_deposit_in', 'stock_adjustment_in',
                'cancellation_in', 'transfer_in', 'sales_return_in', 'purchase_restore_in'
            ];
            $outTypes = [
                'sales_out', 'waste_out', 'stock_adjustment_out',
                'transfer_out', 'purchase_cancel_out', 'purchase_return_out'
            ];
        } else {
            $inTypes = [
                'purchase_in', 'stock_deposit_in', 'stock_adjustment_in',
                'cancellation_in', 'sales_return_in', 'purchase_restore_in'
            ];
            $outTypes = [
                'sales_out', 'waste_out', 'stock_adjustment_out',
                'purchase_cancel_out', 'purchase_return_out'
            ];
        }

        $allFilterInTypes = [
            'purchase_in', 'stock_deposit_in', 'stock_adjustment_in',
            'cancellation_in', 'transfer_in', 'sales_return_in', 'purchase_restore_in'
        ];

        $allFilterOutTypes = [
            'sales_out', 'waste_out', 'stock_adjustment_out',
            'transfer_out', 'purchase_cancel_out', 'purchase_return_out'
        ];

        $adjTypes = ['stock_adjustment_in', 'stock_adjustment_out', 'stock_deposit_in'];

        // Base Query
        $baseQuery = StockMovement::with(['user', 'store'])
            ->where('item_id', $this->item->id)
            ->when($storeFilter, fn($q) => $q->where('store_id', $storeFilter))
            ->when($this->fromDate, fn($q) => $q->whereDate('created_at', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('created_at', '<=', $this->toDate))
            ->when($this->filterType === 'in', fn($q) => $q->whereIn('movement_type', $allFilterInTypes))
            ->when($this->filterType === 'out', fn($q) => $q->whereIn('movement_type', $allFilterOutTypes))
            ->when($this->filterType === 'adjustments', fn($q) => $q->whereIn('movement_type', $adjTypes));

        // Aggregate stats for period
        $statsQuery = clone $baseQuery;
        $allMovements = $statsQuery->get();

        $totalIn = '0.000';
        $totalOut = '0.000';

        foreach ($allMovements as $mov) {
            if (in_array($mov->movement_type, $inTypes)) {
                $totalIn = bcadd($totalIn, (string)$mov->quantity, 3);
            } elseif (in_array($mov->movement_type, $outTypes)) {
                $totalOut = bcadd($totalOut, (string)$mov->quantity, 3);
            }
        }

        $netMovement = bcsub($totalIn, $totalOut, 3);

        // Current Stock in selected scope
        $currentScopeStock = $storeFilter
            ? (string)(StoreStock::where('store_id', $storeFilter)->where('item_id', $this->item->id)->value('quantity') ?: '0.000')
            : (string)$this->item->current_stock;

        $stores = Store::active()->orderBy('is_main', 'desc')->get();
        $movements = $baseQuery->latest('created_at')->latest('id')->paginate(20);

        return view('livewire.item-movements', [
            'stores'            => $stores,
            'movements'         => $movements,
            'totalIn'           => $totalIn,
            'totalOut'          => $totalOut,
            'netMovement'       => $netMovement,
            'currentScopeStock' => $currentScopeStock,
        ])->layout('components.layouts.app', ['title' => "كارت حركة الصنف: {$this->item->name}"]);
    }
}
