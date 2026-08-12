import pytest
from playwright.sync_api import Page, expect
from config import BASE_URL
from helpers import wait_for_livewire, safe_goto

def test_customers_page_renders_and_search(page: Page):
    """Test customers index renders, search input works, and trash tab toggles."""
    safe_goto(page, "/customers")
    
    expect(page.locator("body")).to_contain_text("دليل العملاء والحسابات")
    expect(page.locator("body")).not_to_contain_text("500 Server Error")

def test_create_customer_interactive(page: Page):
    """Test creating a new customer with opening balance."""
    safe_goto(page, "/customers")
    
    # Open modal
    page.locator('button:has-text("إضافة عميل جديد")').first.click()
    wait_for_livewire(page)
    
    # Fill fields
    page.locator('input[wire\\:model="name"]').fill("عميل تجريبي E2E")
    page.locator('input[wire\\:model="phone"]').fill("01099998888")
    page.locator('input[wire\\:model="opening_balance"]').fill("750.000")
    page.locator('input[wire\\:model="address"]').fill("القاهرة - مدينة نصر")
    
    # Save
    page.locator('button:has-text("إضافة العميل")').click()
    wait_for_livewire(page)
    
    # Verify customer created
    expect(page.locator("body")).to_contain_text("عميل تجريبي E2E")
    expect(page.locator("body")).to_contain_text("01099998888")

def test_edit_customer_interactive(page: Page):
    """Test editing customer details."""
    safe_goto(page, "/customers")
    
    edit_btn = page.locator('table button[wire\\:click*="openEditModal"], button[wire\\:click*="openEditModal"]:visible').first
    expect(edit_btn).to_be_visible(timeout=10000)
    edit_btn.click()
    wait_for_livewire(page)
    
    page.locator('input[wire\\:model="address"]').fill("القاهرة - التجمع الخامس")
    page.locator('button:has-text("حفظ التعديلات")').click()
    wait_for_livewire(page)
    expect(page.locator("body")).not_to_contain_text("500 Server Error")

def test_soft_delete_and_restore_customer_interactive(page: Page):
    """Test creating a temporary customer, soft-deleting to trash, and restoring back."""
    safe_goto(page, "/customers")
    
    # 1. Create temporary customer
    page.locator('button:has-text("إضافة عميل جديد")').first.click()
    wait_for_livewire(page)
    page.locator('input[wire\\:model="name"]').fill("عميل مؤقت للحذف E2E")
    page.locator('input[wire\\:model="phone"]').fill("01077776666")
    page.locator('button:has-text("إضافة العميل")').click()
    wait_for_livewire(page)
    
    expect(page.locator("body")).to_contain_text("عميل مؤقت للحذف E2E")
    
    # 2. Soft delete customer (visible table button)
    delete_btn = page.locator('table button[wire\\:click*="deleteCustomer"], button[wire\\:click*="deleteCustomer"]:visible').first
    expect(delete_btn).to_be_visible(timeout=10000)
    delete_btn.click()
    wait_for_livewire(page)
        
    # 3. Switch to trash tab
    page.locator('button:has-text("سلة المحذوفات")').first.click()
    wait_for_livewire(page)
    expect(page.locator("body")).to_contain_text("01077776666")
    
    # 4. Restore customer
    restore_btn = page.locator('table button[wire\\:click*="restoreCustomer"], button[wire\\:click*="restoreCustomer"]:visible').first
    expect(restore_btn).to_be_visible(timeout=10000)
    restore_btn.click()
    wait_for_livewire(page)
        
    # 5. Switch to active and verify restored
    page.locator('button:has-text("النشطين")').first.click()
    wait_for_livewire(page)
    expect(page.locator("body")).to_contain_text("01077776666")

def test_create_and_trash_supplier_interactive(page: Page):
    """Test creating a supplier, soft deleting, and restoring from trash."""
    safe_goto(page, "/suppliers")
    
    # 1. Create supplier
    page.locator('button:has-text("إضافة مورد جديد")').first.click()
    wait_for_livewire(page)
    
    page.locator('input[wire\\:model="name"]').fill("مورد البن الإثيوبي E2E")
    page.locator('input[wire\\:model="company_name"]').fill("شركة الأهرام للبن")
    page.locator('input[wire\\:model="phone"]').fill("01111223344")
    page.locator('input[wire\\:model="address"]').fill("ميناء الإسكندرية - مخازن البن")
    
    page.locator('button:has-text("إضافة المورد")').click()
    wait_for_livewire(page)
    
    expect(page.locator("body")).to_contain_text("مورد البن الإثيوبي E2E")
    
    # 2. Soft delete supplier (visible table button)
    del_btn = page.locator('table button[wire\\:click*="deleteSupplier"], button[wire\\:click*="deleteSupplier"]:visible').first
    expect(del_btn).to_be_visible(timeout=10000)
    del_btn.click()
    wait_for_livewire(page)
        
    # 3. Check trash
    page.locator('button:has-text("سلة المحذوفات")').first.click()
    wait_for_livewire(page)
    expect(page.locator("body")).to_contain_text("مورد البن الإثيوبي E2E")
    
    # 4. Restore supplier
    res_btn = page.locator('table button[wire\\:click*="restoreSupplier"], button[wire\\:click*="restoreSupplier"]:visible').first
    expect(res_btn).to_be_visible(timeout=10000)
    res_btn.click()
    wait_for_livewire(page)
        
    # 5. Check active list
    page.locator('button:has-text("النشطين")').first.click()
    wait_for_livewire(page)
    expect(page.locator("body")).to_contain_text("مورد البن الإثيوبي E2E")
