import paramiko

HOST, PORT, USER, PASS = '145.79.20.98', 65002, 'u910151740', 'Ks@Rr12699'
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)

TARGET_DIRS = [
    '/home/u910151740/domains/sroor.baraa-solutions.com/public_html',
    '/home/u910151740/domains/shipping.baraa-solutions.com/public_html',
    '/home/u910151740/domains/baraa-solutions.com/public_html/sroor',
]

for t in TARGET_DIRS:
    stdin, stdout, stderr = ssh.exec_command(f'cat {t}/.env | grep DB_')
    print("=== " + t + " ===")
    print(stdout.read().decode())

ssh.close()
