import os
import sys
import paramiko

# Fix Windows Unicode Output
if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except Exception:
        pass

# 🚀 سكريبت النشر والتحديث المباشر لمسارات Hostinger بالكامل
# 1. مسار مدير الملفات File Manager: public_html/sroor
# 2. مسار النطاق الفرعي: sroor.baraa-solutions.com

HOST = '145.79.20.98'
PORT = 65002
USER = 'u910151740'
PASS = 'Ks@Rr12699'

TARGET_PATHS = [
    '/home/u910151740/domains/baraa-solutions.com/public_html/sroor',
    '/home/u910151740/domains/sroor.baraa-solutions.com/public_html',
]
PHP84 = '/opt/alt/php84/usr/bin/php'

def main():
    print("=" * 70)
    print("  >> جاري النشر والتحديث المزدوج إلى مسارات Hostinger (Sroor ERP)")
    print("=" * 70)
    
    print("\n[1/3] جاري الاتصال بالسيرفر عبر SSH...")
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    
    try:
        ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)
        print("  -> تم الاتصال بالسيرفر بنجاح!")
    except Exception as e:
        print(f"  -> فشل الاتصال بالسيرفر: {e}")
        sys.exit(1)

    print("\n[2/3] جاري سحب أحدث كود وتحديث الملفات في كلا المسارين...")
    
    for target in TARGET_PATHS:
        print(f"\n📂 تحديث المسار: {target}")
        commands = [
            f"cd {target} && git fetch --all && git reset --hard origin/main",
            f"cd {target} && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache",
            f"cd {target} && chmod -R 775 storage bootstrap/cache",
            f"cd {target} && rm -f bootstrap/cache/*.php",
            f"cd {target} && {PHP84} artisan optimize:clear",
            f"cd {target} && {PHP84} artisan migrate --force",
            f"cd {target} && {PHP84} artisan db:seed --force",
            f"cd {target} && [ -f index.html ] && mv index.html index.html.bak 2>/dev/null || true",
            f"cd {target} && chmod -R 775 storage bootstrap/cache",
        ]
        
        for cmd in commands:
            stdin, stdout, stderr = ssh.exec_command(cmd)
            out = stdout.read().decode('utf-8', errors='ignore')
            if out.strip():
                print("  ", out.strip().replace("\n", "\n   "))

    ssh.close()
    
    print("\n" + "=" * 70)
    print("  >> تم النشر والتحديث لكلا المسارين بنجاح 100%!")
    print("  >> مسار مدير الملفات (File Manager): public_html/sroor/")
    print("  >> رابط الدخول المباشر: https://sroor.baraa-solutions.com/login")
    print("  >> البريد الإلكتروني: admin@sroor.com")
    print("  >> كلمة المرور: password")
    print("=" * 70)

if __name__ == '__main__':
    main()
