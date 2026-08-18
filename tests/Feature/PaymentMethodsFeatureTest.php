<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Invoice;
use App\Models\Customer;
use App\Enums\PaymentMethod;
use App\Services\InvoiceService;
use App\Livewire\InvoiceCreate;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentMethodsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Item $item;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->item = Item::create([
            'code'          => 'ITM-PAY-01',
            'name'          => 'بن حبشي فاخر',
            'current_stock' => '100.000',
            'cost_price'    => '200.000',
            'selling_price' => '300.000',
            'is_active'     => true,
        ]);

        $this->customer = Customer::create([
            'name'      => 'عميل إلكتروني تجريبي',
            'is_active' => true,
        ]);
    }

    public function test_payment_method_enum_contains_only_three_active_methods(): void
    {
        $activeMethods = PaymentMethod::activeMethods();
        $this->assertCount(3, $activeMethods);
        $this->assertContains(PaymentMethod::Cash, $activeMethods);
        $this->assertContains(PaymentMethod::Instapay, $activeMethods);
        $this->assertContains(PaymentMethod::EWallet, $activeMethods);

        // Deactivated methods return isActive = false
        $this->assertFalse(PaymentMethod::Visa->isActive());
        $this->assertFalse(PaymentMethod::BankTransfer->isActive());
        $this->assertFalse(PaymentMethod::Check->isActive());
    }

    public function test_sales_invoice_confirms_with_instapay_payment_method(): void
    {
        $invoiceService = app(InvoiceService::class);

        $invoice = $invoiceService->confirmInvoice([
            'customer_id'    => $this->customer->id,
            'payment_type'   => 'cash',
            'payment_method' => 'instapay',
            'paid_amount'    => '300.000',
            'items'          => [
                [
                    'item_id'    => $this->item->id,
                    'quantity'   => '1.000',
                    'unit_price' => '300.000',
                    'cost_price' => '200.000',
                ]
            ],
        ]);

        $this->assertEquals('instapay', $invoice->payment_method);
        $this->assertEquals('confirmed', $invoice->status);

        // Attached payment voucher also records instapay
        $payment = $invoice->payments()->first();
        $this->assertNotNull($payment);
        $this->assertEquals('instapay', $payment->payment_method);
    }

    public function test_sales_invoice_confirms_with_e_wallet_payment_method(): void
    {
        $invoiceService = app(InvoiceService::class);

        $invoice = $invoiceService->confirmInvoice([
            'customer_id'    => $this->customer->id,
            'payment_type'   => 'cash',
            'payment_method' => 'e_wallet',
            'paid_amount'    => '600.000',
            'items'          => [
                [
                    'item_id'    => $this->item->id,
                    'quantity'   => '2.000',
                    'unit_price' => '300.000',
                    'cost_price' => '200.000',
                ]
            ],
        ]);

        $this->assertEquals('e_wallet', $invoice->payment_method);
        $this->assertEquals('confirmed', $invoice->status);
    }
}
