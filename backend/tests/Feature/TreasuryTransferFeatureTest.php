<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\TreasuryTransfer;
use App\Services\TreasuryService;
use App\Livewire\DailyJournalIndex;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Exception;

class TreasuryTransferFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Store $store;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $perms = [
            'daily_journal.view',
            'daily_journal.close_shift',
            'pos.access',
            'invoices.create',
        ];
        foreach ($perms as $p) {
            $permission = Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
            $role->givePermissionTo($permission);
        }

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->actingAs($this->admin);

        $this->store = Store::create([
            'name'       => 'الفرع الرئيسي',
            'code'       => 'STR-MAIN',
            'type'       => 'store',
            'is_default' => true,
            'is_active'  => true,
        ]);

        $this->customer = Customer::create([
            'name'       => 'عميل تجريبي كاشير',
            'phone'      => '01000000099',
            'is_active'  => true,
        ]);
    }

    public function test_user_can_transfer_money_from_instapay_to_cash_drawer(): void
    {
        /** @var TreasuryService $treasuryService */
        $treasuryService = app(TreasuryService::class);

        // Simulate receiving 10,000 LE via InstaPay and 5,000 LE via E-Wallet
        Payment::create([
            'payment_number' => 'PAY-INSTA-01',
            'customer_id'    => $this->customer->id,
            'user_id'        => $this->admin->id,
            'amount'         => '10000.000',
            'payment_date'   => '2026-08-18',
            'payment_method' => 'instapay',
            'notes'          => 'تحويل إنستاباي من العميل',
        ]);

        Payment::create([
            'payment_number' => 'PAY-WAL-01',
            'customer_id'    => $this->customer->id,
            'user_id'        => $this->admin->id,
            'amount'         => '5000.000',
            'payment_date'   => '2026-08-18',
            'payment_method' => 'e_wallet',
            'notes'          => 'تحويل محفظة فودافون كاش',
        ]);

        // Check Initial Balances: Cash = 0, InstaPay = 10,000, E-Wallet = 5,000
        $initialBalances = $treasuryService->getBalances(storeId: $this->store->id);
        $this->assertEquals('0.000', $initialBalances['cash']['balance']);
        $this->assertEquals('10000.000', $initialBalances['instapay']['balance']);
        $this->assertEquals('5000.000', $initialBalances['e_wallet']['balance']);
        $this->assertEquals('15000.000', $initialBalances['total_liquidity']);

        // Execute Transfer of 4,000 LE from InstaPay to Cash Drawer
        $transfer = $treasuryService->transfer([
            'from_method'   => 'instapay',
            'to_method'     => 'cash',
            'amount'        => '4000.000',
            'transfer_fee'  => '0.000',
            'store_id'      => $this->store->id,
            'transfer_date' => '2026-08-18',
            'notes'         => 'سحب نقدية من الـ ATM لتغذية الدرج',
        ]);

        $this->assertNotNull($transfer);
        $this->assertStringStartsWith('TRF-', $transfer->transfer_number);
        $this->assertEquals('4000.000', $transfer->amount);

        // Check Updated Balances: Cash = 4,000, InstaPay = 6,000, E-Wallet = 5,000, Total = 15,000
        $updatedBalances = $treasuryService->getBalances(storeId: $this->store->id);
        $this->assertEquals('4000.000', $updatedBalances['cash']['balance']);
        $this->assertEquals('6000.000', $updatedBalances['instapay']['balance']);
        $this->assertEquals('5000.000', $updatedBalances['e_wallet']['balance']);
        $this->assertEquals('15000.000', $updatedBalances['total_liquidity']);
    }

    public function test_transfer_with_fees_deducts_fee_from_source_account(): void
    {
        /** @var TreasuryService $treasuryService */
        $treasuryService = app(TreasuryService::class);

        // 5,000 LE in E-Wallet
        Payment::create([
            'payment_number' => 'PAY-WAL-02',
            'customer_id'    => $this->customer->id,
            'user_id'        => $this->admin->id,
            'amount'         => '5000.000',
            'payment_date'   => '2026-08-18',
            'payment_method' => 'e_wallet',
        ]);

        // Transfer 2,000 LE from E-Wallet to Cash with 20 LE withdrawal fee
        $transfer = $treasuryService->transfer([
            'from_method'   => 'e_wallet',
            'to_method'     => 'cash',
            'amount'        => '2000.000',
            'transfer_fee'  => '20.000',
            'store_id'      => $this->store->id,
            'transfer_date' => '2026-08-18',
            'notes'         => 'سحب كاش من فودافون كاش بعمولة 20 ج.م',
        ]);

        $balances = $treasuryService->getBalances(storeId: $this->store->id);
        // E-Wallet = 5000 - 2000 - 20 = 2980.000
        $this->assertEquals('2980.000', $balances['e_wallet']['balance']);
        // Cash = 2000.000
        $this->assertEquals('2000.000', $balances['cash']['balance']);
        // Total Liquidity = 4980.000
        $this->assertEquals('4980.000', $balances['total_liquidity']);
    }

    public function test_transfer_to_same_account_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('لا يمكن التحويل لنفس الحساب');

        /** @var TreasuryService $treasuryService */
        $treasuryService = app(TreasuryService::class);

        $treasuryService->transfer([
            'from_method'   => 'cash',
            'to_method'     => 'cash',
            'amount'        => '1000.000',
            'transfer_date' => '2026-08-18',
        ]);
    }

    public function test_transfer_zero_amount_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('يرجى إدخال مبلغ تحويل صحيح');

        /** @var TreasuryService $treasuryService */
        $treasuryService = app(TreasuryService::class);

        $treasuryService->transfer([
            'from_method'   => 'instapay',
            'to_method'     => 'cash',
            'amount'        => '0.000',
            'transfer_date' => '2026-08-18',
        ]);
    }

    public function test_daily_journal_livewire_component_executes_transfer_successfully(): void
    {
        // Add 10,000 LE in InstaPay
        Payment::create([
            'payment_number' => 'PAY-INSTA-03',
            'customer_id'    => $this->customer->id,
            'user_id'        => $this->admin->id,
            'amount'         => '10000.000',
            'payment_date'   => '2026-08-18',
            'payment_method' => 'instapay',
        ]);

        Livewire::test(DailyJournalIndex::class)
            ->call('openTransferModal')
            ->assertSet('showTransferModal', true)
            ->set('transfer_from_method', 'instapay')
            ->set('transfer_to_method', 'cash')
            ->set('transfer_amount', '3500.000')
            ->set('transfer_fee', '0.000')
            ->set('transfer_notes', 'تغذية الدرج من حساب إنستاباي')
            ->call('executeTransfer')
            ->assertSet('showTransferModal', false)
            ->assertSee('تم تحويل مبلغ 3500.000 ج.م بنجاح');

        $this->assertDatabaseHas('treasury_transfers', [
            'from_method' => 'instapay',
            'to_method'   => 'cash',
            'amount'      => '3500.000',
        ]);
    }

    public function test_transfer_more_than_available_balance_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('غير كافٍ لإتمام التحويل');

        /** @var TreasuryService $treasuryService */
        $treasuryService = app(TreasuryService::class);

        // InstaPay has 0 balance, attempt to transfer 500 LE
        $treasuryService->transfer([
            'from_method'   => 'instapay',
            'to_method'     => 'cash',
            'amount'        => '500.000',
            'transfer_date' => '2026-08-18',
        ]);
    }
}
