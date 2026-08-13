import sys
import requests
import re
import urllib3

sys.stdout.reconfigure(encoding='utf-8', errors='replace')
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

BASE_URL = "https://sroor.baraa-solutions.com"
session = requests.Session()
session.verify = False

print("=" * 60)
print("اختبار تسجيل الدخول وتصفح الشاشات المحمية على السيرفر الحي")
print("=" * 60)

# 1. Get Login Page & CSRF Token
login_page = session.get(f"{BASE_URL}/login")
print(f"1. Login Page Status: {login_page.status_code}")

# Let's test direct routes
routes = [
    ("/roles", "مصفوفة الصلاحيات والأدوار"),
    ("/users", "إدارة المستخدمين والكاشير"),
    ("/stores", "الفروع والمخازن وعربات التوزيع"),
    ("/items", "قائمة الأصناف والمخزون"),
    ("/invoices", "فواتير المبيعات"),
    ("/daily-journal", "اليومية النقدية والورديات"),
    ("/reports", "التقارير المالية والأرباح"),
    ("/trash", "سلة المحذوفات المركزية"),
]

print("\n" + "=" * 60)
print("فحص استجابة الشاشات:")
print("=" * 60)

for path, label in routes:
    r = session.get(f"{BASE_URL}{path}", allow_redirects=False)
    print(f"  [+] {label} ({path}) => Status: {r.status_code} (Location: {r.headers.get('Location', 'None')})")

print("\nاكتمل الفحص بنجاح!")
