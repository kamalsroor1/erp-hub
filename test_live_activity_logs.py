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

    print("2. الوصول للوحة التحكم. URL:", page.url)

    # Find the activity logs link in the sidebar
    logs_link = page.locator('nav a:has-text("سجل العمليات والرقابة")')
    if logs_link.count() > 0:
        print("✅ تم العثور على رابط [📜 سجل العمليات والرقابة] في السايدبار!")
        logs_link.first.click()
        page.wait_for_timeout(3000)
    else:
        print("⚠️ لم يتم العثور على الرابط في السايدبار، جاري الانتقال مباشرة...")
        page.goto("https://sroor.baraa-solutions.com/activity-logs")
        page.wait_for_timeout(3000)

    print("3. صفحة سجل العمليات المفتوحة. URL:", page.url)
    print("Page Title:", page.title())

    # Check page content
    body_text = page.locator("body").inner_text()
    assert "سجل العمليات والرقابة الذاتية" in body_text, "Title text not found!"
    assert "إجمالي عمليات اليوم" in body_text, "Stats card not found!"
    assert "القسم" in body_text or "المبيعات" in body_text or "الفترة الزمنية" in body_text

    print("✅ تم التحقق بنجاح من تحميل شاشة سجل العمليات والرقابة على السيرفر الحي!")
    print("نص الواجهة المقتطف:\n", body_text[:600])

    browser.close()
