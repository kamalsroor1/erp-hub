import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('145.79.20.98', port=65002, username='u910151740', password='Ks@Rr12699')

cmd = """
/opt/alt/php84/usr/bin/php /home/u910151740/domains/shipping.baraa-solutions.com/public_html/artisan tinker --execute="echo 'Items: '.\\App\\Models\\Item::count().' | Invoices: '.\\App\\Models\\Invoice::count().' | Customers: '.\\App\\Models\\Customer::count().' | Settings: '.\\App\\Models\\Setting::count().PHP_EOL;"
"""
stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode('utf-8', errors='ignore'))
ssh.close()
