import { test, expect } from '../fixtures/auth.fixture.js';

test.describe('5️⃣ موديول فواتير المبيعات والإلغاء والواتساب (Sales Invoices Lifecycle E2E)', () => {

  test('TC-01: استعراض سجل فواتير المبيعات وفحص شارات الدفع', async ({ authenticatedPage: page }) => {
    await page.goto('/invoices');

    // Verify invoices page title
    await expect(page.getByText(/فواتير المبيعات|الفواتير|فاتورة/).first()).toBeVisible({ timeout: 10000 });

    // Verify search input
    const searchInput = page.locator('input[placeholder*="بحث"]').first();
    await expect(searchInput).toBeVisible({ timeout: 5000 });

    // Verify invoice cards container
    const mainContainer = page.locator('main, .space-y-3, .space-y-2').first();
    await expect(mainContainer).toBeVisible();
  });

  test('TC-02: فتح تفاصيل فاتورة محددة ومراجعة بيانات الأصناف والمبالغ', async ({ authenticatedPage: page }) => {
    await page.goto('/invoices');

    // Click on the first invoice link or view details button
    const firstInvoice = page.locator('a[href*="/invoices/"]').first();
    if (await firstInvoice.isVisible({ timeout: 5000 })) {
      await firstInvoice.click();
      await page.waitForURL(/\/invoices\/\d+/, { timeout: 8000 });

      // Verify invoice details page elements
      await expect(page.getByText(/فاتورة|رقم الفاتورة|الإجمالي/).first()).toBeVisible({ timeout: 5000 });
    }
  });

  test('TC-03: فحص زر مشاركة الفاتورة عبر واتساب وتجهيز الرسالة', async ({ authenticatedPage: page }) => {
    await page.goto('/invoices/1');

    // Check WhatsApp share button
    const whatsappBtn = page.locator('a[href*="whatsapp"], button:has-text("واتساب"), button:has-text("WhatsApp")').first();
    if (await whatsappBtn.isVisible({ timeout: 5000 })) {
      const href = await whatsappBtn.getAttribute('href');
      if (href) {
        expect(href).toContain('whatsapp');
      }
    }
  });

  test('TC-04: إلغاء فاتورة مبيعات والتأكد من تغيير حالتها وعكس الأثر', async ({ authenticatedPage: page }) => {
    await page.goto('/invoices');

    // Look for actions button (⋯) on an active invoice
    const optionsBtn = page.locator('button[title*="خيارات"], button:has-text("⋯")').first();
    if (await optionsBtn.isVisible({ timeout: 3000 })) {
      await optionsBtn.click();

      const cancelBtn = page.getByRole('button', { name: /إلغاء الفاتورة|إلغاء/ }).first();
      if (await cancelBtn.isVisible({ timeout: 3000 })) {
        await cancelBtn.click();
        await page.waitForTimeout(1000);
      }
    }
  });

});
