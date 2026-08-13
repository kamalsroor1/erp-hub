import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

HOST = "145.79.20.98"
PORT = 65002
USER = "u910151740"
PASS = "Ks@Rr12699"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)
print("Connected to SSH successfully!")

PRIMARY_DIR = "/home/u910151740/domains/sroor.baraa-solutions.com/public_html"
ALL_DIRS = [
    "/home/u910151740/domains/sroor.baraa-solutions.com/public_html",
    "/home/u910151740/domains/shipping.baraa-solutions.com/public_html",
    "/home/u910151740/domains/baraa-solutions.com/public_html/sroor",
]

print("\n" + "=" * 60)
print("🧹 جاري تصفير قاعدة البيانات وإعادة التهيئة النظيفة (migrate:fresh --seed)...")
print("=" * 60)

reset_cmd = f"""
set -e
cd {PRIMARY_DIR}
php artisan migrate:fresh --seed --force
"""

stdin, stdout, stderr = ssh.exec_command(reset_cmd, get_pty=True)
while True:
    line = stdout.readline()
    if not line:
        break
    print("   " + line.rstrip())

print("\n" + "=" * 60)
print("⚡ جاري تنظيف الكاشات في كافة النطاقات...")
print("=" * 60)

for d in ALL_DIRS:
    print(f"\nClearing caches for: {d}")
    clear_cmd = f"""
    cd {d}
    php artisan optimize:clear
    php artisan view:clear
    php artisan route:clear
    php artisan config:clear
    php artisan cache:clear
    chmod -R 775 storage bootstrap/cache
    """
    stdin, stdout, stderr = ssh.exec_command(clear_cmd, get_pty=True)
    while True:
        line = stdout.readline()
        if not line:
            break
        print("   " + line.rstrip())

print("\n" + "=" * 60)
print("🔍 التحقق من محتويات قاعدة البيانات بعد التهيئة:")
print("=" * 60)

verify_script = """
cd /home/u910151740/domains/sroor.baraa-solutions.com/public_html
php artisan tinker --execute="
echo '=== USERS ===' . PHP_EOL;
foreach (App\\Models\\User::all() as \$u) {
    echo '- ID: ' . \$u->id . ' | ' . \$u->name . ' (' . \$u->phone . ') | Roles: ' . \$u->roles->pluck('name')->implode(',') . PHP_EOL;
}
echo '=== STORES ===' . PHP_EOL;
foreach (App\\Models\\Store::all() as \$s) {
    echo '- ID: ' . \$s->id . ' | ' . \$s->name . ' (' . \$s->code . ') | Type: ' . \$s->type . ' | Main: ' . (\$s->is_main ? 'Yes' : 'No') . PHP_EOL;
}
echo '=== PERMISSIONS & ROLES ===' . PHP_EOL;
echo 'Roles count: ' . Spatie\\Permission\\Models\\Role::count() . PHP_EOL;
echo 'Permissions count: ' . Spatie\\Permission\\Models\\Permission::count() . PHP_EOL;
echo '=== FINANCIAL & STOCK TABLES ===' . PHP_EOL;
echo 'Items: ' . App\\Models\\Item::count() . PHP_EOL;
echo 'Invoices: ' . App\\Models\\Invoice::count() . PHP_EOL;
echo 'Purchases: ' . App\\Models\\Purchase::count() . PHP_EOL;
echo 'Customers: ' . App\\Models\\Customer::count() . PHP_EOL;
echo 'Suppliers: ' . App\\Models\\Supplier::count() . PHP_EOL;
echo 'Expenses: ' . App\\Models\\Expense::count() . PHP_EOL;
echo 'Shifts: ' . App\\Models\\CashShift::count() . PHP_EOL;
"
"""

stdin, stdout, stderr = ssh.exec_command(verify_script, get_pty=True)
while True:
    line = stdout.readline()
    if not line:
        break
    print("   " + line.rstrip())

ssh.close()
print("\n🎉 تمت إعادة التهيئة النظيفة للسيرفر بنجاح تام!")
