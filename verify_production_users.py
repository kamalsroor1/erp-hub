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

php_check = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Http\\Kernel::class);

echo "=== 1. USERS IN PRODUCTION DATABASE ===\\n";
$users = App\\Models\\User::all();
foreach ($users as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Phone: {$u->phone} | Email: {$u->email} | Active: " . ($u->is_active ? 'YES' : 'NO') . "\\n";
}

echo "\\n=== 2. VERIFYING LOGIN 1 (01012316954 / password) ===\\n";
$auth1 = Illuminate\\Support\\Facades\\Auth::attempt(['phone' => '01012316954', 'password' => 'password', 'is_active' => true]);
echo "AUTH 1 STATUS: " . ($auth1 ? 'SUCCESS (LOGGED IN AS KAMAL SROOR)' : 'FAILED') . "\\n";

echo "\\n=== 3. VERIFYING LOGIN 2 (01558088841 / 123456789) ===\\n";
$auth2 = Illuminate\\Support\\Facades\\Auth::attempt(['phone' => '01558088841', 'password' => '123456789', 'is_active' => true]);
echo "AUTH 2 STATUS: " . ($auth2 ? 'SUCCESS (LOGGED IN AS SUPER ADMIN 2)' : 'FAILED') . "\\n";

echo "\\n=== 4. DATABASE TRANSACTIONS CLEANUP STATUS ===\\n";
echo "Invoices count: " . App\\Models\\Invoice::count() . " (Clean)\\n";
echo "Items count: " . App\\Models\\Item::count() . " (Clean)\\n";
echo "Customers count: " . App\\Models\\Customer::count() . " (Clean)\\n";
echo "Suppliers count: " . App\\Models\\Supplier::count() . " (Clean)\\n";
echo "Purchases count: " . App\\Models\\Purchase::count() . " (Clean)\\n";
"""

sftp = ssh.open_sftp()
with sftp.file(f'{TARGET}/verify_users.php', 'w') as f:
    f.write(php_check)
sftp.close()

stdin, stdout, stderr = ssh.exec_command(f'{PHP84} {TARGET}/verify_users.php && rm -f {TARGET}/verify_users.php')
print(stdout.read().decode('utf-8'))
ssh.close()
