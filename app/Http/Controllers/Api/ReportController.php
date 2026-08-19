<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Expense;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Profit & Loss Executive Summary (صافي الربح الحقيقي والمبيعات)
     */
    public function summary(Request $request)
    {
        $preset = $request->input('preset', 'this_month');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date', now()->toDateString());
        $storeId = $request->input('store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? Store::getMainStore()?->id;

        if (!$fromDate) {
            if ($preset === 'today') {
                $fromDate = now()->toDateString();
            } elseif ($preset === 'this_week') {
                $fromDate = now()->startOfWeek()->toDateString();
            } elseif ($preset === 'this_month') {
                $fromDate = now()->startOfMonth()->toDateString();
            } elseif ($preset === 'this_year') {
                $fromDate = now()->startOfYear()->toDateString();
            } else {
                $fromDate = now()->startOfMonth()->toDateString();
            }
        }

        // 1. Invoices (Sales & COGS)
        $invoicesQuery = Invoice::where('status', 'confirmed')
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->whereDate('invoice_date', '>=', $fromDate)
            ->whereDate('invoice_date', '<=', $toDate);

        $invoices = (clone $invoicesQuery)->get();

        $totalSales     = '0.000';
        $totalCost      = '0.000'; // COGS
        $totalPaid      = '0.000';
        $totalRemaining = '0.000';
        $invoiceCount   = $invoices->count();

        foreach ($invoices as $inv) {
            $totalSales     = bcadd($totalSales, $inv->net_total, 3);
            $totalCost      = bcadd($totalCost, $inv->total_cost, 3);
            $totalPaid      = bcadd($totalPaid, $inv->paid_amount, 3);
            $totalRemaining = bcadd($totalRemaining, $inv->remaining_amount, 3);
        }

        // Gross Profit from sales = Sales - COGS
        $grossProfit = bcsub($totalSales, $totalCost, 3);

        // 2. Expenses (Operational costs: Bags, Cups, Rent, Utilities, Petty cash)
        $expensesQuery = Expense::whereDate('expense_date', '>=', $fromDate)
            ->whereDate('expense_date', '<=', $toDate)
            ->when($storeId, fn($q) => $q->where('store_id', $storeId));

        $totalExpenses = (string)($expensesQuery->sum('amount') ?: '0.000');
        $expensesCount = $expensesQuery->count();

        // 3. Net True Profit = Gross Profit - Expenses
        $netProfit = bcsub($grossProfit, $totalExpenses, 3);

        // Profit Margin %
        $marginPct = '0.00';
        if (bccomp($totalSales, '0.000', 3) > 0) {
            $marginPct = bcmul(bcdiv($grossProfit, $totalSales, 4), '100', 2);
        }

        $avgTicket = $invoiceCount > 0 ? bcdiv($totalSales, (string)$invoiceCount, 2) : '0.00';

        return response()->json([
            'success' => true,
            'period'  => [
                'preset'    => $preset,
                'from_date' => $fromDate,
                'to_date'   => $toDate,
            ],
            'metrics' => [
                'total_sales'       => (string)$totalSales,
                'total_cogs'        => (string)$totalCost,
                'gross_profit'      => (string)$grossProfit,
                'total_expenses'    => (string)$totalExpenses,
                'expenses_count'    => $expensesCount,
                'net_profit'        => (string)$netProfit,
                'profit_margin_pct' => (string)$marginPct,
                'total_paid'        => (string)$totalPaid,
                'total_remaining'   => (string)$totalRemaining,
                'invoice_count'     => $invoiceCount,
                'average_ticket'    => (string)$avgTicket,
            ]
        ]);
    }

    /**
     * Top Selling & Most Profitable Coffee Items
     */
    public function topItems(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
        $storeId = $request->input('store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? Store::getMainStore()?->id;

        $topItems = InvoiceItem::join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('items', 'items.id', '=', 'invoice_items.item_id')
            ->where('invoices.status', 'confirmed')
            ->when($storeId, fn($q) => $q->where('invoices.store_id', $storeId))
            ->whereDate('invoices.invoice_date', '>=', $fromDate)
            ->whereDate('invoices.invoice_date', '<=', $toDate)
            ->select(
                'items.id as item_id',
                'items.name',
                'items.code',
                'items.category',
                'items.unit',
                DB::raw('SUM(invoice_items.quantity) as total_qty'),
                DB::raw('SUM(invoice_items.total_price) as total_sales'),
                DB::raw('SUM(invoice_items.quantity * invoice_items.cost_price) as total_cost'),
                DB::raw('SUM(invoice_items.total_price - (invoice_items.quantity * invoice_items.cost_price)) as total_profit')
            )
            ->groupBy('items.id', 'items.name', 'items.code', 'items.category', 'items.unit')
            ->orderByDesc('total_sales')
            ->limit(20)
            ->get();

        return response()->json([
            'success'   => true,
            'top_items' => $topItems,
        ]);
    }

    /**
     * Item Movement Card (كارت حركة الصنف)
     */
    public function itemCard(Request $request, $itemId)
    {
        $item = Item::findOrFail($itemId);
        $storeId = $request->input('store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? Store::getMainStore()?->id;

        $movements = StockMovement::where('item_id', $itemId)
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->latest('id')
            ->limit(50)
            ->get();

        return response()->json([
            'success'   => true,
            'item'      => $item,
            'movements' => $movements,
        ]);
    }
}
