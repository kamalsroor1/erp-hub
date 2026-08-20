<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\TenantProvisionerInterface::class,
            \App\Services\TenantProvisionerService::class
        );

        $this->app->bind(
            \App\Contracts\TenantFeatureManagerInterface::class,
            \App\Services\TenantFeatureManager::class
        );

        $this->app->bind(
            \App\Contracts\SuperAdminDashboardAnalyticsInterface::class,
            \App\Services\SuperAdminAnalyticsService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Implicitly grant "admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        // Restrict Laravel Pulse dashboard strictly to Admin users
        Gate::define('viewPulse', function ($user) {
            return $user->hasRole('admin');
        });

        // Register Model Observers
        \App\Models\Tenant::observe(\App\Observers\TenantObserver::class);

        // Multi-Tenant Livewire Universal Routing
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::setUpdateRoute(function ($handle, $path = '/livewire/update') {
                return \Illuminate\Support\Facades\Route::post($path, $handle)
                    ->middleware([
                        'web',
                        \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
                    ]);
            });
        }
    }
}
