<?php

return [
    App\Providers\AppServiceProvider::class,
    Inertia\ServiceProvider::class,
    Stancl\Tenancy\TenancyServiceProvider::class,
    App\Providers\TenancyServiceProvider::class,
    Laravel\Telescope\TelescopeServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
];
