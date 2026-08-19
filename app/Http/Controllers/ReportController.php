<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->input('period', 'this_month');
        $dateFrom = $request->input('from');
        $dateTo = $request->input('to');
        $storeId = $request->input('store_id', 'all');

        if (!$dateFrom || !$dateTo) {
            if ($period === 'today') {
                $dateFrom = now()->toDateString();
                $dateTo = now()->toDateString();
            } elseif ($period === 'yesterday') {
                $dateFrom = now()->subDay()->toDateString();
                $dateTo = now()->subDay()->toDateString();
            } elseif ($period === 'this_week') {
                $dateFrom = now()->startOfWeek()->toDateString();
                $dateTo = now()->toDateString();
            } elseif ($period === 'last_month') {
                $dateFrom = now()->subMonth()->startOfMonth()->toDateString();
                $dateTo = now()->subMonth()->endOfMonth()->toDateString();
            } else { // this_month
                $dateFrom = now()->startOfMonth()->toDateString();
                $dateTo = now()->toDateString();
            }
        }

        // Base Invoices Query
        $invQuery = Invoice::where('status', 'confirmed')
            ->whereDate('invoice_date', '>=', $dateFrom)
            ->whereDate('invoice_date', '<=', $dateTo);

        if ($storeId !== 'all') {
            $invQuery->where('store_id', (int)$storeId);
        }

        $totalSales = (float)$invQuery->sum('net_total');
        $invoicesCount = $invQuery->count();
        $cashSales = (float)(clone $invQuery)->where('payment_type', 'cash')->sum('paid_amount');
        $instapaySales = (float)(clone $invQuery)->where('payment_type', 'instapay')->sum('paid_amount');
        $visaSales = (float)(clone $invQuery)->where('payment_type', 'visa')->sum('paid_amount');

        // Total COGS from invoice items
        $itemsQuery = InvoiceItem::whereHas('invoice', function ($q) use ($dateFrom, $dateTo, $storeId) {
            $q->where('status', 'confirmed')
              ->whereDate('invoice_date', '>=', $dateFrom)
              ->whereDate('invoice_date', '<=', $dateTo);
            if ($storeId !== 'all') {
                $q->where('store_id', (int)$storeId);
            }
        });

        $totalCogs = (float)$itemsQuery->select(DB::raw('SUM(quantity * cost_price) as total_cogs'))->value('total_cogs') ?: 0.0;
        $grossProfit = max($totalSales - $totalCogs, 0.0);

        // Expenses
        $expQuery = Expense::whereDate('expense_date', '>=', $dateFrom)
            ->whereDate('expense_date', '<=', $dateTo);
        if ($storeId !== 'all') {
            $expQuery->where('store_id', (int)$storeId);
        }
        $totalExpenses = (float)$expQuery->sum('amount');
        $netProfit = $grossProfit - $totalExpenses;

        // Purchases
        $purQuery = Purchase::where('status', 'confirmed')
            ->whereDate('purchase_date', '>=', $dateFrom)
            ->whereDate('purchase_date', '<=', $dateTo);
        if ($storeId !== 'all') {
            $purQuery->where('store_id', (int)$storeId);
        }
        $totalPurchases = (float)$purQuery->sum('net_total');

        // Top 10 Best Selling Items
        $topItems = InvoiceItem::whereHas('invoice', function ($q) use ($dateFrom, $dateTo, $storeId) {
            $q->where('status', 'confirmed')
              ->whereDate('invoice_date', '>=', $dateFrom)
              ->whereDate('invoice_date', '<=', $dateTo);
            if ($storeId !== 'all') {
                $q->where('store_id', (int)$storeId);
            }
        })
        ->select('item_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_price) as total_revenue'))
        ->groupBy('item_id')
        ->orderByDesc('total_revenue')
        ->limit(10)
        ->with('item')
        ->get();

        // Expenses by category
        $expensesByCategory = Expense::whereDate('expense_date', '>=', $dateFrom)
            ->whereDate('expense_date', '<=', $dateTo)
            ->select('category', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderByDesc('total_amount')
            ->get();

        $stores = Store::where('is_active', true)->select('id', 'name')->get();

        return Inertia::render('Reports/Index', [
            'summary' => [
                'total_sales' => $totalSales,
                'invoices_count' => $invoicesCount,
                'total_cogs' => $totalCogs,
                'gross_profit' => $grossProfit,
                'total_expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'total_purchases' => $totalPurchases,
                'cash_sales' => $cashSales,
                'instapay_sales' => $instapaySales,
                'visa_sales' => $visaSales,
            ],
            'top_items' => $topItems->map(fn($it) => [
                'name' => $it->item?->name ?: 'صنف محذوف',
                'category' => $it->item?->category,
                'unit' => $it->item?->unit,
                'quantity' => (float)$it->total_qty,
                'revenue' => (float)$it->total_revenue,
            ]),
            'expenses_breakdown' => $expensesByCategory->map(fn($e) => [
                'category' => $e->category,
                'amount' => (float)$e->total_amount,
                'count' => (int)$e->count,
            ]),
            'stores' => $stores,
            'filters' => [
                'period' => $period,
                'from' => $dateFrom,
                'to' => $dateTo,
                'store_id' => $storeId,
            ],
        ]);
    }
}