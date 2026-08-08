import sys
import paramiko

HOST = "145.79.20.98"
PORT = 65002
USER = "u910151740"
PASS = "Ks@Rr12699"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)

stdin, stdout, stderr = ssh.exec_command("cat /home/u910151740/domains/baraa-solutions.com/public_html/.htaccess 2>/dev/null")
print("PARENT HTACCESS:")
print(stdout.read().decode('utf-8', errors='ignore'))
ssh.close()
