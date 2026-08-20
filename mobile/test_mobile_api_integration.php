<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ApiService;

echo "=== 🚀 1. فحص الاتصال بالباك إند ===" . PHP_EOL;
$ping = ApiService::testConnection('http://127.0.0.1:8000/api/v1');
echo "نتيجة الفحص: " . json_encode($ping, JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;

echo "=== 🔐 2. فحص تسجيل الدخول عبر API ===" . PHP_EOL;
$login = ApiService::login('01012316954', 'password');
echo "نتيجة تسجيل الدخول: " . json_encode($login, JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;

if ($login['success'] ?? false) {
    echo "=== 👥 3. إضافة عميل تجريبي عبر الموبايل ===" . PHP_EOL;
    $newCustomer = ApiService::createCustomer([
        'name'            => 'كافيه سحر البن (تجريبي)',
        'phone'           => '01099887766',
        'address'         => 'القاهرة - المعادي',
        'opening_balance' => 1500.500,
        'notes'           => 'عميل جديد تم تسجيله من تطبيق الموبايل',
    ]);
    echo "إضافة عميل: " . json_encode($newCustomer, JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;

    echo "=== 📋 4. جلب قائمة العملاء ===" . PHP_EOL;
    $customers = ApiService::getCustomers();
    echo "قائمة العملاء: " . count($customers['data'] ?? []) . " عميل" . PHP_EOL;
    echo "إجمالي الديون: " . ($customers['summary']['total_receivable'] ?? 0) . " ج.م" . PHP_EOL . PHP_EOL;

    $cust = $customers['data'][0] ?? null;
    if ($cust) {
        echo "=== 📄 5. كشف حساب العميل ({$cust['name']}) ===" . PHP_EOL;
        $statement = ApiService::getCustomerStatement($cust['id']);
        echo "كشف الحساب: " . json_encode($statement['summary'] ?? [], JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;
    }

    echo "=== 🏭 6. إضافة مورد تجريبي عبر الموبايل ===" . PHP_EOL;
    $newSupplier = ApiService::createSupplier([
        'name'            => 'مؤسسة البن البرازيلي (تجريبي)',
        'phone'           => '01234567890',
        'address'         => 'الإسكندرية - الميناء',
        'opening_balance' => 50000.000,
        'notes'           => 'مورد خامات بن أخضر',
    ]);
    echo "إضافة مورد: " . json_encode($newSupplier, JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;

    echo "=== 📋 7. جلب قائمة الموردين ===" . PHP_EOL;
    $suppliers = ApiService::getSuppliers();
    echo "قائمة الموردين: " . count($suppliers['data'] ?? []) . " مورد" . PHP_EOL;
    echo "إجمالي مستحقات الموردين: " . ($suppliers['summary']['total_payable'] ?? 0) . " ج.م" . PHP_EOL . PHP_EOL;

    $supp = $suppliers['data'][0] ?? null;
    if ($supp) {
        echo "=== 📄 8. كشف حساب المورد ({$supp['name']}) ===" . PHP_EOL;
        $suppStatement = ApiService::getSupplierStatement($supp['id']);
        echo "كشف الحساب: " . json_encode($suppStatement['summary'] ?? [], JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;
    }
}
