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
DIR = "/home/u910151740/domains/sroor.baraa-solutions.com/public_html"

print("1. Sending Connection Message...")
stdin, stdout, stderr = ssh.exec_command(f"cd {DIR} && php artisan notify:test")
print(stdout.read().decode('utf-8', errors='replace'))

print("2. Sending Daily Business Summary (EOD)...")
stdin, stdout, stderr = ssh.exec_command(f"cd {DIR} && php artisan notify:daily-summary")
print(stdout.read().decode('utf-8', errors='replace'))

print("3. Sending Low Stock Alert...")
stdin, stdout, stderr = ssh.exec_command(f"""cd {DIR} && php artisan tinker --execute="app(\\App\\Services\\TelegramService::class)->sendLowStockNotification(true);" """)
print(stdout.read().decode('utf-8', errors='replace'))

print("4. Sending Overdue Shift Alert...")
stdin, stdout, stderr = ssh.exec_command(f"""cd {DIR} && php artisan tinker --execute="app(\\App\\Services\\TelegramService::class)->sendOverdueShiftNotification(true);" """)
print(stdout.read().decode('utf-8', errors='replace'))

ssh.close()
print("All 4 notification messages sent to your Telegram chat!")
