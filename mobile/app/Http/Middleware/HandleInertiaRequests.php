<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Services\ApiService;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user'   => ApiService::getUser(),
                'store'  => ApiService::getStore(),
                'stores' => session('accessible_stores', []),
                'check'  => ApiService::isAuthenticated(),
            ],
            'apiUrl' => ApiService::getBaseUrl(),
            'appInfo' => [
                'current_version' => env('APP_VERSION', '1.0.0'),
                'version_code'    => (int)env('APP_VERSION_CODE', 1),
            ],
            'appUpdate' => fn () => \Illuminate\Support\Facades\Cache::remember('app_update_check_cached', 30, fn () => ApiService::checkAppUpdate()),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
            ],
        ];
    }
}
