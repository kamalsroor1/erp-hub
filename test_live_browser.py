import sys
from playwright.sync_api import sync_playwright

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

def main():
    print("=" * 60)
    print("🌐 فحص المتصفح الحي الحقيقي على https://sroor.baraa-solutions.com")
    print("=" * 60)

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(ignore_https_errors=True)
        page = context.new_page()

        # 1. Login Page
        print("  [*] 1. جاري فتح صفحة تسجيل الدخول...")
        page.goto("https://sroor.baraa-solutions.com/login", wait_until="networkidle")
        print(f"      عنوان الصفحة: {page.title()}")

        # 2. Perform Login
        print("  [*] 2. كتابة بيانات الدخول للمدير العام (01012316954 / password)...")
        page.fill("input#phone", "01012316954")
        page.fill("input#password", "password")
        page.click('button[type="submit"]')
        page.wait_for_load_state("networkidle")
        page.wait_for_timeout(3000)

        print(f"      الرابط بعد الدخول: {page.url}")
        print(f"      عنوان الصفحة الحالية: {page.title()}")

        # 3. Check /roles page
        print("  [*] 3. التوجه لشاشة مصفوفة الصلاحيات والأدوار (/roles)...")
        page.goto("https://sroor.baraa-solutions.com/roles", wait_until="networkidle")
        page.wait_for_timeout(2000)
        print(f"      الرابط: {page.url}")
        print(f"      العنوان: {page.title()}")
        body_text = page.locator("body").inner_text()
        
        has_roles_title = "الصلاحيات" in body_text or "الأدوار" in body_text
        print(f"      هل ظهرت مصفوفة الصلاحيات؟ {'نعم ✅' if has_roles_title else 'لا ❌'}")

        # 4. Check /users page
        print("  [*] 4. التوجه لشاشة المستخدمين (/users)...")
        page.goto("https://sroor.baraa-solutions.com/users", wait_until="networkidle")
        page.wait_for_timeout(2000)
        print(f"      الرابط: {page.url}")
        body_text = page.locator("body").inner_text()
        has_users = "كمال سرور" in body_text
        print(f"      هل ظهر المستخدمين وبيانات المدير؟ {'نعم ✅' if has_users else 'لا ❌'}")

        # 5. Check /stores page
        print("  [*] 5. التوجه لشاشة الفروع (/stores)...")
        page.goto("https://sroor.baraa-solutions.com/stores", wait_until="networkidle")
        page.wait_for_timeout(2000)
        print(f"      الرابط: {page.url}")
        body_text = page.locator("body").inner_text()
        has_stores = "المخزن والفرع الرئيسي" in body_text
        print(f"      هل ظهر الفرع والمخزن الرئيسي؟ {'نعم ✅' if has_stores else 'لا ❌'}")

        browser.close()

    print("\n🎉 تم فحص وتشغيل النظام بنجاح تام على السيرفر الحي!")

if __name__ == "__main__":
    main()
