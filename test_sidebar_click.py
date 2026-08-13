import sys
from playwright.sync_api import sync_playwright

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    context = browser.new_context(ignore_https_errors=True)
    page = context.new_page()

    print("1. Login...")
    page.goto("https://sroor.baraa-solutions.com/login")
    page.fill("input#phone", "01012316954")
    page.fill("input#password", "password")
    page.click('button[type="submit"]')
    page.wait_for_load_state("networkidle")
    page.wait_for_timeout(2000)

    print("Current URL:", page.url)
    print("Page Title:", page.title())

    # Find all sidebar links
    links = page.locator('nav a').all()
    print(f"\nعدد الروابط في السايدبار: {len(links)}")
    for l in links:
        txt = l.inner_text().strip().replace('\n', ' ')
        href = l.get_attribute('href')
        print(f" - {txt} => {href}")

    print("\n2. النقر على رابط الأدوار ومجموعات الصلاحيات من السايدبار:")
    roles_link = page.locator('nav a:has-text("الأدوار"), nav a:has-text("الصلاحيات")').first
    if roles_link.count() > 0:
        print("وجدت الرابط! جاري الضغط...")
        roles_link.click()
        page.wait_for_load_state("networkidle")
        page.wait_for_timeout(2000)
        print("URL after click:", page.url)
        print("Title after click:", page.title())
        print("Text snippet:", page.locator('body').inner_text()[:400].replace('\n', ' '))
    else:
        print("لم أجد رابط الصلاحيات في السايدبار!")

    browser.close()
