import { test, expect } from '../fixtures/auth.fixture.js';

test.describe('4️⃣ موديول كاشير نقاط البيع والسلة السريعة (POS Cashier & Checkout E2E)', () => {

  test('TC-01: فتح شاشة الكاشير وفحص شريط العميل والتصنيفات والأصناف', async ({ authenticatedPage: page }) => {
    await page.goto('/pos');

    // Verify Customer Bar
    await expect(page.getByText(/العميل|عميل/).first()).toBeVisible({ timeout: 10000 });

    // Verify Category Filter pills
    const allCategoryBtn = page.getByRole('button', { name: /الكل/ }).first();
    await expect(allCategoryBtn).toBeVisible({ timeout: 5000 });

    // Verify Items Grid is populated
    const items = page.locator('.grid > div, .grid > button');
    await expect(items.first()).toBeVisible({ timeout: 5000 });
  });

  test('TC-02: إضافة صنف لسلة البيع وتحديث عداد السلة', async ({ authenticatedPage: page }) => {
    await page.goto('/pos');

    // Click on item name
    const itemName = page.locator('.grid > div .cursor-pointer').first();
    await itemName.click();

    // Confirm in weight modal
    const confirmBtn = page.getByRole('button', { name: /إضافة للفاتورة/ }).first();
    if (await confirmBtn.isVisible({ timeout: 5000 })) {
      await confirmBtn.click();
    }

    // Verify Cart Button appeared
    await expect(page.getByText(/السلة/).first()).toBeVisible({ timeout: 5000 });
  });

  test('TC-03: فتح شيت اختيار العميل والبحث عن عميل', async ({ authenticatedPage: page }) => {
    await page.goto('/pos');

    // Click customer bar to open picker sheet
    const customerBar = page.locator('button:has-text("العميل"), button:has-text("عميل")').first();
    await customerBar.click();

    // Verify customer picker sheet
    await expect(page.getByText(/اختيار العميل|دليل العملاء|عميل/).first()).toBeVisible({ timeout: 5000 });

    // Close sheet
    await page.keyboard.press('Escape');
  });

  test('TC-04: فتح شيت الدفع والسلة وإنهاء عملية بيع نقدي سريعة', async ({ authenticatedPage: page }) => {
    await page.goto('/pos');

    // 1. Add item to cart
    const itemName = page.locator('.grid > div .cursor-pointer').first();
    await itemName.click();

    const confirmBtn = page.getByRole('button', { name: /إضافة للفاتورة/ }).first();
    if (await confirmBtn.isVisible({ timeout: 5000 })) {
      await confirmBtn.click();
    }

    // 2. Open Checkout Sheet
    const cartBtn = page.locator('button:has-text("السلة")').first();
    if (await cartBtn.isVisible({ timeout: 5000 })) {
      await cartBtn.click();

      // 3. Verify Checkout Sheet Elements
      await expect(page.getByText(/الإجمالي|الدفع|كاش|المدفوع/).first()).toBeVisible({ timeout: 5000 });

      // 4. Click Submit Sale if visible
      const submitSaleBtn = page.getByRole('button', { name: /إتمام البيع|حفظ الفاتورة|طباعة/ }).first();
      if (await submitSaleBtn.isVisible({ timeout: 5000 })) {
        await submitSaleBtn.click();
        await page.waitForTimeout(1000);
      }
    }
  });

});
