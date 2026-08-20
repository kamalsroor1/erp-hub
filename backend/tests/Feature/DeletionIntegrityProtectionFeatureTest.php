<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Store;
use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Livewire\ItemIndex;
use App\Livewire\CustomerIndex;
use App\Livewire\SupplierIndex;
use App\Livewire\StoreIndex;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeletionIntegrityProtectionFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Permissions
        Permission::firstOrCreate(['name' => 'items.view']);
        Permission::firstOrCreate(['name' => 'items.edit']);
        Permission::firstOrCreate(['name' => 'items.delete']);
        Permission::firstOrCreate(['name' => 'customers.manage']);
        Permission::firstOrCreate(['name' => 'suppliers.manage']);
        Permission::firstOrCreate(['name' => 'stores.manage']);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo(Permission::all());

        $this->user = User::factory()->create();
        $this->user->assignRole($role);
        $this->actingAs($this->user);
    }

    public function test_item_with_remaining_stock_cannot_be_deleted(): void
    {
        $item = Item::create([
            'code'          => 'ITM-PROT-01',
            'name'          => 'بن هندي محوج',
            'current_stock' => '25.000',
            'cost_price'    => '100.000',
            'selling_price' => '150.000',
            'is_active'     => true,
        ]);

        $this->assertFalse($item->canBeDeleted());
        $this->assertNotEmpty($item->getDeletionBlockers());

        Livewire::test(ItemIndex::class)
            ->call('deleteItem', $item->id);

        // Must still exist in database and not be soft deleted
        $this->assertDatabaseHas('items', [
            'id'         => $item->id,
            'deleted_at' => null,
        ]);
    }

    public function test_item_with_invoices_cannot_be_deleted_even_if_zero_stock(): void
    {
        $item = Item::create([
            'code'          => 'ITM-PROT-02',
            'name'          => 'شاي أخضر صيني',
            'current_stock' => '0.000',
            'cost_price'    => '50.000',
            'selling_price' => '80.000',
            'is_active'     => true,
        ]);

        $customer = Customer::create(['name' => 'عميل محمي']);

        $invoice = Invoice::create([
            'invoice_number'   => 'INV-PROT-001',
            'invoice_date'     => now(),
            'customer_id'      => $customer->id,
            'user_id'          => $this->user->id,
            'status'           => 'confirmed',
            'subtotal'         => '80.000',
            'discount_amount'  => '0.000',
            'tax_amount'       => '0.000',
            'net_total'        => '80.000',
            'paid_amount'      => '80.000',
            'remaining_amount' => '0.000',
            'payment_type'     => 'cash',
        ]);

        $invoice->items()->create([
            'item_id'     => $item->id,
            'quantity'    => '1.000',
            'unit_price'  => '80.000',
            'total_price' => '80.000',
            'cost_price'  => '50.000',
        ]);

        $this->assertFalse($item->canBeDeleted());

        Livewire::test(ItemIndex::class)
            ->call('deleteItem', $item->id);

        $this->assertDatabaseHas('items', [
            'id'         => $item->id,
            'deleted_at' => null,
        ]);
    }

    public function test_fresh_item_without_any_history_can_be_safely_deleted(): void
    {
        $item = Item::create([
            'code'          => 'ITM-CLEAN',
            'name'          => 'صنف جديد بالخطأ لم يستخدم',
            'current_stock' => '0.000',
            'cost_price'    => '10.000',
            'selling_price' => '20.000',
            'is_active'     => true,
        ]);

        $this->assertTrue($item->canBeDeleted());

        Livewire::test(ItemIndex::class)
            ->call('deleteItem', $item->id);

        $this->assertSoftDeleted('items', ['id' => $item->id]);
    }

    public function test_customer_with_unsettled_balance_cannot_be_deleted(): void
    {
        $customer = Customer::create([
            'name'            => 'عميل مديونية',
            'current_balance' => '500.000',
        ]);

        $this->assertFalse($customer->canBeDeleted());

        Livewire::test(CustomerIndex::class)
            ->call('deleteCustomer', $customer->id);

        $this->assertDatabaseHas('customers', [
            'id'         => $customer->id,
            'deleted_at' => null,
        ]);
    }

    public function test_supplier_with_purchases_cannot_be_deleted(): void
    {
        $supplier = Supplier::create(['name' => 'مورد بن محمي']);
        $item = Item::create([
            'code'          => 'ITM-SUP-P',
            'name'          => 'بن مورد محمي',
            'current_stock' => '10.000',
            'cost_price'    => '100.000',
            'selling_price' => '150.000',
        ]);

        Purchase::create([
            'purchase_number'  => 'PUR-TEST-PROT',
            'purchase_date'    => now(),
            'supplier_id'      => $supplier->id,
            'user_id'          => $this->user->id,
            'status'           => 'confirmed',
            'subtotal'         => '1000.000',
            'net_total'        => '1000.000',
            'paid_amount'      => '1000.000',
            'remaining_amount' => '0.000',
        ]);

        $this->assertFalse($supplier->canBeDeleted());

        Livewire::test(SupplierIndex::class)
            ->call('deleteSupplier', $supplier->id);

        $this->assertDatabaseHas('suppliers', [
            'id'         => $supplier->id,
            'deleted_at' => null,
        ]);
    }
}
