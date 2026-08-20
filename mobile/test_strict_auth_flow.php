<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ApiService;

echo "=== 🔒 1. فحص الحماية: محاولة الدخول للرئيسية بدون تسجيل دخول ===" . PHP_EOL;
ApiService::clearAuth();
$guestReq = Illuminate\Http\Request::create('/', 'GET');
$guestRes = $app->handle($guestReq);
echo "كود الاستجابة لغير المسجل: " . $guestRes->getStatusCode() . " (يجب أن يكون 302 إعادة توجيه إلى login)" . PHP_EOL;
echo "عنوان التحويل: " . $guestRes->headers->get('Location') . PHP_EOL . PHP_EOL;

echo "=== 🔑 2. تسجيل الدخول عبر API ===" . PHP_EOL;
$loginResult = ApiService::login('01012316954', 'password');
echo "نتيجة الدخول: " . ($loginResult['success'] ? 'نجح ✅' : 'فشل ❌') . PHP_EOL;
echo "المستخدم: " . ($loginResult['user']['name'] ?? 'غير معروف') . PHP_EOL;
echo "التحقق من الجلسة: " . (ApiService::isAuthenticated() ? 'مصادق عليه ✅' : 'غير مسجل ❌') . PHP_EOL . PHP_EOL;

echo "=== 🚪 3. تسجيل الخروج وفحص الإلغاء ===" . PHP_EOL;
ApiService::logout();
echo "بعد الخروج، هل لا يزال مسجل؟ " . (ApiService::isAuthenticated() ? 'نعم ❌' : 'لا (تم إغلاق الجلسة بنجاح ✅)') . PHP_EOL;
