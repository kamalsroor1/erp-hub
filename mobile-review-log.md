# 📱 سجل مراجعة تصميم واستجابة الموبايل (Mobile Review & Responsiveness Log)

هذا الملف يوثق كافة مراجعات الـ Responsive Design والـ Touch Ergonomics لتطبيق POS ونظام سرور كوفي ERP لضمان التوافق التام مع شاشات الهواتف الذكية (من ~360px وما فوق) والتابلت والكمبيوتر المكتبي.

---

## مراجعة بتاريخ 2026-08-21

### المشاكل القديمة اللي اتحلت في الجلسة دي
- **عدم وجود ملف المراجعة:** تم إنشاء ملف `mobile-review-log.md` لأول مرة كنواة توثيق مستمرة لمعايير الشاشات الصغيرة.
- **مفاتيح ترجمة مفقودة في نافذة اختيار الأوزان (POS Weight Picker):** ظهور مفاتيح خام `inventory.weight_eighth`, `inventory.weight_quarter`, `inventory.weight_half`, `inventory.weight_kilo` بدلاً من المسميات العربية. تم إضافتها لملفات الترجمة `lang/ar/inventory.php` و `lang/en/inventory.php`.

### الملفات اللي اتراجعت في الجلسة دي
1. `backend/resources/js/Components/POS/POSWeightPickerModal.vue`
2. `backend/resources/js/Components/POS/POSCustomerPickerModal.vue`
3. `backend/resources/js/Components/POS/POSQuickCustomerModal.vue`
4. `backend/resources/js/Components/POS/POSSuccessModal.vue`
5. `backend/resources/js/Components/FilterDrawer.vue`
6. `backend/resources/js/Components/SearchableSelect.vue`
7. `backend/resources/js/Components/DatePicker.vue`
8. `backend/resources/js/Components/POS/POSItemCard.vue`
9. `backend/resources/js/Components/POS/POSCartItem.vue`
10. `backend/resources/js/Pages/POS/Index.vue`
11. `backend/resources/js/Pages/Invoices/Index.vue`
12. `backend/resources/js/Pages/Items/Index.vue`
13. `backend/resources/js/Pages/Customers/Index.vue`
14. `backend/resources/js/Pages/Expenses/Index.vue`
15. `backend/resources/js/Pages/DailyJournal/Index.vue`
16. `backend/resources/js/Pages/Suppliers/Index.vue`
17. `backend/resources/js/Pages/Purchases/Index.vue`
18. `backend/resources/js/Pages/Purchases/Create.vue`
19. `backend/resources/js/Pages/Returns/Index.vue`
20. `backend/resources/js/Pages/StockTransfers/Index.vue`
21. `backend/resources/js/Layouts/AppLayout.vue`
22. `backend/lang/ar/inventory.php` & `backend/lang/en/inventory.php`

### المشاكل الجديدة اللي اتلاقت واتحلت

1. **`POSWeightPickerModal.vue` (نافذة تحديد الوزن السريع للبن):**
   * **المشكلة:** ظهور مفاتيح ترجمة إنجليزية خام داخل الأزرار (اللقطة المرفقة)، وحجم زر الإغلاق صغير جداً `w-7 h-7` (28px)، وحقول الإدخال بدون دعم الوضع الفاتح والداكن المتناسق.
   * **الحل:** تسجيل مفاتيح الترجمة، تكبير زر الإغلاق لـ `w-9 h-9` (36px)، تكبير أزرار الأوزان لـ `p-3.5 rounded-2xl`، وتكبير حقل الوزن المخصص لـ `h-11` وزر التأكيد لـ `h-12`.

2. **`POSCustomerPickerModal.vue` & `POSQuickCustomerModal.vue` (نوافذ اختيار وإضافة العملاء):**
   * **المشكلة:** عناصر القائمة ضيقة على اللمس بالأصبع، مع غياب زر إضافة عميل سريع من داخل نافذة البحث.
   * **الحل:** تكبير أسطر العملاء لـ `p-3 rounded-2xl min-h-[50px]` مع تأثيرات لمس `active:scale-98`، تكبير حقول الإدخال لـ `h-11` (لمنع الـ zoom التلقائي في Safari/Chrome على الموبايل)، وإضافة زر إنشاء عميل سريع.

3. **`FilterDrawer.vue` (درج الفلاتر المتقدمة في كل الشاشات):**
   * **المشكلة:** أزرار الفلترة في أسفل الدرج كانت صغيرة وبارتفاعات ثابتة ضيقة على الموبايل.
   * **الحل:** تكبير أزرار الإلغاء والتطبيق والمسح إلى `h-11 px-4 rounded-2xl` مع تفاعل حركي فوري.

4. **`SearchableSelect.vue` & `DatePicker.vue` (مكونات الاختيار والتواريخ المشتركة):**
   * **المشكلة:** ارتفاعات الحقول كانت صغيرة (`py-2.5` بدون حد أدنى للمس)، وقوائم الخيارات كانت تلتصق بحواف الشاشة.
   * **الحل:** توحيد ارتفاع الحقول الأساسية إلى `h-11 rounded-2xl`، تكبير أزرار المسح `✕` لـ `w-6 h-6`، وتكبير عناصر القائمة المنسدلة لـ `min-h-[40px]`.

5. **جداول المشتريات والموردين والمرتجعات والتحويلات المخزنية:**
   * **الملفات المتأثرة:** `Suppliers/Index.vue`, `Purchases/Index.vue`, `Returns/Index.vue`, `StockTransfers/Index.vue`
   * **المشكلة:** الجداول تحتوي على 6-9 أعمدة كانت تخرج عن حدود الشاشة (Horizontal Overflow) وتجعل الخطوط غير مقروءة على الموبايل.
   * **الحل:** إخفاء الجداول التقليدية على الشاشات الصغيرة بـ `hidden md:block`، وتصميم **كروت موبايل ذكية مخصصة (`md:hidden`)** تعرض البيانات الأساسية بوضوح مع أزرار لمس عريضة بارتفاع `h-10` إلى `h-11` وأزرار إجراءات `w-10 h-10`.

6. **مصفوفات الإحصائيات (KPI Cards) في شاشات النظام:**
   * **الملفات المتأثرة:** `Items/Index.vue`, `Expenses/Index.vue`, `Suppliers/Index.vue`, `Purchases/Index.vue`, `Returns/Index.vue`, `DailyJournal/Index.vue`
   * **المشكلة:** كانت تظهر كعمود فردي طويل جداً يستهلك الشاشة بأكملها ويتطلب تمريرًا متعبًا.
   * **الحل:** تحويلها لنظام شبكة **Bento Grid (2×2 على الموبايل)** تتيح رؤية جميع المؤشرات الحيوية دفعة واحدة بدون تمرير.

7. **`Purchases/Create.vue` (شاشة إنشاء فاتورة توريد):**
   * **المشكلة:** حقول الكمية والتكلفة كانت صغيرة، وأزرار الحذف كانت `w-7 h-7`.
   * **الحل:** تكبير حقول الإدخال لـ `h-10`، تكبير زر الحذف لـ `w-9 h-9`، وتكبير زر الإضافة والتأكيد لـ `h-11` و `h-12`.

---

### ملاحظات لسه محتاجة متابعة / مراجعة يدوية
- [ ] تجربة شاشة خلطات البن `CoffeeBlender/Index.vue` على الموبايل للتأكد من سهولة سحب وإضافة المكونات.
- [ ] تجربة طباعة الفاتورة الحرارية من متصفح الهاتف والتأكد من توافق قياس 80mm/58mm.
