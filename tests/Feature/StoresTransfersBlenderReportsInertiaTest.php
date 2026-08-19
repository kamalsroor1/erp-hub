<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Store;
use App\Models\StoreStock;
use App\Models\StockTransfer;
use App\Models\Invoice;
use App\Models\Expense;
use App\Services\StockTransferService;
use App\Services\InvoiceService;
use App\Services\ProfitService;
use App\Services\TreasuryService;
use App\Services\InventoryAnalyticsService;
use App\Services\ProfitLossService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class StoresTransfersBlenderReportsInertiaTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Store $mainStore;
    protected Store $branchStore;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'items.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'stores.manage', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'transfers.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'transfers.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'pos.access', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'reports.view', 'guard_name' => 'web']);

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::all());

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->mainStore = Store::create([
            'name'      => 'Ø§Ù„Ù…Ø®Ø²Ù† Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ',
            'code'      => 'MAIN-WH-01',
            'type'      => 'main_warehouse',
            'is_active' => true,
            'is_main'   => true,
        ]);

        $this->branchStore = Store::create([
            'name'      => 'ÙØ±Ø¹ Ù…Ø¯ÙŠÙ†Ø© Ù†ØµØ±',
            'code'      => 'NASR-01',
            'type'      => 'retail_shop',
            'is_active' => true,
            'is_main'   => false,
        ]);
    }

    protected function makeRequest(string $uri, string $method = 'GET', array $parameters = []): Request
    {
        $req = Request::create($uri, $method, $parameters);
        $req->setLaravelSession(app('session')->driver());
        $req->setUserResolver(fn() => $this->admin);
        return $req;
    }

    public function test_store_stocks_matrix_renders_with_valuation(): void
    {
        $this->actingAs($this->admin);

        $item = Item::create([
            'name'            => 'Ø¨Ù† ÙƒÙˆÙ„ÙˆÙ…Ø¨ÙŠ Ø³ÙˆØ¨Ø±ÙŠÙ…Ùˆ',
            'code'            => 'COL-SUP-01',
            'unit'            => 'ÙƒØ¬Ù…',
            'cost_price'      => '120.000',
            'selling_price'   => '180.000',
            'current_stock'   => '50.000',
            'min_stock_level' => '10.000',
            'is_active'       => true,
        ]);

        StoreStock::create([
            'store_id'  => $this->mainStore->id,
            'item_id'   => $item->id,
            'quantity'  => '50.000',
            'min_stock' => '10.000',
        ]);

        $controller = new \App\Http\Controllers\StoreController();
        $response = $controller->stocks($this->makeRequest('/store-stocks', 'GET', [
            'store_id' => $this->mainStore->id,
        ]));

        $page = $response->toResponse(request())->getOriginalContent()->getData()['page'];
        $this->assertEquals('Stores/Stocks', $page['component']);
        $this->assertCount(1, $page['props']['stocks']['data']);
        $this->assertEquals(6000.0, $page['props']['stocks']['data'][0]['total_valuation']); // 50 * 120 = 6000
    }

    public function test_stock_transfer_creation_and_instant_movement(): void
    {
        $this->actingAs($this->admin);

        $item = Item::create([
            'name'            => 'Ø¨Ù† Ø¨Ø±Ø§Ø²ÙŠÙ„ÙŠ Ø³Ø§Ù†ØªÙˆØ³',
            'code'            => 'BRZ-SAN-01',
            'unit'            => 'ÙƒØ¬Ù…',
            'cost_price'      => '100.000',
            'selling_price'   => '150.000',
            'current_stock'   => '100.000',
            'min_stock_level' => '10.000',
            'is_active'       => true,
        ]);

        StoreStock::create([
            'store_id'  => $this->mainStore->id,
            'item_id'   => $item->id,
            'quantity'  => '100.000',
            'min_stock' => '10.000',
        ]);

        $controller = new \App\Http\Controllers\StockTransferController();

        // 1. Create Transfer
        $storeReq = $this->makeRequest('/stock-transfers', 'POST', [
            'from_store_id' => $this->mainStore->id,
            'to_store_id'   => $this->branchStore->id,
            'transfer_date' => now()->toDateString(),
            'notes'         => 'Ø´Ø­Ù†Ø© Ø¨Ø¶Ø§Ø¹Ø© Ù„Ù„ÙØ±Ø¹',
            'items'         => [
                [
                    'item_id'  => $item->id,
                    'quantity' => '30.000',
                ],
            ],
        ]);

        $controller->store($storeReq, app(StockTransferService::class));

        $mainStock = StoreStock::where('store_id', $this->mainStore->id)->where('item_id', $item->id)->firstOrFail();
        $branchStock = StoreStock::where('store_id', $this->branchStore->id)->where('item_id', $item->id)->firstOrFail();

        $this->assertEquals('70.000', $mainStock->quantity); // 100 - 30 = 70
        $this->assertEquals('30.000', $branchStock->quantity); // 0 + 30 = 30

        // 2. Index
        $indexResponse = $controller->index($this->makeRequest('/stock-transfers'));
        $page = $indexResponse->toResponse(request())->getOriginalContent()->getData()['page'];
        $this->assertEquals('StockTransfers/Index', $page['component']);
        $this->assertCount(1, $page['props']['transfers']['data']);
    }

    public function test_coffee_blender_invoice_creation(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create([
            'name'            => 'Ø¹Ù…ÙŠÙ„ ØªÙˆÙ„ÙŠÙØ© Ø®Ø§ØµØ©',
            'current_balance' => '0.000',
            'is_active'       => true,
        ]);

        $item1 = Item::create([
            'name'          => 'Ø¨Ù† Ø¨Ø±Ø§Ø²ÙŠÙ„ÙŠ Ù…Ø­Ù…Øµ',
            'code'          => 'COF-001',
            'unit'          => 'ÙƒØ¬Ù…',
            'cost_price'    => '100.000',
            'selling_price' => '150.000',
            'current_stock' => '20.000',
            'is_active'     => true,
        ]);

        $item2 = Item::create([
            'name'          => 'Ø¨Ù† ÙƒÙˆÙ„ÙˆÙ…Ø¨ÙŠ Ù…Ø­Ù…Øµ',
            'code'          => 'COF-002',
            'unit'          => 'ÙƒØ¬Ù…',
            'cost_price'    => '140.000',
            'selling_price' => '200.000',
            'current_stock' => '20.000',
            'is_active'     => true,
        ]);

        $controller = new \App\Http\Controllers\CoffeeBlenderController();

        $blendReq = $this->makeRequest('/coffee-blender/create-invoice', 'POST', [
            'blend_name'  => 'Ø®Ù„Ø·Ø© Ø³Ø±ÙˆØ± Ø§Ù„Ù…Ù…ÙŠØ²Ø© 250Ø¬Ù…',
            'customer_id' => $customer->id,
            'components'  => [
                [
                    'item_id'    => $item1->id,
                    'grams'      => 150, // 0.150 kg * 150 = 22.5
                    'unit_price' => '150.000',
                ],
                [
                    'item_id'    => $item2->id,
                    'grams'      => 100, // 0.100 kg * 200 = 20.0
                    'unit_price' => '200.000',
                ],
            ],
        ]);

        $controller->createInvoice($blendReq, app(InvoiceService::class));

        $invoice = Invoice::where('customer_id', $customer->id)->firstOrFail();
        $this->assertEquals('42.500', $invoice->net_total); // 22.5 + 20.0 = 42.5
    }

    public function test_financial_reports_computes_cogs_and_profits(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create([
            'name'            => 'Ø¹Ù…ÙŠÙ„ Ø§Ù„ØªÙ‚Ø§Ø±ÙŠØ±',
            'current_balance' => '0.000',
            'is_active'       => true,
        ]);

        $item = Item::create([
            'name'          => 'Ø¨Ù† ÙŠÙ…Ù†ÙŠ Ù…Ù…ØªØ§Ø²',
            'code'          => 'YEM-01',
            'unit'          => 'ÙƒØ¬Ù…',
            'cost_price'    => '100.000',
            'selling_price' => '180.000',
            'current_stock' => '20.000',
            'is_active'     => true,
        ]);

        // Invoice with total sales = 360, COGS = 200, Gross Profit = 160
        $invoice = Invoice::create([
            'invoice_number'   => 'INV-REP-001',
            'customer_id'      => $customer->id,
            'store_id'         => $this->mainStore->id,
            'user_id'          => $this->admin->id,
            'invoice_date'     => now()->toDateString(),
            'subtotal'         => '360.000',
            'net_total'        => '360.000',
            'paid_amount'      => '360.000',
            'remaining_amount' => '0.000',
            'total_cost'       => '200.000',
            'status'           => 'confirmed',
            'payment_method'   => 'cash',
        ]);

        $invoice->items()->create([
            'item_id'     => $item->id,
            'quantity'    => '2.000',
            'unit_price'  => '180.000',
            'cost_price'  => '100.000',
            'total_price' => '360.000',
        ]);

        // Expense = 60
        Expense::create([
            'expense_number' => 'EXP-REP-001',
            'title'          => 'ÙØ§ØªÙˆØ±Ø© ÙƒÙ‡Ø±Ø¨Ø§Ø¡',
            'amount'         => '60.000',
            'category'       => 'ÙƒÙ‡Ø±Ø¨Ø§Ø¡ ÙˆÙ…Ø±Ø§ÙÙ‚',
            'cost_center'    => 'utilities',
            'expense_date'   => now()->toDateString(),
            'payment_method' => 'cash',
            'user_id'        => $this->admin->id,
            'store_id'       => $this->mainStore->id,
        ]);

        $controller = new \App\Http\Controllers\ReportController();
        $response = $controller->index(
            $this->makeRequest('/reports', 'GET', ['period' => 'today']),
            app(ProfitService::class),
            app(TreasuryService::class),
            app(InventoryAnalyticsService::class),
            app(ProfitLossService::class)
        );

        $page = $response->toResponse(request())->getOriginalContent()->getData()['page'];
        $this->assertEquals('Reports/Index', $page['component']);
        $this->assertEquals(360.0, $page['props']['summary']['total_sales']);
        $this->assertEquals(200.0, $page['props']['summary']['total_cogs']);
        $this->assertEquals(160.0, $page['props']['summary']['gross_profit']);
        $this->assertEquals(60.0, $page['props']['summary']['total_expenses']);
        $this->assertEquals(100.0, $page['props']['summary']['net_profit']); // 160 - 60 = 100
    }
}
