import paramiko
import sys

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
PHP84 = '/opt/alt/php84/usr/bin/php'

code = """<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

echo "========================================================\\n";
echo "📊 البيانات المسجلة على السيرفر الحي:\\n";
echo "========================================================\\n\\n";

echo "📦 الأصناف (" . \\App\\Models\\Item::count() . "):\\n";
foreach (\\App\\Models\\Item::all() as $item) {
    echo "   - [{$item->code}] {$item->name} | الرصيد: {$item->current_stock} {$item->unit} | الشراء: {$item->cost_price} ج.م | البيع: {$item->selling_price} ج.م\\n";
}

echo "\\n🏭 الموردون (" . \\App\\Models\\Supplier::count() . "):\\n";
foreach (\\App\\Models\\Supplier::all() as $sup) {
    echo "   - {$sup->name} ({$sup->company_name}) | هاتف: {$sup->phone} | الرصيد: {$sup->current_balance} ج.م\\n";
}

echo "\\n👥 العملاء (" . \\App\\Models\\Customer::count() . "):\\n";
foreach (\\App\\Models\\Customer::all() as $cust) {
    echo "   - {$cust->name} | هاتف: {$cust->phone} | الرصيد: {$cust->current_balance} ج.م\\n";
}
echo "\\n========================================================\\n";
"""

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)

sftp = ssh.open_sftp()
with sftp.file('/tmp/verify.php', 'w') as f:
    f.write(code)
sftp.close()

cmd = f"cd {TARGET} && cp /tmp/verify.php ./verify_tmp.php && {PHP84} verify_tmp.php && rm -f verify_tmp.php"
stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode('utf-8'))
ssh.close()
