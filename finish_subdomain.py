import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

HOST = "145.79.20.98"
PORT = 65002
USER = "u910151740"
PASS = "Ks@Rr12699"
PHP84 = "/opt/alt/php84/usr/bin/php"

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
    cmd = f"""
    set -e
    cd /home/u910151740/domains/shipping.baraa-solutions.com/public_html
    
    mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database
    chmod -R 775 storage bootstrap/cache
    
    if [ ! -f ".env" ]; then
        cp .env.example .env
    fi
    
    echo "Installing composer vendor packages with PHP 8.4..."
    {PHP84} $(which composer) install --no-dev --prefer-dist --optimize-autoloader --no-interaction
    
    {PHP84} artisan key:generate --force || true
    
    touch database/database.sqlite
    {PHP84} artisan migrate:fresh --force --seed
    
    {PHP84} artisan config:cache
    {PHP84} artisan route:cache
    {PHP84} artisan view:cache
    {PHP84} artisan storage:link || true
    
    chmod -R 775 storage bootstrap/cache database
    echo "=================================================================="
    echo "SUCCESS: https://shipping.baraa-solutions.com/ is 100% READY!"
    echo "=================================================================="
    """
    run_ssh(ssh, cmd)
finally:
    ssh.close()
