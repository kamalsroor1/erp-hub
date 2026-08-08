import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

HOST = "145.79.20.98"
PORT = 65002
USER = "u910151740"
PASS = "Ks@Rr12699"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)

stdin, stdout, stderr = ssh.exec_command("tail -n 25 /home/u910151740/domains/shipping.baraa-solutions.com/public_html/storage/logs/laravel.log")
print(stdout.read().decode('utf-8', errors='ignore'))
ssh.close()
