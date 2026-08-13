<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

$adminRole = Role::firstOrCreate(['name' => 'admin']);
$adminRole->syncPermissions(Permission::all());

$user = User::firstOrCreate(
    ['phone' => '01012316954'],
    [
        'name' => 'كمال سرور',
        'email' => '01012316954@sroor.com',
        'is_active' => true,
    ]
);
$user->password = Hash::make('password');
$user->is_active = true;
$user->save();
$user->syncRoles([$adminRole]);

echo "SUCCESS: " . $user->name . " has " . $user->roles->pluck('name')->join(',') . " and " . $user->getAllPermissions()->count() . " permissions.\n";
