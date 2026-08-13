import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

HOST, PORT, USER, PASS = '145.79.20.98', 65002, 'u910151740', 'Ks@Rr12699'
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)

cmd = r'''
cd /home/u910151740/domains/sroor.baraa-solutions.com/public_html
php artisan tinker --execute="echo json_encode(App\Models\User::all(['id','name','phone','email'])->toArray(), JSON_UNESCAPED_UNICODE);"
'''

stdin, stdout, stderr = ssh.exec_command(cmd)
print("Users on live server:")
print(stdout.read().decode('utf-8', errors='replace'))

ssh.close()
