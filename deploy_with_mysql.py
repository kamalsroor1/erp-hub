import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

HOST = "145.79.20.98"
PORT = 65002
USER = "u910151740"
PASS = "Ks@Rr12699"

def run_ssh(ssh, cmd):
    print(f"\n>> Command: {cmd}")
    stdin, stdout, stderr = ssh.exec_command(cmd, get_pty=True)
    while True:
        line = stdout.readline()
        if not line:
            break
        print("   " + line.rstrip())
    return stdout.channel.recv_exit_status()

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)
print("SSH Connected!")

try:
    cmd = """
    set -e
    cd /home/u910151740/domains/baraa-solutions.com/public_html/sroor
    
    echo "1. Pulling latest code from GitHub..."
    git fetch --all
    git reset --hard origin/main
    
    mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    
    echo "2. Writing .env with MySQL credentials..."
    cat << 'EOF' > .env
APP_NAME="سرور لإدارة الفواتير والمخزون"
APP_ENV=production
APP_KEY=base64:X8p3v8Xz1r1e8tP4w2Q9y7K5m3n1b2c4d6f8g0h2j4k=
APP_DEBUG=false
APP_TIMEZONE=Africa/Cairo
APP_URL=https://baraa-solutions.com/sroor
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
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

CACHE_STORE=file
CACHE_PREFIX=sroor_cache_
EOF

    echo "3. Updating composer dependencies for PHP 8.3..."
    composer update --no-dev --prefer-dist --optimize-autoloader --no-interaction
    
    echo "4. Running MySQL Migrations and Seeders..."
    php artisan key:generate --force
    php artisan migrate:fresh --force --seed
    
    echo "5. Caching Config, Routes, Views..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan storage:link || true
    
    echo "6. Writing index.php and .htaccess in /sroor..."
    cat << 'EOF' > index.php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/vendor/autoload.php';

(require_once __DIR__.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
EOF

    cat << 'EOF' > .htaccess
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
EOF

    chmod -R 775 storage bootstrap/cache
    chmod 644 index.php .htaccess .env
    
    echo "=========================================================="
    echo "MYSQL DEPLOYMENT SUCCESS: https://baraa-solutions.com/sroor/"
    echo "=========================================================="
    """
    run_ssh(ssh, cmd)
finally:
    ssh.close()
