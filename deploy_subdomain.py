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
    SUBDOMAIN_DIR="/home/u910151740/domains/shipping.baraa-solutions.com/public_html"
    cd $SUBDOMAIN_DIR
    echo "Subdomain Directory: $(pwd)"
    
    # Init git repo directly
    if [ ! -d ".git" ]; then
        git init -b main
        git remote add origin https://github.com/kamalsroor1/sroor-cofe-erp.git || true
    fi
    
    git fetch --all
    git reset --hard origin/main
    
    mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database
    chmod -R 775 storage bootstrap/cache
    
    if [ ! -f ".env" ]; then
        cp .env.example .env
    fi
    
    {PHP84} artisan key:generate --force || true
    {PHP84} $(which composer) dump-autoload --optimize --no-dev
    
    touch database/database.sqlite
    {PHP84} artisan migrate:fresh --force --seed
    
    {PHP84} artisan config:cache
    {PHP84} artisan route:cache
    {PHP84} artisan view:cache
    {PHP84} artisan storage:link || true
    
    chmod -R 775 storage bootstrap/cache database
    echo "=================================================================="
    echo "SUCCESS! https://shipping.baraa-solutions.com/ IS NOW 100% LIVE!"
    echo "=================================================================="
    """
    run_ssh(ssh, cmd)
finally:
    ssh.close()
