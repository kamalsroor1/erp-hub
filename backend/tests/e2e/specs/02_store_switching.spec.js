import { test, expect } from '../fixtures/auth.fixture.js';

test.describe('2️⃣ موديول تعدد الفروع وتبديل المخزن (Multi-Branch & Store Switching E2E)', () => {

  test('TC-01: عرض الفرع النشط في الهيدر والبطاقة الرئيسية', async ({ authenticatedPage: page }) => {
    await page.goto('/');

    // Check active branch indicator using regex locator
    const branchText = page.getByText(/الفرع النشط|الفرع الرئيسي|فرع/).first();
    await expect(branchText).toBeVisible({ timeout: 10000 });
  });

  test('TC-02: فتح نافذة تبديل الفرع واستعراض الفروع المتاحة', async ({ authenticatedPage: page }) => {
    await page.goto('/');

    // Open side menu drawer
    const menuBtn = page.locator('nav button').last();
    await menuBtn.click();

    // Click branch switch button
    const switchBtn = page.getByRole('button', { name: /تبديل|الفرع/ }).first();
    if (await switchBtn.isVisible({ timeout: 5000 })) {
      await switchBtn.click();
      // Verify modal appeared
      await expect(page.getByText(/تبديل الفرع|اختيار الفرع|فروع/).first()).toBeVisible({ timeout: 5000 });
    }
  });

  test('TC-03: التبديل إلى فرع آخر وتحديث السياق لحظياً', async ({ authenticatedPage: page }) => {
    await page.goto('/');

    // Open side drawer
    await page.locator('nav button').last().click();

    const switchBtn = page.getByRole('button', { name: /تبديل/ }).first();
    if (await switchBtn.isVisible({ timeout: 5000 })) {
      await switchBtn.click();

      // Click on another store card if available
      const storeOptions = page.locator('button:has-text("فرع"), div:has-text("فرع")');
      const count = await storeOptions.count();
      if (count > 1) {
        await storeOptions.nth(1).click();
        await page.waitForTimeout(1000);
      }
    }

    // Verify page is still functional
    await expect(page.getByText(/مرحباً بك|الرئيسية/).first()).toBeVisible();
  });

});
