<?php

namespace App\Observers;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class TenantObserver
{
    /**
     * Handle the Tenant "created" event.
     */
    public function created(Tenant $tenant): void
    {
        Log::info("Tenant Provisioned Successfully: [{$tenant->id}] - {$tenant->name}");
    }

    /**
     * Handle the Tenant "updated" event.
     */
    public function updated(Tenant $tenant): void
    {
        if ($tenant->isDirty('status')) {
            Log::warning("Tenant Status Changed: [{$tenant->id}] status is now {$tenant->status}");
        }

        if ($tenant->isDirty('enabled_features')) {
            Log::info("Tenant Feature Overrides Updated: [{$tenant->id}]");
        }
    }

    /**
     * Handle the Tenant "deleted" event.
     */
    public function deleted(Tenant $tenant): void
    {
        Log::alert("Tenant Marked for Deletion: [{$tenant->id}]");
    }
}
