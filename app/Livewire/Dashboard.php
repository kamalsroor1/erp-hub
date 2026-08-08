<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Purchase;
use App\Services\ProfitService;
use App\Livewire\Traits\RequiresAuth;

class Dashboard extends Component
{
    use RequiresAuth;

    public function render(ProfitService $profitService)
    {
        $today = now()->toDateString();

        $todaySales = Invoice::where('status', 'confirmed')
            ->whereDate('invoice_date', $today)
            ->sum('net_total');

        $todayInvoicesCount = Invoice::where('status', 'confirmed')
            ->whereDate('invoice_date', $today)
            ->count();

        $totalCustomersDebt = Customer::where('is_active', true)->sum('current_balance');
        $lowStockItems = Item::active()->lowStock()->take(5)->get();
        $recentInvoices = Invoice::with('customer')->latest()->take(6)->get();

        $periodic = $profitService->getPeriodicProfits(now()->startOfMonth()->toDateString(), $today);

        return view('livewire.dashboard', [
            'todaySales'          => $todaySales,
            'todayInvoicesCount'  => $todayInvoicesCount,
            'totalCustomersDebt'  => $totalCustomersDebt,
            'lowStockItems'       => $lowStockItems,
            'recentInvoices'      => $recentInvoices,
            'monthlySales'        => $periodic['total_sales'],
            'monthlyGrossProfit'  => $periodic['gross_profit'],
            'monthlyMargin'       => $periodic['margin_percentage'],
        ])->layout('components.layouts.app', ['title' => 'لوحة التحكم الرئيسية']);
    }
}
