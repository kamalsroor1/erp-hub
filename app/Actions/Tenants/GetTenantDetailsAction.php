<?php

namespace App\Actions\Tenants;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Http\Resources\TenantResource;
use App\Http\Resources\PlanResource;

class GetTenantDetailsAction
{
    /**
     * جلب تفاصيل المستأجر مع مصفوفة الفيتشرز والباقات عبر JsonResources
     */
    public function execute(string $id): array
    {
        $tenant = Tenant::with(['plan', 'domains', 'subscriptions' => fn($q) => $q->latest()])->findOrFail($id);
        $allFeatures = PlanFeature::orderBy('sort_order')->get();
        $groupedFeatures = PlanFeature::groupedByModule();
        $plans = PlanResource::collection(Plan::where('is_active', true)->orderBy('sort_order')->get())->resolve();

        return [
            'tenant' => (new TenantResource($tenant))->resolve(),
            'features' => $allFeatures,
            'grouped_features' => $groupedFeatures,
            'plans' => $plans,
        ];
    }
}
