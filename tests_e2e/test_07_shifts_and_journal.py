import pytest
from playwright.sync_api import Page, expect
from config import BASE_URL
from helpers import wait_for_livewire

def test_daily_journal_renders_with_date_navigation(page: Page):
    """Test daily sales journal renders properly with presets (today/yesterday)."""
    page.goto(f"{BASE_URL}/daily-journal")
    wait_for_livewire(page)
    
    expect(page.locator("body")).to_contain_text("يومية المبيعات وحركة الدرج")
    expect(page.locator("body")).not_to_contain_text("500 Server Error")
    
    # Test date presets
    today_btn = page.locator('button:has-text("اليوم")').first
    if today_btn.is_visible():
        today_btn.click()
        wait_for_livewire(page)
        expect(page.locator("body")).not_to_contain_text("500 Server Error")

def test_cash_shifts_index(page: Page):
    """Test cash shifts / drawer management screen."""
    page.goto(f"{BASE_URL}/shifts")
    wait_for_livewire(page)
    
    expect(page.locator("body")).not_to_contain_text("500 Server Error")
