<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Item;
use App\Models\Customer;
use App\Models\StoreStock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\ShiftService;
use App\Services\ProfitService;
use App\Livewire\DailyJournalIndex;
use App\Livewire\ReportsIndex;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

class MultiStorePhase4Test extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Store $shop;
    protected Store $van;
    protected Item $item;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->shop = Store::create([
            'name'      => 'محل التجزئة الرئيسي',
            'code'      => 'MAIN-SHOP',
            'type'      => 'retail_shop',
            'is_active' => true,
            'is_main'   => true,
        ]);

        $this->van = Store::create([
            'name'      => 'عربية التوزيع رقم 1',
            'code'      => 'VAN-01',
            'type'      => 'wholesale_van',
            'is_active' => true,
            'is_main'   => false,
        ]);

        $this->item = Item::create([
            'name'             => 'بن برازيلي وسط',
            'code'             => 'COF-BRZ-MED',
            'category'         => 'بن وتوليفات',
            'unit'             => 'كجم',
            'cost_price'       => '100.000',
            'selling_price'    => '180.000',
            'min_stock_level'  => '5.000',
            'current_stock'    => '50.000',
            'is_active'        => true,
        ]);

        StoreStock::create([
            'store_id'             => $this->shop->id,
            'item_id'              => $this->item->id,
            'quantity'             => '30.000',
            'min_stock'            => '5.000',
            'custom_selling_price' => null,
        ]);

        StoreStock::create([
            'store_id'             => $this->van->id,
            'item_id'              => $this->item->id,
            'quantity'             => '20.000',
            'min_stock'            => '5.000',
            'custom_selling_price' => '150.000',
        ]);

        $this->customer = Customer::create([
            'name'            => 'عميل تجريبي',
            'phone'           => '01000000000',
            'current_balance' => '0.000',
            'is_active'       => true,
        ]);
    }

    public function test_shift_service_calculates_store_scoped_totals(): void
    {
        $shiftService = app(ShiftService::class);

        // Open shift for van
        $vanShift = $shiftService->openShift(
            openingCash: '200.000',
            notes: 'وردية عربية التوزيع',
            storeId: $this->van->id
        );

        $this->assertEquals($this->van->id, $vanShift->store_id);

        // Create invoice on van
        $invoice = Invoice::create([
            'store_id'         => $this->van->id,
            'customer_id'      => $this->customer->id,
            'user_id'          => $this->admin->id,
            'invoice_number'   => 'INV-VAN-001',
            'invoice_date'     => now()->toDateString(),
            'payment_type'     => 'cash',
            'subtotal'         => '300.000',
            'discount_type'    => 'fixed',
            'discount_value'   => '0.000',
            'discount_amount'  => '0.000',
            'net_total'        => '300.000',
            'paid_amount'      => '300.000',
            'remaining_amount' => '0.000',
            'total_cost'       => '200.000',
            'status'           => 'confirmed',
        ]);

        $totals = $shiftService->calculateShiftTotals($vanShift);

        // Expected in drawer = 200 (opening) + 300 (cash sales) = 500
        $this->assertEquals('300.000', $totals['total_cash_sales']);
        $this->assertEquals('500.000', $totals['expected_cash_balance']);

        // Close shift with actual 500.000
        $closed = $shiftService->closeShift($vanShift, '500.000');
        $this->assertEquals('closed', $closed->status);
        $this->assertEquals('0.000', $closed->cash_difference);
    }

    public function test_reports_index_livewire_can_filter_and_compare_stores(): void
    {
        $this->actingAs($this->admin);

        // Create 1 invoice on shop (net: 180, cost: 100, profit: 80)
        Invoice::create([
            'store_id'         => $this->shop->id,
            'customer_id'      => $this->customer->id,
            'user_id'          => $this->admin->id,
            'invoice_number'   => 'INV-SHOP-001',
            'invoice_date'     => now()->toDateString(),
            'payment_type'     => 'cash',
            'subtotal'         => '180.000',
            'discount_type'    => 'fixed',
            'discount_value'   => '0.000',
            'discount_amount'  => '0.000',
            'net_total'        => '180.000',
            'paid_amount'      => '180.000',
            'remaining_amount' => '0.000',
            'total_cost'       => '100.000',
            'status'           => 'confirmed',
        ]);

        // Create 1 invoice on van (net: 150, cost: 100, profit: 50)
        Invoice::create([
            'store_id'         => $this->van->id,
            'customer_id'      => $this->customer->id,
            'user_id'          => $this->admin->id,
            'invoice_number'   => 'INV-VAN-002',
            'invoice_date'     => now()->toDateString(),
            'payment_type'     => 'cash',
            'subtotal'         => '150.000',
            'discount_type'    => 'fixed',
            'discount_value'   => '0.000',
            'discount_amount'  => '0.000',
            'net_total'        => '150.000',
            'paid_amount'      => '150.000',
            'remaining_amount' => '0.000',
            'total_cost'       => '100.000',
            'status'           => 'confirmed',
        ]);

        // Test Consolidated Report (All stores): Total sales = 180 + 150 = 330, Profit = 130
        $profitService = app(ProfitService::class);
        $consolidated = $profitService->getPeriodicProfits(now()->toDateString(), now()->toDateString(), null);
        $this->assertEquals('330.000', $consolidated['total_sales']);
        $this->assertEquals('130.000', $consolidated['gross_profit']);

        // Test Van-only Report: Total sales = 150, Profit = 50
        $vanReport = $profitService->getPeriodicProfits(now()->toDateString(), now()->toDateString(), $this->van->id);
        $this->assertEquals('150.000', $vanReport['total_sales']);
        $this->assertEquals('50.000', $vanReport['gross_profit']);

        // Test ReportsIndex Livewire component
        Livewire::test(ReportsIndex::class)
            ->set('selectedStoreId', 'all')
            ->assertSee('مقارنة أداء ومبيعات الفروع وعربيات التوزيع')
            ->assertSee($this->van->name)
            ->assertSee($this->shop->name);
    }
}
