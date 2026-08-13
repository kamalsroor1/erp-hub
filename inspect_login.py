import sys
from playwright.sync_api import sync_playwright

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    page = browser.new_page()

    page.goto("https://sroor.baraa-solutions.com/login")
    page.fill("input#phone", "01012316954")
    page.fill("input#password", "password")
    page.click('button[type="submit"]')
    page.wait_for_timeout(3000)

    print("Full body inner text:\n", page.locator("body").inner_text())

    browser.close()
