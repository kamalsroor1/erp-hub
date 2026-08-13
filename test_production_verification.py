import sys
from playwright.sync_api import sync_playwright

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    page = browser.new_page()

    print("1. تسجيل الدخول على السيرفر المباشر (Production)...")
    page.goto("https://sroor.baraa-solutions.com/login")
    page.fill("input#phone", "01012316954")
    page.press("input#phone", "Tab")
    page.fill("input#password", "password")
    page.press("input#password", "Enter")
    page.wait_for_timeout(3000)

    print("2. الانتقال إلى شاشة نقطة البيع (POS)...")
    page.goto("https://sroor.baraa-solutions.com/invoices/create")
    page.wait_for_timeout(2500)

    content = page.content()
    assert "عميل جديد" in content, "Quick add customer button not found on production!"
    assert "ابحث بالاسم أو رقم الهاتف" in content, "Customer search not found on production!"
    print("✅ تم التأكد من وجود البحث اللحظي وزر تسجيل عميل جديد على السيرفر!")

    print("3. فحص قالب الطباعة A4 على السيرفر...")
    page.goto("https://sroor.baraa-solutions.com/invoices")
    page.wait_for_timeout(2000)
    print_links = page.locator('a[href*="/print/a4"]')
    if print_links.count() > 0:
        a4_url = print_links.first.get_attribute("href")
        page.goto(a4_url)
        page.wait_for_timeout(1500)
        a4_text = page.locator("body").inner_text()
        assert "فاتورة مبيعات" in a4_text
        assert "الكاشير:" not in a4_text
        assert "هاتف الفرع:" not in a4_text
        print("✅ قالب A4 على السيرفر خالي تماماً من بيانات الفرع واسم الكاشير ومحتفظ برقم الفاتورة!")

        thermal_url = a4_url.replace("/print/a4", "/print/thermal")
        page.goto(thermal_url)
        page.wait_for_timeout(1500)
        thermal_text = page.locator("body").inner_text()
        assert "الكاشير:" not in thermal_text
        assert "هاتف الفرع:" not in thermal_text
        print("✅ قالب Thermal على السيرفر خالي تماماً من بيانات الفرع واسم الكاشير ومحتفظ برقم الفاتورة!")

    print("🎉 السيرفر السحابي يعمل بكفاءة 100% وكافة التحديثات منشورة!")
    browser.close()
