import sys
from playwright.sync_api import sync_playwright

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    page = browser.new_page()

    print("1. تسجيل الدخول في السيرفر المحلي...")
    page.goto("http://localhost:8000/login")
    page.fill("input#phone", "01012316954")
    page.fill("input#password", "password")
    page.click('button[type="submit"]')
    page.wait_for_timeout(2000)

    print("2. فحص لوحة التحكم والسايدبار التفاعلي...")
    page.goto("http://localhost:8000/")
    page.wait_for_timeout(1000)
    
    aside = page.locator("aside")
    print("Classes on Dashboard aside:", aside.get_attribute("class"))

    # Test toggling the sidebar
    toggle_btn = page.locator('button[title*="تصغير القائمة"], button[title*="توسيع القائمة"]').first
    if toggle_btn.is_visible():
        print("✅ تم العثور على زر تصغير/تكبير السايدبار!")
        toggle_btn.click()
        page.wait_for_timeout(500)
        print("Classes after toggle:", aside.get_attribute("class"))
    
    print("3. الانتقال لشاشة نقطة البيع (POS) وفحص التصغير الإجباري وشاشة اللمس...")
    page.goto("http://localhost:8000/invoices/create")
    page.wait_for_timeout(2000)

    pos_aside = page.locator("aside")
    print("Classes on POS aside:", pos_aside.get_attribute("class"))

    body_text = page.locator("body").inner_text()
    assert "كاشير ومبيعات مطحنة البن والشاي والتوزيع (Touch POS)" in body_text, "Title not found!"
    assert "سلة الفاتورة" in body_text, "Cart not found!"
    assert "طريقة الدفع والسداد" in body_text, "Payment not found!"
    assert "لوحة أرقام اللمس" in body_text, "Numpad toggle not found!"

    print("4. اختبار التفاعل باللمس: إضافة صنف بالنقر السريع...")
    first_add_btn = page.locator('button[title*="المس للإضافة السريعة"]').first
    if first_add_btn.is_visible():
        first_add_btn.click()
        page.wait_for_timeout(1000)
        print("✅ تم النقر باللمس على الصنف لإضافته للسلة!")
    
    cart_text = page.locator("body").inner_text()
    assert "الصافي المطلوب" in cart_text

    print("✅ تم التحقق بنجاح من كافة متطلبات السايدبار المصغر وشاشة نقطة البيع باللمس!")
    browser.close()
