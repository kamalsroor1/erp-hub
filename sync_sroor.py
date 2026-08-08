import paramiko

HOST = "145.79.20.98"
PORT = 65002
USER = "u910151740"
PASS = "Ks@Rr12699"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)

cmd = """
# Sync to /sroor as well
cp -r /home/u910151740/domains/shipping.baraa-solutions.com/public_html/* /home/u910151740/domains/baraa-solutions.com/public_html/sroor/ 2>/dev/null || true
cp -r /home/u910151740/domains/shipping.baraa-solutions.com/public_html/.[!.]* /home/u910151740/domains/baraa-solutions.com/public_html/sroor/ 2>/dev/null || true
chmod -R 775 /home/u910151740/domains/baraa-solutions.com/public_html/sroor/storage /home/u910151740/domains/baraa-solutions.com/public_html/sroor/bootstrap/cache
echo "Sync complete!"
"""

stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode('utf-8', errors='ignore'))
ssh.close()
