<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class InvoiceService
{
    public function __construct(
        protected StockService $stockService,
        protected CustomerBalanceService $customerBalanceService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Confirm a sales invoice atomically
     */
    public function confirmInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::where('id', $data['customer_id'])->lockForUpdate()->firstOrFail();

            $subtotal  = '0.000';
            $totalCost = '0.000';

            $invoiceNumber = $data['invoice_number'] ?? $this->generateUniqueNumber();

            $invoice = Invoice::create([
                'invoice_number'   => $invoiceNumber,
                'customer_id'      => $customer->id,
                'user_id'          => Auth::id() ?? 1,
                'invoice_date'     => $data['invoice_date'] ?? now()->toDateString(),
                'payment_type'     => $data['payment_type'] ?? 'cash',
                'status'           => 'confirmed',
                'payment_status'   => 'unpaid',
                'subtotal'         => '0.000',
                'discount_type'    => $data['discount_type'] ?? 'fixed',
                'discount_value'   => $data['discount_value'] ?? '0.000',
                'discount_amount'  => '0.000',
                'net_total'        => '0.000',
                'paid_amount'      => '0.000',
                'remaining_amount' => '0.000',
                'total_cost'       => '0.000',
                'notes'            => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $line) {
                // S1: Lock item row and verify stock
                $item = Item::where('id', $line['item_id'])->lockForUpdate()->firstOrFail();

                $qty = $line['quantity'];
                $unitPrice = $line['unit_price'];
                $itemDiscount = $line['discount_amount'] ?? '0.000';

                // Line Total = (Quantity * Unit Price) - Item Discount
                $grossLineTotal = bcmul($qty, $unitPrice, 3);
                $netLineTotal = bcsub($grossLineTotal, $itemDiscount, 3);
                if (bccomp($netLineTotal, '0.000', 3) < 0) {
                    $netLineTotal = '0.000';
                }

                $effectiveCost = bccomp($item->weighted_avg_cost, '0.000', 3) > 0
                    ? $item->weighted_avg_cost
                    : $item->cost_price;

                $lineCost = bcmul($qty, $effectiveCost, 3);
                $totalCost = bcadd($totalCost, $lineCost, 3);

                // Create invoice item row
                $invoice->items()->create([
                    'item_id'         => $item->id,
                    'quantity'        => $qty,
                    'cost_price'      => $effectiveCost,
                    'unit_price'      => $unitPrice,
                    'discount_amount' => $itemDiscount,
                    'total_price'     => $netLineTotal,
                ]);

                // S2: Deduct stock atomically and log movement
                $this->stockService->deductStock(
                    item: $item,
                    quantity: $qty,
                    source: $invoice,
                    documentNumber: $invoice->invoice_number,
                    movementType: 'sales_out',
                    notes: "صرف مبيعات بالفاتورة رقم {$invoice->invoice_number}"
                );

                $subtotal = bcadd($subtotal, $netLineTotal, 3);
            }

            // S3: Invoice-level Discount Calculation
            $discountType  = $data['discount_type'] ?? 'fixed';
            $discountValue = $data['discount_value'] ?? '0.000';
            $invoiceDiscountAmount = '0.000';

            if ($discountType === 'percentage') {
                // (Subtotal * Discount Value) / 100
                $invoiceDiscountAmount = bcdiv(bcmul($subtotal, $discountValue, 4), '100', 3);
            } else {
                $invoiceDiscountAmount = $discountValue;
            }

            if (bccomp($invoiceDiscountAmount, $subtotal, 3) > 0) {
                $invoiceDiscountAmount = $subtotal;
            }

            $netTotal = bcsub($subtotal, $invoiceDiscountAmount, 3);

            // S4: Payments and Remaining Amounts
            $paidAmount = '0.000';
            $paymentType = $data['payment_type'] ?? 'cash';

            if ($paymentType === 'cash') {
                $paidAmount = $netTotal;
            } elseif ($paymentType === 'partial') {
                $paidAmount = $data['paid_amount'] ?? '0.000';
            } else { // credit
                $paidAmount = '0.000';
            }

            $remainingAmount = bcsub($netTotal, $paidAmount, 3);
            if (bccomp($remainingAmount, '0.000', 3) < 0) {
                $remainingAmount = '0.000';
            }

            $paymentStatus = 'unpaid';
            if (bccomp($remainingAmount, '0.000', 3) === 0) {
                $paymentStatus = 'paid';
            } elseif (bccomp($paidAmount, '0.000', 3) > 0) {
                $paymentStatus = 'partially_paid';
            }

            $invoice->update([
                'subtotal'         => $subtotal,
                'discount_type'    => $discountType,
                'discount_value'   => $discountValue,
                'discount_amount'  => $invoiceDiscountAmount,
                'net_total'        => $netTotal,
                'paid_amount'      => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'payment_status'   => $paymentStatus,
                'total_cost'       => $totalCost,
            ]);

            // S5: Record Payment Voucher if money was paid
            if (bccomp($paidAmount, '0.000', 3) > 0) {
                Payment::create([
                    'payment_number' => 'PAY-INV-' . strtoupper(uniqid()),
                    'customer_id'    => $customer->id,
                    'invoice_id'     => $invoice->id,
                    'user_id'        => Auth::id() ?? 1,
                    'amount'         => $paidAmount,
                    'payment_date'   => $invoice->invoice_date,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'notes'          => "سداد عند إصدار الفاتورة رقم {$invoice->invoice_number}",
                ]);
            }

            // S6: Update Customer balance
            $this->customerBalanceService->updateBalance($customer->id);

            // S7: Audit Log
            $this->auditLogService->log(
                action: 'invoice_confirmed',
                auditable: $invoice,
                oldValues: null,
                newValues: $invoice->toArray()
            );

            return $invoice;
        });
    }

    /**
     * Cancel an invoice and reverse stock securely
     */
    public function cancelInvoice(Invoice $invoice, string $reason): Invoice
    {
        return DB::transaction(function () use ($invoice, $reason) {
            $lockedInvoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            if ($lockedInvoice->status === 'cancelled') {
                throw new Exception("هذه الفاتورة ملغاة بالفعل مسبقاً.");
            }

            $oldState = $lockedInvoice->toArray();

            // Reverse stock for each line
            foreach ($lockedInvoice->items as $itemLine) {
                $item = Item::where('id', $itemLine->item_id)->lockForUpdate()->firstOrFail();

                $this->stockService->addStock(
                    item: $item,
                    quantity: $itemLine->quantity,
                    unitCost: $itemLine->cost_price,
                    source: $lockedInvoice,
                    documentNumber: $lockedInvoice->invoice_number,
                    movementType: 'cancellation_in',
                    notes: "إلغاء فاتورة مبيعات رقم {$lockedInvoice->invoice_number} - سبب: {$reason}"
                );
            }

            $lockedInvoice->update([
                'status'           => 'cancelled',
                'remaining_amount' => '0.000',
                'notes'            => ($lockedInvoice->notes ? $lockedInvoice->notes . "\n" : '') . "تم الإلغاء: {$reason}",
            ]);

            // Recalculate customer balance
            $this->customerBalanceService->updateBalance($lockedInvoice->customer_id);

            // Audit log
            $this->auditLogService->log(
                action: 'invoice_cancelled',
                auditable: $lockedInvoice,
                oldValues: $oldState,
                newValues: $lockedInvoice->toArray()
            );

            return $lockedInvoice;
        });
    }

    public function generateUniqueNumber(): string
    {
        $prefix = 'INV-' . date('Ymd');
        $count = Invoice::whereDate('created_at', now()->toDateString())->count() + 1;
        return $prefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
