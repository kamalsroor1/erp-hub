<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Item;
use App\Models\Customer;
use App\Services\TreasuryService;

class NotificationCenter extends Component
{
    public bool $isOpen = false;

    public function toggleDropdown()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function closeDropdown()
    {
        $this->isOpen = false;
    }

    public function render()
    {
        $user = auth()->user();
        $storeId = session('current_store_id') ?? $user?->getCurrentStore()?->id;

        $notifications = [];

        // 1. Low Stock & Out of Stock Alerts
        $lowStockQuery = Item::active()->where(function ($q) {
            $q->whereColumn('current_stock', '<=', 'min_stock_level')
              ->orWhere('current_stock', '<=', 0);
        });

        $lowStockCount = $lowStockQuery->count();
        if ($lowStockCount > 0) {
            $criticalSample = (clone $lowStockQuery)->take(3)->get();
            $itemNames = $criticalSample->pluck('name')->implode('، ');
            if ($lowStockCount > 3) {
                $itemNames .= " وأخرى...";
            }

            $notifications[] = [
                'type'        => 'danger',
                'icon'        => '🚨',
                'title'       => "نواقص بالمخزن ({$lowStockCount} صنف)",
                'description' => "أصناف بلغت حد الخطر: {$itemNames}",
                'link'        => route('purchases.reorder'),
                'link_label'  => 'مساعد المشتريات الذكي ←',
            ];
        }

        // 2. Overdue Customer Balances Alerts
        $debtCustomersCount = Customer::where('is_active', true)->where('current_balance', '>', 0)->count();
        $totalDebt = Customer::where('is_active', true)->sum('current_balance');
        if ($debtCustomersCount > 0 && bccomp((string)$totalDebt, '0.000', 3) > 0) {
            $notifications[] = [
                'type'        => 'warning',
                'icon'        => '👥',
                'title'       => "مستحقات آجلة للتحصيل",
                'description' => "يوجد {$debtCustomersCount} عميل عليهم مديونيات بإجمالي " . number_format((float)$totalDebt, 0) . " ج.م",
                'link'        => route('customers.index'),
                'link_label'  => 'سجل العملاء والتحصيل ←',
            ];
        }

        // 3. Treasury Liquidity / Cash in drawer
        if ($user?->can('daily_journal.view') || $user?->hasRole('admin')) {
            $treasuryService = app(TreasuryService::class);
            $balances = $treasuryService->getBalances($storeId);
            $cashExpected = (float)($balances['cash']['balance'] ?? 0);

            if ($cashExpected >= 10000) {
                $notifications[] = [
                    'type'        => 'info',
                    'icon'        => '💰',
                    'title'       => "سيولة نقدية عالية بالدرج",
                    'description' => "يوجد حالياً " . number_format($cashExpected, 0) . " ج.م نقداً بالدرج. يُنصح بتحويل الفائض للبنك أو المحفظة.",
                    'link'        => route('daily.journal'),
                    'link_label'  => 'دفتر اليومية والتحويلات ←',
                ];
            }
        }

        $totalAlertsCount = count($notifications);

        return view('livewire.notification-center', [
            'notifications'    => $notifications,
            'totalAlertsCount' => $totalAlertsCount,
        ]);
    }
}
