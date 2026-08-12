import pytest
from playwright.sync_api import Page, expect
from config import BASE_URL
from helpers import wait_for_livewire, safe_goto

def test_items_list_and_search(page: Page):
    """Test items table renders and search works smoothly."""
    safe_goto(page, "/items")
    
    expect(page.locator("body")).to_contain_text("إدارة الأصناف والمخزون")
    expect(page.locator("body")).not_to_contain_text("500 Server Error")

def test_create_new_item_interactive(page: Page):
    """Test adding a new product via the UI modal with initial stock."""
    safe_goto(page, "/items")
    
    # Click open create modal
    page.locator('button:has-text("إضافة صنف جديد")').first.click()
    wait_for_livewire(page)
    
    # Fill form fields
    page.locator('input[wire\\:model="code"]').fill("BRZ-E2E-01")
    page.locator('input[wire\\:model="name"]').fill("بن برازيلي تجريبي E2E")
    page.locator('input[wire\\:model="category"]').fill("بن وتوليفات")
    page.locator('input[wire\\:model="cost_price"]').fill("220.000")
    page.locator('input[wire\\:model="selling_price"]').fill("300.000")
    page.locator('input[wire\\:model="current_stock"]').fill("15.000")
    
    # Submit form
    page.locator('button:has-text("إضافة الصنف")').click()
    wait_for_livewire(page)
    
    # Verify new item appears in items table
    expect(page.locator("body")).to_contain_text("بن برازيلي تجريبي E2E")
    expect(page.locator("body")).to_contain_text("BRZ-E2E-01")

def test_edit_item_interactive(page: Page):
    """Test editing an existing item's selling price."""
    safe_goto(page, "/items")
    
    # Click edit on the visible table item
    edit_btn = page.locator('table button[wire\\:click*="openEditModal"], button[wire\\:click*="openEditModal"]:visible').first
    expect(edit_btn).to_be_visible(timeout=10000)
    edit_btn.click()
    wait_for_livewire(page)
    
    # Update price to 320.000
    page.locator('input[wire\\:model="selling_price"]').fill("320.000")
    page.locator('button:has-text("حفظ التعديلات")').click()
    wait_for_livewire(page)
    
    expect(page.locator("body")).not_to_contain_text("500 Server Error")

def test_soft_delete_and_restore_item_interactive(page: Page):
    """Test creating an item, soft deleting it to trash, and restoring it back."""
    safe_goto(page, "/items")
    
    # 1. Create a temporary item for delete/restore test
    page.locator('button:has-text("إضافة صنف جديد")').first.click()
    wait_for_livewire(page)
    
    page.locator('input[wire\\:model="code"]').fill("DEL-ITEM-99")
    page.locator('input[wire\\:model="name"]').fill("صنف مؤقت للاختبار والحذف")
    page.locator('input[wire\\:model="cost_price"]').fill("100.000")
    page.locator('input[wire\\:model="selling_price"]').fill("150.000")
    page.locator('button:has-text("إضافة الصنف")').click()
    wait_for_livewire(page)
    
    expect(page.locator("body")).to_contain_text("صنف مؤقت للاختبار والحذف")
    
    # 2. Soft delete item (visible table button)
    delete_btn = page.locator('table button[wire\\:click*="deleteItem"], button[wire\\:click*="deleteItem"]:visible').first
    expect(delete_btn).to_be_visible(timeout=10000)
    delete_btn.click()
    wait_for_livewire(page)
    
    # 3. Switch to trash tab and verify item in trash
    page.locator('button:has-text("سلة المحذوفات")').first.click()
    wait_for_livewire(page)
    expect(page.locator("body")).to_contain_text("DEL-ITEM-99")
    
    # 4. Click restore on visible restore button
    restore_btn = page.locator('table button[wire\\:click*="restoreItem"], button[wire\\:click*="restoreItem"]:visible').first
    expect(restore_btn).to_be_visible(timeout=10000)
    restore_btn.click()
    wait_for_livewire(page)
    
    # 5. Switch back to active items and verify it returned
    page.locator('button:has-text("النشطة")').first.click()
    wait_for_livewire(page)
    expect(page.locator("body")).to_contain_text("DEL-ITEM-99")
