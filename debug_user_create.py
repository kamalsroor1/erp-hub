import sys
from playwright.sync_api import sync_playwright

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    page = browser.new_page()

    page.goto("http://localhost:8000/login")
    page.fill("input#phone", "01012316954")
    page.fill("input#password", "password")
    page.click("button[type='submit']")
    page.wait_for_url("http://localhost:8000/")
    page.wait_for_timeout(1000)

    page.goto("http://localhost:8000/users")
    page.wait_for_timeout(1000)

    page.locator('button:has-text("إضافة مستخدم جديد")').first.click()
    page.wait_for_timeout(1000)

    page.locator('form input[wire\\:model="name"]').fill("أحمد كاشير المعادي E2E")
    page.locator('form input[wire\\:model="phone"]').fill("01055554444")
    page.locator('form select[wire\\:model="role"]').select_option("cashier")
    page.locator('form input[wire\\:model="password"]').fill("password123")

    page.screenshot(path="before_submit.png")
    print("Clicking save...")
    page.locator('form button[type="submit"]:has-text("حفظ")').click()
    page.wait_for_timeout(3000)
    page.screenshot(path="after_submit.png")

    form_text = page.locator("body").inner_text()
    print("Page / Modal content:")
    print(form_text[:1000])

    browser.close()
