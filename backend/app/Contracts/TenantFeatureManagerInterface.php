<?php

namespace App\Contracts;

use App\Models\Tenant;

interface TenantFeatureManagerInterface
{
    /**
     * التحقق من توفر ميزة معينة للمستأجر
     */
    public function isFeatureEnabled(Tenant $tenant, string $featureKey): bool;

    /**
     * تفعيل أو تعطيل استثناء يدوي لميزة للمستأجر
     */
    public function toggleFeatureOverride(Tenant $tenant, string $featureKey): array;

    /**
     * جلب كافة الفيتشرز المتاحة للمستأجر
     */
    public function resolveAllFeatures(Tenant $tenant): array;
}
