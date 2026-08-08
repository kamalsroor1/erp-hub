import urllib.request

req = urllib.request.Request(
    'https://shipping.baraa-solutions.com/invoices/create',
    headers={'User-Agent': 'Mozilla/5.0'}
)

with urllib.request.urlopen(req, timeout=15) as res:
    print("POS STATUS:", res.status)
    html = res.read().decode('utf-8')
    print("PAGE TITLE / HEADER FOUND:", "كاشير ومبيعات" in html)
