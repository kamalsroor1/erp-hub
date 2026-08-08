<?php

use Illuminate\Support\Facades\Route;
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

// Dashboard
Route::get('/', Dashboard::class)->name('dashboard');

// Invoices & POS
Route::get('/invoices', InvoiceIndex::class)->name('invoices.index');
Route::get('/invoices/create', InvoiceCreate::class)->name('invoices.create');
Route::get('/invoices/{id}', InvoiceShow::class)->name('invoices.show');

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

// Financial & Profit Reports
Route::get('/reports', ReportsIndex::class)->name('reports.index');

// Coffee Blending Master & Roastery Recipe
Route::get('/coffee-blender', App\Livewire\CoffeeBlender::class)->name('coffee.blender');

// Cashier Shifts & Drawer Z-Reports
Route::get('/shifts', App\Livewire\CashShiftManager::class)->name('shifts.index');

// Excel & CSV Exports
Route::get('/customers/{id}/export-csv', [App\Http\Controllers\ExportController::class, 'exportCustomerStatement'])->name('customers.export.csv');
Route::get('/suppliers/{id}/export-csv', [App\Http\Controllers\ExportController::class, 'exportSupplierStatement'])->name('suppliers.export.csv');
Route::get('/items/export-csv', [App\Http\Controllers\ExportController::class, 'exportInventory'])->name('items.export.csv');
