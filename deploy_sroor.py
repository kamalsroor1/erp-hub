import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

HOST = "145.79.20.98"
PORT = 65002
USER = "u910151740"
PASS = "Ks@Rr12699"
TARGET_DIR = "/home/u910151740/domains/baraa-solutions.com/public_html/sroor"
PHP84 = "/opt/alt/php84/usr/bin/php"

def run_ssh_command(ssh, cmd):
    print(f"\n>> Running: {cmd}")
    stdin, stdout, stderr = ssh.exec_command(cmd, get_pty=True)
    while True:
        line = stdout.readline()
        if not line:
            break
        print("   " + line.rstrip())
    return stdout.channel.recv_exit_status()

def main():
    print(f"Connecting to Hostinger ({HOST}:{PORT}) as {USER} for [sroor] folder deployment...")
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)
    print("SSH Connection established successfully!")

    try:
        deploy_commands = """
        set -e
        TARGET_DIR="/home/u910151740/domains/baraa-solutions.com/public_html/sroor"
        PHP84="/opt/alt/php84/usr/bin/php"
        mkdir -p $TARGET_DIR
        cd $TARGET_DIR
        echo "📂 Target Directory: $(pwd)"
        
        # 1. Init / Clone or pull from GitHub
        if [ ! -d ".git" ]; then
            echo "📥 Initializing and pulling from GitHub..."
            git init -b main
            git remote add origin https://github.com/kamalsroor1/sroor-cofe-erp.git || true
        fi
        
        git fetch --all
        git reset --hard origin/main
        
        # 2. Storage and Database Setup
        mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database
        chmod -R 775 storage bootstrap/cache database
        
        # 3. Environment setup
        if [ ! -f ".env" ]; then
            cp .env.example .env
        fi
        
        # 4. Generate Application Key
        $PHP84 artisan key:generate --force || true
        
        # 5. Install Composer Dependencies
        echo "📦 Installing Composer dependencies with PHP 8.4..."
        $PHP84 $(which composer) install --no-dev --prefer-dist --optimize-autoloader --no-interaction
        
        # 6. Database Migrations and Coffee/Tea Seeder
        touch database/database.sqlite
        echo "🗄️ Running Migrations and Seeding Coffee/Tea Store Data..."
        $PHP84 artisan migrate:fresh --force --seed
        
        # 7. Optimize Caches
        echo "⚡ Caching Config, Routes, Views..."
        $PHP84 artisan config:cache
        $PHP84 artisan route:cache
        $PHP84 artisan view:cache
        $PHP84 artisan storage:link || true
        
        # 8. Setup proper Apache URL rewriting for /sroor folder
        cat << 'EOF' > .htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/sroor/public/
    RewriteRule ^(.*)$ public/$1 [L,QSA]
</IfModule>
EOF

        # 9. Set permissions
        chmod -R 775 storage bootstrap/cache database
        
        echo "=========================================================="
        echo "🎉 Sroor Coffee ERP is LIVE at: https://baraa-solutions.com/sroor/"
        echo "=========================================================="
        """
        
        run_ssh_command(ssh, deploy_commands)

    finally:
        ssh.close()
        print("\n🔒 SSH Connection closed.")

if __name__ == "__main__":
    main()
