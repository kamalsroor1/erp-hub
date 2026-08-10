import paramiko
import sys

# Fix Windows Unicode Output
if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except Exception:
        pass

HOST = '145.79.20.98'
PORT = 65002
USER = 'u910151740'
PASS = 'Ks@Rr12699'
TARGET = '/home/u910151740/domains/sroor.baraa-solutions.com/public_html'
TARGET_FM = '/home/u910151740/domains/baraa-solutions.com/public_html/sroor'
PHP84 = '/opt/alt/php84/usr/bin/php'

tinker_code = r"""
$stockService = app(\App\Services\StockService::class);

$item1 = \App\Models\Item::firstOrCreate(
    ['code' => 'COF-001'],
    [
        'name'              => 'بن برازيلي سانتوس فاخر',
        'category'          => 'بن وتوليفات',
        'unit'              => 'كجم',
        'current_stock'     => '0.000',
        'cost_price'        => '220.000',
        'weighted_avg_cost' => '220.000',
        'selling_price'     => '280.000',
        'min_stock_level'   => '10.000',
        'is_active'         => true,
    ]
);
if (bccomp((string)$item1->current_stock, '0.000', 3) == 0) {
    $stockService->depositStock($item1, '100.000', '220.000', 'manual_deposit', 'رصيد افتتاحي لبداية التشغيل');
}

$item2 = \App\Models\Item::firstOrCreate(
    ['code' => 'COF-002'],
    [
        'name'              => 'بن كولومبي سوبريمو',
        'category'          => 'بن وتوليفات',
        'unit'              => 'كجم',
        'current_stock'     => '0.000',
        'cost_price'        => '260.000',
        'weighted_avg_cost' => '260.000',
        'selling_price'     => '330.000',
        'min_stock_level'   => '10.000',
        'is_active'         => true,
    ]
);
if (bccomp((string)$item2->current_stock, '0.000', 3) == 0) {
    $stockService->depositStock($item2, '80.000', '260.000', 'manual_deposit', 'رصيد افتتاحي لبداية التشغيل');
}

// 2. Suppliers
$sup1 = \App\Models\Supplier::firstOrCreate(
    ['phone' => '01001122334'],
    [
        'name'            => 'شركة الأهرام لاستيراد البن الأخضر',
        'company_name'    => 'مطاحن الأهرام للبن',
        'current_balance' => '0.000',
        'is_active'       => true,
        'notes'           => 'مورد رئيسي لخامات البن الأخضر والبرازيلي',
    ]
);

$sup2 = \App\Models\Supplier::firstOrCreate(
    ['phone' => '01122334455'],
    [
        'name'            => 'مؤسسة النيل لتجارة خامات وتغليف البن',
        'company_name'    => 'النيل للتغليف والشنط',
        'current_balance' => '0.000',
        'is_active'       => true,
        'notes'           => 'مورد خامات التغليف والشنط والأكواب',
    ]
);

// 3. Customers
$cust1 = \App\Models\Customer::firstOrCreate(
    ['phone' => '01000000000'],
    [
        'name'            => 'عميل نقدي / مبيعات المحل',
        'current_balance' => '0.000',
        'credit_limit'    => '0.000',
        'is_active'       => true,
    ]
);

$cust2 = \App\Models\Customer::firstOrCreate(
    ['phone' => '01012345678'],
    [
        'name'            => 'كافيه رويال (أحمد فؤاد)',
        'current_balance' => '0.000',
        'credit_limit'    => '10000.000',
        'is_active'       => true,
    ]
);

$cust3 = \App\Models\Customer::firstOrCreate(
    ['phone' => '01023456789'],
    [
        'name'            => 'مطحنة البركة للبن (محمد طارق)',
        'current_balance' => '0.000',
        'credit_limit'    => '15000.000',
        'is_active'       => true,
    ]
);

$cust4 = \App\Models\Customer::firstOrCreate(
    ['phone' => '01134567890'],
    [
        'name'            => 'قهوة الفيشاوي (حسام حسن)',
        'current_balance' => '0.000',
        'credit_limit'    => '8000.000',
        'is_active'       => true,
    ]
);

$cust5 = \App\Models\Customer::firstOrCreate(
    ['phone' => '01245678901'],
    [
        'name'            => 'كافيه ومطحنة السلطان (محمود إبراهيم)',
        'current_balance' => '0.000',
        'credit_limit'    => '20000.000',
        'is_active'       => true,
    ]
);

echo "SUCCESS_DATA_SEEDED\n";
"""

print("[1/2] جاري الاتصال بالسيرفر عبر SSH لإضافة البيانات...")
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)

# Save script to remote tmp and execute
sftp = ssh.open_sftp()
with sftp.file('/tmp/seed_data.php', 'w') as f:
    f.write('<?php\nrequire __DIR__."/vendor/autoload.php";\n$app = require_once __DIR__."/bootstrap/app.php";\n$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);\n$kernel->bootstrap();\n' + tinker_code)
sftp.close()

cmd = f"""
cd {TARGET}
cp /tmp/seed_data.php {TARGET}/seed_tmp.php
{PHP84} {TARGET}/seed_tmp.php
rm -f {TARGET}/seed_tmp.php

cd {TARGET_FM}
cp /tmp/seed_data.php {TARGET_FM}/seed_tmp.php
{PHP84} {TARGET_FM}/seed_tmp.php
rm -f {TARGET_FM}/seed_tmp.php
"""

stdin, stdout, stderr = ssh.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='ignore')
print(out)
err = stderr.read().decode('utf-8', errors='ignore')
if err.strip():
    print("ERR:", err)
ssh.close()
print("[2/2] اكتملت العملية بنجاح!")
