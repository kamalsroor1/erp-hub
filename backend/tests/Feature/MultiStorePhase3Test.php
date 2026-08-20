<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Item;
use App\Models\Customer;
use App\Models\StoreStock;
use App\Models\StockTransfer;
use App\Services\StockService;
use App\Livewire\StoreIndex;
use App\Livewire\StoreStockIndex;
use App\Livewire\StockTransferCreate;
use App\Livewire\StockTransferIndex;
use App\Livewire\InvoiceCreate;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

class MultiStorePhase3Test extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Store $mainStore;
    protected Store $vanStore;
    protected Item $item;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);

        $this->admin = User::factory()->create([
            'email' => 'admin@sroor.test',
            'phone' => '01000000001',
        ]);
        $this->admin->assignRole('admin');

        $this->mainStore = Store::create([
            'name'      => 'المخزن الرئيسي',
            'code'      => 'MAIN-WH',
            'type'      => 'main_warehouse',
            'is_active' => true,
            'is_main'   => true,
        ]);

        $this->vanStore = Store::create([
            'name'      => 'عربية توزيع رقم 1',
            'code'      => 'VAN-01',
            'type'      => 'wholesale_van',
            'is_active' => true,
            'is_main'   => false,
        ]);

        $this->admin->stores()->sync([$this->mainStore->id, $this->vanStore->id]);
        $this->admin->update(['default_store_id' => $this->mainStore->id]);

        $this->item = Item::create([
            'name'             => 'بن برازيلي فاخر',
            'code'             => 'COF-BRZ',
            'category'         => 'بن وتوليفات',
            'unit'             => 'كجم',
            'cost_price'       => '150.000',
            'selling_price'    => '220.000',
            'min_stock_level'  => '10.000',
            'current_stock'    => '100.000',
            'is_active'        => true,
        ]);

        StoreStock::create([
            'store_id'             => $this->mainStore->id,
            'item_id'              => $this->item->id,
            'quantity'             => '80.000',
            'min_stock'            => '10.000',
            'custom_selling_price' => null,
        ]);

        StoreStock::create([
            'store_id'             => $this->vanStore->id,
            'item_id'              => $this->item->id,
            'quantity'             => '20.000',
            'min_stock'            => '5.000',
            'custom_selling_price' => '200.000', // Wholesale price for the van
        ]);

        $this->customer = Customer::create([
            'name'            => 'عميل جملة تجريبي',
            'phone'           => '01011112222',
            'current_balance' => '0.000',
            'is_active'       => true,
        ]);
    }

    public function test_store_index_livewire_can_create_and_switch_stores(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(StoreIndex::class)
            ->call('openCreateModal')
            ->set('name', 'فرع المعادي')
            ->set('code', 'SHOP-MAADI')
            ->set('type', 'retail_shop')
            ->set('phone', '01099998888')
            ->set('is_active', true)
            ->call('saveStore')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stores', [
            'name' => 'فرع المعادي',
            'code' => 'SHOP-MAADI',
            'type' => 'retail_shop',
        ]);

        $newStore = Store::where('code', 'SHOP-MAADI')->first();

        // Switch to the new store
        Livewire::test(StoreIndex::class)
            ->call('switchToStore', $newStore->id);

        $this->assertEquals($newStore->id, session('current_store_id'));
    }

    public function test_store_stock_index_can_update_custom_selling_price_and_min_stock(): void
    {
        $this->actingAs($this->admin);

        $vanStock = StoreStock::where('store_id', $this->vanStore->id)
            ->where('item_id', $this->item->id)
            ->first();

        Livewire::test(StoreStockIndex::class, ['store_id' => $this->vanStore->id])
            ->call('openEditModal', $vanStock->id)
            ->set('editingCustomPrice', '195.000')
            ->set('editingMinStock', '8.000')
            ->call('saveStockSettings')
            ->assertHasNoErrors();

        $vanStock->refresh();
        $this->assertEquals('195.000', $vanStock->custom_selling_price);
        $this->assertEquals('8.000', $vanStock->min_stock);
    }

    public function test_stock_transfer_create_and_cancel_flow(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(StockTransferCreate::class)
            ->set('from_store_id', $this->mainStore->id)
            ->set('to_store_id', $this->vanStore->id)
            ->set('transfer_date', now()->toDateString())
            ->call('addItem', $this->item->id, '15.000')
            ->call('saveTransfer')
            ->assertHasNoErrors()
            ->assertRedirect(route('stock-transfers'));

        // Check stocks updated: Main -15 (80-15=65), Van +15 (20+15=35)
        $mainStock = StoreStock::where('store_id', $this->mainStore->id)->where('item_id', $this->item->id)->first();
        $vanStock  = StoreStock::where('store_id', $this->vanStore->id)->where('item_id', $this->item->id)->first();

        $this->assertEquals('65.000', $mainStock->quantity);
        $this->assertEquals('35.000', $vanStock->quantity);

        $transfer = StockTransfer::latest('id')->first();
        $this->assertEquals('confirmed', $transfer->status);

        // Cancel the transfer
        Livewire::test(StockTransferIndex::class)
            ->call('confirmCancel', $transfer->id)
            ->set('cancelReason', 'خطأ في التحميل')
            ->call('executeCancel')
            ->assertHasNoErrors();

        $transfer->refresh();
        $this->assertEquals('cancelled', $transfer->status);

        // Verify stock reverted: Main back to 80, Van back to 20
        $mainStock->refresh();
        $vanStock->refresh();
        $this->assertEquals('80.000', $mainStock->quantity);
        $this->assertEquals('20.000', $vanStock->quantity);
    }

    public function test_invoice_create_uses_active_store_and_customer_pricing_recall(): void
    {
        $this->actingAs($this->admin);
        session(['current_store_id' => $this->vanStore->id]);

        // 1. First invoice sold at custom price 190.000
        Livewire::test(InvoiceCreate::class)
            ->set('store_id', $this->vanStore->id)
            ->set('customer_id', $this->customer->id)
            ->call('addItem', $this->item->id, '2.000')
            ->set('items.0.unit_price', '190.000')
            ->call('saveInvoice')
            ->assertHasNoErrors();

        // 2. Second invoice for same customer & item: verify last customer price is detected and applied
        $testComponent = Livewire::test(InvoiceCreate::class)
            ->set('store_id', $this->vanStore->id)
            ->set('customer_id', $this->customer->id)
            ->call('addItem', $this->item->id, '1.000');

        // Check that last_customer_price is set to 190.000
        $items = $testComponent->get('items');
        $this->assertNotEmpty($items[0]['last_customer_price']);
        $this->assertEquals('190.000', $items[0]['last_customer_price']['unit_price']);

        // Apply customer last price
        $testComponent->call('applyCustomerLastPrice', 0);
        $items = $testComponent->get('items');
        $this->assertEquals('190.000', $items[0]['unit_price']);

        // Save invoice and check van stock deduction
        $testComponent->call('saveInvoice')->assertHasNoErrors();

        $vanStock = StoreStock::where('store_id', $this->vanStore->id)->where('item_id', $this->item->id)->first();
        // Started with 20.000 - 2.000 (inv 1) - 1.000 (inv 2) = 17.000
        $this->assertEquals('17.000', $vanStock->quantity);
    }
}
