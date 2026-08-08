import sys
import paramiko

HOST = "145.79.20.98"
PORT = 65002
USER = "u910151740"
PASS = "Ks@Rr12699"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)

cmd = """
cd /home/u910151740/domains/baraa-solutions.com/public_html/sroor

cat << 'EOF' > .htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [L,QSA]
</IfModule>
EOF

php artisan config:cache
php artisan route:cache
chmod 644 .htaccess
"""

stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode('utf-8', errors='ignore'))
ssh.close()
