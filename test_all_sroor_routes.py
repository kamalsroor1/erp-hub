import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

BASE_URL = "https://sroor.baraa-solutions.com"
routes = [
    "/",
    "/items",
    "/invoices",
    "/invoices/create",
    "/customers",
    "/purchases",
    "/purchases/create",
    "/suppliers",
    "/returns",
    "/returns/create",
    "/reports"
]

for route in routes:
    url = BASE_URL + route
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
    try:
        with urllib.request.urlopen(req, timeout=15) as res:
            print(f"✅ {route.ljust(20)} -> HTTP {res.status}")
    except Exception as e:
        print(f"❌ {route.ljust(20)} -> ERROR: {e}")
