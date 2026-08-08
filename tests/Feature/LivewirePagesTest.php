<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Supplier;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LivewirePagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected Supplier $supplier;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->customer = Customer::create([
            'name'            => 'عميل تجريبي',
            'phone'           => '01011112222',
            'current_balance' => '0.000',
            'is_active'       => true,
        ]);

        $this->supplier = Supplier::create([
            'name'            => 'مورد تجريبي للبن',
            'company_name'    => 'شركة التوريدات',
            'current_balance' => '0.000',
            'is_active'       => true,
        ]);

        $this->item = Item::create([
            'code'              => 'TEST-001',
            'name'              => 'بن برازيلي تجريبي',
            'unit'              => 'كجم',
            'current_stock'     => '10.000',
            'cost_price'        => '50.000',
            'weighted_avg_cost' => '50.000',
            'selling_price'     => '80.000',
            'min_stock_level'   => '2.000',
            'is_active'         => true,
        ]);
    }

    public function test_dashboard_renders_successfully(): void
    {
        $this->get(route('dashboard'))->assertStatus(200)->assertSee('نظام سرور لإدارة الفواتير');
    }

    public function test_pos_invoice_create_renders_successfully(): void
    {
        $this->get(route('invoices.create'))->assertStatus(200)->assertSee('كاشير ومبيعات مطحنة البن');
    }

    public function test_items_index_renders_successfully(): void
    {
        $this->get(route('items.index'))->assertStatus(200)->assertSee('إدارة الأصناف والمخزون');
    }

    public function test_customers_index_renders_successfully(): void
    {
        $this->get(route('customers.index'))->assertStatus(200)->assertSee('دليل العملاء');
    }

    public function test_customer_statement_renders_successfully(): void
    {
        $this->get(route('customers.statement', $this->customer->id))->assertStatus(200)->assertSee('كشف حساب تفصيلي');
    }

    public function test_suppliers_index_renders_successfully(): void
    {
        $this->get(route('suppliers.index'))->assertStatus(200)->assertSee('دليل الموردين والشركات');
    }

    public function test_supplier_statement_renders_successfully(): void
    {
        $this->get(route('suppliers.statement', $this->supplier->id))->assertStatus(200)->assertSee('كشف حساب تفصيلي للمورد');
    }

    public function test_purchases_index_renders_successfully(): void
    {
        $this->get(route('purchases.index'))->assertStatus(200)->assertSee('فواتير المشتريات');
    }

    public function test_purchases_create_renders_successfully(): void
    {
        $this->get(route('purchases.create'))->assertStatus(200)->assertSee('فاتورة شراء بضاعة');
    }

    public function test_returns_index_renders_successfully(): void
    {
        $this->get(route('returns.index'))->assertStatus(200)->assertSee('سجل مرتجعات المبيعات');
    }

    public function test_returns_create_renders_successfully(): void
    {
        $this->get(route('returns.create'))->assertStatus(200)->assertSee('تسجيل مستند مرتجع');
    }

    public function test_reports_index_renders_successfully(): void
    {
        $this->get(route('reports.index'))->assertStatus(200)->assertSee('التقارير المالية ومجمل الأرباح');
    }

    public function test_thermal_and_a4_print_views_render_successfully(): void
    {
        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->confirmInvoice([
            'customer_id'  => $this->customer->id,
            'payment_type' => 'cash',
            'items'        => [
                ['item_id' => $this->item->id, 'quantity' => '1.000', 'unit_price' => '80.000']
            ],
        ]);

        $this->get(route('invoices.print.thermal', $invoice->id))
            ->assertStatus(200)
            ->assertSee($invoice->invoice_number);

        $this->get(route('invoices.print.a4', $invoice->id))
            ->assertStatus(200)
            ->assertSee($invoice->invoice_number);
    }

    public function test_export_customer_statement_csv_downloads_successfully(): void
    {
        $this->get(route('customers.export.csv', $this->customer->id))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_export_supplier_statement_csv_downloads_successfully(): void
    {
        $this->get(route('suppliers.export.csv', $this->supplier->id))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_export_inventory_csv_downloads_successfully(): void
    {
        $this->get(route('items.export.csv'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_coffee_blender_renders_successfully(): void
    {
        $this->get(route('coffee.blender'))
            ->assertStatus(200)
            ->assertSee('خلاط وتوليفات البن');
    }

    public function test_cash_shifts_renders_successfully(): void
    {
        $this->get(route('shifts.index'))
            ->assertStatus(200)
            ->assertSee('إدارة ورديات الكاشير');
    }
}
