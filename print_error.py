import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

req = urllib.request.Request(
    'https://baraa-solutions.com/sroor/',
    headers={'User-Agent': 'Mozilla/5.0'}
)

try:
    with urllib.request.urlopen(req, timeout=15) as response:
        print("STATUS:", response.status)
        print(response.read().decode('utf-8')[:500])
except urllib.error.HTTPError as e:
    print("STATUS:", e.code)
    print(e.read().decode('utf-8', errors='ignore'))
except Exception as e:
    print("ERROR:", e)
