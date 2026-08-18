<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define all permissions grouped by module
        $permissions = [
            // POS & Invoices
            'pos.access'          => 'الوصول لشاشة نقطة البيع (POS)',
            'invoices.view'       => 'عرض سجل فواتير المبيعات',
            'invoices.create'     => 'إنشاء واعتماد فواتير المبيعات',
            'invoices.edit'       => 'تعديل فواتير المبيعات المعتمدة',
            'invoices.cancel'     => 'إلغاء الفواتير وعكس أثر المخزون',
            'invoices.delete'     => 'حذف وأرشفة فواتير المبيعات',
            'invoices.discount'   => 'صلاحية منح خصومات للعملاء',

            // Items & Inventory
            'items.view'          => 'عرض قائمة الأصناف والأسعار',
            'items.create'        => 'إضافة أصناف جديدة للمخزون',
            'items.edit'          => 'تعديل بيانات وأسعار الأصناف',
            'items.delete'        => 'أرشفة وحذف الأصناف',
            'items.view_cost'     => 'رؤية سعر التكلفة وهوامش الربح',

            // Purchases
            'purchases.view'      => 'عرض سجل فواتير المشتريات',
            'purchases.create'    => 'تسجيل وتوريد مشتريات جديدة للمخزن',
            'purchases.delete'    => 'أرشفة فواتير المشتريات',

            // Stores & Transfers
            'stores.manage'       => 'إدارة الفروع وتعيين الموظفين',
            'transfers.view'      => 'عرض أذونات التحويل المخزني',
            'transfers.create'    => 'إنشاء أذونات تحويل وشحن عربات التوزيع',

            // Contacts
            'customers.manage'    => 'إدارة دليل العملاء',
            'customers.statement' => 'عرض وتصدير كشف حساب عميل',
            'suppliers.manage'    => 'إدارة دليل الموردين وحساباتهم',
            'suppliers.statement' => 'عرض وتصدير كشف حساب مورد',

            // Financials & Daily Journal
            'daily_journal.view'        => 'عرض اليومية النقدية وحركة الدرج والشفتات',
            'daily_journal.close_shift' => 'فتح وتقفيل ورديات الكاشير واليومية',
            'expenses.manage'           => 'تسجيل وتعديل وحذف المصروفات',
            'returns.manage'            => 'إدارة مرتجعات المبيعات والمشتريات',

            // Admin & Reports
            'reports.view'        => 'عرض التقارير المالية والأرباح ومقارنة الفروع',
            'trash.access'        => 'الوصول لسلة المحذوفات المركزية واسترجاع البيانات',
            'roles.manage'        => 'إدارة المستخدمين والأدوار والصلاحيات',
            'logs.view'           => 'عرض وفحص سجل العمليات والرقابة الذاتية',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(['name' => $name], ['guard_name' => 'web']);
        }

        // 2. Roles Setup & Permission Assignment
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $storeRole = Role::firstOrCreate(['name' => 'storekeeper']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);

        // Admin gets ALL permissions
        $adminRole->syncPermissions(Permission::all());

        // Cashier permissions
        $cashierRole->syncPermissions([
            'pos.access',
            'invoices.view',
            'invoices.create',
            'items.view',
            'customers.manage',
            'customers.statement',
            'daily_journal.view',
            'daily_journal.close_shift',
            'returns.manage',
        ]);

        // Storekeeper permissions
        $storeRole->syncPermissions([
            'items.view',
            'items.create',
            'items.edit',
            'purchases.view',
            'purchases.create',
            'transfers.view',
            'transfers.create',
            'suppliers.manage',
            'suppliers.statement',
            'returns.manage',
        ]);

        // Accountant permissions
        $accountantRole->syncPermissions([
            'invoices.view',
            'purchases.view',
            'customers.manage',
            'customers.statement',
            'suppliers.manage',
            'suppliers.statement',
            'daily_journal.view',
            'daily_journal.close_shift',
            'expenses.manage',
            'reports.view',
            'items.view',
            'items.view_cost',
        ]);

        // 3. Auto-Heal any previous Treasury Additional Expenses to ensure they exist in Expense table
        try {
            $treasuryExpenses = \App\Models\AdditionalExpense::where('paid_by', 'like', 'treasury_%')->get();
            foreach ($treasuryExpenses as $exp) {
                $invoice = $exp->document;
                if ($invoice instanceof \App\Models\Invoice) {
                    $exists = \App\Models\Expense::where('title', 'like', "%{$invoice->invoice_number}%")->where('amount', $exp->amount)->exists();
                    if (!$exists) {
                        $pm = str_replace('treasury_', '', $exp->paid_by) ?: 'cash';
                        $expenseNumber = 'EXP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
                        \App\Models\Expense::create([
                            'expense_number' => $expenseNumber,
                            'category'       => 'shipping',
                            'title'          => "مصروف فاتورة [{$invoice->invoice_number}]: {$exp->title}",
                            'amount'         => $exp->amount,
                            'payment_method' => $pm,
                            'expense_date'   => $invoice->invoice_date,
                            'store_id'       => $invoice->store_id,
                            'user_id'        => $invoice->user_id ?? 1,
                            'notes'          => "مصروف خدمات/شحن مسدد من الخزينة لفاتورة مبيعات رقم {$invoice->invoice_number}",
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore if tables not yet migrated
        }
    }
}
