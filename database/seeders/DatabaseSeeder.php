<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\ReturnRecord;
use App\Models\ReturnRecordItem;
use App\Models\CashShift;
use App\Services\StockService;
use App\Services\InvoiceService;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(StockService $stockService, InvoiceService $invoiceService): void
    {
        // 1. Disable Foreign Key checks to cleanly wipe previous dummy data
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        // 2. Setup Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $storeRole = Role::firstOrCreate(['name' => 'storekeeper']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);

        // 3. Clear all dummy transactions & inventory data
        InvoiceItem::query()->delete();
        Invoice::query()->delete();
        PurchaseItem::query()->delete();
        Purchase::query()->delete();
        ReturnRecordItem::query()->delete();
        ReturnRecord::query()->delete();
        StockMovement::query()->delete();
        Payment::query()->delete();
        AuditLog::query()->delete();
        Customer::query()->delete();
        Supplier::query()->delete();
        Item::query()->delete();

        if (Schema::hasTable('cash_shifts')) {
            CashShift::query()->delete();
        }

        // 4. Wipe users table to leave ONLY the 2 requested Super Admins
        User::query()->delete();

        // 5. Super Admin 1: كمال سرور (01012316954 / password)
        $admin1 = User::create([
            'name'      => 'كمال سرور - المدير العام',
            'phone'     => '01012316954',
            'email'     => '01012316954@sroor.com',
            'password'  => bcrypt('password'),
            'is_active' => true,
        ]);
        $admin1->syncRoles([$adminRole]);

        // 6. Super Admin 2: المدير العام 2 (01558088841 / 123456789)
        $admin2 = User::create([
            'name'      => 'المدير العام 2',
            'phone'     => '01558088841',
            'email'     => '01558088841@sroor.com',
            'password'  => bcrypt('123456789'),
            'is_active' => true,
        ]);
        $admin2->syncRoles([$adminRole]);

        // 7. Re-enable Foreign Key checks
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
