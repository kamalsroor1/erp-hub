<?php

namespace App\Actions\Tenants;

use App\Models\Tenant;

class ToggleTenantStatusAction
{
    public function execute(Tenant $tenant, string $status, int $extendDays = 0): Tenant
    {
        $updateData = ['status' => $status];

        if ($extendDays > 0) {
            $currentEnd = $tenant->subscription_ends_at && $tenant->subscription_ends_at->isFuture()
                ? $tenant->subscription_ends_at
                : now();
            $updateData['subscription_ends_at'] = $currentEnd->addDays($extendDays);
        }

        $tenant->update($updateData);

        return $tenant;
    }
}
