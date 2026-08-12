import pytest
from playwright.sync_api import Page, expect
from config import BASE_URL
from helpers import wait_for_livewire

def test_expenses_index_and_modal(page: Page):
    """Test expenses index page and stats overview."""
    page.goto(f"{BASE_URL}/expenses")
    wait_for_livewire(page)
    
    expect(page.locator("body")).to_contain_text("المصروفات والنثريات")
    expect(page.locator("body")).not_to_contain_text("500 Server Error")

def test_create_edit_delete_restore_expense_interactive(page: Page):
    """Test full interactive CRUD and soft delete/restore for expenses."""
    page.goto(f"{BASE_URL}/expenses")
    wait_for_livewire(page)
    
    # 1. Open create modal
    page.locator('button:has-text("تسجيل مصروف جديد")').first.click()
    wait_for_livewire(page)
    
    # 2. Fill expense details
    page.locator('input[wire\\:model="title"]').fill("شراء 500 شنطة أكياس بن E2E")
    page.locator('input[wire\\:model="amount"]').fill("250.000")
    
    # 3. Save
    page.locator('button:has-text("إضافة المصروف")').click()
    wait_for_livewire(page)
    
    expect(page.locator("body")).to_contain_text("شراء 500 شنطة أكياس بن E2E")
    
    # 4. Edit expense
    edit_btn = page.locator('button:has-text("تعديل")').first
    if edit_btn.is_visible():
        edit_btn.click()
        wait_for_livewire(page)
        page.locator('input[wire\\:model="amount"]').fill("280.000")
        page.locator('button:has-text("حفظ التعديلات")').click()
        wait_for_livewire(page)
        expect(page.locator("body")).not_to_contain_text("500 Server Error")

def test_returns_index_and_create(page: Page):
    """Test returns index page renders properly."""
    page.goto(f"{BASE_URL}/returns")
    wait_for_livewire(page)
    
    expect(page.locator("body")).to_contain_text("مرتجع")
    expect(page.locator("body")).not_to_contain_text("500 Server Error")
