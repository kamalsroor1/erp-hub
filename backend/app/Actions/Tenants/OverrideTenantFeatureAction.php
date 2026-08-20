<?php

namespace App\Actions\Tenants;

use App\Contracts\TenantFeatureManagerInterface;
use App\Models\Tenant;

class OverrideTenantFeatureAction
{
    public function __construct(
        protected TenantFeatureManagerInterface $featureManager
    ) {}

    public function execute(Tenant $tenant, string $featureKey): array
    {
        return $this->featureManager->toggleFeatureOverride($tenant, $featureKey);
    }
}
