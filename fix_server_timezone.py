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
    print(f"\n🌍 ضبط المنطقة الزمنية (Africa/Cairo) في: {t}")
    cmd = f"""
    cd {t}
    # Update or append APP_TIMEZONE in .env
    if grep -q "APP_TIMEZONE" .env; then
        sed -i 's/^APP_TIMEZONE=.*/APP_TIMEZONE=Africa\\/Cairo/' .env
    else
        echo "APP_TIMEZONE=Africa/Cairo" >> .env
    fi
    
    # Pull latest code
    git fetch --all
    git reset --hard origin/main
    
    # Clear configs and caches
    php artisan optimize:clear
    php artisan config:clear
    php artisan cache:clear
    
    # Check current time in Laravel
    php artisan tinker --execute="echo 'Current Laravel Time: ' . now()->format('Y-m-d h:i:s A (T)') . PHP_EOL;"
    """
    stdin, stdout, stderr = ssh.exec_command(cmd)
    print(stdout.read().decode('utf-8'))

ssh.close()
print("\n🎉 تم ضبط التوقيت بالكامل على توقيت القاهرة (Africa/Cairo)!")
