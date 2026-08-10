<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Expense;
use App\Services\ProfitService;
use Illuminate\Support\Facades\DB;

class ReportsIndex extends Component
{
    public $dateFilter = 'this_month'; // today, this_week, this_month, custom
    public $fromDate;
    public $toDate;

    public function mount()
    {
        if (!auth()->check() || !auth()->user()->hasAnyRole(['admin', 'accountant'])) {
            abort(403, 'غير مصرح لك بالوصول للتقارير المالية والأرباح.');
        }
        $this->setFilter('this_month');
    }

    public function setFilter($filter)
    {
        $this->dateFilter = $filter;

        if ($filter === 'today') {
            $this->fromDate = now()->toDateString();
            $this->toDate = now()->toDateString();
        } elseif ($filter === 'this_week') {
            $this->fromDate = now()->startOfWeek()->toDateString();
            $this->toDate = now()->toDateString();
        } elseif ($filter === 'this_month') {
            $this->fromDate = now()->startOfMonth()->toDateString();
            $this->toDate = now()->toDateString();
        }
    }

    public function render(ProfitService $profitService)
    {
        // 1. Overall Periodic Profit
        $periodic = $profitService->getPeriodicProfits($this->fromDate, $this->toDate);

        // 2. Operational Expenses
        $totalExpenses = Expense::when($this->fromDate, fn($q) => $q->whereDate('expense_date', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('expense_date', '<=', $this->toDate))
            ->sum('amount') ?: '0.000';

        $grossProfit = $periodic['gross_profit'] ?? '0.000';
        $netProfitAfterExpenses = bcsub((string)$grossProfit, (string)$totalExpenses, 3);

        // 3. Item-level Profitability
        $itemProfits = InvoiceItem::whereHas('invoice', function ($q) {
                $q->where('status', 'confirmed')
                  ->when($this->fromDate, fn($sub) => $sub->whereDate('invoice_date', '>=', $this->fromDate))
                  ->when($this->toDate, fn($sub) => $sub->whereDate('invoice_date', '<=', $this->toDate));
            })
            ->select(
                'item_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total_price) as total_revenue'),
                DB::raw('SUM(quantity * cost_price) as total_cogs')
            )
            ->groupBy('item_id')
            ->with('item')
            ->get()
            ->map(function ($row) {
                $profit = bcsub((string)$row->total_revenue, (string)$row->total_cogs, 3);
                $margin = '0.0';
                if (bccomp((string)$row->total_revenue, '0.000', 3) > 0) {
                    $margin = bcmul(bcdiv($profit, (string)$row->total_revenue, 4), '100', 1);
                }
                return [
                    'item'          => $row->item,
                    'total_qty'     => $row->total_qty,
                    'total_revenue' => $row->total_revenue,
                    'total_cogs'    => $row->total_cogs,
                    'profit'        => $profit,
                    'margin'        => $margin,
                ];
            });

        // 4. Stock Inventory Valuation
        $allItems = Item::active()->get();
        $stockCostValuation = '0.000';
        $stockSellingValuation = '0.000';

        foreach ($allItems as $itm) {
            $costVal = bcmul($itm->current_stock, $itm->cost_price, 3);
            $sellVal = bcmul($itm->current_stock, $itm->selling_price, 3);
            $stockCostValuation = bcadd($stockCostValuation, $costVal, 3);
            $stockSellingValuation = bcadd($stockSellingValuation, $sellVal, 3);
        }

        $expectedStockProfit = bcsub($stockSellingValuation, $stockCostValuation, 3);

        return view('livewire.reports-index', [
            'periodic'               => $periodic,
            'totalExpenses'          => $totalExpenses,
            'netProfitAfterExpenses' => $netProfitAfterExpenses,
            'itemProfits'            => $itemProfits,
            'stockCostValuation'     => $stockCostValuation,
            'stockSellingValuation'  => $stockSellingValuation,
            'expectedStockProfit'    => $expectedStockProfit,
        ])->layout('components.layouts.app', ['title' => 'التقارير المالية وحسابات الأرباح']);
    }
}
