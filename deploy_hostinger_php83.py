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
    
    echo "1. Pulling latest commit from GitHub..."
    git fetch --all
    git reset --hard origin/main
    
    echo "2. Running composer update with PHP 8.3 platform on Hostinger..."
    composer update --no-dev --prefer-dist --optimize-autoloader --no-interaction
    
    echo "3. Creating database and storage directories..."
    mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database
    chmod -R 775 storage bootstrap/cache database
    
    if [ ! -f ".env" ]; then
        cp .env.example .env
    fi
    
    php artisan key:generate --force || true
    touch database/database.sqlite
    
    echo "4. Migrating and Seeding..."
    php artisan migrate:fresh --force --seed
    
    echo "5. Optimizing caches..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan storage:link || true
    
    chmod -R 775 storage bootstrap/cache database
    echo "ALL COMPLETE AND LIVE ON PHP 8.3!"
    """
    run_ssh(ssh, cmd)
finally:
    ssh.close()
