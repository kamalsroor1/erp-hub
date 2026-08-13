import subprocess
import sys
from playwright.sync_api import sync_playwright

if hasattr(sys.stdout, 'reconfigure'):
    try:
        sys.stdout.reconfigure(encoding='utf-8', errors='replace')
    except Exception:
        pass

code = """
DB::table('users')->whereIn('phone', ['01055554444','01033332222'])->delete();
DB::table('stores')->whereIn('code', ['SHOP-MAADI','VAN-E2E-02','TEMP-SHOP-99'])->delete();
DB::table('roles')->where('name', 'branch_manager')->delete();
"""
subprocess.run(["php", "artisan", "tinker", f"--execute={code}"], capture_output=True)

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    page = browser.new_page()

    page.on("console", lambda msg: print(f"CONSOLE: {msg.type}: {msg.text}"))
    page.on("pageerror", lambda err: print(f"PAGE ERROR: {err}"))
    page.on("response", lambda resp: print(f"HTTP {resp.status}: {resp.url}") if "livewire" in resp.url else None)

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

    page.locator('input[placeholder*="أحمد محمود"]').fill("أحمد كاشير المعادي E2E")
    page.locator('input[placeholder*="01012316954"]').fill("01055554444")
    page.locator('select[wire\\:model="role"]').select_option("cashier")
    page.locator('input[placeholder*="••••••••"]').fill("password123")
    page.wait_for_timeout(500)

    page.screenshot(path="before_submit.png")

    print("Clicking submit button...")
    page.locator('button:has-text("حفظ بيانات المستخدم")').click()
    page.wait_for_timeout(2000)

    page.screenshot(path="after_submit.png")

    print("\nErrors visible in modal:")
    errors = page.locator('span.text-rose-500').all_inner_texts()
    print("Rose errors:", errors)

    print("\nModal visible:", page.locator('h3:has-text("إضافة مستخدم جديد")').is_visible())
    browser.close()
