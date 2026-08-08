import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

req = urllib.request.Request(
    'https://sroor.baraa-solutions.com/items',
    headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}
)

try:
    with urllib.request.urlopen(req, timeout=15) as res:
        print("STATUS:", res.status)
        html = res.read().decode('utf-8')
        print("LENGTH:", len(html))
        print("TITLE / CONTENT FOUND:")
        print("إدارة الأصناف والمخزون" in html or "الأصناف" in html)
        print(html[:500])
except urllib.error.HTTPError as e:
    print(f"HTTP ERROR: {e.code}")
    print(e.read().decode('utf-8', errors='ignore')[:500])
except Exception as e:
    print(f"ERROR: {e}")
