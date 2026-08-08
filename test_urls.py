import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

for url in [
    'https://sroor.baraa-solutions.com/',
    'https://sroor.baraa-solutions.com/login',
    'https://sroor.baraa-solutions.com/index.php/login',
]:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        with urllib.request.urlopen(req, timeout=15) as res:
            html = res.read().decode('utf-8')
            print(f"URL: {url} -> HTTP {res.status} | Length: {len(html)} | HasLogin: {'تسجيل الدخول' in html}")
    except Exception as e:
        print(f"URL: {url} -> ERROR: {e}")
