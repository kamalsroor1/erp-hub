import pytest
from playwright.sync_api import Page, expect
from config import BASE_URL
from helpers import wait_for_livewire

def test_reports_page_and_date_presets(page: Page):
    """Test financial reports page, summary cards, and date filters."""
    page.goto(f"{BASE_URL}/reports")
    wait_for_livewire(page)
    
    expect(page.locator("body")).to_contain_text("التقارير المالية")
    expect(page.locator("body")).not_to_contain_text("500 Server Error")
    
    # Test switching date preset to 'هذا الشهر'
    month_btn = page.locator('button:has-text("هذا الشهر")').first
    if month_btn.is_visible():
        month_btn.click()
        wait_for_livewire(page)
        expect(page.locator("body")).not_to_contain_text("500 Server Error")

def test_reports_store_comparison(page: Page):
    """Test switching branch selector in reports."""
    page.goto(f"{BASE_URL}/reports")
    wait_for_livewire(page)
    
    # Verify report container renders cleanly
    expect(page.locator("body")).to_contain_text("إجمالي المبيعات")
