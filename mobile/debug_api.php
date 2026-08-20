<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ApiService;

echo "Base URL: " . ApiService::getBaseUrl() . "\n";
echo "Token before login: " . (ApiService::getToken() ?? 'none') . "\n";

$login = ApiService::login('01012316954', 'password');
echo "Login success: " . ($login['success'] ? 'YES' : 'NO') . "\n";
echo "Token after login: " . (ApiService::getToken() ?? 'none') . "\n";

$res = ApiService::getCustomers();
echo "GetCustomers response:\n";
print_r($res);
