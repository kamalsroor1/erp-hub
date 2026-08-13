import sys
from playwright.sync_api import sync_playwright

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    page = browser.new_page()

    page.goto("http://localhost:8000/login")
    page.fill("input#phone", "01012316954")
    page.fill("input#password", "password")
    page.click('button[type="submit"]')
    page.wait_for_timeout(2000)

    print("URL after login:", page.url)

    res = page.goto("http://localhost:8000/invoices/create")
    page.wait_for_timeout(2000)
    print("URL after invoices/create:", page.url)
    print("Response status:", res.status)

    browser.close()
