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
print("SSH Connected successfully!")

try:
    # 1. Check existing domains
    run_ssh(ssh, "ls -la /home/u910151740/domains/")
    
    # 2. Setup sroor.baraa-solutions.com directory
    cmd = """
    set -e
    
    # Check if sroor.baraa-solutions.com exists in domains
    SROOR_DOMAIN_DIR="/home/u910151740/domains/sroor.baraa-solutions.com/public_html"
    mkdir -p $SROOR_DOMAIN_DIR
    cd $SROOR_DOMAIN_DIR
    echo "Deploying into: $(pwd)"
    
    if [ ! -d ".git" ]; then
        git init -b main
        git remote add origin https://github.com/kamalsroor1/sroor-cofe-erp.git || true
    fi
    
    git fetch --all
    git reset --hard origin/main
    
    mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    
    cat << 'EOF' > .env
APP_NAME="سرور لإدارة الفواتير والمخزون"
APP_ENV=production
APP_KEY=base64:X8p3v8Xz1r1e8tP4w2Q9y7K5m3n1b2c4d6f8g0h2j4k=
APP_DEBUG=false
APP_TIMEZONE=Africa/Cairo
APP_URL=https://sroor.baraa-solutions.com
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
CACHE_PREFIX=sroor_subdomain_
EOF

    echo "Running composer update on sroor.baraa-solutions.com..."
    composer update --no-dev --prefer-dist --optimize-autoloader --no-interaction
    
    php artisan key:generate --force
    php artisan migrate --force --seed
    
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan storage:link || true
    
    cat << 'EOF' > .htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L,QSA]
</IfModule>
EOF

    chmod -R 775 storage bootstrap/cache
    chmod 644 .htaccess .env
    
    echo "======================================================================"
    echo "🎉 SUCCESS: https://sroor.baraa-solutions.com/items IS LIVE AND READY!"
    echo "======================================================================"
    """
    run_ssh(ssh, cmd)
finally:
    ssh.close()
