<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Store;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Models\Expense;
use App\Services\InventoryAnalyticsService;
use App\Services\ProfitLossService;
use App\Services\DashboardAnalyticsService;
use App\Services\ReorderAssistantService;
use App\Services\InvoiceService;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SelectedFeaturesSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Store $mainStore;
    protected Store $vanStore;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $role = Role::create(['name' => 'admin']);
        foreach ([
            'invoices.view', 'invoices.create', 'purchases.view', 'purchases.create',
            'reports.view', 'expenses.manage', 'items.view', 'items.create', 'items.edit',
            'customers.manage', 'suppliers.manage', 'daily_journal.view', 'daily_journal.close_shift',
        ] as $perm) {
            Permission::create(['name' => $perm]);
            $role->givePermissionTo($perm);
        }

        $this->mainStore = Store::create([
            'name'       => 'الفرع الرئيسي',
            'code'       => 'MAIN01',
            'is_default' => true,
            'is_active'  => true,
        ]);

        $this->vanStore = Store::create([
            'name'       => 'عربة التوزيع 01',
            'code'       => 'VAN01',
            'is_default' => false,
            'is_active'  => true,
        ]);

        $this->adminUser = User::create([
            'name'      => 'المدير العام',
            'email'     => '01000000000@sroor.com',
            'phone'     => '01000000000',
            'password'  => bcrypt('password'),
            'store_id'  => $this->mainStore->id,
            'is_active' => true,
        ]);
        $this->adminUser->assignRole('admin');

        $this->customer = Customer::create([
            'name'      => 'عميل تجريبي كاشير',
            'phone'     => '01011112222',
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser);
    }

    public function test_abc_analysis_categorizes_items_into_classes_correctly(): void
    {
        // Create 3 items:
        // Item A: High profit
        $itemA = Item::create([
            'name'              => 'بن كولومبي فاخر (A)',
            'code'              => 'ITEM-A',
            'unit'              => 'كجم',
            'cost_price'        => '100.000',
            'sale_price'        => '300.000', // 200 LE profit/kg
            'current_stock'     => '50.000',
            'weighted_avg_cost' => '100.000',
            'is_active'         => true,
        ]);

        // Item B: Medium profit
        $itemB = Item::create([
            'name'              => 'شاي أسود كيني (B)',
            'code'              => 'ITEM-B',
            'unit'              => 'كجم',
            'cost_price'        => '80.000',
            'sale_price'        => '120.000', // 40 LE profit/kg
            'current_stock'     => '30.000',
            'weighted_avg_cost' => '80.000',
            'is_active'         => true,
        ]);

        // Item C: Unsold dead stock
        $itemC = Item::create([
            'name'              => 'أكواب خزف راكدة (C)',
            'code'              => 'ITEM-C',
            'unit'              => 'قطعة',
            'cost_price'        => '20.000',
            'sale_price'        => '40.000',
            'current_stock'     => '15.000',
            'weighted_avg_cost' => '20.000',
            'is_active'         => true,
        ]);

        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class);

        // Sell 10 kg of Item A (Profit = 2,000 LE)
        $invoiceService->confirmInvoice([
            'customer_id'    => $this->customer->id,
            'store_id'       => $this->mainStore->id,
            'invoice_date'   => '2026-08-19',
            'payment_type'   => 'cash',
            'payment_method' => 'cash',
            'items'          => [
                ['item_id' => $itemA->id, 'quantity' => '10.000', 'unit_price' => '300.000'],
            ],
        ]);

        // Sell 5 kg of Item B (Profit = 200 LE)
        $invoiceService->confirmInvoice([
            'customer_id'    => $this->customer->id,
            'store_id'       => $this->mainStore->id,
            'invoice_date'   => '2026-08-19',
            'payment_type'   => 'cash',
            'payment_method' => 'cash',
            'items'          => [
                ['item_id' => $itemB->id, 'quantity' => '5.000', 'unit_price' => '120.000'],
            ],
        ]);

        $abcService = app(InventoryAnalyticsService::class);
        $result = $abcService->getAbcAnalysis('2026-08-01', '2026-08-19');

        $this->assertEquals(3, $result['total_items_count']);
        $this->assertEquals('2200.000', $result['total_profit']);

        $itemAData = collect($result['items'])->firstWhere('code', 'ITEM-A');
        $this->assertEquals('A', $itemAData['abc_class']);
        $this->assertEquals('2000.000', $itemAData['gross_profit']);

        $itemCData = collect($result['items'])->firstWhere('code', 'ITEM-C');
        $this->assertEquals('C', $itemCData['abc_class']);
        $this->assertCount(1, $result['dead_stock']);
    }

    public function test_branch_and_van_pnl_calculates_revenues_cogs_and_cost_centers(): void
    {
        $item = Item::create([
            'name'              => 'بن محوج ممتاز',
            'code'              => 'MHW-01',
            'unit'              => 'كجم',
            'cost_price'        => '100.000',
            'sale_price'        => '200.000',
            'current_stock'     => '100.000',
            'weighted_avg_cost' => '100.000',
            'is_active'         => true,
        ]);

        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class);

        // Main Store: Sell 10 kg = 2,000 LE Revenue, 1,000 LE COGS, 1,000 LE Gross Profit
        $invoiceService->confirmInvoice([
            'customer_id'    => $this->customer->id,
            'store_id'       => $this->mainStore->id,
            'invoice_date'   => '2026-08-19',
            'payment_type'   => 'cash',
            'payment_method' => 'cash',
            'items'          => [
                ['item_id' => $item->id, 'quantity' => '10.000', 'unit_price' => '200.000'],
            ],
        ]);

        // Main Store: Expense 300 LE rent
        Expense::create([
            'expense_number' => 'EXP-20260819-0001',
            'category'       => 'إيجارات',
            'cost_center'    => 'rent',
            'title'          => 'إيجار مقر الفرع الرئيسي',
            'amount'         => '300.000',
            'expense_date'   => '2026-08-19',
            'payment_method' => 'cash',
            'store_id'       => $this->mainStore->id,
        ]);

        // Van Store: Sell 5 kg = 1,000 LE Revenue, 500 LE COGS, 500 LE Gross Profit
        $invoiceService->confirmInvoice([
            'customer_id'    => $this->customer->id,
            'store_id'       => $this->vanStore->id,
            'invoice_date'   => '2026-08-19',
            'payment_type'   => 'cash',
            'payment_method' => 'cash',
            'items'          => [
                ['item_id' => $item->id, 'quantity' => '5.000', 'unit_price' => '200.000'],
            ],
        ]);

        // Van Store: Expense 100 LE fuel
        Expense::create([
            'expense_number' => 'EXP-20260819-0002',
            'category'       => 'بنزين وسيارات',
            'cost_center'    => 'vehicles',
            'title'          => 'بنزين ووقود سيارة التوزيع',
            'amount'         => '100.000',
            'expense_date'   => '2026-08-19',
            'payment_method' => 'cash',
            'store_id'       => $this->vanStore->id,
        ]);

        $pnlService = app(ProfitLossService::class);
        $pnlReport = $pnlService->getProfitLossReport('2026-08-01', '2026-08-19');

        $this->assertEquals('3000.000', $pnlReport['grand_revenue']);
        $this->assertEquals('1500.000', $pnlReport['grand_cogs']);
        $this->assertEquals('1500.000', $pnlReport['grand_gross_profit']);
        $this->assertEquals('400.000', $pnlReport['grand_expenses']);
        $this->assertEquals('1100.000', $pnlReport['grand_net_profit']);

        $mainStorePnl = collect($pnlReport['stores'])->firstWhere('store_code', 'MAIN01');
        $this->assertEquals('700.000', $mainStorePnl['net_operating_profit']); // 1000 - 300 = 700

        $vanStorePnl = collect($pnlReport['stores'])->firstWhere('store_code', 'VAN01');
        $this->assertEquals('400.000', $vanStorePnl['net_operating_profit']); // 500 - 100 = 400
    }

    public function test_dashboard_analytics_calculates_basket_size_and_daily_trend(): void
    {
        $item = Item::create([
            'name'              => 'صنف مبيعات تجريبي',
            'code'              => 'DASH-01',
            'unit'              => 'قطعة',
            'cost_price'        => '50.000',
            'sale_price'        => '100.000',
            'current_stock'     => '50.000',
            'weighted_avg_cost' => '50.000',
            'is_active'         => true,
        ]);

        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class);

        // 2 invoices today: one 100 LE, one 300 LE => Total 400 LE, Basket Size = 200.00 LE
        $invoiceService->confirmInvoice([
            'customer_id'    => $this->customer->id,
            'store_id'       => $this->mainStore->id,
            'invoice_date'   => now()->toDateString(),
            'payment_type'   => 'cash',
            'payment_method' => 'cash',
            'items'          => [['item_id' => $item->id, 'quantity' => '1.000', 'unit_price' => '100.000']],
        ]);

        $invoiceService->confirmInvoice([
            'customer_id'    => $this->customer->id,
            'store_id'       => $this->mainStore->id,
            'invoice_date'   => now()->toDateString(),
            'payment_type'   => 'cash',
            'payment_method' => 'instapay',
            'items'          => [['item_id' => $item->id, 'quantity' => '3.000', 'unit_price' => '100.000']],
        ]);

        $dashboardService = app(DashboardAnalyticsService::class);
        $analytics = $dashboardService->getAnalytics();

        $this->assertEquals(0, bccomp((string)$analytics['today']['sales'], '400.000', 3));
        $this->assertEquals(2, $analytics['today']['invoices_count']);
        $this->assertEquals('200.00', $analytics['today']['basket_size']);

        // Livewire Dashboard Test
        Livewire::test(\App\Livewire\Dashboard::class)
            ->assertStatus(200)
            ->assertSee('أوقات وساعات الذروة');
    }

    public function test_smart_reorder_assistant_calculates_velocity_and_depletion(): void
    {
        $item = Item::create([
            'name'              => 'صنف يقترب من النفاد',
            'code'              => 'REORDER-01',
            'unit'              => 'كجم',
            'cost_price'        => '80.000',
            'sale_price'        => '120.000',
            'current_stock'     => '34.000', // 34 kg initial stock
            'weighted_avg_cost' => '80.000',
            'is_active'         => true,
        ]);

        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class);

        // Sell 28 kg over the period (e.g. 2 kg per day for 14 days) -> 6 kg remaining
        $invoiceService->confirmInvoice([
            'customer_id'    => $this->customer->id,
            'store_id'       => $this->mainStore->id,
            'invoice_date'   => now()->toDateString(),
            'payment_type'   => 'cash',
            'payment_method' => 'cash',
            'items'          => [['item_id' => $item->id, 'quantity' => '28.000', 'unit_price' => '120.000']],
        ]);

        $reorderService = app(ReorderAssistantService::class);
        $suggestions = $reorderService->getReorderSuggestions(analysisDays: 14, targetCoverDays: 15);

        $itemSuggestion = collect($suggestions['suggestions'])->firstWhere('code', 'REORDER-01');
        $this->assertNotNull($itemSuggestion);
        $this->assertEquals('2.000', $itemSuggestion['daily_consumption']); // 28 / 14 = 2.000 kg/day
        $this->assertEquals(3, $itemSuggestion['days_remaining']); // 6 kg / 2 = 3 days remaining => critical
        $this->assertEquals('critical', $itemSuggestion['urgency']);

        // Target for 15 days = 30 kg needed - 6 kg in stock = 24 kg suggested
        $this->assertEquals('24.000', $itemSuggestion['suggested_quantity']);

        // Livewire SmartReorderIndex test
        Livewire::test(\App\Livewire\Purchases\SmartReorderIndex::class)
            ->assertStatus(200)
            ->assertSee('مساعد المشتريات الذكي')
            ->assertSee('REORDER-01');
    }

    public function test_expense_index_saves_cost_center_and_filters_correctly(): void
    {
        Livewire::test(\App\Livewire\ExpenseIndex::class)
            ->set('title', 'فاتورة كهرباء ومياه شهرية')
            ->set('category', 'إيجار وكهرباء ومرافق')
            ->set('cost_center', 'utilities')
            ->set('amount', '450.000')
            ->set('expense_date', '2026-08-19')
            ->set('payment_method', 'cash')
            ->call('saveExpense')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('expenses', [
            'title'       => 'فاتورة كهرباء ومياه شهرية',
            'cost_center' => 'utilities',
            'amount'      => '450.000',
        ]);
    }
}
