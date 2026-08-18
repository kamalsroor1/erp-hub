<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\TreasuryTransfer;
use App\Services\TreasuryService;
use Livewire\Livewire;
use App\Livewire\ReportsIndex;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TreasuryReportFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'reports.view'], ['guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole('admin');

        $this->store = Store::create([
            'name'      => 'المخزن الرئيسي للتجربة',
            'code'      => 'MAIN-TEST',
            'type'      => 'main_warehouse',
            'is_main'   => true,
            'is_active' => true,
        ]);
    }

    public function test_treasury_service_calculates_per_account_balances_and_total_liquidity(): void
    {
        $customer = Customer::create([
            'name'  => 'عميل تجربة',
            'phone' => '01000000000',
        ]);
        $supplier = Supplier::create([
            'name'  => 'مورد تجربة',
            'phone' => '01100000000',
        ]);

        // 1. Cash Inflow 5,000 via Cashier
        Payment::create([
            'payment_number' => 'PAY-CUST-101',
            'customer_id'    => $customer->id,
            'payment_method' => 'cash',
            'amount'         => '5000.000',
            'payment_date'   => now()->toDateString(),
            'user_id'        => $this->admin->id,
        ]);

        // 2. InstaPay Inflow 10,000
        Payment::create([
            'payment_number' => 'PAY-CUST-102',
            'customer_id'    => $customer->id,
            'payment_method' => 'instapay',
            'amount'         => '10000.000',
            'payment_date'   => now()->toDateString(),
            'user_id'        => $this->admin->id,
        ]);

        // 3. E-Wallet Inflow 3,000
        Payment::create([
            'payment_number' => 'PAY-CUST-103',
            'customer_id'    => $customer->id,
            'payment_method' => 'e_wallet',
            'amount'         => '3000.000',
            'payment_date'   => now()->toDateString(),
            'user_id'        => $this->admin->id,
        ]);

        // 4. Expense from Cash: 1,000
        Expense::create([
            'expense_number' => 'EXP-001',
            'title'          => 'فواتير كهرباء',
            'category'       => 'utilities',
            'amount'         => '1000.000',
            'payment_method' => 'cash',
            'expense_date'   => now()->toDateString(),
            'store_id'       => $this->store->id,
            'user_id'        => $this->admin->id,
        ]);

        // 5. Transfer 2,000 from InstaPay to Cash with 10 EGP fee
        TreasuryTransfer::create([
            'transfer_number' => 'TRF-001',
            'from_method'     => 'instapay',
            'to_method'       => 'cash',
            'amount'          => '2000.000',
            'transfer_fee'    => '10.000',
            'store_id'        => $this->store->id,
            'user_id'         => $this->admin->id,
            'transfer_date'   => now()->toDateString(),
            'notes'           => 'تحويل لتغذية درج الكاش',
        ]);

        $treasuryService = app(TreasuryService::class);
        $report = $treasuryService->getTreasuryReport(
            fromDate: now()->startOfMonth()->toDateString(),
            toDate: now()->toDateString(),
            storeId: $this->store->id
        );

        // Expected Balances:
        // Cash: 5000 (inflow) - 1000 (expense) + 2000 (transfer in) = 6000
        $this->assertEquals('6000.000', $report['accounts']['cash']['closing_balance']);

        // InstaPay: 10000 (inflow) - 2000 (transfer out) - 10 (fee) = 7990
        $this->assertEquals('7990.000', $report['accounts']['instapay']['closing_balance']);

        // E-Wallet: 3000 (inflow) = 3000
        $this->assertEquals('3000.000', $report['accounts']['e_wallet']['closing_balance']);

        // Total Combined Liquidity (الكامل في الجميع): 6000 + 7990 + 3000 = 16990
        $this->assertEquals('16990.000', $report['total_liquidity']);

        // Verify transfers list
        $this->assertCount(1, $report['transfers']);
        $this->assertEquals('TRF-001', $report['transfers'][0]->transfer_number);
    }

    public function test_reports_index_livewire_renders_treasury_tab(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ReportsIndex::class)
            ->call('setTab', 'treasury')
            ->assertSet('activeTab', 'treasury')
            ->assertSee('جدول ملخص ومقارنة الخزائن وحسابات الدفع')
            ->assertSee('سجل التحويلات المالية بين الخزن')
            ->assertSee('كشف حركة الخزينة التسلسلي والرصيد اللحظي')
            ->assertSee('طباعة تقرير الخزينة A4');
    }

    public function test_treasury_report_print_a4_route(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('reports.print', [
            'tab'   => 'treasury',
            'from'  => now()->startOfMonth()->toDateString(),
            'to'    => now()->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertSee('تقرير الخزائن والسيولة وسجل التحويلات المالية');
        $response->assertSee('درج النقدية (كاش)');
        $response->assertSee('إنستاباي (InstaPay)');
        $response->assertSee('المحافظ الذكية');
    }
}
