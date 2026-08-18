<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Store;
use App\Models\Purchase;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\AdditionalExpense;
use App\Services\PurchaseService;
use App\Services\InvoiceService;
use App\Livewire\PurchaseCreate;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LandedCostAndAdditionalExpensesFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Store $store;
    protected Supplier $supplier;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'purchases.view']);
        Permission::firstOrCreate(['name' => 'purchases.create']);
        Permission::firstOrCreate(['name' => 'purchases.delete']);
        Permission::firstOrCreate(['name' => 'invoices.view']);
        Permission::firstOrCreate(['name' => 'invoices.create']);
        Permission::firstOrCreate(['name' => 'invoices.discount']);
        Permission::firstOrCreate(['name' => 'pos.access']);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo(Permission::all());

        $this->user = User::factory()->create();
        $this->user->assignRole($role);
        $this->actingAs($this->user);

        $this->store = Store::create([
            'name'       => 'المخزن الرئيسي',
            'code'       => 'MAIN-01',
            'type'       => 'main',
            'is_active'  => true,
            'is_main'    => true,
        ]);

        $this->supplier = Supplier::create([
            'name'             => 'شركة بن البرازيل العالمية',
            'phone'            => '01011112222',
            'current_balance'  => '0.000',
            'is_active'        => true,
        ]);

        $this->customer = Customer::create([
            'name'            => 'كافيه الأهرام',
            'phone'           => '01233334444',
            'current_balance' => '0.000',
            'is_active'       => true,
        ]);
    }

    public function test_purchase_with_multi_expenses_by_quantity_allocates_landed_costs_correctly(): void
    {
        $itemA = Item::create([
            'name'              => 'بن برازيلي كولومبي',
            'code'              => 'COF-BRA-01',
            'unit'              => 'كجم',
            'cost_price'        => '50.000',
            'sale_price'        => '80.000',
            'current_stock'     => '0.000',
            'weighted_avg_cost' => '0.000',
            'is_active'         => true,
        ]);

        $itemB = Item::create([
            'name'              => 'بن يمني مطري',
            'code'              => 'COF-YEM-01',
            'unit'              => 'كجم',
            'cost_price'        => '100.000',
            'sale_price'        => '160.000',
            'current_stock'     => '0.000',
            'weighted_avg_cost' => '0.000',
            'is_active'         => true,
        ]);

        /** @var PurchaseService $purchaseService */
        $purchaseService = app(PurchaseService::class);

        $purchase = $purchaseService->createPurchase([
            'supplier_id'   => $this->supplier->id,
            'store_id'      => $this->store->id,
            'purchase_date' => '2026-08-18',
            'items'         => [
                [
                    'item_id'    => $itemA->id,
                    'quantity'   => '100.000',
                    'cost_price' => '50.000',
                ],
                [
                    'item_id'    => $itemB->id,
                    'quantity'   => '100.000',
                    'cost_price' => '100.000',
                ],
            ],
            'additional_expenses' => [
                [
                    'title'             => 'شحن ونقل بضاعة',
                    'amount'            => '500.000',
                    'allocation_method' => 'by_quantity', // 500 / 200kg = +2.500 LE/kg
                    'paid_by'           => 'treasury_cash',
                ],
                [
                    'title'             => 'عتالة وتنزيل',
                    'amount'            => '100.000',
                    'allocation_method' => 'by_quantity', // 100 / 200kg = +0.500 LE/kg
                    'paid_by'           => 'supplier_account',
                ],
            ],
        ]);

        $purchase->refresh();
        $this->assertEquals('600.000', $purchase->additional_expenses_total);

        // Subtotal = (100*50) + (100*100) = 15000.000
        $this->assertEquals('15000.000', $purchase->subtotal);

        // Supplier-charged expense = 100.000 => Net total = 15000 + 100 = 15100.000
        $this->assertEquals('15100.000', $purchase->net_total);

        // Total expense allocated per kg = (500 + 100) / 200 = 3.000 LE/kg
        // Item A Landed cost = 50 + 3 = 53.000
        // Item B Landed cost = 100 + 3 = 103.000
        $itemLineA = $purchase->items()->where('item_id', $itemA->id)->first();
        $itemLineB = $purchase->items()->where('item_id', $itemB->id)->first();

        $this->assertEquals('50.000', $itemLineA->base_cost_price);
        $this->assertEquals('300.000', $itemLineA->allocated_expense);
        $this->assertEquals('53.000', $itemLineA->cost_price);

        $this->assertEquals('100.000', $itemLineB->base_cost_price);
        $this->assertEquals('300.000', $itemLineB->allocated_expense);
        $this->assertEquals('103.000', $itemLineB->cost_price);

        // Verify updated Item WAC & Cost price in database
        $itemA->refresh();
        $itemB->refresh();
        $this->assertEquals('53.000', $itemA->cost_price);
        $this->assertEquals('53.000', $itemA->weighted_avg_cost);
        $this->assertEquals('103.000', $itemB->cost_price);
        $this->assertEquals('103.000', $itemB->weighted_avg_cost);
    }

    public function test_purchase_with_multi_expenses_by_value_allocates_landed_costs_correctly(): void
    {
        $itemA = Item::create([
            'name'              => 'أكياس تعبئة وتغليف',
            'code'              => 'PKG-01',
            'unit'              => 'قطعة',
            'cost_price'        => '10.000',
            'sale_price'        => '15.000',
            'current_stock'     => '0.000',
            'weighted_avg_cost' => '0.000',
            'is_active'         => true,
        ]);

        $itemB = Item::create([
            'name'              => 'ماكينة تحميص اسبريسو',
            'code'              => 'EQ-01',
            'unit'              => 'قطعة',
            'cost_price'        => '900.000',
            'sale_price'        => '1200.000',
            'current_stock'     => '0.000',
            'weighted_avg_cost' => '0.000',
            'is_active'         => true,
        ]);

        /** @var PurchaseService $purchaseService */
        $purchaseService = app(PurchaseService::class);

        // Item A: 10 pcs * 10 LE = 100 LE (10% of total 1,000 LE base value)
        // Item B: 1 pc * 900 LE = 900 LE (90% of total 1,000 LE base value)
        // Total base = 1000 LE
        // Expense: 200 LE allocated by value -> A gets 20 LE (+2 LE/pc), B gets 180 LE (+180 LE/pc)
        $purchase = $purchaseService->createPurchase([
            'supplier_id'   => $this->supplier->id,
            'store_id'      => $this->store->id,
            'purchase_date' => '2026-08-18',
            'items'         => [
                [
                    'item_id'    => $itemA->id,
                    'quantity'   => '10.000',
                    'cost_price' => '10.000',
                ],
                [
                    'item_id'    => $itemB->id,
                    'quantity'   => '1.000',
                    'cost_price' => '900.000',
                ],
            ],
            'additional_expenses' => [
                [
                    'title'             => 'جمارك ونولون موانئ',
                    'amount'            => '200.000',
                    'allocation_method' => 'by_value',
                    'paid_by'           => 'treasury_cash',
                ],
            ],
        ]);

        $itemLineA = $purchase->items()->where('item_id', $itemA->id)->first();
        $itemLineB = $purchase->items()->where('item_id', $itemB->id)->first();

        $this->assertEquals('20.000', $itemLineA->allocated_expense);
        $this->assertEquals('12.000', $itemLineA->cost_price); // 10 + (20/10) = 12.000

        $this->assertEquals('180.000', $itemLineB->allocated_expense);
        $this->assertEquals('1080.000', $itemLineB->cost_price); // 900 + 180 = 1080.000
    }

    public function test_treasury_paid_expense_creates_payment_voucher_correctly(): void
    {
        $item = Item::create([
            'name'              => 'بن حبوب كولومبي',
            'code'              => 'COF-01',
            'unit'              => 'كجم',
            'cost_price'        => '100.000',
            'sale_price'        => '150.000',
            'current_stock'     => '0.000',
            'weighted_avg_cost' => '0.000',
            'is_active'         => true,
        ]);

        /** @var PurchaseService $purchaseService */
        $purchaseService = app(PurchaseService::class);

        $purchase = $purchaseService->createPurchase([
            'supplier_id'   => $this->supplier->id,
            'store_id'      => $this->store->id,
            'purchase_date' => '2026-08-18',
            'items'         => [
                [
                    'item_id'    => $item->id,
                    'quantity'   => '10.000',
                    'cost_price' => '100.000',
                ],
            ],
            'additional_expenses' => [
                [
                    'title'             => 'مصاريف شحن وتوصيل',
                    'amount'            => '150.000',
                    'allocation_method' => 'by_quantity',
                    'paid_by'           => 'treasury_cash',
                ],
            ],
        ]);

        $expense = $purchase->additionalExpenses()->first();
        $this->assertNotNull($expense);
        $this->assertNotNull($expense->payment_id);

        $payment = Payment::find($expense->payment_id);
        $this->assertNotNull($payment);
        $this->assertEquals('150.000', $payment->amount);
        $this->assertEquals('cash', $payment->payment_method);
        $this->assertEquals($purchase->id, $payment->purchase_id);
    }

    public function test_purchase_create_livewire_component_saves_expenses_and_landed_costs(): void
    {
        $item = Item::create([
            'name'              => 'بن برازيلي سانتوس',
            'code'              => 'COF-SAN-01',
            'unit'              => 'كجم',
            'cost_price'        => '80.000',
            'sale_price'        => '120.000',
            'current_stock'     => '0.000',
            'weighted_avg_cost' => '0.000',
            'is_active'         => true,
        ]);

        Livewire::test(PurchaseCreate::class)
            ->set('supplier_id', $this->supplier->id)
            ->set('store_id', $this->store->id)
            ->call('addItem', $item->id, '50.000')
            ->call('addExpenseRow', 'نولون وشحن', 'by_quantity', 'supplier_account')
            ->set('additional_expenses.0.amount', '250.000')
            ->call('savePurchase')
            ->assertRedirect(route('purchases.index'))
            ->assertSessionHas('success');

        $purchase = Purchase::latest()->first();
        $this->assertNotNull($purchase);
        $this->assertEquals('250.000', $purchase->additional_expenses_total);

        // Base: 50 * 80 = 4000
        // Expense: 250 => Net total = 4250.000
        $this->assertEquals('4250.000', $purchase->net_total);

        // Landed cost = 80 + (250/50) = 85.000
        $item->refresh();
        $this->assertEquals('85.000', $item->cost_price);
        $this->assertEquals('85.000', $item->weighted_avg_cost);
    }

    public function test_sales_invoice_with_shipping_cost_calculates_net_total_correctly(): void
    {
        $item = Item::create([
            'name'              => 'بن اسبريسو فاخر',
            'code'              => 'COF-ESP-01',
            'unit'              => 'كجم',
            'cost_price'        => '60.000',
            'sale_price'        => '100.000',
            'current_stock'     => '50.000',
            'weighted_avg_cost' => '60.000',
            'is_active'         => true,
        ]);

        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class);

        // Sale: 2 kg @ 100 LE = 200 LE
        // Shipping cost: 40 LE
        // Discount: 10 LE
        // Net total = (200 - 10) + 40 = 230.000 LE
        $invoice = $invoiceService->confirmInvoice([
            'customer_id'    => $this->customer->id,
            'store_id'       => $this->store->id,
            'invoice_date'   => '2026-08-18',
            'payment_type'   => 'cash',
            'payment_method' => 'cash',
            'discount_type'  => 'fixed',
            'discount_value' => '10.000',
            'shipping_cost'  => '40.000',
            'items'          => [
                [
                    'item_id'    => $item->id,
                    'quantity'   => '2.000',
                    'unit_price' => '100.000',
                ],
            ],
        ]);

        $this->assertEquals('200.000', $invoice->subtotal);
        $this->assertEquals('10.000', $invoice->discount_amount);
        $this->assertEquals('40.000', $invoice->shipping_cost);
        $this->assertEquals('230.000', $invoice->net_total);
        $this->assertEquals('230.000', $invoice->paid_amount);
    }

    public function test_sales_invoice_with_multiple_dynamic_expenses_creates_records_and_calculates_correctly(): void
    {
        $item = Item::create([
            'name'              => 'بن كولومبي سوبريمو',
            'code'              => 'COF-COL-01',
            'unit'              => 'كجم',
            'cost_price'        => '70.000',
            'sale_price'        => '120.000',
            'current_stock'     => '100.000',
            'weighted_avg_cost' => '70.000',
            'is_active'         => true,
        ]);

        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class);

        // Sale: 5 kg @ 120 = 600 LE
        // Expenses:
        // 1. شحن وتوصيل للمنزل = 50 LE (charged to customer)
        // 2. كيس هدايا وتغليف فاخر = 20 LE (charged to customer)
        // 3. إكرامية طيار الدليفري = 15 LE (paid from treasury_cash)
        // Net total to customer = 600 + 50 + 20 = 670.000 LE
        $invoice = $invoiceService->confirmInvoice([
            'customer_id'    => $this->customer->id,
            'store_id'       => $this->store->id,
            'invoice_date'   => '2026-08-18',
            'payment_type'   => 'cash',
            'payment_method' => 'cash',
            'items'          => [
                [
                    'item_id'    => $item->id,
                    'quantity'   => '5.000',
                    'unit_price' => '120.000',
                ],
            ],
            'additional_expenses' => [
                [
                    'title'             => 'شحن وتوصيل للمنزل',
                    'amount'            => '50.000',
                    'allocation_method' => 'by_quantity',
                    'paid_by'           => 'customer_account',
                ],
                [
                    'title'             => 'تغليف هدايا فاخر',
                    'amount'            => '20.000',
                    'allocation_method' => 'by_value',
                    'paid_by'           => 'customer_account',
                ],
                [
                    'title'             => 'إكرامية طيار الدليفري',
                    'amount'            => '15.000',
                    'allocation_method' => 'equal',
                    'paid_by'           => 'treasury_cash',
                ],
            ],
        ]);

        $this->assertEquals('600.000', $invoice->subtotal);
        $this->assertEquals('70.000', $invoice->shipping_cost); // Total customer-charged expenses
        $this->assertEquals('670.000', $invoice->net_total);
        $this->assertCount(3, $invoice->additionalExpenses);

        // Verify treasury expense payment voucher for the 15 LE tip
        $treasuryExpense = $invoice->additionalExpenses()->where('title', 'إكرامية طيار الدليفري')->first();
        $this->assertNotNull($treasuryExpense);
        $this->assertNotNull($treasuryExpense->payment_id);
        $this->assertEquals('15.000', $treasuryExpense->payment->amount);
    }

    public function test_customer_charged_expenses_appear_in_a4_and_thermal_prints(): void
    {
        $item = Item::create([
            'name'              => 'صنف مبيعات فاخر',
            'code'              => 'EXP-ITEM-01',
            'unit'              => 'قطعة',
            'cost_price'        => '50.000',
            'sale_price'        => '100.000',
            'current_stock'     => '10.000',
            'weighted_avg_cost' => '50.000',
            'is_active'         => true,
        ]);

        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class);

        $invoice = $invoiceService->confirmInvoice([
            'customer_id'    => $this->customer->id,
            'store_id'       => $this->store->id,
            'invoice_date'   => '2026-08-18',
            'payment_type'   => 'cash',
            'payment_method' => 'cash',
            'items'          => [
                [
                    'item_id'    => $item->id,
                    'quantity'   => '2.000',
                    'unit_price' => '100.000',
                ],
            ],
            'additional_expenses' => [
                [
                    'title'             => 'مصاريف الشحن والتوصيل',
                    'amount'            => '35.000',
                    'allocation_method' => 'by_quantity',
                    'paid_by'           => 'customer_account',
                ],
                [
                    'title'             => 'تغليف هدايا',
                    'amount'            => '15.000',
                    'allocation_method' => 'equal',
                    'paid_by'           => 'customer_account',
                ],
                [
                    'title'             => 'إكرامية داخلية للمحل',
                    'amount'            => '10.000',
                    'allocation_method' => 'equal',
                    'paid_by'           => 'treasury_cash',
                ],
            ],
        ]);

        // 1. Check A4 Print contains customer expenses
        $this->get(route('invoices.print.a4', $invoice->id))
            ->assertStatus(200)
            ->assertSee('مصاريف الشحن والتوصيل')
            ->assertSee('35.00')
            ->assertSee('تغليف هدايا')
            ->assertSee('15.00')
            ->assertDontSee('إكرامية داخلية للمحل'); // Internal treasury expense must NOT appear to customer

        // 2. Check Thermal Print contains customer expenses
        $this->get(route('invoices.print.thermal', $invoice->id))
            ->assertStatus(200)
            ->assertSee('مصاريف الشحن والتوصيل')
            ->assertSee('35.00')
            ->assertSee('تغليف هدايا')
            ->assertSee('15.00')
            ->assertDontSee('إكرامية داخلية للمحل');
    }
}
