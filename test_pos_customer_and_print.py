import sys
from playwright.sync_api import sync_playwright

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    page = browser.new_page()

    print("1. تسجيل الدخول في السيرفر المحلي...")
    page.goto("http://localhost:8000/login")
    page.fill("input#phone", "01012316954")
    page.press("input#phone", "Tab")
    page.fill("input#password", "password")
    page.press("input#password", "Enter")
    page.wait_for_timeout(3000)

    print("2. الانتقال لشاشة الـ POS وفحص بحث وتسجيل العميل...")
    page.goto("http://localhost:8000/invoices/create")
    page.wait_for_timeout(2000)

    body_text = page.locator("body").inner_text()
    assert "عميل جديد" in body_text, "Quick add customer button not found!"
    assert "ابحث بالاسم أو رقم الهاتف" in page.content(), "Customer search placeholder not found!"

    # Test opening quick customer modal
    print("3. فتح نافذة تسجيل عميل جديد...")
    page.click('button:has-text("عميل جديد")')
    page.wait_for_timeout(1000)

    assert "تسجيل عميل جديد فوراً" in page.locator("body").inner_text()

    # Fill new customer form
    test_phone = "01099998888"
    page.fill('input[placeholder*="سوبر ماركت الأمانة"]', "عميل تجريبي سريع")
    page.fill('input[placeholder="مثال: 01012345678"]', test_phone)
    page.click('button[type="submit"]:has-text("حفظ وتحديد العميل")')
    page.wait_for_timeout(2000)

    print("4. التحقق من اختيار العميل الجديد تلقائياً في الفاتورة...")
    updated_body = page.locator("body").inner_text()
    assert "عميل تجريبي سريع" in updated_body, "New customer was not auto-selected!"
    print("✅ تم تسجيل العميل وتحديده بنجاح!")

    print("5. فحص قائمة الفواتير والطباعة (A4 والحراري)...")
    page.goto("http://localhost:8000/invoices")
    page.wait_for_timeout(2000)

    # Click first invoice view or print if exists
    print_links = page.locator('a[href*="/print/a4"]')
    if print_links.count() > 0:
        a4_href = print_links.first.get_attribute("href")
        page.goto(a4_href)
        page.wait_for_timeout(1500)
        a4_text = page.locator("body").inner_text()
        assert "فاتورة مبيعات" in a4_text
        assert "الكاشير:" not in a4_text
        assert "هاتف الفرع:" not in a4_text
        print("✅ قالب A4 سليم تماماً وبدون تفاصيل الفرع والكاشير!")

        thermal_href = a4_href.replace("/print/a4", "/print/thermal")
        page.goto(thermal_href)
        page.wait_for_timeout(1500)
        thermal_text = page.locator("body").inner_text()
        assert "الكاشير:" not in thermal_text
        assert "هاتف الفرع:" not in thermal_text
        print("✅ قالب Thermal سليم تماماً وبدون تفاصيل الفرع والكاشير!")

    print("🎉 جميع الاختبارات تمت بنجاح تام!")
    browser.close()
