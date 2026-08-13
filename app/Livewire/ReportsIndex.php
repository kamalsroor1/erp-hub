<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Expense;
use App\Models\Store;
use App\Models\Customer;
use App\Services\ProfitService;
use Illuminate\Support\Facades\DB;

class ReportsIndex extends Component
{
    public $activeTab = 'sales'; // sales, items, stores, customers, expenses, inventory
    public $dateFilter = 'today'; // today, this_week, this_month, this_year, custom
    public $selectedStoreId = 'all';
    public $fromDate;
    public $toDate;

    public function mount()
    {
        abort_if(!auth()->user()?->can('reports.view'), 403, 'غير مصرح لك بالوصول للتقارير المالية والأرباح.');
        $this->setFilter('today');
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
        } elseif ($filter === 'this_year') {
            $this->fromDate = now()->startOfYear()->toDateString();
            $this->toDate = now()->toDateString();
        }
    }

    public function updatedFromDate()
    {
        $this->dateFilter = 'custom';
    }

    public function updatedToDate()
    {
        $this->dateFilter = 'custom';
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render(ProfitService $profitService)
    {
        $storeFilter = ($this->selectedStoreId && $this->selectedStoreId !== 'all') 
            ? (int)$this->selectedStoreId 
            : null;

        $stores = Store::active()->get();

        // 1. Invoices base query for the period & store
        $invoicesQuery = Invoice::where('status', 'confirmed')
            ->when($this->fromDate, fn($q) => $q->whereDate('invoice_date', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('invoice_date', '<=', $this->toDate))
            ->when($storeFilter, fn($q) => $q->where('store_id', $storeFilter));

        $invoices = (clone $invoicesQuery)->get();

        $totalSales     = '0.000';
        $totalPaid      = '0.000';
        $totalRemaining = '0.000';
        $totalCost      = '0.000';
        $invoiceCount   = $invoices->count();

        foreach ($invoices as $inv) {
            $totalSales     = bcadd($totalSales, $inv->net_total, 3);
            $totalPaid      = bcadd($totalPaid, $inv->paid_amount, 3);
            $totalRemaining = bcadd($totalRemaining, $inv->remaining_amount, 3);
            $totalCost      = bcadd($totalCost, $inv->total_cost, 3);
        }

        $grossProfit = bcsub($totalSales, $totalCost, 3);
        $marginPct   = '0.00';
        if (bccomp($totalSales, '0.000', 3) > 0) {
            $marginPct = bcmul(bcdiv($grossProfit, $totalSales, 4), '100', 2);
        }

        $avgInvoiceValue = '0.00';
        if ($invoiceCount > 0) {
            $avgInvoiceValue = bcdiv($totalSales, (string)$invoiceCount, 2);
        }

        $periodic = [
            'invoice_count'     => $invoiceCount,
            'total_sales'       => $totalSales,
            'total_paid'        => $totalPaid,
            'total_remaining'   => $totalRemaining,
            'total_cost'        => $totalCost,
            'gross_profit'      => $grossProfit,
            'margin_percentage' => $marginPct,
            'avg_invoice'       => $avgInvoiceValue,
        ];

        // 2. Operational Expenses in Period
        $expensesQuery = Expense::when($this->fromDate, fn($q) => $q->whereDate('expense_date', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('expense_date', '<=', $this->toDate))
            ->when($storeFilter, fn($q) => $q->where('store_id', $storeFilter));

        $totalExpenses = (clone $expensesQuery)->sum('amount') ?: '0.000';
        $netProfitAfterExpenses = bcsub((string)$grossProfit, (string)$totalExpenses, 3);

        $expensesByCategory = (clone $expensesQuery)
            ->select('category', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderByDesc('total_amount')
            ->get();

        // 3. Item-level Sales & Profitability
        $itemProfits = InvoiceItem::whereHas('invoice', function ($q) use ($storeFilter) {
                $q->where('status', 'confirmed')
                  ->when($this->fromDate, fn($sub) => $sub->whereDate('invoice_date', '>=', $this->fromDate))
                  ->when($this->toDate, fn($sub) => $sub->whereDate('invoice_date', '<=', $this->toDate))
                  ->when($storeFilter, fn($sub) => $sub->where('store_id', $storeFilter));
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
            })
            ->sortByDesc('total_revenue')
            ->values();

        // 4. Store-by-Store Comparative Breakdown
        $storeBreakdown = [];
        foreach ($stores as $st) {
            $stInvoices = Invoice::where('status', 'confirmed')
                ->where('store_id', $st->id)
                ->when($this->fromDate, fn($q) => $q->whereDate('invoice_date', '>=', $this->fromDate))
                ->when($this->toDate, fn($q) => $q->whereDate('invoice_date', '<=', $this->toDate))
                ->get();

            $stSales = '0.000';
            $stPaid = '0.000';
            $stRemaining = '0.000';
            $stCost = '0.000';

            foreach ($stInvoices as $si) {
                $stSales     = bcadd($stSales, $si->net_total, 3);
                $stPaid      = bcadd($stPaid, $si->paid_amount, 3);
                $stRemaining = bcadd($stRemaining, $si->remaining_amount, 3);
                $stCost      = bcadd($stCost, $si->total_cost, 3);
            }

            $stProfit = bcsub($stSales, $stCost, 3);
            $stMargin = '0.00';
            if (bccomp($stSales, '0.000', 3) > 0) {
                $stMargin = bcmul(bcdiv($stProfit, $stSales, 4), '100', 2);
            }

            $sharePct = '0.0';
            if (bccomp($totalSales, '0.000', 3) > 0) {
                $sharePct = bcmul(bcdiv($stSales, $totalSales, 4), '100', 1);
            }

            $storeBreakdown[] = [
                'store'         => $st,
                'invoice_count' => $stInvoices->count(),
                'total_sales'   => $stSales,
                'total_paid'    => $stPaid,
                'total_remaining'=> $stRemaining,
                'total_cost'    => $stCost,
                'gross_profit'  => $stProfit,
                'margin'        => $stMargin,
                'share_pct'     => $sharePct,
            ];
        }

        // 5. Customer Sales & Receivables in this period
        $customerSales = (clone $invoicesQuery)
            ->select(
                'customer_id',
                DB::raw('COUNT(*) as total_invoices'),
                DB::raw('SUM(net_total) as total_bought'),
                DB::raw('SUM(paid_amount) as total_paid'),
                DB::raw('SUM(remaining_amount) as total_debt_in_period')
            )
            ->groupBy('customer_id')
            ->with('customer')
            ->orderByDesc('total_bought')
            ->take(20)
            ->get();

        $totalAllCustomersDebt = Customer::active()->sum('current_balance') ?: '0.000';

        // 6. Stock Inventory Valuation
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
            'stores'                 => $stores,
            'periodic'               => $periodic,
            'totalExpenses'          => $totalExpenses,
            'expensesByCategory'     => $expensesByCategory,
            'netProfitAfterExpenses' => $netProfitAfterExpenses,
            'itemProfits'            => $itemProfits,
            'storeBreakdown'         => $storeBreakdown,
            'customerSales'          => $customerSales,
            'totalAllCustomersDebt'  => $totalAllCustomersDebt,
            'stockCostValuation'     => $stockCostValuation,
            'stockSellingValuation'  => $stockSellingValuation,
            'expectedStockProfit'    => $expectedStockProfit,
            'allItems'               => $allItems,
        ])->layout('components.layouts.app', ['title' => 'التقارير المالية والمبيعات والأرباح']);
    }
}

