import sys
import os
import paramiko

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

HOST = "145.79.20.98"
PORT = 65002
USER = "u910151740"
PASS = "Ks@Rr12699"
TARGET_DIR = "/home/u910151740/domains/baraa-solutions.com/public_html/shipping"

def run_ssh_command(ssh, cmd):
    print(f"\n>> Command: {cmd}")
    stdin, stdout, stderr = ssh.exec_command(cmd, get_pty=True)
    
    while True:
        line = stdout.readline()
        if not line:
            break
        print("   " + line.rstrip())
    
    return stdout.channel.recv_exit_status()

def main():
    print(f"Connecting to Hostinger ({HOST}:{PORT}) as {USER}...")
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)
    print("SSH Connection established!")

    try:
        deploy_commands = f"""
        set -e
        cd {TARGET_DIR}
        echo "Working Directory: $(pwd)"
        
        # 1. Update composer dependencies compatible with server PHP 8.3
        echo "Installing / Updating composer packages on server..."
        composer update --no-dev --prefer-dist --optimize-autoloader --no-interaction
        
        # 2. Database and SQLite setup
        mkdir -p database storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
        touch database/database.sqlite
        
        # 3. Generate key if needed
        if ! grep -q "APP_KEY=base64" .env 2>/dev/null; then
            php artisan key:generate --force
        fi
        
        # 4. Migrate and Seed
        echo "Running Migrations and Coffee/Tea Seeder..."
        php artisan migrate --force --seed
        
        # 5. Optimize Laravel caches
        echo "Caching Laravel configuration and routes..."
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        php artisan storage:link || true
        
        # 6. Permissions
        chmod -R 775 storage bootstrap/cache database
        
        echo "=========================================================="
        echo "SUCCESS: Laravel Sroor Coffee ERP is LIVE on Hostinger!"
        echo "=========================================================="
        """
        
        run_ssh_command(ssh, deploy_commands)

    finally:
        ssh.close()
        print("\nSSH Session Closed.")

if __name__ == "__main__":
    main()
