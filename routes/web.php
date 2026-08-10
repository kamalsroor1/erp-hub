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
    // Dashboard
    Route::get('/', Dashboard::class)->name('dashboard');

    // Invoices & POS
    Route::get('/invoices', InvoiceIndex::class)->name('invoices.index');
    Route::get('/invoices/create', InvoiceCreate::class)->name('invoices.create');
    Route::get('/invoices/{id}', InvoiceShow::class)->name('invoices.show');
    Route::get('/invoices/{id}/edit', App\Livewire\InvoiceEdit::class)->name('invoices.edit');

    // Printing Routes
    Route::get('/invoices/{id}/print/thermal', function ($id) {
        $invoice = Invoice::with(['customer', 'items.item'])->findOrFail($id);
        return view('layouts.print-thermal', compact('invoice'));
    })->name('invoices.print.thermal');

    Route::get('/invoices/{id}/print/a4', function ($id) {
        $invoice = Invoice::with(['customer', 'items.item'])->findOrFail($id);
        return view('layouts.print-a4', compact('invoice'));
    })->name('invoices.print.a4');

    // Items & Inventory
    Route::get('/items', ItemIndex::class)->name('items.index');

    // Customers & Statements
    Route::get('/customers', CustomerIndex::class)->name('customers.index');
    Route::get('/customers/{id}/statement', CustomerStatement::class)->name('customers.statement');

    // Suppliers & Purchases & Statements
    Route::get('/suppliers', SupplierIndex::class)->name('suppliers.index');
    Route::get('/suppliers/{id}/statement', SupplierStatement::class)->name('suppliers.statement');
    Route::get('/purchases', PurchaseIndex::class)->name('purchases.index');
    Route::get('/purchases/create', PurchaseCreate::class)->name('purchases.create');

    // Returns & Reversals
    Route::get('/returns', ReturnIndex::class)->name('returns.index');
    Route::get('/returns/create', ReturnCreate::class)->name('returns.create');

    // Financial & Profit Reports (Admin & Accountant only)
    Route::get('/reports', ReportsIndex::class)->name('reports.index')->middleware('role:admin|accountant');

    // Operational Expenses & Supplies
    Route::get('/expenses', App\Livewire\ExpenseIndex::class)->name('expenses.index');

    // Coffee Blending Master & Roastery Recipe
    Route::get('/coffee-blender', App\Livewire\CoffeeBlender::class)->name('coffee.blender');

    // Daily Journal & Cashier Shifts (يوم بيوم)
    Route::get('/daily-journal', App\Livewire\DailyJournalIndex::class)->name('daily.journal');
    Route::get('/shifts', App\Livewire\DailyJournalIndex::class)->name('shifts.index');

    // Auth, Profile & User Management (Admin only)
    Route::get('/profile', App\Livewire\Auth\Profile::class)->name('profile');
    Route::get('/users', App\Livewire\Auth\UserManager::class)->name('users.index')->middleware('role:admin');

    // Excel & CSV Exports
    Route::get('/customers/{id}/export-csv', [App\Http\Controllers\ExportController::class, 'exportCustomerStatement'])->name('customers.export.csv');
    Route::get('/suppliers/{id}/export-csv', [App\Http\Controllers\ExportController::class, 'exportSupplierStatement'])->name('suppliers.export.csv');
    Route::get('/items/export-csv', [App\Http\Controllers\ExportController::class, 'exportInventory'])->name('items.export.csv');

    // Theme Toggle (Dark / Light Mode)
    Route::post('/theme-toggle', function (\Illuminate\Http\Request $request) {
        $theme = $request->input('theme', 'dark');
        if (in_array($theme, ['dark', 'light']) && Auth::check()) {
            Auth::user()->update(['theme_preference' => $theme]);
        }
        return response()->json(['status' => 'success', 'theme' => $theme]);
    })->name('theme.toggle');
});
