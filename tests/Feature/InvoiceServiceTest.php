<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\StockMovement;
use App\Services\InvoiceService;
use App\Services\StockService;
use App\Services\CustomerBalanceService;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Exception;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InvoiceService $invoiceService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invoiceService = app(InvoiceService::class);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_invoice_creation_with_db_transaction_and_stock_deduction(): void
    {
        $item = Item::create([
            'code'              => 'ITM-001',
            'name'              => 'شاشه ديل 27 بوصة',
            'unit'              => 'قطعة',
            'current_stock'     => '10.000',
            'cost_price'        => '4500.000',
            'weighted_avg_cost' => '4500.000',
            'selling_price'     => '5500.000',
            'min_stock_level'   => '2.000',
            'is_active'         => true,
        ]);

        $customer = Customer::create([
            'name'            => 'شركة الأمل للتجارة',
            'phone'           => '01012345678',
            'current_balance' => '0.000',
            'is_active'       => true,
        ]);

        $invoiceData = [
            'customer_id'    => $customer->id,
            'invoice_date'   => now()->toDateString(),
            'payment_type'   => 'cash',
            'discount_type'  => 'fixed',
            'discount_value' => '100.000',
            'items'          => [
                [
                    'item_id'         => $item->id,
                    'quantity'        => '2.000',
                    'unit_price'      => '5500.000',
                    'discount_amount' => '0.000',
                ]
            ],
        ];

        $invoice = $this->invoiceService->confirmInvoice($invoiceData);

        // 2 items * 5500 = 11,000 subtotal - 100 fixed discount = 10,900 net total
        $this->assertEquals('11000.000', $invoice->subtotal);
        $this->assertEquals('100.000', $invoice->discount_amount);
        $this->assertEquals('10900.000', $invoice->net_total);
        $this->assertEquals('10900.000', $invoice->paid_amount);
        $this->assertEquals('0.000', $invoice->remaining_amount);
        $this->assertEquals('paid', $invoice->payment_status);

        // Stock deduction check: 10 - 2 = 8
        $item->refresh();
        $this->assertEquals('8.000', $item->current_stock);

        // Stock movement ledger check
        $movement = StockMovement::where('item_id', $item->id)->first();
        $this->assertNotNull($movement);
        $this->assertEquals('sales_out', $movement->movement_type);
        $this->assertEquals('2.000', $movement->quantity);
        $this->assertEquals('10.000', $movement->stock_before);
        $this->assertEquals('8.000', $movement->stock_after);
    }

    public function test_insufficient_stock_throws_exception_and_rolls_back(): void
    {
        $item = Item::create([
            'code'          => 'ITM-LOW',
            'name'          => 'ماوس لاسلكي',
            'current_stock' => '1.000',
            'cost_price'    => '100.000',
            'selling_price' => '150.000',
            'is_active'     => true,
        ]);

        $customer = Customer::create([
            'name'      => 'عميل نقدي',
            'is_active' => true,
        ]);

        $invoiceData = [
            'customer_id'  => $customer->id,
            'payment_type' => 'cash',
            'items'        => [
                [
                    'item_id'    => $item->id,
                    'quantity'   => '5.000', // Greater than available 1.000
                    'unit_price' => '150.000',
                ]
            ],
        ];

        $this->expectException(Exception::class);
        $this->invoiceService->confirmInvoice($invoiceData);

        // Verify rollback: stock remains 1.000 and 0 invoices created
        $item->refresh();
        $this->assertEquals('1.000', $item->current_stock);
        $this->assertEquals(0, Invoice::count());
    }

    public function test_invoice_cancellation_reverses_stock(): void
    {
        $item = Item::create([
            'code'          => 'ITM-REV',
            'name'          => 'لوحة مفاتيح ميكانيكية',
            'current_stock' => '5.000',
            'cost_price'    => '300.000',
            'selling_price' => '400.000',
            'is_active'     => true,
        ]);

        $customer = Customer::create(['name' => 'عميل اختبار', 'is_active' => true]);

        $invoice = $this->invoiceService->confirmInvoice([
            'customer_id'  => $customer->id,
            'payment_type' => 'credit',
            'items'        => [
                ['item_id' => $item->id, 'quantity' => '3.000', 'unit_price' => '400.000']
            ],
        ]);

        $item->refresh();
        $this->assertEquals('2.000', $item->current_stock);

        // Cancel invoice
        $this->invoiceService->cancelInvoice($invoice, 'طلب العميل إلغاء الطلب');

        $invoice->refresh();
        $this->assertEquals('cancelled', $invoice->status);

        $item->refresh();
        $this->assertEquals('5.000', $item->current_stock);

        $cancellationMovement = StockMovement::where('item_id', $item->id)
            ->where('movement_type', 'cancellation_in')
            ->first();
        $this->assertNotNull($cancellationMovement);
        $this->assertEquals('3.000', $cancellationMovement->quantity);
    }
}
