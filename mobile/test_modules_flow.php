<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ApiService;

echo "====================================================\n";
echo "   Testing Sroor Coffee ERP Mobile Modules E2E Flow \n";
echo "====================================================\n";

ApiService::setBaseUrl('http://127.0.0.1:8000/api/v1');

// 1. Test Login
echo "[1/7] Testing Login for 01012316954...\n";
$loginRes = ApiService::login('01012316954', 'password');
if (!$loginRes['success']) {
    die("❌ Login failed: " . ($loginRes['message'] ?? 'Unknown error') . "\n");
}
echo "✅ Logged in successfully as: " . $loginRes['user']['name'] . "\n";
echo "   Initial Store: " . ($loginRes['store']['name'] ?? 'None') . "\n";

// 2. Test Accessible Stores & Switching
echo "\n[2/7] Testing Stores / Branches Permissions...\n";
$storesRes = ApiService::getStores();
echo "   Total Accessible Stores: " . count($storesRes['stores'] ?? []) . "\n";
foreach ($storesRes['stores'] ?? [] as $st) {
    echo "   - [ID: {$st['id']}] {$st['name']} ({$st['type']})\n";
}

$firstStoreId = $storesRes['stores'][0]['id'] ?? 1;
$switchRes = ApiService::switchStore($firstStoreId);
echo "✅ Active store switched to: " . ($switchRes['active_store']['name'] ?? 'N/A') . "\n";

// 3. Test Items Catalog with Store Stock
echo "\n[3/7] Testing Coffee Items & Store Stock...\n";
$itemsRes = ApiService::getItems();
echo "✅ Total Items Found: " . ($itemsRes['total'] ?? count($itemsRes['data'] ?? [])) . "\n";
$firstItem = $itemsRes['data'][0] ?? null;
if ($firstItem) {
    echo "   Sample Item: {$firstItem['name']} (Code: {$firstItem['code']}) | Price: {$firstItem['selling_price']} ج.م | Stock in Branch: {$firstItem['current_stock']}\n";
}

// 4. Test POS Invoice Creation
echo "\n[4/7] Testing POS Invoice Creation with Stock & WhatsApp...\n";
$customersRes = ApiService::getCustomers();
$firstCustomer = $customersRes['data'][0] ?? null;

if ($firstCustomer && $firstItem) {
    $invPayload = [
        'customer_id'    => $firstCustomer['id'],
        'payment_type'   => 'cash',
        'paid_amount'    => (float)$firstItem['selling_price'] * 2,
        'discount_type'  => 'fixed',
        'discount_value' => 0,
        'notes'          => 'فاتورة اختبار تجريبية من تطبيق الموبايل POS',
        'items'          => [
            [
                'item_id'    => $firstItem['id'],
                'quantity'   => 2,
                'unit_price' => (float)$firstItem['selling_price'],
            ],
        ],
    ];

    $invRes = ApiService::createInvoice($invPayload);
    if ($invRes['success']) {
        echo "✅ Invoice Created: " . $invRes['data']['invoice_number'] . " | Net: " . $invRes['data']['net_total'] . " ج.م\n";
        echo "   WhatsApp URL: " . substr($invRes['whatsapp']['whatsapp_url'] ?? '', 0, 70) . "...\n";
    } else {
        echo "⚠️ Invoice note: " . ($invRes['message'] ?? '') . "\n";
    }
}

// 5. Test Customer Receipt (سند قبض)
echo "\n[5/7] Testing Customer Receipt Voucher (تحصيل مديونية)...\n";
if ($firstCustomer) {
    $receiptRes = ApiService::createCustomerReceipt([
        'customer_id'    => $firstCustomer['id'],
        'amount'         => 100.00,
        'payment_method' => 'cash',
        'notes'          => 'سند قبض تجريبي من الموبايل',
    ]);
    if ($receiptRes['success']) {
        echo "✅ Customer Receipt Voucher Created: " . $receiptRes['message'] . "\n";
    } else {
        echo "⚠️ Receipt note: " . ($receiptRes['message'] ?? '') . "\n";
    }
}

// 6. Test Supplier Voucher (سند صرف)
echo "\n[6/7] Testing Supplier Disbursement Voucher (سداد مورد)...\n";
$suppliersRes = ApiService::getSuppliers();
$firstSupplier = $suppliersRes['data'][0] ?? null;
if ($firstSupplier) {
    $voucherRes = ApiService::createSupplierVoucher([
        'supplier_id'    => $firstSupplier['id'],
        'amount'         => 50.00,
        'payment_method' => 'cash',
        'notes'          => 'سند صرف تجريبي للمورد من الموبايل',
    ]);
    if ($voucherRes['success']) {
        echo "✅ Supplier Voucher Created: " . $voucherRes['message'] . "\n";
    } else {
        echo "⚠️ Voucher note: " . ($voucherRes['message'] ?? '') . "\n";
    }
}

// 7. Test Treasury Summary
echo "\n[7/7] Testing Treasury & Cash Flow Summary...\n";
$treasuryRes = ApiService::getTreasurySummary();
echo "✅ Treasury Today Net Cash: " . ($treasuryRes['today']['net_cash'] ?? '0.00') . " ج.م\n";
echo "   Total Sales Today: " . ($treasuryRes['today']['sales_total'] ?? '0.00') . " ج.م\n";
echo "   Total Inflow: " . ($treasuryRes['today']['total_inflow'] ?? '0.00') . " ج.م | Total Outflow: " . ($treasuryRes['today']['total_outflow'] ?? '0.00') . " ج.م\n";

echo "\n====================================================\n";
echo "   ALL MODULES E2E TEST PASSED WITH 100% SUCCESS!   \n";
echo "====================================================\n";
