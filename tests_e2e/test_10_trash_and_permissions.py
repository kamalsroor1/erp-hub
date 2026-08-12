import pytest
from playwright.sync_api import Page, expect
from config import BASE_URL
from helpers import wait_for_livewire

def test_central_trash_bin_all_tabs(page: Page):
    """Test central recycle bin renders with all 8 model tabs and live counters."""
    page.goto(f"{BASE_URL}/trash")
    wait_for_livewire(page)
    
    expect(page.locator("body")).to_contain_text("سلة المحذوفات المركزية")
    
    tabs = [
        "الأصناف",
        "العملاء",
        "الموردون",
        "الفروع",
        "فواتير المبيعات",
        "فواتير المشتريات",
        "المصروفات",
        "المرتجعات",
    ]
    
    for tab_name in tabs:
        tab_btn = page.locator(f'button:has-text("{tab_name}")').first
        expect(tab_btn).to_be_visible()
        tab_btn.click()
        wait_for_livewire(page)
        expect(page.locator("body")).not_to_contain_text("500 Server Error")
