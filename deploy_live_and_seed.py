import sys
import paramiko
import urllib.request
import ssl

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

HOST = "145.79.20.98"
PORT = 65002
USER = "u910151740"
PASS = "Ks@Rr12699"
PHP84 = "/opt/alt/php84/usr/bin/php"

TARGET_DIRS = [
    ("/home/u910151740/domains/sroor.baraa-solutions.com/public_html", "https://sroor.baraa-solutions.com"),
]

def run_ssh_command(ssh, cmd):
    print(f"\n>> Running command:\n{cmd}")
    stdin, stdout, stderr = ssh.exec_command(cmd, get_pty=True)
    while True:
        line = stdout.readline()
        if not line:
            break
        print("   " + line.rstrip())
    exit_status = stdout.channel.recv_exit_status()
    print(f">> Exit code: {exit_status}")
    return exit_status

def main():
    print("=" * 60)
    print("🚀 بدء الرفع والتحديث على السيرفر وضبط الصلاحيات والمجموعات")
    print("=" * 60)

    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)
    print("✅ تم الاتصال بالسيرفر بنجاح عبر SSH!")

    try:
        # Check domain directories
        run_ssh_command(ssh, "ls -la /home/u910151740/domains/")

        for target_dir, domain_url in TARGET_DIRS:
            print("\n" + "=" * 60)
            print(f"📦 جاري تحديث ونشر: {target_dir} ({domain_url})")
            print("=" * 60)

            deploy_script = (
                "set -e\n"
                f"cd {target_dir}\n"
                'echo "📂 المجلد الحالي: $(pwd)"\n'
                'echo "📥 جاري سحب أحدث نسخة من GitHub (main)..."\n'
                "git fetch --all\n"
                "git reset --hard origin/main\n"
                "mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache\n"
                "chmod -R 775 storage bootstrap/cache\n"
                f"{PHP84} $(which composer) install --no-dev --prefer-dist --optimize-autoloader --no-interaction || true\n"
                'echo "🗄️ تشغيل Migrations..."\n'
                f"{PHP84} artisan migrate --force\n"
                'echo "🛡️ زرع وتحديث مصفوفة الصلاحيات والأدوار والمستخدمين..."\n'
                f"{PHP84} artisan db:seed --class=PermissionsSeeder --force\n"
                f"{PHP84} artisan db:seed --force\n"
                'echo "⚡ تنظيف وبناء الكاش (Config, Routes, Views)..."\n'
                f"{PHP84} artisan optimize:clear\n"
                f"{PHP84} artisan config:cache\n"
                f"{PHP84} artisan route:cache\n"
                f"{PHP84} artisan view:cache\n"
                f"{PHP84} artisan storage:link || true\n"
                "chmod -R 775 storage bootstrap/cache\n"
            )
            
            run_ssh_command(ssh, deploy_script)

            # Tinker verify
            tinker_cmd = (
                f"cd {target_dir} && {PHP84} artisan tinker --execute=\""
                "echo '=== إحصائيات النظام الحية ==='.PHP_EOL;"
                "echo 'عدد الصلاحيات (Permissions): '.\\Spatie\\Permission\\Models\\Permission::count().PHP_EOL;"
                "echo 'عدد الأدوار (Roles): '.\\Spatie\\Permission\\Models\\Role::count().PHP_EOL;"
                "echo 'الأدوار المسجلة: '.\\Spatie\\Permission\\Models\\Role::pluck('name')->implode(', ').PHP_EOL;"
                "echo 'عدد المستخدمين (Users): '.\\App\\Models\\User::count().PHP_EOL;"
                "echo 'عدد الفروع (Stores): '.\\App\\Models\\Store::count().PHP_EOL;"
                "echo 'عدد الأصناف (Items): '.\\App\\Models\\Item::count().PHP_EOL;"
                "foreach (\\App\\Models\\User::with('roles')->get() as \\$u) {"
                "  echo ' - ' . \\$u->name . ' (' . \\$u->phone . ') => Roles: ' . \\$u->roles->pluck('name')->implode(', ') . PHP_EOL;"
                "}"
                "\""
            )
            run_ssh_command(ssh, tinker_cmd)

    finally:
        ssh.close()
        print("\n🔒 تم إغلاق اتصال SSH.")

    # 9. اختبار الاستجابة لروابط الموقع
    print("\n" + "=" * 60)
    print("🌐 فحص استجابة روابط الموقع الحية عبر HTTPS")
    print("=" * 60)

    ctx = ssl.create_default_context()
    ctx.check_hostname = False
    ctx.verify_mode = ssl.CERT_NONE

    test_routes = [
        "https://sroor.baraa-solutions.com/login",
        "https://sroor.baraa-solutions.com/items",
        "https://sroor.baraa-solutions.com/roles",
        "https://sroor.baraa-solutions.com/stores",
        "https://sroor.baraa-solutions.com/invoices",
        "https://sroor.baraa-solutions.com/daily-journal",
    ]

    for url in test_routes:
        try:
            req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
            with urllib.request.urlopen(req, context=ctx, timeout=10) as res:
                print(f"  [+] {url} => HTTP Status: {res.status} OK")
        except Exception as e:
            print(f"  [-] {url} => Error: {e}")

    print("\n🎉 اكتمل الرفع والتحديث وضبط الصلاحيات بنجاح تام!")

if __name__ == "__main__":
    main()
