<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$deletedUsers = DB::table('users')->whereIn('phone', ['01055554444', '01033332222'])->delete();
$deletedStores = DB::table('stores')->whereIn('code', ['SHOP-MAADI', 'VAN-E2E-02', 'TEMP-SHOP-99'])->delete();
$deletedRoles = DB::table('roles')->where('name', 'branch_manager')->delete();

$users = DB::table('users')->get(['id', 'name', 'phone', 'deleted_at']);
echo "ALL USERS IN DB:\n";
foreach ($users as $u) {
    echo "ID: {$u->id}, Name: {$u->name}, Phone: {$u->phone}, Deleted: {$u->deleted_at}\n";
}
