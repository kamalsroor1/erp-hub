<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Store;
use App\Models\StoreStock;
use App\Services\ProfitService;
use App\Livewire\Traits\RequiresAuth;

class Dashboard extends Component
{
    use RequiresAuth;

    public function render(ProfitService $profitService, \App\Services\DashboardAnalyticsService $analyticsService)
    {
        $today = now()->toDateString();
        
        $activeStoreId = session('current_store_id') ?? auth()->user()?->getCurrentStore()?->id;
        $isAdmin = auth()->user()?->hasRole('admin');
        
        // Non-admin always scopes to their assigned store
        $storeFilter = (!$isAdmin && $activeStoreId) ? $activeStoreId : null;

        $analytics = $analyticsService->getAnalytics(storeId: $storeFilter, trendDays: 7);

        $todaySales = Invoice::where('status', 'confirmed')
            ->whereDate('invoice_date', $today)
            ->when($storeFilter, fn($q) => $q->where('store_id', $storeFilter))
            ->sum('net_total');

        $todayInvoicesCount = Invoice::where('status', 'confirmed')
            ->whereDate('invoice_date', $today)
            ->when($storeFilter, fn($q) => $q->where('store_id', $storeFilter))
            ->count();

        $totalCustomersDebt = Customer::where('is_active', true)->sum('current_balance');
        
        // Low stock items: if store scoped, check StoreStock, else Item global
        if ($storeFilter) {
            $lowStockItems = Item::active()
                ->whereHas('storeStocks', function($q) use ($storeFilter) {
                    $q->where('store_id', $storeFilter)->whereColumn('quantity', '<=', 'min_stock');
                })
                ->take(5)
                ->get();
        } else {
            $lowStockItems = Item::active()->lowStock()->take(5)->get();
        }

        $recentInvoices = Invoice::with(['customer', 'store'])
            ->when($storeFilter, fn($q) => $q->where('store_id', $storeFilter))
            ->latest()
            ->take(6)
            ->get();

        $periodic = $profitService->getPeriodicProfits(
            now()->startOfMonth()->toDateString(), 
            $today,
            $storeFilter
        );

        return view('livewire.dashboard', [
            'todaySales'          => $todaySales,
            'todayInvoicesCount'  => $todayInvoicesCount,
            'totalCustomersDebt'  => $totalCustomersDebt,
            'lowStockItems'       => $lowStockItems,
            'recentInvoices'      => $recentInvoices,
            'monthlySales'        => $periodic['total_sales'],
            'monthlyGrossProfit'  => $periodic['gross_profit'],
            'monthlyMargin'       => $periodic['margin_percentage'],
            'currentStore'        => $storeFilter ? Store::find($storeFilter) : null,
            'analytics'           => $analytics,
        ])->layout('components.layouts.app', ['title' => 'لوحة التحكم الرئيسية']);
    }
}
