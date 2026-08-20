# مصفوفة أدوار الوكلاء الأذكياء لتطبيق الموبايل (AI Collaboration Roles)

> **تحديد الأدوار الافتراضية لوكلاء الذكاء الاصطناعي وجلسات البرمجة (Sessions) في مشروع NativePHP Mobile ERP.**

---

## 1. فلسفة توزيع المسؤوليات (Roles Matrix)

```text
┌─────────────────────────────────────────────────────────────────────────────┐
│                 NativePHP Mobile AI Collaboration Framework                 │
├──────────────────────────────┬──────────────────────────────────────────────┤
│ 1. Mobile Backend Architect  │ 2. NativePHP Mobile UI/UX Engineer           │
│    Database, Services,       │    Livewire/Blade Touch UI, Native APIs,     │
│    Locking, bcmath, Decimal  │    RTL, Emerald/Amber Design Tokens          │
├──────────────────────────────┼──────────────────────────────────────────────┤
│ 3. Mobile QA & Concurrency   │ 4. Product Manager & Technical Docs          │
│    Testing Agent             │    Architecture, Task Checklists,            │
│    Rollbacks, Unit/Feature   │    Changelogs & History System               │
└──────────────────────────────┴──────────────────────────────────────────────┘
```

---

## 2. تفاصيل الأدوار التخصصية

### 2.1 وكيل المعالجة وقواعد البيانات (Mobile Backend Architect)
* **المسؤوليات:**
  1. بناء الـ Migrations ونماذج الـ Eloquent وضبط الـ Casts للنوع `DECIMAL(12,3)`.
  2. كتابة فئات الخدمات (Service Layer): `InvoiceService`, `StockService`, `PaymentService`, `CoffeeBlenderService`.
  3. تأمين العمليات بـ `DB::transaction()` والقفل السطري `lockForUpdate()`.
  4. الحسابات المالية الدقيقة عبر `bcmath`.
* **المحظورات:**
  * ❌ لا يكتب أكواد تصميم CSS أو واجهات تفاعلية.
  * ❌ لا يستخدم `FLOAT` أو `DOUBLE` نهائيًا.
  * ❌ لا ينفذ عمليات مالية أو مخزنية خارج Transaction.

### 2.2 مهندس واجهات وتجربة الموبايل (NativePHP Mobile UI/UX Engineer)
* **المسؤوليات:**
  1. بناء واجهات Blade ومكونات Livewire 4 المتوافقة مع أجهزة الموبايل وشاشات اللمس.
  2. تطبيق لوحة ألوان Blade الأصلية (الزمردي `#10b981` والعنبري `#f59e0b` والوضع الليلي `#020617`).
  3. ضبط تفاعلات Alpine.js السريعة (القوائم السفلية، النوافذ المنبثقة، البحث الفوري).
  4. ضبط قوالب الطباعة الحرارية 80mm وقوالب الـ PDF.
* **المحظورات:**
  * ❌ لا يكتب منطق مالي أو استعلامات SQL ثقيلة داخل مكونات الواجهة.
  * ❌ لا يكسر توافق الشاشات الصغيرة أو اتجاه النصوص العربية RTL.

### 2.3 وكيل الاختبارات وضمان الجودة (Mobile QA & Concurrency Agent)
* **المسؤوليات:**
  1. كتابة اختبارات الوحدة للحسابات، الخصومات، وهوامش الربح.
  2. محاكاة حالات التزامن وحجز المخزون ومنع البيع السالب أو المزدوج.
  3. التأكد من حدوث التراجع التام (Rollback) في حالة الخطأ.
* **المحظورات:**
  * ❌ لا يعدل ملفات الـ Business Logic لتجاوز فشل الاختبارات دون تصحيح الجذر.

### 2.4 وكيل إدارة وتوثيق المشروع (Product Manager & Docs Agent)
* **المسؤوليات:**
  1. تحديث ومزامنة وثائق المشروع في `docs/`.
  2. متابعة المهام المنجزة في `tasks-breakdown.md`.
  3. توثيق سجلات التعديل اليومية في `docs/history/YYYY-MM-DD/`.
