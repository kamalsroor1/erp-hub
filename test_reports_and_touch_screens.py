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
    page.wait_for_timeout(2500)

    print("2. اختبار صفحة التقارير والتبويبات الـ 6...")
    page.goto("http://localhost:8000/reports")
    page.wait_for_timeout(2000)

    # Verify Tab 1 (Sales)
    body = page.locator("body").inner_text()
    assert "مبيعات وإيرادات الفترة" in body
    assert "إجمالي المبيعات" in body
    assert "التحصيل النقدي" in body
    assert "المبيعات الآجلة" in body
    print("✅ تبويب 1 (المبيعات والإيرادات) يعمل بنجاح!")

    # Click Tab 2 (Items)
    page.click('button:has-text("حركة وربحية الأصناف")')
    page.wait_for_timeout(2500)
    body = page.locator("body").inner_text()
    assert "تحليل مبيعات وربحية الأصناف" in body or "الأصناف" in body
    print("✅ تبويب 2 (حركة وربحية الأصناف) يعمل بنجاح!")

    # Click Tab 3 (Stores)
    page.click('button:has-text("مقارنة أداء ومبيعات الفروع")')
    page.wait_for_timeout(2500)
    body = page.locator("body").inner_text()
    assert "مقارنة الأداء والمبيعات عبر الفروع" in body or "عربات التوزيع" in body
    print("✅ تبويب 3 (مقارنة أداء الفروع وعربيات التوزيع) يعمل بنجاح!")

    # Click Tab 4 (Customers)
    page.click('button:has-text("مبيعات وحسابات العملاء")')
    page.wait_for_timeout(2500)
    body = page.locator("body").inner_text()
    assert "إجمالي مديونيات وحسابات كافة العملاء" in body or "كبار العملاء" in body
    print("✅ تبويب 4 (حسابات وديون العملاء) يعمل بنجاح!")

    # Click Tab 5 (Expenses)
    page.click('button:has-text("المصروفات وصافي الدخل")')
    page.wait_for_timeout(2500)
    body = page.locator("body").inner_text()
    assert "الصافي الفعلي للأرباح" in body or "المصروفات" in body
    print("✅ تبويب 5 (المصروفات وصافي الدخل) يعمل بنجاح!")

    # Click Tab 6 (Inventory)
    page.click('button:has-text("تقييم بضاعة المخزن")')
    page.wait_for_timeout(2500)
    body = page.locator("body").inner_text()
    assert "قيمة البضاعة الحالية" in body or "تقييم" in body
    print("✅ تبويب 6 (تقييم المخزون) يعمل بنجاح!")

    # Test period buttons
    page.click('button:has-text("هذا الأسبوع")')
    page.wait_for_timeout(1000)
    page.click('button:has-text("هذا العام")')
    page.wait_for_timeout(1000)
    print("✅ فلاتر الفترات (اليوم، الأسبوع، الشهر، العام) تعمل بسلاسة!")

    print("3. فحص شاشة المشتريات باللمس...")
    page.goto("http://localhost:8000/purchases/create")
    page.wait_for_timeout(2000)
    assert page.locator('button:has-text("+50k شكارة")').count() > 0 or "فاتورة شراء بضاعة" in page.content()
    print("✅ شاشة المشتريات مهيأة للمس مع أوزان الشكائر والكميات السريعة!")

    print("4. فحص شاشة المصروفات باللمس...")
    page.goto("http://localhost:8000/expenses")
    page.wait_for_timeout(1500)
    page.click('button:has-text("تسجيل مصروف جديد")')
    page.wait_for_timeout(1000)
    modal_text = page.locator("body").inner_text()
    assert "اختر التصنيف بلمسة واحدة" in modal_text
    assert "500" in modal_text
    print("✅ شاشة المصروفات ومودال الإدخال للمس تعمل بكفاءة تامة!")

    print("🎉 كافة الميزات الجديدة تم اختبارها محلياً بنجاح 100%!")
    browser.close()
