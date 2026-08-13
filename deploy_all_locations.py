import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

HOST = "145.79.20.98"
PORT = 65002
USER = "u910151740"
PASS = "Ks@Rr12699"

TARGET_DIRS = [
    "/home/u910151740/domains/sroor.baraa-solutions.com/public_html",
    "/home/u910151740/domains/shipping.baraa-solutions.com/public_html",
    "/home/u910151740/domains/baraa-solutions.com/public_html/sroor",
]

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)
print("Connected to SSH successfully!")

for t in TARGET_DIRS:
    print("\n" + "=" * 60)
    print(f"📦 جاري التحديث والتنظيف والتسريع في: {t}")
    print("=" * 60)

    cmd = f"""
    set -e
    cd {t}
    echo "Folder: $(pwd)"
    
    # 1. Reset from Github main
    git fetch --all
    git reset --hard origin/main
    
    # 2. Storage dirs and permissions
    mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    
    # 3. Database migrations and seeder
    php artisan migrate --force
    php artisan db:seed --class=PermissionsSeeder --force
    php artisan db:seed --force
    
    # 4. Clear and rebuild complete production caches
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    
    chmod -R 775 storage bootstrap/cache
    echo "✅ تم تحديث وتسريع {t} بنمط الإنتاج الكامل!"
    """
    
    stdin, stdout, stderr = ssh.exec_command(cmd, get_pty=True)
    while True:
        line = stdout.readline()
        if not line:
            break
        print("   " + line.rstrip())
    print("Exit code:", stdout.channel.recv_exit_status())

ssh.close()
print("\n🎉 تم تحديث ومزامنة كافة مجلدات وسيرفرات النظام بنجاح!")
