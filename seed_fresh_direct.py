import sys
import paramiko

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

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)

seed_script = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Disabling Foreign Keys...\\n";
    Illuminate\\Support\\Facades\\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    echo "2. Truncating tables...\\n";
    $tables = [
        'invoice_items', 'invoices', 'purchase_items', 'purchases',
        'return_items', 'returns', 'sales_returns', 'purchase_returns',
        'stock_movements', 'payments', 'audit_logs', 'customers', 'suppliers',
        'items', 'cash_shifts', 'users', 'model_has_roles', 'model_has_permissions'
    ];
    foreach ($tables as $t) {
        if (Illuminate\\Support\\Facades\\Schema::hasTable($t)) {
            Illuminate\\Support\\Facades\\DB::table($t)->truncate();
            echo "   - Truncated table: {$t}\\n";
        }
    }

    echo "3. Creating Roles...\\n";
    $adminRole = Spatie\\Permission\\Models\\Role::firstOrCreate(['name' => 'admin']);
    $cashierRole = Spatie\\Permission\\Models\\Role::firstOrCreate(['name' => 'cashier']);
    $storeRole = Spatie\\Permission\\Models\\Role::firstOrCreate(['name' => 'storekeeper']);
    $accountantRole = Spatie\\Permission\\Models\\Role::firstOrCreate(['name' => 'accountant']);

    echo "4. Creating Super Admin 1 (01012316954 / password)...\\n";
    $admin1 = App\\Models\\User::create([
        'name'      => 'كمال سرور - المدير العام',
        'phone'     => '01012316954',
        'email'     => '01012316954@sroor.com',
        'password'  => bcrypt('password'),
        'is_active' => true,
    ]);
    $admin1->syncRoles([$adminRole]);
    echo "   -> Admin 1 Created! ID: {$admin1->id}\\n";

    echo "5. Creating Super Admin 2 (01558088841 / 123456789)...\\n";
    $admin2 = App\\Models\\User::create([
        'name'      => 'المدير العام 2',
        'phone'     => '01558088841',
        'email'     => '01558088841@sroor.com',
        'password'  => bcrypt('123456789'),
        'is_active' => true,
    ]);
    $admin2->syncRoles([$adminRole]);
    echo "   -> Admin 2 Created! ID: {$admin2->id}\\n";

    echo "6. Re-enabling Foreign Keys...\\n";
    Illuminate\\Support\\Facades\\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    echo "\\n=== ALL USERS IN DB ===\\n";
    foreach (App\\Models\\User::all() as $u) {
        echo "ID: {$u->id} | Name: {$u->name} | Phone: {$u->phone} | Email: {$u->email} | Active: " . ($u->is_active ? 'YES' : 'NO') . "\\n";
    }

    echo "\\n=== AUTH TEST ===\\n";
    $auth1 = Illuminate\\Support\\Facades\\Auth::attempt(['phone' => '01012316954', 'password' => 'password', 'is_active' => true]);
    echo "AUTH 1 (01012316954 / password) -> " . ($auth1 ? "SUCCESS (LOGGED IN)" : "FAILED") . "\\n";

    $auth2 = Illuminate\\Support\\Facades\\Auth::attempt(['phone' => '01558088841', 'password' => '123456789', 'is_active' => true]);
    echo "AUTH 2 (01558088841 / 123456789) -> " . ($auth2 ? "SUCCESS (LOGGED IN)" : "FAILED") . "\\n";

} catch (\\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\\n" . $e->getTraceAsString() . "\\n";
}
"""

sftp = ssh.open_sftp()
with sftp.file(f'{TARGET}/seed_fresh.php', 'w') as f:
    f.write(seed_script)
sftp.close()

stdin, stdout, stderr = ssh.exec_command(f'{PHP84} {TARGET}/seed_fresh.php && rm -f {TARGET}/seed_fresh.php')
print(stdout.read().decode('utf-8'))
ssh.close()
