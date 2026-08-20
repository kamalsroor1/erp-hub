<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TreasuryController;
use App\Http\Middleware\MobileApiAuth;

// 1. Guest Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 2. Protected Inertia SPA Routes
Route::middleware(MobileApiAuth::class)->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Branch Switching
    Route::post('/stores/switch', [StoreController::class, 'switchStore'])->name('stores.switch');

    // Customers & Statements
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::get('/customers/{id}/statement', [CustomerController::class, 'statement'])->name('customers.statement');

    // Suppliers & Statements
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::put('/suppliers/{id}', [SupplierController::class, 'update'])->name('suppliers.update');
    // Purchases & Coffee Bean Inbound
    Route::get('/purchases', [\App\Http\Controllers\PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/{id}', [\App\Http\Controllers\PurchaseController::class, 'show'])->name('purchases.show');
    Route::post('/purchases', [\App\Http\Controllers\PurchaseController::class, 'store'])->name('purchases.store');
    Route::post('/purchases/{id}/cancel', [\App\Http\Controllers\PurchaseController::class, 'cancel'])->name('purchases.cancel');

    // Coffee Items & Inventory
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');

    // Mobile POS (Fast Cashier)
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');

    // Coffee Blender Master (Custom Blend Studio)
    Route::get('/blender', [\App\Http\Controllers\BlenderController::class, 'index'])->name('blender.index');
    Route::post('/blender/checkout', [\App\Http\Controllers\BlenderController::class, 'checkout'])->name('blender.checkout');

    // Sales Invoices & WhatsApp & Printing & Cancel
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{id}/print/thermal', [InvoiceController::class, 'printThermal'])->name('invoices.print.thermal');
    Route::get('/invoices/{id}/print/a4', [InvoiceController::class, 'printA4'])->name('invoices.print.a4');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::post('/invoices/{id}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');

    // Customer Statement Print
    Route::get('/customers/{id}/statement/print', [CustomerController::class, 'printStatement'])->name('customers.statement.print');

    // Payments & Collections (Receipts & Disbursements)
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/customer-receipt', [PaymentController::class, 'customerReceipt'])->name('payments.customer_receipt');
    Route::post('/payments/supplier-voucher', [PaymentController::class, 'supplierVoucher'])->name('payments.supplier_voucher');

    // Cashier Shifts & Z-Report
    Route::get('/shifts', [\App\Http\Controllers\ShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts/open', [\App\Http\Controllers\ShiftController::class, 'open'])->name('shifts.open');
    Route::post('/shifts/close', [\App\Http\Controllers\ShiftController::class, 'close'])->name('shifts.close');
    Route::get('/shifts/{id}/z-report', [\App\Http\Controllers\ShiftController::class, 'zReport'])->name('shifts.z_report');

    // Expenses & Petty Cash
    Route::get('/expenses', [\App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [\App\Http\Controllers\ExpenseController::class, 'store'])->name('expenses.store');
    Route::put('/expenses/{id}', [\App\Http\Controllers\ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{id}', [\App\Http\Controllers\ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Treasury Overview
    Route::get('/treasury', [TreasuryController::class, 'index'])->name('treasury.index');

    // Profit & Loss Reports & Business Analytics
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/items/{id}/card', [\App\Http\Controllers\ReportController::class, 'itemCard'])->name('reports.item_card');

    // Returns (Sales & Purchases)
    Route::get('/returns', [\App\Http\Controllers\ReturnController::class, 'index'])->name('returns.index');
    Route::post('/returns/sales', [\App\Http\Controllers\ReturnController::class, 'storeSales'])->name('returns.sales');
    Route::post('/returns/purchases', [\App\Http\Controllers\ReturnController::class, 'storePurchases'])->name('returns.purchases');
    Route::post('/returns/{id}/cancel', [\App\Http\Controllers\ReturnController::class, 'cancel'])->name('returns.cancel');

    // Stock Transfers between stores
    Route::get('/transfers', [\App\Http\Controllers\StockTransferController::class, 'index'])->name('transfers.index');
    Route::post('/transfers', [\App\Http\Controllers\StockTransferController::class, 'store'])->name('transfers.store');
    Route::post('/transfers/{id}/cancel', [\App\Http\Controllers\StockTransferController::class, 'cancel'])->name('transfers.cancel');

    // Admin System Settings
    Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');

    // Audit Trail & Activity Logs
    Route::get('/audit-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('audit_logs.index');
});
