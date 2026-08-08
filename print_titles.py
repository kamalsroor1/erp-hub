import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

for path in ['/', '/login', '/index.php', '/index.php/login']:
    url = f"https://sroor.baraa-solutions.com{path}"
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        with urllib.request.urlopen(req, timeout=15) as res:
            html = res.read().decode('utf-8')
            title_match = re.search(r'<title>(.*?)</title>', html)
            title = title_match.group(1) if title_match else 'NO TITLE'
            print(f"{path} -> HTTP {res.status} | Title: {title}")
    except Exception as e:
        print(f"{path} -> ERROR: {e}")
