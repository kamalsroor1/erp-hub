<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Setup Roles safely (never delete existing data)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $storeRole = Role::firstOrCreate(['name' => 'storekeeper']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);

        // 2. Super Admin 1: كمال سرور (01012316954 / password)
        $admin1 = User::firstOrCreate(
            ['phone' => '01012316954'],
            [
                'name'      => 'كمال سرور - المدير العام',
                'email'     => '01012316954@sroor.com',
                'password'  => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $admin1->syncRoles([$adminRole]);

        // 3. Super Admin 2: المدير العام 2 (01558088841 / 123456789)
        $admin2 = User::firstOrCreate(
            ['phone' => '01558088841'],
            [
                'name'      => 'المدير العام 2',
                'phone'     => '01558088841',
                'email'     => '01558088841@sroor.com',
                'password'  => bcrypt('123456789'),
                'is_active' => true,
            ]
        );
        $admin2->syncRoles([$adminRole]);
    }
}
