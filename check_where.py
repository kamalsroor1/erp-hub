import paramiko
import requests

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('145.79.20.98', port=65002, username='u910151740', password='Ks@Rr12699')

php_test = "<?php echo 'DOCUMENT_ROOT: ' . $_SERVER['DOCUMENT_ROOT'] . ' | FILE: ' . __FILE__; ?>"

sftp = ssh.open_sftp()
with sftp.file('/home/u910151740/domains/sroor.baraa-solutions.com/public_html/public/where_am_i.php', 'w') as f:
    f.write(php_test)
sftp.close()

r = requests.get('https://sroor.baraa-solutions.com/where_am_i.php', verify=False)
print("HTTP Response:", r.text)

ssh.close()
