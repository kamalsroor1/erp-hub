import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

HOST = "145.79.20.98"
PORT = 65002
USER = "u910151740"
PASS = "Ks@Rr12699"

def run_ssh(ssh, cmd):
    print(f"\n>> Command: {cmd}")
    stdin, stdout, stderr = ssh.exec_command(cmd, get_pty=True)
    while True:
        line = stdout.readline()
        if not line:
            break
        print("   " + line.rstrip())

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)

try:
    run_ssh(ssh, "which php; php -v")
    run_ssh(ssh, "ls -la /usr/bin/php* /usr/local/bin/php* /opt/alt/php*/usr/bin/php* /opt/php*/bin/php* 2>/dev/null")
    run_ssh(ssh, "find /opt -name 'php' 2>/dev/null")
finally:
    ssh.close()
