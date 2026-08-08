import paramiko
import urllib.request
import ssl

HOST = '145.79.20.98'
PORT = 65002
USER = 'u910151740'
PASS = 'Ks@Rr12699'
TARGET = '/home/u910151740/domains/sroor.baraa-solutions.com/public_html'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)

htaccess_raw = """DirectoryIndex index.php
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On
    RewriteBase /

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
"""

sftp = ssh.open_sftp()
with sftp.file(f'{TARGET}/.htaccess', 'w') as f:
    f.write(htaccess_raw)
sftp.close()

stdin, stdout, stderr = ssh.exec_command(f'cd {TARGET} && [ -f index.html ] && mv index.html index.html.bak || true; chmod 644 {TARGET}/.htaccess')
stdout.channel.recv_exit_status()
ssh.close()

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

urls = [
    'https://sroor.baraa-solutions.com/login',
    'https://sroor.baraa-solutions.com/',
    'https://sroor.baraa-solutions.com/items',
    'https://sroor.baraa-solutions.com/invoices/create',
    'https://sroor.baraa-solutions.com/users',
    'https://sroor.baraa-solutions.com/profile',
]

print('=== TESTING ALL LIVE URLS ===')
for u in urls:
    try:
        req = urllib.request.Request(u, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, context=ctx) as res:
            html = res.read().decode('utf-8')
            has_login = 'تسجيل الدخول' in html or 'admin@sroor.com' in html
            print(f'LIVE SUCCESS: {u} -> Status: {res.status} | HasLoginWord: {has_login} | Length: {len(html)}')
    except urllib.error.HTTPError as e:
        print(f'LIVE HTTP ERROR: {u} -> Status: {e.code}')
    except Exception as e:
        print(f'LIVE ERROR: {u} -> {e}')
