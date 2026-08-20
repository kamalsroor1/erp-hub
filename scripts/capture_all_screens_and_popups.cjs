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

    console.log('🚀 Starting Comprehensive Mobile Screenshots + Popups Engine...');
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
        await sleep(800);
        const filePath = path.join(outputDir, filename);
        await page.screenshot({ path: filePath, fullPage: false });
        console.log(`✅ [${filename}] Captured: ${title}`);
    };

    try {
        // 01. Login Screen
        await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });
        await capture('01_login_screen.png', 'شاشة تسجيل الدخول');

        // Perform Login
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
            const btns = Array.from(document.querySelectorAll('button'));
            const menuBtn = btns.find(b => b.textContent.includes('☰') || b.innerHTML.includes('☰') || b.getAttribute('title')?.includes('قائمة'));
            if (menuBtn) menuBtn.click();
            else if (btns[btns.length - 1]) btns[btns.length - 1].click();
        });
        await sleep(600);
        await capture('03_side_menu_drawer.png', 'القائمة الجانبية والصلاحيات');

        // 04. Popup: Branch Switcher Modal
        await page.evaluate(() => {
            const switchBtn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('تبديل') || b.textContent.includes('الفرع'));
            if (switchBtn) switchBtn.click();
        });
        await sleep(600);
        await capture('04_popup_branch_switcher.png', 'نافذة منبثقة: تبديل الفرع والمخزن');
        await page.keyboard.press('Escape');
        await sleep(400);

        // 05. POS Cashier Main Screen
        await page.goto(`${baseUrl}/pos`, { waitUntil: 'domcontentloaded' });
        await capture('05_pos_cashier.png', 'كاشير نقاط البيع POS');

        // 06. Popup: POS Weight/Quantity Picker Modal (click first item)
        await page.evaluate(() => {
            const itemCards = document.querySelectorAll('.grid > div, .grid > button, [class*="cursor-pointer"]');
            if (itemCards[0]) itemCards[0].click();
        });
        await sleep(700);
        await capture('06_popup_pos_weight_picker.png', 'نافذة منبثقة: ميزان وتحديد وزن وكمية الصنف في الكاشير');

        // Close or Confirm weight
        await page.evaluate(() => {
            const addBtn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('إضافة') || b.textContent.includes('تأكيد'));
            if (addBtn) addBtn.click();
        });
        await sleep(500);

        // 07. Popup: POS Customer Picker Bottom Sheet
        await page.evaluate(() => {
            const custBtn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('العميل') || b.textContent.includes('عميل'));
            if (custBtn) custBtn.click();
        });
        await sleep(600);
        await capture('07_popup_pos_customer_picker.png', 'شيت منبثق: اختيار العميل أو إضافة عميل جديد بالكاشير');
        await page.keyboard.press('Escape');
        await sleep(400);

        // 08. Popup: POS Checkout / Cart Bottom Sheet
        await page.evaluate(() => {
            const cartBtn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('السلة') || b.textContent.includes('الدفع') || b.textContent.includes('إتمام'));
            if (cartBtn) cartBtn.click();
        });
        await sleep(700);
        await capture('08_popup_pos_checkout_sheet.png', 'شيت منبثق: سلة البيع وحساب المتبقي وطريقة الدفع');
        await page.keyboard.press('Escape');
        await sleep(400);

        // 09. Invoices List
        await page.goto(`${baseUrl}/invoices`, { waitUntil: 'domcontentloaded' });
        await capture('09_invoices_list.png', 'سجل فواتير المبيعات');

        // 10. Single Invoice Details
        await page.goto(`${baseUrl}/invoices/1`, { waitUntil: 'domcontentloaded' }).catch(() => {});
        await capture('10_invoice_details.png', 'تفاصيل الفاتورة ومشاركة واتساب والطباعة');

        // 11. Inventory Items Catalog & Low Stock Radar
        await page.goto(`${baseUrl}/items`, { waitUntil: 'domcontentloaded' });
        await capture('11_items_inventory.png', 'دليل الأصناف والمخزون');

        // 12. Purchases List
        await page.goto(`${baseUrl}/purchases`, { waitUntil: 'domcontentloaded' });
        await capture('12_purchases_list.png', 'فواتير المشتريات والتوريد');

        // 13. Popup: Create Purchase Modal
        await page.evaluate(() => {
            const createBtn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('فاتورة توريد جديدة') || b.textContent.includes('توريد جديدة') || b.textContent.includes('+'));
            if (createBtn) createBtn.click();
        });
        await sleep(700);
        await capture('13_popup_new_purchase_modal.png', 'نافذة منبثقة: إنشاء فاتورة شراء وتوريد بضاعة');
        await page.keyboard.press('Escape');
        await sleep(400);

        // 14. Single Purchase Detail
        await page.goto(`${baseUrl}/purchases/1`, { waitUntil: 'domcontentloaded' }).catch(() => {});
        await capture('14_purchase_details.png', 'تفاصيل فاتورة الشراء والتوريد');

        // 15. Customers Directory
        await page.goto(`${baseUrl}/customers`, { waitUntil: 'domcontentloaded' });
        await capture('15_customers_directory.png', 'دليل العملاء والمديونيات');

        // 16. Popup: Create New Customer Modal
        await page.evaluate(() => {
            const addBtn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('إضافة عميل') || b.textContent.includes('عميل جديد') || b.textContent.includes('+'));
            if (addBtn) addBtn.click();
        });
        await sleep(700);
        await capture('16_popup_new_customer_modal.png', 'نافذة منبثقة: إضافة وتعديل بيانات عميل جديد');
        await page.keyboard.press('Escape');
        await sleep(400);

        // 17. Customer Statement
        await page.goto(`${baseUrl}/customers/1/statement`, { waitUntil: 'domcontentloaded' }).catch(() => {});
        await capture('17_customer_statement.png', 'كشف حساب العميل وحركات البيع والسداد');

        // 18. Suppliers Directory
        await page.goto(`${baseUrl}/suppliers`, { waitUntil: 'domcontentloaded' });
        await capture('18_suppliers_directory.png', 'دليل الموردين والمستحقات');

        // 19. Popup: Create New Supplier Modal
        await page.evaluate(() => {
            const addBtn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('إضافة مورد') || b.textContent.includes('مورد جديد') || b.textContent.includes('+'));
            if (addBtn) addBtn.click();
        });
        await sleep(700);
        await capture('19_popup_new_supplier_modal.png', 'نافذة منبثقة: إضافة وتعديل بيانات مورد وشركة');
        await page.keyboard.press('Escape');
        await sleep(400);

        // 20. Cash Shifts & Z-Report
        await page.goto(`${baseUrl}/shifts`, { waitUntil: 'domcontentloaded' });
        await capture('20_cash_shifts.png', 'ورديات الكاشير وإقفال الدرج Z-Report');

        // 21. Popup: Open / Close Shift Modal
        await page.evaluate(() => {
            const shiftBtn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('فتح وردية') || b.textContent.includes('إغلاق') || b.textContent.includes('تقفيل'));
            if (shiftBtn) shiftBtn.click();
        });
        await sleep(700);
        await capture('21_popup_shift_manage_modal.png', 'نافذة منبثقة: فتح أو إغلاق وردية الكاشير وعد النقدية');
        await page.keyboard.press('Escape');
        await sleep(400);

        // 22. Payments & Receipts
        await page.goto(`${baseUrl}/payments`, { waitUntil: 'domcontentloaded' });
        await capture('22_payment_vouchers.png', 'سندات القبض والصرف');

        // 23. Popup: Create Payment Voucher Modal
        await page.evaluate(() => {
            const payBtn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('سند جديد') || b.textContent.includes('إضافة سند') || b.textContent.includes('+'));
            if (payBtn) payBtn.click();
        });
        await sleep(700);
        await capture('23_popup_new_payment_modal.png', 'نافذة منبثقة: إنشاء سند قبض أو صرف نقدي');
        await page.keyboard.press('Escape');
        await sleep(400);

        // 24. Daily Expenses
        await page.goto(`${baseUrl}/expenses`, { waitUntil: 'domcontentloaded' });
        await capture('24_expenses.png', 'المصروفات وتكلفة التشغيل');

        // 25. Popup: Create Expense Modal
        await page.evaluate(() => {
            const expBtn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('تسجيل مصروف') || b.textContent.includes('مصروف جديد') || b.textContent.includes('+'));
            if (expBtn) expBtn.click();
        });
        await sleep(700);
        await capture('25_popup_new_expense_modal.png', 'نافذة منبثقة: تسجيل مصروف درج ونثريات');
        await page.keyboard.press('Escape');
        await sleep(400);

        // 26. Safe & Treasury
        await page.goto(`${baseUrl}/treasury`, { waitUntil: 'domcontentloaded' });
        await capture('26_treasury.png', 'حركة الخزينة والصندوق ومطابقة الأرصدة');

        // 27. Reports & Profit Analytics
        await page.goto(`${baseUrl}/reports`, { waitUntil: 'domcontentloaded' });
        await capture('27_reports_analytics.png', 'تقارير الأرباح وتحليلات المبيعات');

        // 28. Stock Movement Card
        await page.goto(`${baseUrl}/reports/item-card/1`, { waitUntil: 'domcontentloaded' }).catch(() => {});
        await capture('28_item_movement_card.png', 'كارت حركة الصنف وتتبع الرصيد');

        // 29. Stock Transfers
        await page.goto(`${baseUrl}/transfers`, { waitUntil: 'domcontentloaded' });
        await capture('29_stock_transfers.png', 'التحويل المخزني بين الفروع');

        // 30. Popup: Create Stock Transfer Modal
        await page.evaluate(() => {
            const trBtn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('إذن تحويل') || b.textContent.includes('تحويل جديد') || b.textContent.includes('+'));
            if (trBtn) trBtn.click();
        });
        await sleep(700);
        await capture('30_popup_new_transfer_modal.png', 'نافذة منبثقة: إنشاء إذن تحويل مخزني بين الفروع');
        await page.keyboard.press('Escape');
        await sleep(400);

        // 31. Returns
        await page.goto(`${baseUrl}/returns`, { waitUntil: 'domcontentloaded' });
        await capture('31_returns.png', 'مرتجعات المبيعات والمشتريات');

        // 32. Popup: Create Return Modal
        await page.evaluate(() => {
            const retBtn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('مرتجع جديد') || b.textContent.includes('إضافة مرتجع') || b.textContent.includes('+'));
            if (retBtn) retBtn.click();
        });
        await sleep(700);
        await capture('32_popup_new_return_modal.png', 'نافذة منبثقة: إنشاء مرتجع بيع أو شراء');
        await page.keyboard.press('Escape');
        await sleep(400);

        // 33. System Settings
        await page.goto(`${baseUrl}/settings`, { waitUntil: 'domcontentloaded' });
        await capture('33_settings.png', 'إعدادات النظام والطباعة الحرارية وتيليجرام');

        // 34. Audit & Security Trail
        await page.goto(`${baseUrl}/audit-logs`, { waitUntil: 'domcontentloaded' });
        await capture('34_audit_logs.png', 'سجل الرقابة وتدقيق العمليات الحساسة');

        // 35. Popup: In-App Update Modal
        await page.evaluate(() => {
            window.dispatchEvent(new CustomEvent('open-app-update-modal'));
        });
        await sleep(800);
        await capture('35_popup_app_update_modal.png', 'نافذة منبثقة: التحديث الذاتي المباشر داخل التطبيق In-App Update');

        console.log('🎉 All 35 Mobile Screenshots & Popups captured successfully in docs/screenshots/!');
    } catch (err) {
        console.error('Error taking screenshots:', err);
    } finally {
        await browser.close();
    }
}

run();
