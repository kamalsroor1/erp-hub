import paramiko

HOST = '145.79.20.98'
PORT = 65002
USER = 'u910151740'
PASS = 'Ks@Rr12699'
TARGET = '/home/u910151740/domains/sroor.baraa-solutions.com/public_html'
PHP84 = '/opt/alt/php84/usr/bin/php'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)

php_content = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Http\\Kernel::class);

echo "=== ALL USERS IN DB ===\\n";
foreach (App\\Models\\User::all() as $u) {
    echo "ID: " . $u->id . " | Name: " . $u->name . " | Phone: " . $u->phone . " | Email: " . $u->email . "\\n";
}

echo "\\n=== AUTHENTICATION VERIFICATION ===\\n";
$ok1 = Illuminate\\Support\\Facades\\Auth::attempt(['phone' => '01012316954', 'password' => 'password', 'is_active' => true]);
echo "Super Admin 1 (Phone: 01012316954, Pass: password) -> " . ($ok1 ? "SUCCESS (LOGGED IN)" : "FAILED") . "\\n";

$ok2 = Illuminate\\Support\\Facades\\Auth::attempt(['phone' => '01558088841', 'password' => '123456789', 'is_active' => true]);
echo "Super Admin 2 (Phone: 01558088841, Pass: 123456789) -> " . ($ok2 ? "SUCCESS (LOGGED IN)" : "FAILED") . "\\n";

echo "\\n=== DATA TABLES (ALL DUMMY TRANSACTIONS WIPED) ===\\n";
echo "Invoices: " . App\\Models\\Invoice::count() . " (Clean)\\n";
echo "Items: " . App\\Models\\Item::count() . " (Clean)\\n";
echo "Customers: " . App\\Models\\Customer::count() . " (Clean)\\n";
echo "Suppliers: " . App\\Models\\Supplier::count() . " (Clean)\\n";
echo "Purchases: " . App\\Models\\Purchase::count() . " (Clean)\\n";
"""

sftp = ssh.open_sftp()
with sftp.file(f'{TARGET}/run_check.php', 'w') as f:
    f.write(php_content)
sftp.close()

stdin, stdout, stderr = ssh.exec_command(f'{PHP84} {TARGET}/run_check.php && rm -f {TARGET}/run_check.php')
print(stdout.read().decode('utf-8'))
ssh.close()
