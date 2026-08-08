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
print("SSH Connected!")

cmd = """
set -e
cd /home/u910151740/domains/shipping.baraa-solutions.com/public_html

echo "1. Pulling latest code with platform PHP 8.3..."
git fetch --all
git reset --hard origin/main

echo "2. Writing .env with MySQL credentials..."
cat << 'EOF' > .env
APP_NAME="سرور لإدارة الفواتير والمخزون"
APP_ENV=production
APP_KEY=base64:X8p3v8Xz1r1e8tP4w2Q9y7K5m3n1b2c4d6f8g0h2j4k=
APP_DEBUG=false
APP_TIMEZONE=Africa/Cairo
APP_URL=https://shipping.baraa-solutions.com
APP_LOCALE=ar
APP_FALLBACK_LOCALE=ar

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u910151740_sroor
DB_USERNAME=u910151740_sroor
DB_PASSWORD=Ks@Rr3172024

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

CACHE_STORE=file
CACHE_PREFIX=sroor_shipping_
EOF

echo "3. Running composer update for PHP 8.3.30..."
composer update --no-dev --prefer-dist --optimize-autoloader --no-interaction

echo "4. Migrating & Seeding MySQL database..."
php artisan key:generate --force
php artisan migrate:fresh --force --seed

echo "5. Caching Config, Routes, Views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link || true

echo "6. Writing clean root .htaccess..."
cat << 'EOF' > .htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L,QSA]
</IfModule>
EOF

chmod -R 775 storage bootstrap/cache
chmod 644 .htaccess .env
echo "=========================================================="
echo "DEPLOYMENT COMPLETE ON: https://shipping.baraa-solutions.com/"
echo "=========================================================="
"""

stdin, stdout, stderr = ssh.exec_command(cmd, get_pty=True)
while True:
    line = stdout.readline()
    if not line:
        break
    print("   " + line.rstrip())

ssh.close()
