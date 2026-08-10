<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\Expense;
use App\Services\InvoiceService;
use App\Services\StockService;
use App\Services\PaymentService;
use App\Livewire\ItemIndex;
use App\Livewire\SupplierIndex;
use App\Livewire\ExpenseIndex;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class SystemImprovementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Customer $customer;
    protected Supplier $supplier;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);

        $this->admin = User::create([
            'name'      => 'كمال سرور',
            'phone'     => '01012316954',
            'email'     => 'admin@sroor.com',
            'password'  => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->admin->assignRole('admin');

        $this->customer = Customer::create([
            'name'            => 'عميل تجريبي',
            'phone'           => '01011111111',
            'current_balance' => '0.000',
            'is_active'       => true,
        ]);

        $this->supplier = Supplier::create([
            'name'            => 'مورد تجريبي للبن',
            'company_name'    => 'مطاحن البن',
            'phone'           => '01022222222',
            'current_balance' => '0.000',
            'is_active'       => true,
        ]);

        $this->item = Item::create([
            'code'              => 'COF-TEST',
            'name'              => 'بن برازيلي فاخر',
            'category'          => 'بن وتوليفات',
            'unit'              => 'كجم',
            'current_stock'     => '100.000',
            'cost_price'        => '200.000',
            'weighted_avg_cost' => '200.000',
            'selling_price'     => '260.000',
            'min_stock_level'   => '10.000',
            'is_active'         => true,
        ]);
    }

    public function test_item_creation_with_opening_stock_does_not_double_weight()
    {
        $this->actingAs($this->admin);

        Livewire::test(ItemIndex::class)
            ->call('openCreateModal')
            ->set('code', 'COF-60KG')
            ->set('name', 'شيكارة بن حبوب 60 كجم')
            ->set('current_stock', '60.000')
            ->set('cost_price', '150.000')
            ->set('selling_price', '220.000')
            ->set('min_stock_level', '5.000')
            ->call('saveItem')
            ->assertHasNoErrors();

        $savedItem = Item::where('code', 'COF-60KG')->firstOrFail();
        // MUST BE EXACTLY 60.000, NOT 120.000!
        $this->assertEquals('60.000', $savedItem->current_stock);
    }

    public function test_invoice_permanent_deletion_reverses_stock_and_removes_record()
    {
        $this->actingAs($this->admin);

        $invoiceService = app(InvoiceService::class);

        // 1. Confirm an invoice selling 20 kg
        $invoice = $invoiceService->confirmInvoice([
            'customer_id'   => $this->customer->id,
            'payment_type'  => 'credit',
            'discount_type' => 'fixed',
            'discount_value'=> '0.000',
            'items'         => [
                [
                    'item_id'    => $this->item->id,
                    'quantity'   => '20.000',
                    'unit_price' => '260.000',
                ]
            ]
        ]);

        $this->item->refresh();
        $this->customer->refresh();
        $this->assertEquals('80.000', $this->item->current_stock);
        $this->assertEquals('5200.000', $this->customer->current_balance);

        // 2. Permanently delete invoice
        $deleted = $invoiceService->deleteInvoice($invoice);
        $this->assertTrue($deleted);

        // 3. Verify stock restored to 100.000, customer balance returned to 0, invoice deleted
        $this->item->refresh();
        $this->customer->refresh();
        $this->assertEquals('100.000', $this->item->current_stock);
        $this->assertEquals('0.000', $this->customer->current_balance);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_supplier_payment_deducts_debt_balance_accurately()
    {
        $this->actingAs($this->admin);

        // 1. Create a confirmed purchase of 5,000 to establish real debt
        $purchaseService = app(\App\Services\PurchaseService::class);
        $purchase = $purchaseService->createPurchase([
            'supplier_id'   => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'paid_amount'   => '0.000',
            'items'         => [
                [
                    'item_id'    => $this->item->id,
                    'quantity'   => '25.000',
                    'cost_price' => '200.000',
                ]
            ]
        ]);

        $this->supplier->refresh();
        $this->assertEquals('5000.000', $this->supplier->current_balance);

        // 2. Pay 2,000 towards the supplier debt
        Livewire::test(SupplierIndex::class)
            ->call('openPaymentModal', $this->supplier->id)
            ->set('paymentAmount', '2000.000')
            ->set('paymentMethod', 'cash')
            ->set('paymentNotes', 'سداد جزئي للمورد')
            ->call('savePayment')
            ->assertHasNoErrors();

        // 3. Verify supplier balance is now exactly 3,000.000 (5000 - 2000)
        $this->supplier->refresh();
        $this->assertEquals('3000.000', $this->supplier->current_balance);
        $this->assertDatabaseHas('payments', [
            'supplier_id' => $this->supplier->id,
            'amount'      => '2000.000',
        ]);
    }

    public function test_expenses_creation_updating_and_deletion()
    {
        $this->actingAs($this->admin);

        Livewire::test(ExpenseIndex::class)
            ->call('openCreateModal')
            ->call('selectQuickCategory', 'شنط وأكياس')
            ->set('title', 'شراء شنط مقاس 1 كجم مطبوعة')
            ->set('amount', '450.000')
            ->set('expense_date', now()->toDateString())
            ->set('payment_method', 'cash')
            ->call('saveExpense')
            ->assertHasNoErrors();

        $expense = Expense::where('title', 'شراء شنط مقاس 1 كجم مطبوعة')->firstOrFail();
        $this->assertEquals('450.000', $expense->amount);
        $this->assertEquals('شنط وأكياس', $expense->category);

        // Delete expense
        Livewire::test(ExpenseIndex::class)
            ->call('deleteExpense', $expense->id);

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_setting_model_and_caching()
    {
        \App\Models\Setting::set('company_name', 'مؤسسة سرور لتجارة البن');
        \App\Models\Setting::set('show_print_subtitle', '0');

        $this->assertEquals('مؤسسة سرور لتجارة البن', \App\Models\Setting::get('company_name'));
        $this->assertFalse(\App\Models\Setting::getBool('show_print_subtitle'));

        // Test caching retrieval
        $all = \App\Models\Setting::allCached();
        $this->assertEquals('مؤسسة سرور لتجارة البن', $all['company_name']);
        $this->assertEquals('0', $all['show_print_subtitle']);
    }

    public function test_profile_general_printing_settings_update()
    {
        $this->actingAs($this->admin);

        Livewire::test(\App\Livewire\Auth\Profile::class)
            ->set('company_name', 'سرور كوفي والمطاحن الحديثة')
            ->set('company_subtitle', 'أجود أنواع البن والشاي')
            ->set('show_print_subtitle', false)
            ->call('updateGeneralSettings')
            ->assertHasNoErrors()
            ->assertDispatched('swal:toast');

        $this->assertEquals('سرور كوفي والمطاحن الحديثة', \App\Models\Setting::get('company_name'));
        $this->assertEquals('أجود أنواع البن والشاي', \App\Models\Setting::get('company_subtitle'));
        $this->assertFalse(\App\Models\Setting::getBool('show_print_subtitle'));
    }
}
