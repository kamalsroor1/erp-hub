import time
import os
import sys
from playwright.sync_api import Page, expect
from config import BASE_URL, ADMIN_PHONE, ADMIN_PASSWORD, SCREENSHOTS_DIR

if hasattr(sys.stdout, 'reconfigure'):
    try:
        sys.stdout.reconfigure(encoding='utf-8', errors='replace')
    except Exception:
        pass
if hasattr(sys.stderr, 'reconfigure'):
    try:
        sys.stderr.reconfigure(encoding='utf-8', errors='replace')
    except Exception:
        pass

def login_as_admin(page: Page, phone: str = ADMIN_PHONE, password: str = ADMIN_PASSWORD):
    """Ensures page is on dashboard, logs in only if not already authenticated."""
    page.goto(f"{BASE_URL}/", wait_until="domcontentloaded", timeout=30000)
    time.sleep(0.3)
    
    # If redirected to login page, fill credentials
    if "/login" in page.url:
        page.wait_for_selector('#phone', timeout=10000)
        page.locator('#phone').fill(phone)
        page.locator('#password').fill(password)
        page.locator('button[type="submit"]').click()
        page.wait_for_url(f"{BASE_URL}/", timeout=15000)
        time.sleep(0.5)

def wait_for_livewire(page: Page):
    """Waits for Livewire requests to settle using networkidle + small buffer."""
    try:
        page.wait_for_load_state("networkidle", timeout=8000)
    except Exception:
        pass
    time.sleep(0.6)

def wait_for_modal_close(page: Page):
    """Waits for any open fixed backdrop/modal to close and detach."""
    wait_for_livewire(page)
    try:
        page.wait_for_selector('div.fixed.inset-0.z-50', state='detached', timeout=3000)
    except Exception:
        pass
    time.sleep(0.4)

def safe_goto(page: Page, path: str):
    """Navigate to a page with proper timeout and networkidle wait."""
    page.goto(f"{BASE_URL}{path}", wait_until="domcontentloaded", timeout=30000)
    wait_for_livewire(page)

def capture_screenshot(page: Page, name: str):
    """Captures a screenshot in the screenshots directory."""
    path = os.path.join(SCREENSHOTS_DIR, f"{name}_{int(time.time())}.png")
    page.screenshot(path=path, full_page=True)
    print(f"📸 Screenshot saved: {path}")
    return path
