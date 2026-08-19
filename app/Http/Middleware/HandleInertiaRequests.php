<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Store;
use App\Models\CashShift;

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
        $user = $request->user();
        $tenant = function_exists('tenant') ? tenant() : null;

        // Current Active Store Resolution
        $activeStore = null;
        $stores = [];

        if ($user) {
            $currentStoreId = session('current_store_id');
            if ($currentStoreId) {
                $activeStore = Store::where('id', $currentStoreId)->where('is_active', true)->first();
            }

            if (!$activeStore) {
                $activeStore = $user->getCurrentStore();
            }

            $stores = $user->hasRole('admin')
                ? Store::where('is_active', true)->orderBy('is_main', 'desc')->get()
                : $user->stores()->where('is_active', true)->get();
        }

        // Active Cash Shift
        $activeShift = null;
        if ($user && $activeStore) {
            $activeShift = CashShift::where('store_id', $activeStore->id)
                ->where('status', 'open')
                ->latest('id')
                ->first();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? (new \App\Http\Resources\UserResource($user))->resolve() : null,
            ],
            'tenant' => $tenant ? (new \App\Http\Resources\TenantResource($tenant))->resolve() : null,
            'activeStore' => $activeStore ? [
                'id' => $activeStore->id,
                'name' => $activeStore->name,
                'type' => $activeStore->type,
                'is_main' => (bool)$activeStore->is_main,
            ] : null,
            'stores' => $stores,
            'activeShift' => $activeShift ? [
                'id' => $activeShift->id,
                'shift_number' => $activeShift->shift_number ?? $activeShift->id,
                'opened_at' => $activeShift->opened_at,
                'opening_cash_balance' => (float)$activeShift->opening_cash_balance,
            ] : null,
            'locale' => fn () => app()->getLocale(),
            'translations' => fn () => [
                'auth' => trans('auth'),
                'nav' => trans('nav'),
                'pos' => trans('pos'),
                'super' => trans('super'),
                'dashboard' => trans('dashboard'),
                'common' => trans('common'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
        ];
    }
}
