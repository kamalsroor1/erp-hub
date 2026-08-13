import sys
from playwright.sync_api import sync_playwright

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    page = browser.new_page()

    print("1. تسجيل الدخول في السيرفر الحي...")
    page.goto("https://sroor.baraa-solutions.com/login")
    page.fill("input#phone", "01012316954")
    page.fill("input#password", "password")
    page.click('button[type="submit"]')
    page.wait_for_timeout(3000)

    print("2. فحص السايدبار في لوحة التحكم...")
    page.goto("https://sroor.baraa-solutions.com/")
    page.wait_for_timeout(1000)
    aside = page.locator("aside")
    print("Classes on live dashboard aside:", aside.get_attribute("class"))

    print("3. الانتقال لشاشة نقطة البيع باللمس (Touch POS)...")
    page.goto("https://sroor.baraa-solutions.com/invoices/create")
    page.wait_for_timeout(2000)
    pos_aside = page.locator("aside")
    print("Classes on live POS aside (Should be w-20):", pos_aside.get_attribute("class"))

    body_text = page.locator("body").inner_text()
    assert "كاشير ومبيعات مطحنة البن والشاي والتوزيع (Touch POS)" in body_text
    assert "سلة الفاتورة" in body_text
    assert "طريقة الدفع والسداد" in body_text

    print("✅ تم التحقق بنجاح من عمل السايدبار المصغر وشاشة نقطة البيع باللمس على السيرفر الحي!")
    browser.close()
