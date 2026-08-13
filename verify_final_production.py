import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

HOST = "145.79.20.98"
PORT = 65002
USER = "u910151740"
PASS = "Ks@Rr12699"
TARGET = "/home/u910151740/domains/sroor.baraa-solutions.com/public_html"

print("=" * 60)
print(f"🔍 فحص نهائي وتأكيد لحالة المشروع في: {TARGET}")
print("=" * 60)

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)

cmd = (
    f"cd {TARGET}\n"
    "echo '1. Git Status & Commit:'\n"
    "git log -n 1 --oneline\n"
    "echo ''\n"
    "echo '2. Database Roles & Permissions Summary:'\n"
    "php artisan tinker --execute=\""
    "echo '• عدد الصلاحيات: ' . \\Spatie\\Permission\\Models\\Permission::count() . PHP_EOL;"
    "echo '• عدد الأدوار: ' . \\Spatie\\Permission\\Models\\Role::count() . PHP_EOL;"
    "foreach (\\Spatie\\Permission\\Models\\Role::with('permissions')->get() as \\$r) {"
    "  echo '  - دور [' . \\$r->name . '] لديه ' . \\$r->permissions->count() . ' صلاحية' . PHP_EOL;"
    "}"
    "echo '• المستخدمين الحاليين:' . PHP_EOL;"
    "foreach (\\App\\Models\\User::with('roles')->get() as \\$u) {"
    "  echo '  - ' . \\$u->name . ' (' . \\$u->phone . ') => [' . \\$u->roles->pluck('name')->implode(', ') . ']' . PHP_EOL;"
    "}"
    "\"\n"
    "echo ''\n"
    "echo '3. Routes Check:'\n"
    "php artisan route:list --path=roles\n"
    "php artisan route:list --path=users\n"
)

stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode('utf-8'))
ssh.close()
