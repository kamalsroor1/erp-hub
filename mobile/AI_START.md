# 🚀 دليل البدء السريع للمطور والذكاء الاصطناعي (AI Start Guide)

> **مشروع: تطبيق سرور كوفي ERP Mobile (NativePHP for Mobile)**

---

## 1. ملخص هيكل المشروع (Project Directory Layout)

المشروع مقسم إلى جزأين متكاملين داخل مجلد العمل:
```text
i:\projects\erp-2026\
├── backend/                  # سورس كود ومراجع الـ ERP الأصلي (Laravel 12 + Livewire 4)
│   ├── app/
│   ├── database/
│   ├── docs/                 # وثائق النظام الأصلي وقواعد البيانات
│   ├── resources/views/      # قوالب Blade وتفاصيل التصميم الأصلية
│   └── project-spec.md       # المواصفات الفنية الكاملة
│
├── mobile/                   # مشروع تطبيق الموبايل الأصلي المبني بـ NativePHP
│   ├── app/
│   │   ├── Models/           # نماذج Eloquent المطابقة للباك إند
│   │   └── Services/         # طبقة المعاملات المالية والمخزنية
│   ├── config/
│   │   └── nativephp.php     # إعدادات NativePHP Mobile
│   ├── database/             # Migrations و Seeders لقاعدة الموبايل المحلية
│   ├── docs/                 # وثائق الموبايل، الهوية البصرية، وتوزيع المهام
│   ├── resources/views/      # واجهات الموبايل المحسنة للمس
│   └── .env                  # إعدادات التطبيق ومعرف NativePHP
│
├── AGENTS.md                 # دليل تشغيل الذكاء الاصطناعي الرئيسي
└── AI_START.md               # هذا الدليل السريع
```

---

## 2. البدء السريع والتشغيل (Quick Start Commands)

### 2.1 إعداد بيئة الموبايل:
```bash
cd mobile
php artisan key:generate
php artisan migrate
```

### 2.2 تشغيل التطبيق في المتصفح للاختبار السريع:
```bash
php artisan serve
```

### 2.3 تشغيل التطبيق على محاكي أو جهاز NativePHP Mobile:
```bash
php artisan native:run
```

---

## 3. تفاصيل الهوية البصرية والألوان (Identical to Blade UI)

* **الخطوط (Typography):** `Cairo` و `Tajawal`
* **الاتجاه (Direction):** RTL كامل `dir="rtl"`
* **درجات الألوان الأساسية:**
  * **الأخضر الزمردي الرئيسي (Primary Emerald):**
    * `primary-50`: `#ecfdf5`
    * `primary-500`: `#10b981` (الزر الأساسي وحالة النجاح)
    * `primary-600`: `#059669`
    * `primary-700`: `#047857`
  * **العنبري / الذهبي الخاص بالبن (Roastery Amber):**
    * `amber-500`: `#f59e0b`
    * `amber-600`: `#d97706` (أشرطة التقدم وشاشات التوليفات)
  * **الوضع الليلي الفاخر (Dark Theme):**
    * `dark-950`: `#020617` (خلفية التطبيق الكلية)
    * `dark-900`: `#0f172a` (البطاقات والجداول)
    * `dark-850`: `#172033` (الهيدر والفوتر)
    * `dark-800`: `#1e293b` (حقول الإدخال والأزرار الثانوية)
    * `border-dark`: `#334155`
  * **الوضع الفاتح (Light Theme):**
    * `light-bg`: `#f8fafc`
    * `card-bg`: `#ffffff`
    * `border-light`: `#e2e8f0`
    * `text-main`: `#0f172a`

---

## 4. الوحدات الأساسية لتطبيق الموبايل (Core Mobile Modules)

1. **شاشة كاشير ونقاط البيع السريعة (Touch POS & Fast Invoicing):**
   * بحث بالباركود والاسم السريع
   * اختيار العميل ونوع الدفع (نقدي / آجل / جزئي)
   * خصومات بالنسبة والمبلغ
   * طباعة حرارية 80mm وإيصالات فورية
2. **إدارة الأصناف والمخزون (Items & Live Stock):**
   * كود، اسم، سعر تكلفة Cost Price، وسعر بيع Selling Price
   * رصيد المخزون، تنبيهات الحد الأدنى Low Stock Alerts
3. **العملاء والحسابات (Customers & Statement of Account):**
   * كشف حساب العميل، متابعة الديون والآجل، تسجيل المدفوعات
4. **الموردين والمشتريات (Suppliers & Purchases):**
   * فواتير شراء، كشف حساب مورد، سداد الموردين
5. **توليفة البن وخلاط المطاحن (Coffee Blender Tool):**
   * حساب نسب حبوب البن (برازيلي، كولومبي، حبشي، روبوستا...)
   * حساب تكلفة الكيلو بعد التحميص والخلط وخصم الخامات
6. **دفتر اليومية وورديات الخزينة (Daily Journal & Cash Shifts):**
   * فتح وإغلاق الوردية، جرد الدرج، الإيرادات والمصروفات
7. **التقارير والأرباح (Mobile Reports & Analytics):**
   * مبيعات اليوم، صافي الأرباح، حركة المخزون، تصدير PDF و Excel
