<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Store;
use App\Models\StockMovement;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\CustomerBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CustomerAndItemMovementInertiaTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'customers.manage', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'customers.statement', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'items.view', 'guard_name' => 'web']);

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::all());

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->store = Store::create([
            'name'      => 'المخزن الرئيسي',
            'code'      => 'MAIN-01',
            'type'      => 'main',
            'is_active' => true,
            'is_main'   => true,
        ]);
    }

    public function test_customer_index_renders_with_metrics_and_filters(): void
    {
        $this->actingAs($this->admin);

        Customer::create([
            'name'            => 'عميل مدين',
            'phone'           => '01011111111',
            'current_balance' => '750.000',
            'is_active'       => true,
        ]);

        Customer::create([
            'name'            => 'عميل خالص',
            'phone'           => '01022222222',
            'current_balance' => '0.000',
            'is_active'       => true,
        ]);

        $controller = new \App\Http\Controllers\CustomerController();
        $response = $controller->index(new \Illuminate\Http\Request());

        $page = $response->toResponse(request())->getOriginalContent()->getData()['page'];
        $this->assertEquals('Customers/Index', $page['component']);
        $this->assertEquals(750.0, $page['props']['metrics']['total_debt']);
        $this->assertEquals(1, $page['props']['metrics']['debtors_count']);
        $this->assertEquals(2, $page['props']['metrics']['total_customers']);
    }

    public function test_can_create_customer_via_controller(): void
    {
        $this->actingAs($this->admin);

        $controller = new \App\Http\Controllers\CustomerController();
        $request = new \Illuminate\Http\Request();
        $request->replace([
            'name'            => 'كافيه السلام',
            'phone'           => '01234567890',
            'address'         => 'القاهرة',
            'opening_balance' => '250.000',
            'notes'           => 'عميل جملة أسبوعي',
        ]);

        $controller->store($request);

        $this->assertDatabaseHas('customers', [
            'name'            => 'كافيه السلام',
            'current_balance' => '250.000',
        ]);
    }

    public function test_can_collect_customer_payment_and_update_balance(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create([
            'name'            => 'عميل للدفع',
            'phone'           => '01111111111',
            'current_balance' => '0.000',
            'is_active'       => true,
        ]);

        Invoice::create([
            'invoice_number'   => 'INV-TST-PAY',
            'customer_id'      => $customer->id,
            'store_id'         => $this->store->id,
            'user_id'          => $this->admin->id,
            'invoice_date'     => now()->toDateString(),
            'payment_type'     => 'credit',
            'subtotal'         => '500.000',
            'total_amount'     => '500.000',
            'net_total'        => '500.000',
            'paid_amount'      => '0.000',
            'remaining_amount' => '500.000',
            'status'           => 'confirmed',
        ]);

        $controller = new \App\Http\Controllers\CustomerController();
        $request = new \Illuminate\Http\Request();
        $request->replace([
            'amount'         => '200.000',
            'payment_method' => 'instapay',
            'payment_date'   => now()->toDateString(),
            'notes'          => 'سداد جزء من الحساب إنستاباي',
        ]);

        $controller->collectPayment($request, $customer->id, app(PaymentService::class));

        $customer->refresh();
        $this->assertEquals('300.000', $customer->current_balance);
        $this->assertDatabaseHas('payments', [
            'customer_id'    => $customer->id,
            'amount'         => '200.000',
            'payment_method' => 'instapay',
        ]);
    }

    public function test_can_toggle_customer_active_status(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create([
            'name'            => 'عميل نشط',
            'current_balance' => '0.000',
            'is_active'       => true,
        ]);

        $controller = new \App\Http\Controllers\CustomerController();
        $controller->toggleActive($customer->id);

        $customer->refresh();
        $this->assertFalse((bool)$customer->is_active);

        $controller->toggleActive($customer->id);
        $customer->refresh();
        $this->assertTrue((bool)$customer->is_active);
    }

    public function test_customer_statement_returns_chronological_ledger_and_summary(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create([
            'name'            => 'عميل كشف الحساب',
            'current_balance' => '0.000',
            'is_active'       => true,
        ]);

        // Create Invoice
        $invoice = Invoice::create([
            'invoice_number'   => 'INV-TST-001',
            'customer_id'      => $customer->id,
            'store_id'         => $this->store->id,
            'user_id'          => $this->admin->id,
            'invoice_date'     => now()->subDays(2)->toDateString(),
            'payment_type'     => 'credit',
            'subtotal'         => '1000.000',
            'total_amount'     => '1000.000',
            'net_total'        => '1000.000',
            'paid_amount'      => '0.000',
            'remaining_amount' => '1000.000',
            'status'           => 'confirmed',
        ]);

        // Create Payment
        Payment::create([
            'payment_number' => 'PAY-TST-001',
            'customer_id'    => $customer->id,
            'user_id'        => $this->admin->id,
            'amount'         => '400.000',
            'payment_date'   => now()->subDay()->toDateString(),
            'payment_method' => 'cash',
            'notes'          => 'دفعة كاش',
        ]);

        // Recalculate customer balance
        app(CustomerBalanceService::class)->updateBalance($customer->id);

        $controller = new \App\Http\Controllers\CustomerController();
        $response = $controller->statement(new \Illuminate\Http\Request(), $customer->id, app(CustomerBalanceService::class));

        $page = $response->toResponse(request())->getOriginalContent()->getData()['page'];
        $this->assertEquals('Customers/Statement', $page['component']);
        $this->assertEquals($customer->id, $page['props']['customer']['id']);
        $this->assertCount(2, $page['props']['ledger']);
        $this->assertEquals(1000.0, $page['props']['summary']['total_debit']);
        $this->assertEquals(400.0, $page['props']['summary']['total_credit']);
        $this->assertEquals(600.0, $page['props']['summary']['current_balance']);
    }

    public function test_item_movements_returns_aggregates_and_list(): void
    {
        $this->actingAs($this->admin);

        $item = Item::create([
            'name'          => 'بن يمني ممتاز',
            'code'          => 'YEM-01',
            'unit'          => 'كجم',
            'cost_price'    => '200.000',
            'selling_price' => '300.000',
            'current_stock' => '25.000',
            'is_active'     => true,
        ]);

        // In movement (Purchase)
        StockMovement::create([
            'item_id'       => $item->id,
            'store_id'      => $this->store->id,
            'user_id'       => $this->admin->id,
            'movement_type' => 'purchase_in',
            'quantity'      => '30.000',
            'stock_before'  => '0.000',
            'stock_after'   => '30.000',
            'unit_cost'     => '200.000',
            'source_type'   => 'purchase',
            'source_id'     => 1,
            'created_at'    => now(),
        ]);

        // Out movement (Sale)
        StockMovement::create([
            'item_id'       => $item->id,
            'store_id'      => $this->store->id,
            'user_id'       => $this->admin->id,
            'movement_type' => 'sales_out',
            'quantity'      => '5.000',
            'stock_before'  => '30.000',
            'stock_after'   => '25.000',
            'unit_cost'     => '200.000',
            'source_type'   => 'invoice',
            'source_id'     => 1,
            'created_at'    => now(),
        ]);

        $controller = new \App\Http\Controllers\ItemController();
        $response = $controller->movements($item->id, new \Illuminate\Http\Request());

        $page = $response->toResponse(request())->getOriginalContent()->getData()['page'];
        $this->assertEquals('Items/Movements', $page['component']);
        $this->assertEquals($item->id, $page['props']['item']['id']);
        $this->assertEquals(30.0, $page['props']['stats']['total_in']);
        $this->assertEquals(5.0, $page['props']['stats']['total_out']);
        $this->assertEquals(25.0, $page['props']['stats']['net_movement']);
        $this->assertEquals(25.0, $page['props']['stats']['current_scope_stock']);
    }
}
