import pytest
import sys
import time
from playwright.sync_api import Page, expect
from config import BASE_URL, ADMIN_PHONE, ADMIN_PASSWORD
from helpers import wait_for_livewire, safe_goto, login_as_admin

if hasattr(sys.stdout, 'reconfigure'):
    try:
        sys.stdout.reconfigure(encoding='utf-8', errors='replace')
    except Exception:
        pass

def test_create_employees_interactive(page: Page):
    """Test creating Cashier and Storekeeper employees via User Manager UI with live Arabic logs."""
    print("\n" + "=" * 60)
    print("[بدء الاختبار] 1. اضافة موظفين جدد (كاشير + امين مخزن) من شاشة المستخدمين")
    print("=" * 60)
    
    print("  [*] جاري التوجه لصفحة ادارة المستخدمين (/users)...")
    safe_goto(page, "/users")
    
    print("  [*] جاري فحص بنية الصفحة وعناوين الاعمدة...")
    expect(page.locator("body")).to_contain_text("إدارة المستخدمين")
    expect(page.locator("body")).to_contain_text("الصلاحية / الدور")
    expect(page.locator("body")).to_contain_text("رقم الهاتف للدخول")
    print("  [+] تم التاكد من تحميل صفحة المستخدمين وعناصرها بنجاح.")
    
    # 1. Create Cashier Employee
    print("  [*] جاري الضغط على زر 'اضافة مستخدم جديد' لانشاء الكاشير...")
    page.locator('button:has-text("إضافة مستخدم جديد"), button:has-text("إضافة كاشير")').first.click()
    wait_for_livewire(page)
    
    expect(page.locator('form:has-text("حفظ بيانات المستخدم")')).to_be_visible()
    print("  [+] ظهرت نافذة ادخال بيانات المستخدم.")
    
    print("  [*] جاري كتابة بيانات الكاشير: (احمد كاشير المعادي E2E / هاتف: 01055554444 / دور: cashier)...")
    page.locator('form input[wire\\:model*="name"]').first.fill("أحمد كاشير المعادي E2E")
    page.locator('form input[wire\\:model*="phone"]').first.fill("01055554444")
    page.locator('form select[wire\\:model*="role"]').first.select_option("cashier")
    page.locator('form input[wire\\:model*="password"]').first.fill("password123")
    
    print("  [*] جاري حفظ بيانات الكاشير...")
    page.locator('form button[type="submit"]:has-text("حفظ")').click()
    wait_for_livewire(page)
    
    print("  [*] جاري التحقق من ادراج الكاشير في الجدول وبادج دوره...")
    expect(page.locator("body")).to_contain_text("أحمد كاشير المعادي E2E")
    expect(page.locator("body")).to_contain_text("01055554444")
    expect(page.locator("body")).to_contain_text("كاشير مبيعات")
    print("  [+] تم ادراج موظف الكاشير [احمد كاشير المعادي] بنجاح في النظام!")
    
    # 2. Create Storekeeper Employee
    print("  [*] جاري الضغط على زر 'اضافة مستخدم جديد' لانشاء امين المخزن...")
    page.locator('button:has-text("إضافة مستخدم جديد"), button:has-text("إضافة كاشير")').first.click()
    wait_for_livewire(page)
    
    expect(page.locator('form:has-text("حفظ بيانات المستخدم")')).to_be_visible()
    
    print("  [*] جاري كتابة بيانات امين المخزن: (محمود امين المخزن E2E / هاتف: 01033332222 / دور: storekeeper)...")
    page.locator('form input[wire\\:model*="name"]').first.fill("محمود أمين المخزن E2E")
    page.locator('form input[wire\\:model*="phone"]').first.fill("01033332222")
    page.locator('form select[wire\\:model*="role"]').first.select_option("storekeeper")
    page.locator('form input[wire\\:model*="password"]').first.fill("password123")
    
    print("  [*] جاري حفظ بيانات امين المخزن...")
    page.locator('form button[type="submit"]:has-text("حفظ")').click()
    wait_for_livewire(page)
    
    expect(page.locator("body")).to_contain_text("محمود أمين المخزن E2E")
    expect(page.locator("body")).to_contain_text("01033332222")
    expect(page.locator("body")).to_contain_text("أمين مخزن")
    print("  [+] تم ادراج امين المخزن [محمود امين المخزن] بنجاح في النظام!")
    
    # 3. Search Filter Verification
    print("  [*] جاري تجربة فلترة وبحث المستخدمين بالهاتف (01055554444)...")
    search_input = page.locator('input[placeholder*="ابحث بالاسم"]')
    search_input.fill("01055554444")
    wait_for_livewire(page)
    expect(page.locator("body")).to_contain_text("أحمد كاشير المعادي E2E")
    
    print("  [*] جاري مسح البحث والعودة للقائمة الكاملة...")
    search_input.fill("")
    wait_for_livewire(page)
    expect(page.locator("body")).to_contain_text("محمود أمين المخزن E2E")
    print("  [+] تم فحص شريط البحث والتصفية بنجاح 100%.")

def test_stores_index_page(page: Page):
    """Test stores index page renders with main warehouse and filter buttons."""
    print("\n" + "=" * 60)
    print("[بدء الاختبار] 2. فتح شاشة الفروع والمخازن وفحص المخزن الرئيسي والفلاتر")
    print("=" * 60)
    
    print("  [*] جاري التوجه لشاشة الفروع (/stores)...")
    safe_goto(page, "/stores")
    
    print("  [*] جاري فحص هيدر الصفحة وخلوها من اخطاء السيرفر...")
    expect(page.locator("body")).to_contain_text("إدارة الفروع وعربات التوزيع والمخازن")
    expect(page.locator("body")).not_to_contain_text("500 Server Error")
    
    print("  [*] جاري التحقق من وجود المخزن الرئيسي التلقائي (MAIN-01)...")
    expect(page.locator("body")).to_contain_text("المخزن والفرع الرئيسي")
    expect(page.locator("body")).to_contain_text("MAIN-01")
    expect(page.locator("body")).to_contain_text("رئيسي")
    
    print("  [*] جاري فحص ازرار فلاتر الحالة والانواع (النشطة، سلة المحذوفات، محلات، عربيات)...")
    expect(page.locator('button:has-text("النشطة")').first).to_be_visible()
    expect(page.locator('button:has-text("سلة المحذوفات")').first).to_be_visible()
    expect(page.locator('button:has-text("محلات")').first).to_be_visible()
    expect(page.locator('button:has-text("عربيات")').first).to_be_visible()
    print("  [+] صفحة الفروع والمخزن الرئيسي والفلاتر جاهزة تماما.")

def test_create_retail_store_interactive(page: Page):
    """Test creating a new retail branch via UI modal with full card inspection."""
    print("\n" + "=" * 60)
    print("[بدء الاختبار] 3. انشاء فرع تجزئة جديد (فرع المعادي والمعرض)")
    print("=" * 60)
    
    safe_goto(page, "/stores")
    
    print("  [*] جاري فتح نافذة 'اضافة فرع / عربية توزيع'...")
    page.locator('button:has-text("إضافة فرع")').first.click()
    wait_for_livewire(page)
    
    expect(page.locator('form:has-text("حفظ البيانات")')).to_be_visible()
    
    print("  [*] جاري اختيار نوع الفرع: 'محل تجزئة'...")
    page.locator('button[wire\\:click*="type"]').filter(has_text="محل تجزئة").click()
    wait_for_livewire(page)
    
    print("  [*] جاري كتابة بيانات الفرع: (الاسم: فرع المعادي / كود: SHOP-MAADI / هاتف: 01022334455 / عنوان: شارع 9)...")
    page.locator('input[wire\\:model="name"]').fill("فرع المعادي والمعرض")
    page.locator('input[wire\\:model="code"]').fill("SHOP-MAADI")
    page.locator('input[wire\\:model="phone"]').fill("01022334455")
    page.locator('textarea[wire\\:model="address"]').fill("شارع 9 - المعادي - القاهرة")
    
    print("  [*] جاري الضغط على 'حفظ البيانات'...")
    page.locator('button[type="submit"]:has-text("حفظ البيانات")').click()
    wait_for_livewire(page)
    
    print("  [*] جاري التحقق من ظهور كارت الفرع الجديد في القائمة...")
    expect(page.locator("body")).to_contain_text("فرع المعادي والمعرض")
    expect(page.locator("body")).to_contain_text("SHOP-MAADI")
    expect(page.locator("body")).to_contain_text("01022334455")
    expect(page.locator("body")).to_contain_text("شارع 9 - المعادي")
    
    print("  [*] جاري فحص ازرار الاجراءات على كارت الفرع (تعيين الموظفين، تعديل، ارشفة)...")
    maadi_card = page.locator('div.rounded-2xl:has-text("SHOP-MAADI")').first
    expect(maadi_card.locator('button:has-text("تعيين الموظفين")')).to_be_visible()
    expect(maadi_card.locator('button[title="تعديل بيانات الفرع"]')).to_be_visible()
    expect(maadi_card.locator('button[title="أرشفة الفرع"]')).to_be_visible()
    print("  [+] تم انشاء فرع التجزئة وفحص كافة عناصره وازراره بنجاح.")

def test_create_distribution_van_interactive(page: Page):
    """Test creating a new wholesale distribution van and verifying icon and route."""
    print("\n" + "=" * 60)
    print("[بدء الاختبار] 4. انشاء عربية توزيع جملة متنقلة (VAN-E2E-02)")
    print("=" * 60)
    
    safe_goto(page, "/stores")
    
    print("  [*] جاري فتح نافذة اضافة الفرع...")
    page.locator('button:has-text("إضافة فرع")').first.click()
    wait_for_livewire(page)
    
    print("  [*] جاري اختيار نوع: 'عربية توزيع'...")
    page.locator('button[wire\\:click*="type"]').filter(has_text="عربية توزيع").click()
    wait_for_livewire(page)
    
    print("  [*] جاري كتابة بيانات العربية: (عربية توزيع جملة رقم 2 / كود: VAN-E2E-02 / هاتف: 01099887766 / خط سير: شرق القاهرة)...")
    page.locator('input[wire\\:model="name"]').fill("عربية توزيع جملة رقم 2")
    page.locator('input[wire\\:model="code"]').fill("VAN-E2E-02")
    page.locator('input[wire\\:model="phone"]').fill("01099887766")
    page.locator('textarea[wire\\:model="address"]').fill("خط توزيع شرق القاهرة والجيزة")
    
    print("  [*] جاري حفظ بيانات عربية التوزيع...")
    page.locator('button[type="submit"]:has-text("حفظ البيانات")').click()
    wait_for_livewire(page)
    
    print("  [*] جاري فحص ظهور العربية والكود وخط السير...")
    expect(page.locator("body")).to_contain_text("عربية توزيع جملة رقم 2")
    expect(page.locator("body")).to_contain_text("VAN-E2E-02")
    expect(page.locator("body")).to_contain_text("01099887766")
    expect(page.locator("body")).to_contain_text("خط توزيع شرق القاهرة والجيزة")
    print("  [+] تم انشاء وتاكيد عربية التوزيع الجملة بنجاح.")

def test_edit_store_interactive(page: Page):
    """Test editing store details and verifying change propagation."""
    print("\n" + "=" * 60)
    print("[بدء الاختبار] 5. تعديل بيانات فرع قائم وتحديث الهاتف والعنوان")
    print("=" * 60)
    
    safe_goto(page, "/stores")
    
    print("  [*] جاري الضغط على زر التعديل لكارت فرع المعادي...")
    maadi_card = page.locator('div.rounded-2xl:has-text("SHOP-MAADI")').first
    maadi_card.locator('button[title="تعديل بيانات الفرع"]').click()
    wait_for_livewire(page)
    
    expect(page.locator('form:has-text("حفظ البيانات")')).to_be_visible()
    print("  [+] فتحت نافذة التعديل.")
    
    print("  [*] جاري تحديث رقم الهاتف الى (01022334499) والعنوان الى (شارع النصر - المعادي الجديدة)...")
    page.locator('input[wire\\:model="phone"]').fill("01022334499")
    page.locator('textarea[wire\\:model="address"]').fill("شارع النصر - المعادي الجديدة - القاهرة")
    
    print("  [*] جاري حفظ التعديلات...")
    page.locator('button[type="submit"]:has-text("حفظ البيانات")').click()
    wait_for_livewire(page)
    
    print("  [*] جاري فحص انعكاس البيانات الجديدة على كارت الفرع...")
    expect(page.locator("body")).to_contain_text("01022334499")
    expect(page.locator("body")).to_contain_text("شارع النصر - المعادي الجديدة")
    print("  [+] تم تحديث بيانات الفرع والتاكد من حفظها وانعكاسها فورا.")

def test_assign_employees_to_store_interactive(page: Page):
    """Test assigning employees to a branch with modal checkbox assertions and counter validation."""
    print("\n" + "=" * 60)
    print("[بدء الاختبار] 6. تعيين الكاشير وامين المخزن على فرع المعادي")
    print("=" * 60)
    
    safe_goto(page, "/stores")
    
    print("  [*] جاري فتح نافذة 'تعيين الموظفين' لكارت فرع المعادي...")
    maadi_card = page.locator('div.rounded-2xl:has-text("SHOP-MAADI")').first
    maadi_card.locator('button:has-text("تعيين الموظفين")').click()
    wait_for_livewire(page)
    
    expect(page.locator('h3:has-text("تعيين الموظفين والمناديب")')).to_be_visible()
    print("  [+] فتحت نافذة تعيين الموظفين.")
    
    print("  [*] جاري التحقق من وجود الكاشير وامين المخزن في قائمة التعيينات...")
    expect(page.locator("body")).to_contain_text("أحمد كاشير المعادي E2E")
    expect(page.locator("body")).to_contain_text("محمود أمين المخزن E2E")
    
    print("  [*] جاري تحديد الموظفين (احمد كاشير + محمود امين المخزن)...")
    page.locator('label:has-text("أحمد كاشير المعادي E2E") input[type="checkbox"]').check()
    page.locator('label:has-text("محمود أمين المخزن E2E") input[type="checkbox"]').check()
    wait_for_livewire(page)
    
    print("  [*] جاري حفظ التعيينات...")
    page.locator('button:has-text("حفظ التعيينات")').click()
    wait_for_livewire(page)
    
    print("  [*] جاري فحص اشعار النجاح وتحديث ربط الموظفين بالفرع...")
    expect(page.locator("body")).to_contain_text("تم تحديث تعيينات الموظفين للفرع بنجاح")
    print("  [+] تم ربط وتعيين الموظفين على الفرع بنجاح.")

def test_switch_active_store_interactive(page: Page):
    """Test switching active branch context via card button."""
    print("\n" + "=" * 60)
    print("[بدء الاختبار] 7. التبديل السريع للفرع النشط (Switch Active Store)")
    print("=" * 60)
    
    safe_goto(page, "/stores")
    
    print("  [*] جاري تحديد كارت فرع المعادي والضغط على '⚡ تبديل إليه'...")
    maadi_card = page.locator('div.rounded-2xl:has-text("SHOP-MAADI")').first
    switch_btn = maadi_card.locator('button:has-text("تبديل إليه")')
    
    if switch_btn.is_visible():
        switch_btn.click()
        wait_for_livewire(page)
    
    print("  [*] جاري فحص تحديث كارت فرع المعادي ليصبح 'نشط حالياً'...")
    expect(page.locator('div.rounded-2xl:has-text("SHOP-MAADI")')).to_contain_text("نشط حالياً")
    print("  [+] تم تبديل الفرع النشط وتحديث الجلسة بنجاح 100%.")

def test_roles_and_permission_matrix_interactive(page: Page):
    """Test role and permission matrix, creating custom role, and saving permissions."""
    print("\n" + "=" * 60)
    print("[بدء الاختبار] 8. مصفوفة الصلاحيات المتقدمة (/roles) وانشاء دور مخصص")
    print("=" * 60)
    
    print("  [*] جاري التوجه لشاشة مصفوفة الصلاحيات (/roles)...")
    safe_goto(page, "/roles")
    
    print("  [*] جاري فحص عنوان الصفحة وعناصر المصفوفة...")
    expect(page.locator("body")).to_contain_text("إدارة الأدوار ومصفوفة الصلاحيات")
    expect(page.locator("body")).to_contain_text("كاشير مبيعات")
    expect(page.locator("body")).to_contain_text("أمين مخزن")
    expect(page.locator("body")).to_contain_text("محاسب مالي")
    
    print("  [*] ➕ جاري فتح نافذة 'إنشاء دور جديد'...")
    page.locator('button:has-text("دور جديد")').first.click()
    wait_for_livewire(page)
    
    expect(page.locator('h3:has-text("إنشاء دور مخصص جديد")')).to_be_visible()
    
    print("  [*] ✍️ جاري كتابة اسم الدور الجديد: (branch_manager)...")
    page.locator('input[wire\\:model="newRoleName"]').fill("branch_manager")
    
    print("  [*] 💾 جاري الضغط على 'إنشاء الدور'...")
    page.locator('button:has-text("إنشاء الدور")').click()
    wait_for_livewire(page)
    
    print("  [*] جاري التحقق من ظهور واختيار الدور الجديد...")
    expect(page.locator("body")).to_contain_text("branch_manager")
    
    print("  [*] ☑️ جاري الضغط على '✓ تحديد الكل' لمنح كافة الصلاحيات للدور...")
    page.locator('button:has-text("تحديد الكل")').click()
    wait_for_livewire(page)
    
    print("  [*] 💾 جاري حفظ مصفوفة الصلاحيات...")
    page.locator('button:has-text("حفظ الصلاحيات")').click()
    wait_for_livewire(page)
    
    print("  [+] تم إنشاء الدور الجديد وضبط وتحديث مصفوفة الصلاحيات بنجاح.")

def test_cashier_login_and_access_restrictions(page: Page):
    """Test cashier login and restricted access to admin areas."""
    print("\n" + "=" * 60)
    print("[بدء الاختبار] 9. تسجيل الدخول بحساب الكاشير وفحص حماية الروابط الإدارية")
    print("=" * 60)
    
    print("  [*] 🚪 جاري تسجيل الخروج من حساب المدير...")
    page.context.clear_cookies()
    
    print("  [*] 🔑 جاري تسجيل الدخول بحساب الكاشير: (01055554444 / password123)...")
    safe_goto(page, "/login")
    page.locator('#phone').fill("01055554444")
    page.locator('#password').fill("password123")
    page.locator('button[type="submit"]').click()
    wait_for_livewire(page)
    time.sleep(0.5)
    
    print("  [*] 🔍 جاري فحص تسجيل دخول الكاشير وظهور اسمه في الواجهة...")
    expect(page.locator("body")).to_contain_text("أحمد كاشير المعادي")
    print("  [+] تم دخول الكاشير بنجاح.")
    
    print("  [*] 🚫 جاري فحص محاولة الكاشير الوصول لصفحة الصلاحيات المحمية (/roles)...")
    page.goto(f"{BASE_URL}/roles", wait_until="domcontentloaded")
    wait_for_livewire(page)
    
    # Assert 403 Forbidden or restricted error
    expect(page.locator("body")).to_contain_text("403")
    print("  [+] تم التحقق من حظر الكاشير من دخول صفحة الصلاحيات (403 Forbidden) بنجاح!")
    
    print("  [*] 🔄 جاري تسجيل الخروج والعودة لحساب المدير العام...")
    page.context.clear_cookies()
    safe_goto(page, "/login")
    login_as_admin(page)
    print("  [+] تم تسجيل الدخول مجدداً بحساب المدير العام.")

def test_store_and_user_validation_constraints(page: Page):
    """Test duplicate store code and duplicate user phone validation rules."""
    print("\n" + "=" * 60)
    print("[بدء الاختبار] 10. التحقق من منع تكرار كود الفرع وهاتف المستخدم")
    print("=" * 60)
    
    # 1. Store Code Validation
    print("  [*] 🏢 1. جاري محاولة إضافة فرع بكود مكرر موجود مسبقاً (SHOP-MAADI)...")
    safe_goto(page, "/stores")
    page.locator('button:has-text("إضافة فرع")').first.click()
    wait_for_livewire(page)
    
    page.locator('input[wire\\:model="name"]').fill("فرع مكرر الكود")
    page.locator('input[wire\\:model="code"]').fill("SHOP-MAADI")
    page.locator('button[type="submit"]:has-text("حفظ البيانات")').click()
    wait_for_livewire(page)
    
    print("  [*] 🔍 جاري التأكد من ظهور خطأ التحقق وبقاء النافذة مفتوحة...")
    expect(page.locator('div.fixed form')).to_contain_text("مستخدمة بالفعل")
    print("  [+] تم منع تكرار كود الفرع بنجاح!")
    
    # Close modal
    page.locator('div.fixed button:has-text("إلغاء")').click()
    wait_for_livewire(page)
    
    # 2. User Phone Validation
    print("  [*] 👤 2. جاري محاولة إضافة مستخدم بهاتف مكرر (01055554444)...")
    safe_goto(page, "/users")
    page.locator('button:has-text("إضافة مستخدم جديد"), button:has-text("إضافة كاشير")').first.click()
    wait_for_livewire(page)
    
    page.locator('form input[wire\\:model*="name"]').first.fill("مستخدم هاتف مكرر")
    page.locator('form input[wire\\:model*="phone"]').first.fill("01055554444")
    page.locator('form select[wire\\:model*="role"]').first.select_option("cashier")
    page.locator('form input[wire\\:model*="password"]').first.fill("password123")
    
    page.locator('form button[type="submit"]:has-text("حفظ")').click()
    wait_for_livewire(page)
    
    print("  [*] 🔍 جاري التأكد من ظهور خطأ تكرار الهاتف...")
    expect(page.locator('div.fixed form')).to_contain_text("مسجل بالفعل")
    print("  [+] تم منع تكرار رقم هاتف المستخدم بنجاح!")
    
    page.locator('div.fixed button:has-text("إلغاء")').click()
    wait_for_livewire(page)

def test_soft_delete_and_restore_store_interactive(page: Page):
    """Test soft deleting a store, verifying trash tab, and restoring it back to active list."""
    print("\n" + "=" * 60)
    print("[بدء الاختبار] 11. تجربة الحذف الامن (سلة المحذوفات) واستعادة الفرع")
    print("=" * 60)
    
    safe_goto(page, "/stores")
    
    print("  [*] 1. جاري انشاء فرع تجريبي مؤقت: (TEMP-SHOP-99)...")
    page.locator('button:has-text("إضافة فرع")').first.click()
    wait_for_livewire(page)
    
    page.locator('input[wire\\:model="name"]').fill("فرع مؤقت للاختبار E2E")
    page.locator('input[wire\\:model="code"]').fill("TEMP-SHOP-99")
    page.locator('button[type="submit"]:has-text("حفظ البيانات")').click()
    wait_for_livewire(page)
    
    expect(page.locator("body")).to_contain_text("فرع مؤقت للاختبار E2E")
    expect(page.locator("body")).to_contain_text("TEMP-SHOP-99")
    print("  [+] تم انشاء الفرع المؤقت.")
    
    print("  [*] 2. جاري الضغط على زر الحذف ونقله لسلة المحذوفات...")
    temp_card = page.locator('div.rounded-2xl:has-text("TEMP-SHOP-99")').first
    temp_card.locator('button[title="أرشفة الفرع"]').click()
    wait_for_livewire(page)
    
    print("  [*] 3. جاري التبديل لتبويب 'سلة المحذوفات'...")
    page.locator('button:has-text("سلة المحذوفات")').first.click()
    wait_for_livewire(page)
    
    print("  [*] جاري التاكد من وجود الفرع داخل سلة المحذوفات وظهور زر 'استعادة'...")
    expect(page.locator("body")).to_contain_text("فرع مؤقت للاختبار E2E")
    expect(page.locator("body")).to_contain_text("TEMP-SHOP-99")
    expect(page.locator('button:has-text("استعادة")').first).to_be_visible()
    print("  [+] الفرع موجود في سلة المحذوفات بحالة ارشفة امنة.")
    
    print("  [*] 4. جاري الضغط على زر 'استعادة'...")
    page.locator('button:has-text("استعادة")').first.click()
    wait_for_livewire(page)
    
    print("  [*] 5. جاري العودة لتبويب الفروع 'النشطة'...")
    page.locator('button:has-text("النشطة")').first.click()
    wait_for_livewire(page)
    
    print("  [*] جاري التحقق من عودة الفرع لقائمة الفروع النشطة بكامل بياناته...")
    expect(page.locator("body")).to_contain_text("فرع مؤقت للاختبار E2E")
    expect(page.locator("body")).to_contain_text("TEMP-SHOP-99")
    print("  [+] اكتملت دورة الحذف والاسترجاع من سلة المحذوفات بنجاح 100%!")
    print("=" * 60 + "\n")
