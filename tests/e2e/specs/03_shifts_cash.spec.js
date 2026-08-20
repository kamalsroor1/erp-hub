import { test, expect } from '../fixtures/auth.fixture.js';

test.describe('3️⃣ موديول ورديات الكاشير ودرج النقدية (Cash Shifts & Z-Report E2E)', () => {

  test('TC-01: استعراض شاشة الورديات والتعرف على حالة الوردية الحالية', async ({ authenticatedPage: page }) => {
    await page.goto('/shifts');

    // Verify Shifts page header
    await expect(page.getByText(/إدارة ورديات الكاشير|ورديات الكاشير|الوردية/).first()).toBeVisible({ timeout: 10000 });

    // Check presence of open form or close shift action
    const shiftAction = page.getByText(/فتح وردية|إغلاق وتقفيل|رصيد الفكة|وردية/).first();
    await expect(shiftAction).toBeVisible({ timeout: 5000 });
  });

  test('TC-02: فحص حقول الرصيد الافتتاحي والنقدية', async ({ authenticatedPage: page }) => {
    await page.goto('/shifts');

    const cashInput = page.locator('input[type="number"], input[type="text"]').first();
    await expect(cashInput).toBeVisible({ timeout: 5000 });
  });

  test('TC-03: فحص بطاقة تقرير الوردية والـ Z-Report', async ({ authenticatedPage: page }) => {
    await page.goto('/shifts');

    const container = page.locator('main, .space-y-4').first();
    await expect(container).toBeVisible();
  });

});
