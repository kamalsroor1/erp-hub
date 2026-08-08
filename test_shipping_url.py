import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

req = urllib.request.Request(
    'https://shipping.baraa-solutions.com/',
    headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}
)

try:
    with urllib.request.urlopen(req, timeout=15) as response:
        print("STATUS:", response.status)
        print("TITLE / CONTENT:")
        html = response.read().decode('utf-8')
        print(html[:1000])
except urllib.error.HTTPError as e:
    print("STATUS:", e.code)
    print(e.read().decode('utf-8', errors='ignore')[:1000])
except Exception as e:
    print("ERROR:", e)
