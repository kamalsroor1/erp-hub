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
                'is_impersonating' => (bool)session('is_impersonating', false),
            ],
            'tenant' => $tenant ? (new \App\Http\Resources\TenantResource($tenant))->resolve() : null,
            'system_theme_color' => fn () => \App\Models\Setting::get('system_theme_color', 'amber'),
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
            'translations' => function () {
                $locale = app()->getLocale();
                $translations = [];

                // 1. Load all PHP translation groups for current locale (e.g. auth, nav, pos, dashboard, inventory, etc.)
                $langPath = lang_path($locale);
                if (is_dir($langPath)) {
                    foreach (glob($langPath . '/*.php') as $file) {
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
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'system_notifications' => function () use ($user) {
                if (!$user) return [];
                $alerts = [];

                // 1. Low stock alert
                $lowStockCount = \App\Models\Item::where('is_active', true)
                    ->whereColumn('current_stock', '<=', 'min_stock_level')
                    ->count();

                if ($lowStockCount > 0) {
                    $alerts[] = [
                        'type' => 'danger',
                        'icon' => '🚨',
                        'title' => "نواقص بالمخزن ({$lowStockCount} صنف)",
                        'description' => 'أصناف بلغت أو تجاوزت حد الطلب الأدنى',
                        'link' => '/purchases/smart-reorder',
                        'link_label' => 'إعادة الطلب الذكي',
                    ];
                }

                // 2. Customer Debt alert
                $debtCount = \App\Models\Customer::where('is_active', true)->where('current_balance', '>', 0)->count();
                if ($debtCount > 0) {
                    $alerts[] = [
                        'type' => 'warning',
                        'icon' => '👥',
                        'title' => "مديونيات عملاء ({$debtCount} عميل)",
                        'description' => 'يوجد عملاء مستحق عليهم مبالغ آجلة بحاجة للتحصيل',
                        'link' => '/customers',
                        'link_label' => 'قائمة العملاء المدينين',
                    ];
                }

                // 3. High Cash in Drawer Alert
                if ($user->can('daily_journal.view') || $user->hasRole('admin')) {
                    try {
                        $treasuryService = app(\App\Services\TreasuryService::class);
                        $balances = $treasuryService->getBalances($activeStore?->id);
                        $cashExpected = (float)($balances['cash']['balance'] ?? 0);
                        if ($cashExpected >= 10000) {
                            $alerts[] = [
                                'type' => 'info',
                                'icon' => '💰',
                                'title' => 'سيولة نقدية عالية بالدرج',
                                'description' => 'يوجد حالياً ' . number_format($cashExpected, 0) . ' ج.م نقداً بالدرج. يُنصح بتوريد الفائض.',
                                'link' => '/daily-journal',
                                'link_label' => 'دفتر اليومية والخزينة',
                            ];
                        }
                    } catch (\Throwable $e) {}
                }

                return $alerts;
            },
        ];
    }
}
