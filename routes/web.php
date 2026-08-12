<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\InvoiceCreate;
use App\Livewire\InvoiceIndex;
use App\Livewire\InvoiceShow;
use App\Livewire\ItemIndex;
use App\Livewire\CustomerIndex;
use App\Livewire\CustomerStatement;
use App\Livewire\SupplierIndex;
use App\Livewire\SupplierStatement;
use App\Livewire\PurchaseCreate;
use App\Livewire\PurchaseIndex;
use App\Livewire\ReturnCreate;
use App\Livewire\ReturnIndex;
use App\Livewire\ReportsIndex;
use App\Models\Invoice;

// 1. Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// 2. Logout Route
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout')->middleware('auth');

// 3. Protected POS, ERP & Inventory Routes
Route::middleware('auth')->group(function () {
    // Dashboard (All authenticated users can see dashboard)
    Route::get('/', Dashboard::class)->name('dashboard');

    // Invoices & POS
    Route::get('/invoices', InvoiceIndex::class)->name('invoices.index')->middleware('can:invoices.view');
    Route::get('/invoices/create', InvoiceCreate::class)->name('invoices.create')->middleware('can:pos.access');
    Route::get('/invoices/{id}', InvoiceShow::class)->name('invoices.show')->middleware('can:invoices.view');
    Route::get('/invoices/{id}/edit', App\Livewire\InvoiceEdit::class)->name('invoices.edit')->middleware('can:invoices.edit');

    // Printing Routes
    Route::get('/invoices/{id}/print/thermal', function ($id) {
        $invoice = Invoice::with(['customer', 'items.item'])->findOrFail($id);
        return view('layouts.print-thermal', compact('invoice'));
    })->name('invoices.print.thermal')->middleware('can:invoices.view');

    Route::get('/invoices/{id}/print/a4', function ($id) {
        $invoice = Invoice::with(['customer', 'items.item'])->findOrFail($id);
        return view('layouts.print-a4', compact('invoice'));
    })->name('invoices.print.a4')->middleware('can:invoices.view');

    // Items & Inventory
    Route::get('/items', ItemIndex::class)->name('items.index')->middleware('can:items.view');

    // Multi-Store, Vans & Warehouse Management
    Route::get('/stores', App\Livewire\StoreIndex::class)->name('stores')->middleware('can:stores.manage');
    Route::get('/store-stocks', App\Livewire\StoreStockIndex::class)->name('store-stocks')->middleware('can:items.view');
    Route::get('/stock-transfers', App\Livewire\StockTransferIndex::class)->name('stock-transfers')->middleware('can:transfers.view');
    Route::get('/stock-transfers/create', App\Livewire\StockTransferCreate::class)->name('stock-transfers.create')->middleware('can:transfers.create');

    // Customers & Statements
    Route::get('/customers', CustomerIndex::class)->name('customers.index')->middleware('can:customers.manage');
    Route::get('/customers/{id}/statement', CustomerStatement::class)->name('customers.statement')->middleware('can:customers.statement');

    // Suppliers & Purchases & Statements
    Route::get('/suppliers', SupplierIndex::class)->name('suppliers.index')->middleware('can:suppliers.manage');
    Route::get('/suppliers/{id}/statement', SupplierStatement::class)->name('suppliers.statement')->middleware('can:suppliers.manage');
    Route::get('/purchases', PurchaseIndex::class)->name('purchases.index')->middleware('can:purchases.view');
    Route::get('/purchases/create', PurchaseCreate::class)->name('purchases.create')->middleware('can:purchases.create');

    // Returns & Reversals
    Route::get('/returns', ReturnIndex::class)->name('returns.index')->middleware('can:returns.manage');
    Route::get('/returns/create', ReturnCreate::class)->name('returns.create')->middleware('can:returns.manage');

    // Financial & Profit Reports (Admin & Accountant / reports.view)
    Route::get('/reports', ReportsIndex::class)->name('reports.index')->middleware('can:reports.view');

    // Operational Expenses & Supplies
    Route::get('/expenses', App\Livewire\ExpenseIndex::class)->name('expenses.index')->middleware('can:expenses.manage');

    // Coffee Blending Master & Roastery Recipe
    Route::get('/coffee-blender', App\Livewire\CoffeeBlender::class)->name('coffee.blender')->middleware('can:items.create');

    // Daily Journal & Cashier Shifts (يوم بيوم)
    Route::get('/daily-journal', App\Livewire\DailyJournalIndex::class)->name('daily.journal')->middleware('can:daily_journal.view');
    Route::get('/shifts', App\Livewire\DailyJournalIndex::class)->name('shifts.index')->middleware('can:daily_journal.view');

    // Auth, Profile, Trash, Activity Logs & User Management
    Route::get('/activity-logs', App\Livewire\ActivityLogIndex::class)->name('activity-logs.index')->middleware('can:logs.view');
    Route::get('/trash', App\Livewire\TrashIndex::class)->name('trash.index')->middleware('can:trash.access');
    Route::get('/profile', App\Livewire\Auth\Profile::class)->name('profile');
    Route::get('/users', App\Livewire\Auth\UserManager::class)->name('users.index')->middleware('can:roles.manage');
    Route::get('/roles', App\Livewire\Auth\RolePermissionManager::class)->name('roles.index')->middleware('can:roles.manage');

    // Excel & CSV Exports
    Route::get('/customers/{id}/export-csv', [App\Http\Controllers\ExportController::class, 'exportCustomerStatement'])->name('customers.export.csv')->middleware('can:customers.statement');
    Route::get('/suppliers/{id}/export-csv', [App\Http\Controllers\ExportController::class, 'exportSupplierStatement'])->name('suppliers.export.csv')->middleware('can:suppliers.manage');
    Route::get('/items/export-csv', [App\Http\Controllers\ExportController::class, 'exportInventory'])->name('items.export.csv')->middleware('can:items.view');

    // Theme Toggle (Dark / Light Mode)
    Route::post('/theme-toggle', function (\Illuminate\Http\Request $request) {
        $theme = $request->input('theme', 'dark');
        if (in_array($theme, ['dark', 'light']) && Auth::check()) {
            Auth::user()->update(['theme_preference' => $theme]);
        }
        return response()->json(['status' => 'success', 'theme' => $theme]);
    })->name('theme.toggle');

    // Store Switcher (Fast active branch/van switch for authorized users)
    Route::post('/store/switch', function (\Illuminate\Http\Request $request) {
        $storeId = (int)$request->input('store_id');
        $store = \App\Models\Store::where('id', $storeId)->where('is_active', true)->first();

        if ($store) {
            $user = Auth::user();
            if ($user->hasRole('admin') || $user->stores()->where('stores.id', $storeId)->exists() || (int)$user->default_store_id === $storeId) {
                session(['current_store_id' => $storeId]);
                return response()->json(['status' => 'success', 'store' => $store]);
            }
        }

        return response()->json(['status' => 'error', 'message' => 'غير مصرح'], 403);
    })->name('store.switch');
});

// PWA Assets Routing with dynamic canonical URLs and proper headers
Route::get('/manifest.json', function () {
    $baseUrl = url('/');
    $manifest = [
        'id' => 'sroor-coffee-pos-app',
        'name' => 'سرور كوفي | نظام إدارة الفواتير والمخزون',
        'short_name' => 'سرور POS',
        'description' => 'تطبيق سرور لإدارة مبيعات وفواتير ومخزون مطاحن البن',
        'start_url' => $baseUrl . '/',
        'scope' => $baseUrl . '/',
        'display' => 'standalone',
        'background_color' => '#020617',
        'theme_color' => '#0f172a',
        'orientation' => 'portrait-primary',
        'dir' => 'rtl',
        'lang' => 'ar',
        'prefer_related_applications' => false,
        'icons' => [
            [
                'src' => asset('logo.png'),
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => asset('logo.png'),
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
            [
                'src' => asset('logo.png'),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => asset('logo.png'),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
        ],
    ];

    return response()->json($manifest, 200, [
        'Content-Type' => 'application/manifest+json; charset=utf-8',
        'Cache-Control' => 'no-cache',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
});

Route::get('/sw.js', function () {
    $path = public_path('sw.js');
    if (!file_exists($path)) {
        return response('console.log("SW not found");', 404, ['Content-Type' => 'application/javascript']);
    }
    return response()->file($path, [
        'Content-Type' => 'application/javascript; charset=utf-8',
        'Service-Worker-Allowed' => '/',
        'Cache-Control' => 'no-cache',
    ]);
});
