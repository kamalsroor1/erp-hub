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
            'locale' => app()->getLocale(),
            'direction' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr',
            'translations' => function () {
                $locale = app()->getLocale();
                $translations = [];

                // 1. Load all PHP translation groups dynamically
                $localePath = lang_path($locale);
                if (is_dir($localePath)) {
                    foreach (glob($localePath . '/*.php') as $file) {
                        $group = basename($file, '.php');
                        $translations[$group] = trans($group);
                    }
                }

                // 2. Load Fallback 'ar' if current locale is missing groups
                if ($locale !== 'ar') {
                    $arPath = lang_path('ar');
                    if (is_dir($arPath)) {
                        foreach (glob($arPath . '/*.php') as $file) {
                            $group = basename($file, '.php');
                            if (!isset($translations[$group])) {
                                $translations[$group] = trans($group);
                            }
                        }
                    }
                }

                // 3. Merge JSON translation strings
                $jsonFile = lang_path($locale . '.json');
                if (file_exists($jsonFile)) {
                    $json = json_decode(file_get_contents($jsonFile), true) ?: [];
                    $translations = array_merge($translations, $json);
                }

                return $translations;
            },
            'appUpdate' => fn () => \Illuminate\Support\Facades\Cache::remember('app_update_check_cached', 30, fn () => ApiService::checkAppUpdate()),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
            ],
        ];
    }
}
