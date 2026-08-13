import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('145.79.20.98', port=65002, username='u910151740', password='Ks@Rr12699')

cmd = """
cd /home/u910151740/domains/sroor.baraa-solutions.com/public_html
head -n 305 resources/views/components/layouts/app.blade.php | tail -n 25
"""
stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode('utf-8'))
ssh.close()
