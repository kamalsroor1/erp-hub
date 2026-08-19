<?php

namespace App\Contracts;

interface SuperAdminDashboardAnalyticsInterface
{
    /**
     * حساب وتحليل مؤشرات أداء المنصة المركزية
     */
    public function getPlatformMetrics(): array;

    /**
     * إحصائيات توزيع الباقات
     */
    public function getPlanStatistics(): array;

    /**
     * أحدث المستأجرين المسجلين
     */
    public function getRecentTenants(int $limit = 5): array;
}
