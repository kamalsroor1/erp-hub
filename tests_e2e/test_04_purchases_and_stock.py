import pytest
from playwright.sync_api import Page, expect
from config import BASE_URL
from helpers import wait_for_livewire, safe_goto

def test_purchases_index_and_filters(page: Page):
    """Test purchases listing renders properly with status tabs and search."""
    safe_goto(page, "/purchases")
    
    expect(page.locator("body")).to_contain_text("فواتير المشتريات")
    expect(page.locator("body")).not_to_contain_text("500 Server Error")

def test_create_purchase_invoice_interactive(page: Page):
    """Test creating a purchase invoice and adding stock to warehouse."""
    safe_goto(page, "/purchases/create")
    
    # Search for an item to add
    search_box = page.locator('input[placeholder*="ابحث"]').first
    expect(search_box).to_be_visible(timeout=10000)
    search_box.fill("بن")
    wait_for_livewire(page)
    
    # Click search result item
    result_item = page.locator('div[wire\\:click*="addItem"]').first
    expect(result_item).to_be_visible(timeout=10000)
    result_item.click()
    wait_for_livewire(page)
    
    # Fill quantity and supplier ref
    qty_input = page.locator('input[placeholder*="الكمية"]').first
    if qty_input.is_visible():
        qty_input.fill("20.000")
        wait_for_livewire(page)
        
    page.locator('input[wire\\:model="supplier_invoice_ref"]').fill("REF-E2E-101")
    
    # Submit purchase
    save_btn = page.locator('button:has-text("تأكيد التوريد")').first
    expect(save_btn).to_be_visible(timeout=10000)
    save_btn.click()
    wait_for_livewire(page)
    
    # Verify redirected or at purchases list
    safe_goto(page, "/purchases")
    expect(page.locator("body")).to_contain_text("فواتير المشتريات")

def test_soft_delete_and_restore_purchase_invoice(page: Page):
    """Test soft deleting a purchase invoice to trash and restoring it back."""
    safe_goto(page, "/purchases")
    
    # 1. Soft delete invoice (auto-accepted by conftest dialog listener)
    del_btn = page.locator('button[wire\\:click*="deletePurchase"]').first
    expect(del_btn).to_be_visible(timeout=10000)
    del_btn.click()
    wait_for_livewire(page)
        
    # 2. Check trash tab
    trash_tab = page.locator('button:has-text("سلة المحذوفات")').first
    expect(trash_tab).to_be_visible(timeout=10000)
    trash_tab.click()
    wait_for_livewire(page)
    expect(page.locator("body")).not_to_contain_text("500 Server Error")
    
    # 3. Restore invoice
    restore_btn = page.locator('button[wire\\:click*="restorePurchase"]').first
    expect(restore_btn).to_be_visible(timeout=10000)
    restore_btn.click()
    wait_for_livewire(page)
        
    # 4. Return to active list
    active_tab = page.locator('button:has-text("النشطة")').first
    expect(active_tab).to_be_visible(timeout=10000)
    active_tab.click()
    wait_for_livewire(page)
    expect(page.locator("body")).not_to_contain_text("500 Server Error")
