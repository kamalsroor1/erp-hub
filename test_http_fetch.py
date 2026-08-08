import urllib.request

req = urllib.request.Request(
    'https://baraa-solutions.com/sroor/',
    headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}
)

try:
    with urllib.request.urlopen(req, timeout=15) as response:
        html = response.read().decode('utf-8')
        print(f"STATUS: {response.status}")
        print(f"LENGTH: {len(html)}")
        print("FIRST 500 CHARS:")
        print(html[:500])
except urllib.error.HTTPError as e:
    print(f"HTTP ERROR: {e.code} - {e.reason}")
    print(e.read().decode('utf-8', errors='ignore')[:500])
except Exception as e:
    print(f"ERROR: {e}")
