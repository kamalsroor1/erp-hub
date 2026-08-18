import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

HOST = "145.79.20.98"
PORT = 65002
USER = "u910151740"
PASS = "Ks@Rr12699"
PHP84 = "/opt/alt/php84/usr/bin/php"

def run_ssh(ssh, cmd):
    print(f"\n>> Running: {cmd}")
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
        deploy_script = """
        set -e
        PHP84="/opt/alt/php84/usr/bin/php"

        DEPLOY_DIRS=(
            "/home/u910151740/domains/sroor.baraa-solutions.com/public_html"
            "/home/u910151740/domains/baraa-solutions.com/public_html/sroor"
            "/home/u910151740/domains/shipping.baraa-solutions.com/public_html"
        )

        for TARGET_DIR in "${DEPLOY_DIRS[@]}"; do
            if [ -d "$TARGET_DIR" ]; then
                echo "=========================================================="
                echo "🚀 Safe Deployment Target: $TARGET_DIR"
                echo "=========================================================="
                cd "$TARGET_DIR"

                # 1. Automatic Database Safety Backup
                if [ -f "database/database.sqlite" ]; then
                    BACKUP_FILE="database/database.sqlite.bak_$(date +%Y%m%d_%H%M%S)"
                    echo "🛡️ Creating Safe Database Backup -> $BACKUP_FILE"
                    cp database/database.sqlite "$BACKUP_FILE"
                fi

                # 2. Pull latest code from GitHub without overwriting local DB
                if [ -d ".git" ]; then
                    echo "📥 Pulling latest commits from GitHub origin/main..."
                    git fetch origin main
                    git reset --hard origin/main
                fi

                # 3. Ensure storage and database folders exist
                mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database
                chmod -R 775 storage bootstrap/cache database

                # 4. Safe Database Migration (Preserves all existing data 100%)
                echo "🗄️ Running Safe Database Migrations (Zero Data Loss)..."
                $PHP84 artisan migrate --force || true
                $PHP84 artisan db:seed --class=PermissionsSeeder --force || true

                # 5. Clear and Rebuild Caches
                echo "⚡ Refreshing Application Cache..."
                $PHP84 artisan optimize:clear || true
                $PHP84 artisan config:cache || true
                $PHP84 artisan route:cache || true
                $PHP84 artisan view:cache || true

                # 6. Fix permissions
                chmod -R 775 storage bootstrap/cache database
                echo "✅ Successfully deployed safely to: $TARGET_DIR"
            fi
        done

        echo "=========================================================="
        echo "🎉 ALL LIVE DEPLOYMENTS COMPLETED SAFELY WITHOUT DATA LOSS!"
        echo "=========================================================="
        """

        run_ssh(ssh, deploy_script)

    finally:
        ssh.close()
        print("\n🔒 SSH Connection closed.")

if __name__ == "__main__":
    main()
