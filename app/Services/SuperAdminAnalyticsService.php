<?php

namespace App\Services;

use App\Contracts\SuperAdminDashboardAnalyticsInterface;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Subscription;

class SuperAdminAnalyticsService implements SuperAdminDashboardAnalyticsInterface
{
    /**
     * حساب وتحليل مؤشرات أداء المنصة المركزية
     */
    public function getPlatformMetrics(): array
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $trialTenants = Tenant::where('status', 'trial')->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();

        // Calculate MRR (Monthly Recurring Revenue)
        $monthlyRevenue = Subscription::where('status', 'active')
            ->where('billing_cycle', 'monthly')
            ->sum('amount');

        $yearlyRevenue = Subscription::where('status', 'active')
            ->where('billing_cycle', 'yearly')
            ->sum('amount');

        $mrr = (float)$monthlyRevenue + ((float)$yearlyRevenue / 12);

        return [
            'total_tenants' => $totalTenants,
            'active_tenants' => $activeTenants,
            'trial_tenants' => $trialTenants,
            'suspended_tenants' => $suspendedTenants,
            'mrr' => round($mrr, 2),
        ];
    }

    /**
     * إحصائيات توزيع الباقات
     */
    public function getPlanStatistics(): array
    {
        return Plan::withCount('tenants')->get()->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'price_monthly' => (float)$p->price_monthly,
            'tenants_count' => $p->tenants_count,
        ])->toArray();
    }

    /**
     * أحدث المستأجرين المسجلين
     */
    public function getRecentTenants(int $limit = 5): array
    {
        return Tenant::with(['plan', 'domains'])
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'domain' => $t->domains->first()?->domain ?? ($t->slug . '.' . config('tenancy.central_domains.2', 'sroor-erp.com')),
                'plan_name' => $t->plan?->name ?? 'غير محدد',
                'status' => $t->status,
                'created_at' => $t->created_at->diffForHumans(),
            ])
            ->toArray();
    }
}
