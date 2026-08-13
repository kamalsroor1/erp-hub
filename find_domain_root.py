import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('145.79.20.98', port=65002, username='u910151740', password='Ks@Rr12699')

cmd = """
echo "=== Searching all public_html folders for Laravel .env ==="
find /home/u910151740/ -name ".env" -exec ls -l {} +

echo "=== Searching /home/u910151740/domains ==="
ls -la /home/u910151740/domains/
ls -la /home/u910151740/domains/baraa-solutions.com/public_html/
ls -la /home/u910151740/domains/sroor.baraa-solutions.com/
ls -la /home/u910151740/domains/shipping.baraa-solutions.com/public_html/ || true
"""
stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode('utf-8'))
ssh.close()
