<?php

namespace App\Actions\Dashboard;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\CashShift;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GetTenantDashboardAnalyticsAction
{
    /**
     * حساب وتجميع مؤشرات أداء الداشبورد لمستأجر ERP
     */
    public function execute(?User $user): array
    {
        $today = now()->toDateString();

        // 1. Resolve Active Store
        $storeId = session('current_store_id');
        $activeStore = null;
        if ($storeId) {
            $activeStore = Store::where('id', $storeId)->where('is_active', true)->first();
        }
        if (!$activeStore && $user) {
            $activeStore = $user->getCurrentStore();
            if ($activeStore) {
                $storeId = $activeStore->id;
            }
        }

        // 2. Today's Invoices & Sales Metrics
        $todayInvoicesQuery = Invoice::with(['customer', 'store'])
            ->where('status', 'confirmed')
            ->whereDate('invoice_date', $today);

        if ($storeId) {
            $todayInvoicesQuery->where('store_id', $storeId);
        }

        $todayInvoices = (clone $todayInvoicesQuery)->latest('id')->get();
        $totalSales = (string)($todayInvoices->sum('total_amount') ?: '0.000');
        $invoicesCount = $todayInvoices->count();

        $cashSales = (string)($todayInvoices->where('payment_type', 'cash')->sum('total_amount') ?: '0.000');
        $creditSales = (string)($todayInvoices->where('payment_type', 'credit')->sum('total_amount') ?: '0.000');
        $partialSales = (string)($todayInvoices->where('payment_type', 'partial')->sum('total_amount') ?: '0.000');
        $partialPaid = (string)($todayInvoices->where('payment_type', 'partial')->sum('paid_amount') ?: '0.000');

        // 3. Cash Collected from Customer Vouchers
        $customerPayments = (string)(Payment::whereDate('payment_date', $today)
            ->whereNotNull('customer_id')
            ->sum('amount') ?: '0.000');

        $totalCashCollected = bcadd(bcadd($cashSales, $partialPaid, 3), $customerPayments, 3);

        // 4. Operating Expenses Today
        $expensesQuery = Expense::whereDate('expense_date', $today);
        if ($storeId) {
            $expensesQuery->where('store_id', $storeId);
        }
        $totalExpenses = (string)($expensesQuery->sum('amount') ?: '0.000');

        // 5. Supplier Payments Today
        $supplierPaid = (string)(Payment::whereDate('payment_date', $today)
            ->whereNotNull('supplier_id')
            ->sum('amount') ?: '0.000');

        $totalOutflows = bcadd($totalExpenses, $supplierPaid, 3);
        $netCashToday = bcsub($totalCashCollected, $totalOutflows, 3);

        // 6. Low Stock Radar
        $lowStockQuery = Item::where('is_active', true)
            ->whereNotNull('min_stock_level')
            ->where('min_stock_level', '>', 0)
            ->whereColumn('current_stock', '<=', 'min_stock_level')
            ->orderBy('current_stock', 'asc')
            ->take(6);

        $lowStockItems = $lowStockQuery->get(['id', 'name', 'code', 'current_stock', 'min_stock_level', 'unit']);

        // 7. Top Selling Coffee & Products this Month
        $startOfMonth = now()->startOfMonth()->toDateString();
        $topSellingItems = InvoiceItem::select(
                'items.id as item_id',
                'items.name as item_name',
                DB::raw('SUM(invoice_items.quantity) as total_qty'),
                DB::raw('SUM(invoice_items.total_price) as total_revenue')
            )
            ->join('items', 'items.id', '=', 'invoice_items.item_id')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoices.status', 'confirmed')
            ->whereDate('invoices.invoice_date', '>=', $startOfMonth)
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // 8. Active Cash Shift
        $activeShift = null;
        if ($storeId) {
            $activeShift = CashShift::where('store_id', $storeId)
                ->where('status', 'open')
                ->latest('id')
                ->first();
        }

        return [
            'metrics' => [
                'total_sales' => (float)$totalSales,
                'invoices_count' => $invoicesCount,
                'cash_sales' => (float)$cashSales,
                'credit_sales' => (float)$creditSales,
                'partial_sales' => (float)$partialSales,
                'total_cash_collected' => (float)$totalCashCollected,
                'total_expenses' => (float)$totalExpenses,
                'supplier_payments' => (float)$supplierPaid,
                'net_cash_today' => (float)$netCashToday,
            ],
            'recent_invoices' => \App\Http\Resources\InvoiceSummaryResource::collection($todayInvoices->take(6))->resolve(),
            'low_stock_items' => \App\Http\Resources\POSItemResource::collection($lowStockItems)->resolve(),
            'top_selling_items' => $topSellingItems->map(fn($t) => [
                'item_id' => $t->item_id,
                'name' => $t->item_name,
                'total_qty' => (float)$t->total_qty,
                'total_revenue' => (float)$t->total_revenue,
            ]),
            'active_shift' => $activeShift ? [
                'id' => $activeShift->id,
                'shift_number' => $activeShift->shift_number ?? $activeShift->id,
                'opened_at' => $activeShift->opened_at,
            ] : null,
            'active_store' => $activeStore ? [
                'id' => $activeStore->id,
                'name' => $activeStore->name,
                'type' => $activeStore->type,
            ] : null,
        ];
    }
}
