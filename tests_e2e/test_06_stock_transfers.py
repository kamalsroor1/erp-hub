import pytest
from playwright.sync_api import Page, expect
from config import BASE_URL
from helpers import wait_for_livewire

def test_stock_transfers_index(page: Page):
    """Test stock transfers list page renders properly."""
    page.goto(f"{BASE_URL}/stock-transfers")
    wait_for_livewire(page)
    
    expect(page.locator("body")).to_contain_text("أذونات شحن وتحويل البضاعة")
    expect(page.locator("body")).not_to_contain_text("500 Server Error")

def test_stock_transfer_create_screen(page: Page):
    """Test stock transfer creation screen renders with source and destination stores."""
    page.goto(f"{BASE_URL}/stock-transfers/create")
    wait_for_livewire(page)
    
    expect(page.locator("body")).to_contain_text("إذن تحويل")
    expect(page.locator("body")).not_to_contain_text("500 Server Error")
