# 🚀 دليل البدء السريع للمطور والذكاء الاصطناعي (AI Start Guide)

> **مشروع: تطبيق سرور كوفي ERP Mobile (NativePHP for Mobile v4 + Inertia.js + Vue 3)**

---

## 1. ملخص هيكل المشروع (Project Directory Layout)

المشروع مقسم إلى جزأين متكاملين:
```text
i:\projects\erp-2026\
├── backend/                  # خادم السحابة وقواعد البيانات (Laravel 12 REST API)
│   ├── app/Http/Controllers/Api/ # متحكمات المصادقة، العملاء، والموردين
│   ├── routes/api.php        # مسارات REST API المعتمدة
│   └── docs/                 # وثائق النظام الأصلي وقواعد البيانات
│
├── mobile/                   # تطبيق الموبايل (NativePHP Mobile v4 + Inertia + Vue 3)
│   ├── app/
│   │   ├── Http/Controllers/ # متحكمات Inertia (Auth, Dashboard, Customers, Suppliers)
│   │   └── Services/         # ApiService (حلقة الوصل مع سيرفر الباك إند)
│   ├── resources/js/         # واجهات Vue 3 (Composition API) و Layouts
│   │   ├── Components/       # SplashScreen, Toasts, Inputs
│   │   ├── Layouts/          # MobileLayout الموحد (Header, Bottom Nav, Loaders)
│   │   └── Pages/            # Auth, Dashboard, Customers, Suppliers
│   ├── build-apk.bat         # سكريبت بناء تطبيق الأندرويد المباشر (APK)
│   └── nativephp/            # بيئة الأندرويد المضمنة
│
├── docs/                     # التوثيق الشامل
│   └── NATIVEPHP_MOBILE_V4_REFERENCE.md # مرجع NativePHP Mobile v4 و EDGE Components
│
├── AGENTS.md                 # دليل تشغيل الذكاء الاصطناعي الرئيسي
└── AI_START.md               # هذا الدليل السريع
```

---

## 2. البدء السريع والتشغيل (Quick Start Commands)

### 2.1 تشغيل خادم الباك إند (REST API Server):
```bash
cd backend
php artisan serve --host=0.0.0.0 --port=8000
```

### 2.2 تشغيل تطبيق الموبايل (Vue 3 SPA Mode):
```bash
cd mobile
npm run build
php artisan serve --host=0.0.0.0 --port=8080
```
> رابط الهاتف المباشر (عبر الواي فاي): `http://192.168.1.32:8080`

### 2.3 بناء ملف الـ Android APK المباشر:
```bash
cd mobile
build-apk.bat
```

---

## 3. تفاصيل الهوية البصرية والألوان (Light & Dark Modes)

* **الخطوط (Typography):** `Cairo` و `Tajawal`
* **الاتجاه (Direction):** RTL كامل `dir="rtl"`
* **الأخضر الزمردي (Emerald):** `#10b981` / `#059669`
* **الذهبي/العنبري (Amber/Gold):** `#f59e0b` / `#d97706`
* **الوضع الداكن الفاخر (Dark Slate):** `#020617` / `#0f172a` / `#1e293b`
* **الوضع الفاتح الصافي (Light Shell):** `#f8fafc` / `#ffffff` / `#e2e8f0`

---

## 4. المرجع الشامل لـ NativePHP Mobile v4

للاطلاع على التوثيق التفصيلي لكافة مكونات EDGE Components، والـ Safe Area، والـ Dialogs، والـ Inputs، يرجى مراجعة:
[`docs/NATIVEPHP_MOBILE_V4_REFERENCE.md`](file:///i:/projects/erp-2026/docs/NATIVEPHP_MOBILE_V4_REFERENCE.md)
