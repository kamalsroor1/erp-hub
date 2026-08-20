<?php

namespace App\Contracts;

use App\DTOs\CreateTenantDTO;
use App\Models\Tenant;

interface TenantProvisionerInterface
{
    /**
     * إنشاء وتوليد مستأجر جديد وقاعدة بياناته واشتراكه تلقائياً
     */
    public function provision(CreateTenantDTO $dto): Tenant;
}
