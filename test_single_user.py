import subprocess
import sys
import time
from playwright.sync_api import sync_playwright, expect

if hasattr(sys.stdout, 'reconfigure'):
    try:
        sys.stdout.reconfigure(encoding='utf-8', errors='replace')
    except Exception:
        pass

subprocess.run(["php", "clean_e2e.php"])

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    page = browser.new_page()
    
    page.on("console", lambda msg: print(f"CONSOLE: {msg.type}: {msg.text}"))
    page.on("pageerror", lambda err: print(f"PAGE ERROR: {err}"))
    
    page.goto("http://localhost:8000/login")
    page.fill("#phone", "01012316954")
    page.fill("#password", "password")
    page.click("button[type='submit']")
    page.wait_for_url("http://localhost:8000/")
    
    page.goto("http://localhost:8000/users")
    page.wait_for_load_state("networkidle")
    
    # 1. Create cashier
    print("[1] Opening modal...")
    page.locator('button:has-text("إضافة مستخدم جديد")').first.click()
    page.wait_for_selector('form:has-text("حفظ بيانات المستخدم")')
    
    print("[2] Filling fields...")
    page.locator('div.fixed input[placeholder*="أحمد محمود"]').fill("أحمد كاشير المعادي E2E")
    page.locator('div.fixed input[placeholder*="01012316954"]').fill("01055554444")
    page.locator('div.fixed select[wire\\:model="role"]').select_option("cashier")
    page.locator('div.fixed input[placeholder*="••••••••"]').fill("password123")
    
    print("[3] Clicking submit button...")
    page.locator('div.fixed button:has-text("حفظ بيانات المستخدم")').click()
    
    time.sleep(2)
    
    print("[4] Checking modal visibility after submit...")
    modal_vis = page.locator('div.fixed.inset-0.z-50').is_visible()
    print("Modal is visible:", modal_vis)
    
    if modal_vis:
        print("Modal text content:", page.locator('div.fixed.inset-0.z-50').inner_text())
        page.screenshot(path="modal_failure.png")
    else:
        print("Success! Table text:")
        print(page.locator('table').inner_text())
        
    browser.close()
