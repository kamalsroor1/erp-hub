import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

# 1. Test Login Page
req = urllib.request.Request(
    'https://sroor.baraa-solutions.com/login',
    headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}
)

with urllib.request.urlopen(req, timeout=15) as res:
    print(f"LOGIN PAGE: HTTP {res.status}")
    html = res.read().decode('utf-8')
    print("HAS LOGIN FORM:", "تسجيل الدخول" in html)
    print("HAS EMAIL INPUT:", "admin@sroor.com" in html)

# 2. Test Items Protected Page (should redirect to login or show login)
req_items = urllib.request.Request(
    'https://sroor.baraa-solutions.com/items',
    headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}
)

try:
    with urllib.request.urlopen(req_items, timeout=15) as res:
        print(f"ITEMS REDIRECT TARGET: {res.geturl()} (HTTP {res.status})")
except Exception as e:
    print("ITEMS STATUS:", e)
