<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Livewire\Auth\UserManager;
use App\Models\User;

$um = new UserManager();
$um->name = 'أحمد كاشير المعادي E2E';
$um->phone = '01055554444';
$um->email = '';
$um->role = 'cashier';
$um->password = 'password123';
$um->is_active = true;

try {
    $um->saveUser();
    echo "SUCCESS! User count: " . User::where('phone', '01055554444')->count() . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    if (method_exists($e, 'errors')) {
        echo "VALIDATION ERRORS: " . json_encode($e->errors(), JSON_UNESCAPED_UNICODE) . "\n";
    }
}
