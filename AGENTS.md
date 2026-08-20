# 🤖 دليل تشغيل الذكاء الاصطناعي لتطبيق الموبايل (NativePHP Mobile ERP Playbook)

> **⚠️ تنبيه إلزامي لأي AI Agent / Session قبل بدء أي عمل في مشروع الموبايل:**
> يجب قراءة هذا الملف بالكامل قبل كتابة أو تعديل أي سطر كود. هذا الملف يحدد قواعد العمل الصارمة، المعايير المعمارية لتطبيق الموبايل المبني بـ **NativePHP for Mobile v4** ونظام الفواتير والمخزون **سرور كوفي ERP**.

---

## 1. نبذة عن المشروع والمعايير غير القابلة للتفاوض (Non-Negotiable Rules)

أنت تعمل على تطوير تطبيق الموبايل **"سرور كوفي ERP Mobile"** المبني باستخدام **NativePHP Mobile v4** و **Laravel** و **Inertia.js + Vue 3 (Composition API)** و **Tailwind CSS**.

### 🚫 المحظورات والقواعد المعمارية الصارمة (Strict Prohibitions & Architectural Rules):
1. **ممنوع استخدام `FLOAT` أو `DOUBLE` نهائيًا:** كافة القيم المالية والكميات والأوزان والخصومات والأسعار يجب أن تكون **`DECIMAL(12,3)`** وتُعالج بدوال `bcmath` في PHP لضمان الدقة المالية بنسبة 100%.
2. **ممنوع تعديل المخزون أو الحسابات خارج `DB::transaction()`:** أي عملية تؤثر على الرصيد أو الفواتير أو الخزينة يجب أن تتم داخل Transaction آمنة.
3. **ممنوع بيع الصنف دون قفل سطري:** استخدام `lockForUpdate()` إلزامي عند قراءة رصيد الصنف لخصمه لمنع الـ Race Conditions والبيع المزدوج.
4. **نمط الإجراء الفردي (Single Action Pattern - `app/Actions/`):** ممنوع تكديس كلاسات الخدمات الضخمة. كل عملية (إنشاء، تعديل، حذف، معالجة، تفعيل) تُبنى داخل كلاس Action مستقل يحتوي على دالة واحدة فقط `execute()`.
5. **فصل التحقق التام (Form Request Pattern - `app/Http/Requests/`):** الكنترولرز ممنوع أن تحتوي على كود `$request->validate()`، بل تستخدم كلاسات Form Request مخصصة.
6. **فلاتر الاستعلام عبر Pipeline (`app/Filters/` + `Illuminate\Pipeline\Pipeline`):** أي استعلام يحتوي على فلاتر متعددة (بحث، حالة، فئة، تاريخ، باقة...) يمرر عبر Laravel Pipeline وفلاتر منفصلة لمنع جمل `if` المتداخلة في الكنترولر.
7. **كائنات نقل البيانات (DTOs - `app/DTOs/`):** تمرير البيانات بين الطلب و الـ Action يتم عبر كائنات DTO محكمة النوع (Strictly Typed).
8. **المراقبون (Observers - `app/Observers/`):** معالجة الآثار الجانبية وسجلات التدقيق والأحداث التلقائية للموديلات عبر Observers.
9. **الموديلات النقية (Lean Models - `app/Models/`):** الموديلات مخصصة فقط للعلاقات (Relationships)، الـ Scopes، والـ Casts.
10. **الاعتماد التقني لواجهات المنصة والموبايل:** **Inertia.js + Vue 3 (Composition API) + Tailwind CSS + NativePHP Mobile v4 Bridge**.
11. **ممنوع كسر التوافق مع الهوية البصرية والعربية (RTL):** النظام يعمل بـ `dir="rtl"`، خطوط Cairo/Tajawal، ومحاذاة منطقية بـ Tailwind (`ms-*`, `me-*`, `text-start`, `text-end`).
12. **الالتزام بهوية الألوان والوضعين الفاتح والداكن:**
    * **الأخضر الزمردي (Emerald):** `#10b981` / `#059669` (الأساسي والتحصيل)
    * **الذهبي/العنبري (Amber/Gold):** `#f59e0b` / `#d97706` (التمييز والتنبيهات)
    * **الوضع الداكن الفاخر (Dark Slate):** `#020617` / `#0f172a` / `#1e293b`
    * **الوضع الفاتح الصافي (Light Shell):** `#f8fafc` / `#ffffff` / `#e2e8f0`
13. **إلزامية الترجمة التامة ومنع النصوص الثابتة (Zero Hardcoded Strings & Mandatory Localization Gate):**
    * **ممنوع كتابة نصوص ثابتة (Static / Hardcoded Strings) داخل أي سطر كود نهائيًا:** سواء في رسائل الخطأ، الاستجابات (API Responses)، الفلاش ميسدجز (Flash Messages)، مسميات الحقول والجداول، أو شاشات Vue 3 و Blade.
    * **ممنوع رفع أو إنشاء أو تعديل أي ملف دون هندلة ترجمته بنسبة 100%:** أي نص جديد يجب أن يُسجل فوراً في ملفات الترجمة الرسمية للغتين (`lang/ar/` و `lang/en/`).
    * دوال الترجمة الإلزامية:
      * في PHP / Laravel: `__('file.key')` أو `trans('file.key')`.
      * في Vue 3 / Inertia: استخدام `$t('file.key')` أو `trans('file.key')` أو دوال `useTrans()`.
14. **نمط محولات البيانات (Data Transformers / JsonResources - `app/Http/Resources/`):**
    * **ممنوع تمرير موديلات Eloquent مباشرة لواجهات Inertia:** يجب تغليف وتمرير كافة البيانات عبر كلاسات `JsonResource` لتنظيف وتنسيق البيانات، وحماية الحقول الحساسة، وتزويد الواجهة بالصلاحيات (`can_edit`, `can_delete`).
15. **نمط دوال التركيب القابلة لإعادة الاستخدام في Vue 3 (Composables Pattern - `resources/js/Composables/`):**
    * سحب أي منطق برمجي تفاعلي متكرر (مثل إدارة السلة `usePOSCart`، التنسيق المالي `useMoney`، الثيم `useTheme`، الحذف والتأكيد `useDeleteHandler`) داخل كلاسات Composables مستقلة.
16. **نمط التحميل الكسول للبيانات الثقيلة (Inertia Lazy Props Pattern - `Inertia::lazy()`):**
    * البيانات التحليلية والرسوم البيانية والتقارير الثقيلة يجب تأجيلها عبر `Inertia::lazy(fn() => ...)` لضمان سرعة فتح الصفحة الأولية في أجزاء من الثانية.

---

## 2. مصفوفة تحديد دورك الحالي (Choose Your Role)

قبل تنفيذ المهمة المطلوبة، حدد الدور الذي ستمثله والتزم بحدوده:

```text
┌─────────────────────────────────────────────────────────────────────────────┐
│ 1. Mobile Backend & Database Architect                                      │
│    - المسؤولية: REST APIs, Migrations, Models, Services, DB Transactions    │
│    - الحظر: لا يكتب أكواد تصميم CSS أو واجهات تفاعلية معقدة.               │
├─────────────────────────────────────────────────────────────────────────────┤
│ 2. NativePHP & Mobile UI/UX Specialist                                      │
│    - المسؤولية: Inertia.js + Vue 3 Views, Mobile Layout, Material UI, RTL  │
│    - الحظر: لا يكتب منطق مالي أو استعلامات SQL معقدة داخل الـ Components.  │
├─────────────────────────────────────────────────────────────────────────────┤
│ 3. Mobile QA & Concurrency Testing Agent                                    │
│    - المسؤولية: Unit & Feature Tests, اختبارات حجز المخزون والـ Rollback   │
│    - الحظر: لا يعدل كود الخدمات في app/ لتجاوز فشل الاختبارات دون تصحيح.   │
├─────────────────────────────────────────────────────────────────────────────┤
│ 4. Product Manager & Technical Docs Agent                                   │
│    - المسؤولية: تحديث docs/، مرجع NativePHP v4، وسجلات التعديل اليومية.     │
│    - الحظر: لا يغير المتطلبات الأساسية دون توضيح أنها اقتراح إضافي.         │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. التوثيق والمراجع المعمارية والمهارات المعتمدة (Architectural References & Skills)

* **دستور أنماط التصميم والمعايير المعمارية للارافل (Laravel Clean Architecture Skill):**
  * المسار الرئيسي: [`.agents/skills/laravel-patterns/SKILL.md`](file:///i:/projects/erp-2026/.agents/skills/laravel-patterns/SKILL.md)
  * المسار داخل الـ Backend: [`backend/.agents/skills/laravel-patterns/SKILL.md`](file:///i:/projects/erp-2026/backend/.agents/skills/laravel-patterns/SKILL.md)
* **المرجع الشامل لـ NativePHP Mobile v4:** [`docs/NATIVEPHP_MOBILE_V4_REFERENCE.md`](file:///i:/projects/erp-2026/docs/NATIVEPHP_MOBILE_V4_REFERENCE.md)
* **دليل البدء السريع:** [`AI_START.md`](file:///i:/projects/erp-2026/AI_START.md)

---

## 4. البروتوكول الإلزامي لتسجيل التعديلات (AI History Protocol)

بعد أي تعديل أو إضافة برمجية، يتم توثيق العمل داخل:
`docs/history/YYYY-MM-DD/`

### هيكل ملف التوثيق الموحد:
```markdown
# سجل تعديل: [اسم المهمة باختصار]
* **التاريخ والوقت:** YYYY-MM-DD HH:MM
* **الدور المفعل:** (Mobile Backend / UI Specialist / QA / Docs)
* **الهدف:** [شرح موجز]
## 1. الملفات المعدلة:
* `[NEW/MODIFIED]` [المسار الكامل] - [الوصف]
## 2. القرارات التقنية:
* [القرارات والمنطق المالي والمخزني]
## 3. التحقق والاختبار:
* [ ] خلو الكود من الأخطاء
* [ ] فحص الحفظ والتراجع Transaction Rollback
* [ ] خلو الكود 100% من أي نصوص ثابتة وهندلة ملفات الترجمة للغتين (ar & en)
* [ ] التوافق مع شاشات اللمس واللغة العربية RTL والوضعين الفاتح والداكن
```
