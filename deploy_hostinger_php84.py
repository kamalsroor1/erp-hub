import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

HOST = "145.79.20.98"
PORT = 65002
USER = "u910151740"
PASS = "Ks@Rr12699"
TARGET_DIR = "/home/u910151740/domains/baraa-solutions.com/public_html/shipping"
PHP84 = "/opt/alt/php84/usr/bin/php"

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
    print("SSH Connection established successfully!")

    try:
        deploy_commands = f"""
        set -e
        cd {TARGET_DIR}
        echo "📂 Directory: $(pwd)"
        
        # 1. Ensure all Laravel storage and cache directories exist before discovery
        echo "📁 Creating complete storage directory tree..."
        mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database
        chmod -R 775 storage bootstrap/cache
        
        # 2. Setup production .env if not present
        if [ ! -f ".env" ]; then
            cp .env.example .env
        fi
        
        # 3. Generate APP_KEY
        {PHP84} artisan key:generate --force || true
        
        # 4. Finish composer dump-autoload
        echo "📦 Finishing composer autoload & discovery..."
        {PHP84} $(which composer) dump-autoload --optimize --no-dev
        
        # 5. Run Migrations & Seeders
        echo "🗄️ Running Migrations and Coffee/Tea Store Seeders..."
        touch database/database.sqlite
        {PHP84} artisan migrate:fresh --force --seed
        
        # 6. Optimize Caches
        echo "⚡ Caching Config, Routes, Views..."
        {PHP84} artisan config:cache
        {PHP84} artisan route:cache
        {PHP84} artisan view:cache
        {PHP84} artisan storage:link || true
        
        # 7. Final Permissions
        chmod -R 775 storage bootstrap/cache database
        
        echo "=========================================================="
        echo "🎉 DEPLOYMENT FINISHED! Sroor Coffee ERP is LIVE & SEEDED!"
        echo "=========================================================="
        """
        
        run_ssh_command(ssh, deploy_commands)

    finally:
        ssh.close()
        print("\n🔒 SSH Connection closed.")

if __name__ == "__main__":
    main()
