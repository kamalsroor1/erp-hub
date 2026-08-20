<?php

namespace App\Actions\Tenants;

use App\Contracts\TenantProvisionerInterface;
use App\DTOs\CreateTenantDTO;
use App\Models\Tenant;

class ProvisionTenantAction
{
    public function __construct(
        protected TenantProvisionerInterface $provisioner
    ) {}

    public function execute(CreateTenantDTO $dto): Tenant
    {
        return $this->provisioner->provision($dto);
    }
}
