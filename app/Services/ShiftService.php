<?php

namespace App\Services;

use App\Models\CashShift;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ReturnDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class ShiftService
{
    /**
     * Get or open active shift for user
     */
    public function getActiveShift(): ?CashShift
    {
        return CashShift::where('status', 'open')->latest()->first();
    }

    /**
     * Open a new shift
     */
    public function openShift(string $openingCash = '0.000', ?string $notes = null): CashShift
    {
        $existing = $this->getActiveShift();
        if ($existing) {
            throw new Exception("يوجد وردية عمل مفتوحة بالفعل برقم {$existing->shift_number}. يجب إغلاقها أولاً.");
        }

        $shiftCount = CashShift::whereDate('opened_at', now()->toDateString())->count() + 1;
        $shiftNumber = 'SHIFT-' . date('Ymd') . '-' . str_pad($shiftCount, 3, '0', STR_PAD_LEFT);

        return CashShift::create([
            'user_id'              => Auth::id() ?? 1,
            'shift_number'         => $shiftNumber,
            'status'               => 'open',
            'opened_at'            => now(),
            'opening_cash_balance' => $openingCash,
            'notes'                => $notes,
        ]);
    }

    /**
     * Calculate current live metrics for open shift
     */
    public function calculateShiftTotals(CashShift $shift): array
    {
        $openedAt = $shift->opened_at;

        // Cash sales
        $cashSales = Invoice::where('status', 'confirmed')
            ->where('payment_type', 'cash')
            ->where('created_at', '>=', $openedAt)
            ->sum('paid_amount');

        // Credit sales
        $creditSales = Invoice::where('status', 'confirmed')
            ->whereIn('payment_type', ['credit', 'partial'])
            ->where('created_at', '>=', $openedAt)
            ->sum('remaining_amount');

        // Customer payments collected into cash drawer
        $paymentsCollected = Payment::where('created_at', '>=', $openedAt)
            ->whereNotNull('customer_id')
            ->sum('amount');

        // Sales Returns refunded in cash
        $refunds = ReturnDocument::where('created_at', '>=', $openedAt)
            ->where('return_type', 'sales_return')
            ->sum('total_amount');

        // Expected in drawer = Opening Cash + Cash Sales + Customer Payments Collected - Refunds
        $expectedCash = bcadd((string)$shift->opening_cash_balance, (string)$cashSales, 3);
        $expectedCash = bcadd($expectedCash, (string)$paymentsCollected, 3);
        $expectedCash = bcsub($expectedCash, (string)$refunds, 3);

        return [
            'total_cash_sales'          => (string) $cashSales,
            'total_credit_sales'        => (string) $creditSales,
            'total_payments_collected'  => (string) $paymentsCollected,
            'total_refunds'             => (string) $refunds,
            'expected_cash_balance'     => (string) $expectedCash,
        ];
    }

    /**
     * Close shift with actual counted cash
     */
    public function closeShift(CashShift $shift, string $actualCash, ?string $notes = null): CashShift
    {
        return DB::transaction(function () use ($shift, $actualCash, $notes) {
            $totals = $this->calculateShiftTotals($shift);

            $diff = bcsub($actualCash, $totals['expected_cash_balance'], 3);

            $shift->update([
                'status'                    => 'closed',
                'closed_at'                 => now(),
                'total_cash_sales'          => $totals['total_cash_sales'],
                'total_credit_sales'        => $totals['total_credit_sales'],
                'total_payments_collected'  => $totals['total_payments_collected'],
                'total_refunds'             => $totals['total_refunds'],
                'expected_cash_balance'     => $totals['expected_cash_balance'],
                'actual_cash_balance'       => $actualCash,
                'cash_difference'           => $diff,
                'notes'                     => $notes ?: $shift->notes,
            ]);

            return $shift;
        });
    }
}
