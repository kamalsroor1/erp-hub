import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('145.79.20.98', port=65002, username='u910151740', password='Ks@Rr12699')

cmd = """
cd /home/u910151740/domains/sroor.baraa-solutions.com/public_html
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
"""
stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode('utf-8'))
ssh.close()
