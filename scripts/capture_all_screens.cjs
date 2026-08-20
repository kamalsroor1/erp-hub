const fs = require('fs');
const path = require('path');

async function run() {
    let puppeteer;
    try {
        puppeteer = require('puppeteer');
    } catch (e) {
        puppeteer = require(path.join(__dirname, '../node_modules/puppeteer'));
    }

    const outputDir = path.join(__dirname, '../docs/screenshots');
    if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, { recursive: true });
    }

    let executablePath = '';
    const chromePaths = [
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe'
    ];
    for (const p of chromePaths) {
        if (fs.existsSync(p)) {
            executablePath = p;
            break;
        }
    }

    console.log('🚀 Starting Automated Mobile Screenshot Engine using browser:', executablePath || 'bundled');
    const browser = await puppeteer.launch({
        headless: 'new',
        executablePath: executablePath || undefined,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-web-security']
    });

    const page = await browser.newPage();

    // Mobile Viewport (iPhone 14 Pro / Modern Pixel HD)
    await page.setViewport({
        width: 390,
        height: 844,
        isMobile: true,
        hasTouch: true,
        deviceScaleFactor: 2.5
    });

    const baseUrl = 'http://127.0.0.1:8080';
    const sleep = (ms) => new Promise(r => setTimeout(r, ms));

    const capture = async (filename, title) => {
        await sleep(1000);
        const filePath = path.join(outputDir, filename);
        await page.screenshot({ path: filePath, fullPage: false });
        console.log(`✅ [${filename}] Captured: ${title}`);
    };

    try {
        // 01. Login Screen
        console.log('Navigating to Login...');
        await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });
        await capture('01_login_screen.png', 'شاشة تسجيل الدخول');

        // Perform Login
        console.log('Logging in...');
        await page.evaluate(() => {
            const inputs = document.querySelectorAll('input');
            if (inputs[0]) inputs[0].value = '01000000000';
            if (inputs[1]) inputs[1].value = 'password';
            inputs[0].dispatchEvent(new Event('input', { bubbles: true }));
            inputs[1].dispatchEvent(new Event('input', { bubbles: true }));
            const btn = document.querySelector('button[type="submit"]');
            if (btn) btn.click();
        });

        await sleep(2500);

        // 02. Executive Dashboard
        await page.goto(`${baseUrl}/`, { waitUntil: 'domcontentloaded' });
        await capture('02_dashboard_home.png', 'الرئيسية - مركز العمليات والنبض اللحظي');

        // 03. Side Menu Drawer (Opened)
        await page.evaluate(() => {
            const buttons = Array.from(document.querySelectorAll('button'));
            const menuBtn = buttons.find(b => b.textContent.includes('☰') || b.innerHTML.includes('☰') || b.getAttribute('title')?.includes('قائمة'));
            if (menuBtn) menuBtn.click();
            else if (buttons[buttons.length - 1]) buttons[buttons.length - 1].click();
        });
        await sleep(800);
        await capture('03_side_menu_drawer.png', 'القائمة الجانبية والصلاحيات');

        // Close drawer
        await page.keyboard.press('Escape');
        await sleep(400);

        // 04. POS Cashier
        await page.goto(`${baseUrl}/pos`, { waitUntil: 'domcontentloaded' });
        await capture('04_pos_cashier.png', 'كاشير نقاط البيع POS');

        // 05. Invoices List
        await page.goto(`${baseUrl}/invoices`, { waitUntil: 'domcontentloaded' });
        await capture('05_invoices_list.png', 'سجل فواتير المبيعات');

        // 06. Single Invoice Detail
        await page.goto(`${baseUrl}/invoices/1`, { waitUntil: 'domcontentloaded' }).catch(() => {});
        await capture('06_invoice_details.png', 'تفاصيل الفاتورة ومشاركة واتساب والطباعة');

        // 07. Inventory Items
        await page.goto(`${baseUrl}/items`, { waitUntil: 'domcontentloaded' });
        await capture('07_items_inventory.png', 'دليل الأصناف والمخزون');

        // 08. Purchases List
        await page.goto(`${baseUrl}/purchases`, { waitUntil: 'domcontentloaded' });
        await capture('08_purchases_list.png', 'فواتير المشتريات والتوريد');

        // 09. Customers Directory
        await page.goto(`${baseUrl}/customers`, { waitUntil: 'domcontentloaded' });
        await capture('09_customers_directory.png', 'دليل العملاء والمديونيات');

        // 10. Customer Statement
        await page.goto(`${baseUrl}/customers/1/statement`, { waitUntil: 'domcontentloaded' }).catch(() => {});
        await capture('10_customer_statement.png', 'كشف حساب العميل التفصيلي');

        // 11. Suppliers Directory
        await page.goto(`${baseUrl}/suppliers`, { waitUntil: 'domcontentloaded' });
        await capture('11_suppliers_directory.png', 'دليل الموردين والمستحقات');

        // 12. Cash Shifts & Z-Report
        await page.goto(`${baseUrl}/shifts`, { waitUntil: 'domcontentloaded' });
        await capture('12_cash_shifts.png', 'ورديات الكاشير وإقفال الدرج');

        // 13. Payments & Receipts
        await page.goto(`${baseUrl}/payments`, { waitUntil: 'domcontentloaded' });
        await capture('13_payment_vouchers.png', 'سندات القبض والصرف');

        // 14. Daily Expenses
        await page.goto(`${baseUrl}/expenses`, { waitUntil: 'domcontentloaded' });
        await capture('14_expenses.png', 'المصروفات وتكلفة التشغيل');

        // 15. Safe & Treasury
        await page.goto(`${baseUrl}/treasury`, { waitUntil: 'domcontentloaded' });
        await capture('15_treasury.png', 'حركة الخزينة والصندوق');

        // 16. Reports & Profit Analytics
        await page.goto(`${baseUrl}/reports`, { waitUntil: 'domcontentloaded' });
        await capture('16_reports_analytics.png', 'تقارير الأرباح وتحليلات المبيعات');

        // 17. Item Stock Card
        await page.goto(`${baseUrl}/reports/item-card/1`, { waitUntil: 'domcontentloaded' }).catch(() => {});
        await capture('17_item_movement_card.png', 'كارت حركة الصنف وتتبع الرصيد');

        // 18. Stock Transfers
        await page.goto(`${baseUrl}/transfers`, { waitUntil: 'domcontentloaded' });
        await capture('18_stock_transfers.png', 'التحويل المخزني بين الفروع');

        // 19. Returns
        await page.goto(`${baseUrl}/returns`, { waitUntil: 'domcontentloaded' });
        await capture('19_returns.png', 'مرتجعات المبيعات والمشتريات');

        // 20. System Settings
        await page.goto(`${baseUrl}/settings`, { waitUntil: 'domcontentloaded' });
        await capture('20_settings.png', 'إعدادات النظام والطباعة الحرارية');

        // 21. Audit & Security Logs
        await page.goto(`${baseUrl}/audit-logs`, { waitUntil: 'domcontentloaded' });
        await capture('21_audit_logs.png', 'سجل الرقابة وتدقيق العمليات');

        console.log('🎉 All mobile screenshots captured successfully in docs/screenshots/!');
    } catch (err) {
        console.error('Error taking screenshots:', err);
    } finally {
        await browser.close();
    }
}

run();
