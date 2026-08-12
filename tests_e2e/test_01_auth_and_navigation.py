import pytest
import re
from playwright.sync_api import Page, expect
from config import BASE_URL, ADMIN_PHONE, ADMIN_PASSWORD
from helpers import wait_for_livewire, safe_goto

def test_login_flow_and_authentication(page: Page):
    """Test login form elements, credential submission, and redirection to dashboard."""
    page.goto(f"{BASE_URL}/login", wait_until="domcontentloaded", timeout=30000)
    wait_for_livewire(page)
    
    # If not already on dashboard, perform full login verification
    if "/login" in page.url:
        expect(page).to_have_title(re.compile("تسجيل الدخول"))
        expect(page.locator('#phone')).to_be_visible()
        expect(page.locator('#password')).to_be_visible()
        
        page.locator('#phone').fill(ADMIN_PHONE)
        page.locator('#password').fill(ADMIN_PASSWORD)
        page.locator('button[type="submit"]').click()
        
        page.wait_for_url(f"{BASE_URL}/", timeout=15000)
    
    expect(page.locator("body")).to_contain_text("سرور كوفي")

def test_admin_dashboard_overview(page: Page):
    """Test dashboard elements and store selector in the active window."""
    safe_goto(page, "/")
    expect(page).to_have_url(f"{BASE_URL}/")
    expect(page.locator("body")).to_contain_text("سرور كوفي")

def test_sidebar_navigation_links(page: Page):
    """Test that all key navigation links in sidebar load cleanly in the continuous session."""
    routes_to_test = [
        ("/invoices", "سجل فواتير المبيعات"),
        ("/invoices/create", "كاشير ومبيعات"),
        ("/items", "الأصناف"),
        ("/customers", "العملاء"),
        ("/suppliers", "المورد"),
        ("/purchases", "المشتريات"),
        ("/stores", "الفروع"),
        ("/returns", "المرتجع"),
        ("/expenses", "المصروفات"),
        ("/daily-journal", "اليومية"),
        ("/reports", "التقارير"),
        ("/trash", "سلة المحذوفات"),
    ]
    
    for route_path, expected_text in routes_to_test:
        safe_goto(page, route_path)
        expect(page.locator("body")).not_to_contain_text("500 Server Error")
        expect(page.locator("body")).to_contain_text(expected_text)
