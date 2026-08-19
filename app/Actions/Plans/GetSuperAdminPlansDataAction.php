<?php

namespace App\Actions\Plans;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Http\Resources\PlanResource;

class GetSuperAdminPlansDataAction
{
    /**
     * جلب بيانات الباقات والميزات للوحة الإدارة عبر PlanResource
     */
    public function execute(): array
    {
        $plans = Plan::withCount('tenants')->orderBy('sort_order')->get();
        $groupedFeatures = PlanFeature::groupedByModule();

        return [
            'plans' => PlanResource::collection($plans)->resolve(),
            'grouped_features' => $groupedFeatures,
        ];
    }
}
