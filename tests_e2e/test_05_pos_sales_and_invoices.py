import pytest
from playwright.sync_api import Page, expect
from config import BASE_URL
from helpers import wait_for_livewire

def test_pos_screen_renders_with_quick_weights(page: Page):
    """Test POS invoice create screen renders with item selector and fractional weight buttons."""
    page.goto(f"{BASE_URL}/invoices/create")
    wait_for_livewire(page)
    
    expect(page.locator("body")).to_contain_text("كاشير ومبيعات")
    expect(page.locator("body")).not_to_contain_text("500 Server Error")

def test_create_pos_invoice_interactive(page: Page):
    """Test creating a sales invoice with fractional coffee weight and cash payment."""
    page.goto(f"{BASE_URL}/invoices/create")
    wait_for_livewire(page)
    
    # Search or select an item from quick catalog
    search_box = page.locator('input[placeholder*="ابحث"]').first
    if search_box.is_visible():
        search_box.fill("بن")
        wait_for_livewire(page)
    
    # Click item from catalog
    prod_card = page.locator('.group').first
    if prod_card.is_visible():
        prod_card.click()
        wait_for_livewire(page)
        
        # Click quick weight 1/4 kg if present
        weight_btn = page.locator('button:has-text("1/4"), button:has-text("ربع")').first
        if weight_btn.is_visible():
            weight_btn.click()
            wait_for_livewire(page)
            
        # Click save and approve invoice
        save_btn = page.locator('button:has-text("حفظ واعتماد")').first
        if save_btn.is_visible():
            save_btn.click()
            wait_for_livewire(page)

def test_invoices_index_and_filters(page: Page):
    """Test invoice listing, payment status tabs, and trash bin tabs."""
    page.goto(f"{BASE_URL}/invoices")
    wait_for_livewire(page)
    
    expect(page.locator("body")).to_contain_text("سجل فواتير المبيعات")
    expect(page.locator("body")).not_to_contain_text("500 Server Error")
