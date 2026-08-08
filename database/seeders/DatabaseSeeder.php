<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Disable Foreign Key checks for clean wipe
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        // 2. Setup Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $storeRole = Role::firstOrCreate(['name' => 'storekeeper']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);

        // 3. Truncate all transaction and item tables
        $tables = [
            'invoice_items',
            'invoices',
            'purchase_items',
            'purchases',
            'return_items',
            'returns',
            'sales_returns',
            'purchase_returns',
            'stock_movements',
            'payments',
            'audit_logs',
            'customers',
            'suppliers',
            'items',
            'cash_shifts',
            'users',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        // 4. Super Admin 1: كمال سرور (01012316954 / password)
        $admin1 = User::create([
            'name'      => 'كمال سرور - المدير العام',
            'phone'     => '01012316954',
            'email'     => '01012316954@sroor.com',
            'password'  => bcrypt('password'),
            'is_active' => true,
        ]);
        $admin1->syncRoles([$adminRole]);

        // 5. Super Admin 2: المدير العام 2 (01558088841 / 123456789)
        $admin2 = User::create([
            'name'      => 'المدير العام 2',
            'phone'     => '01558088841',
            'email'     => '01558088841@sroor.com',
            'password'  => bcrypt('123456789'),
            'is_active' => true,
        ]);
        $admin2->syncRoles([$adminRole]);

        // 6. Re-enable Foreign Key checks
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
