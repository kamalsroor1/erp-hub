<?php

namespace App\Services;

use App\Models\TreasuryTransfer;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\CashShift;
use App\Enums\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class TreasuryService
{
    public function __construct(
        protected AuditLogService $auditLogService,
        protected ActivityLogService $activityLogService
    ) {}

    /**
     * Get real-time financial balances per payment method / treasury account
     */
    public function getBalances(?int $storeId = null, ?string $date = null): array
    {
        $methods = PaymentMethod::activeMethods();
        $balances = [];
        $totalLiquidity = '0.000';

        foreach ($methods as $methodEnum) {
            $methodKey = $methodEnum->value;

            // 1. Inflows: All payments collected via this method (Sales + Debt collections)
            $inflows = (string) Payment::where('payment_method', $methodKey)
                ->whereNotNull('customer_id')
                ->when($date, fn($q) => $q->whereDate('payment_date', '<=', $date))
                ->sum('amount');

            // 2. Outflows: Supplier payments + General Expenses recorded via this method
            $supplierOutflows = (string) Payment::where('payment_method', $methodKey)
                ->whereNotNull('supplier_id')
                ->when($date, fn($q) => $q->whereDate('payment_date', '<=', $date))
                ->sum('amount');

            // Operational Expenses not generated via payments
            $generalExpenses = (string) Expense::where('payment_method', $methodKey)
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->when($date, fn($q) => $q->whereDate('expense_date', '<=', $date))
                ->sum('amount');

            // Expense payments (PAY-EXP)
            $expensePayments = (string) Payment::where('payment_method', $methodKey)
                ->where('payment_number', 'like', 'PAY-EXP-%')
                ->when($date, fn($q) => $q->whereDate('payment_date', '<=', $date))
                ->sum('amount');

            $totalOutflows = bcadd($supplierOutflows, bcadd($generalExpenses, $expensePayments, 3), 3);

            // 3. Inbound Transfers (money received into this method)
            $inboundTransfers = (string) TreasuryTransfer::where('to_method', $methodKey)
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->when($date, fn($q) => $q->whereDate('transfer_date', '<=', $date))
                ->sum('amount');

            // 4. Outbound Transfers (money sent out from this method)
            $outboundTransfers = (string) TreasuryTransfer::where('from_method', $methodKey)
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->when($date, fn($q) => $q->whereDate('transfer_date', '<=', $date))
                ->sum('amount');

            // Outbound Transfer Fees
            $transferFees = (string) TreasuryTransfer::where('from_method', $methodKey)
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->when($date, fn($q) => $q->whereDate('transfer_date', '<=', $date))
                ->sum('transfer_fee');

            // Net Balance calculation: (Inflows + Inbound Transfers) - (Outflows + Outbound Transfers + Fees)
            $totalIn = bcadd($inflows, $inboundTransfers, 3);
            $totalOut = bcadd($totalOutflows, bcadd($outboundTransfers, $transferFees, 3), 3);
            $netBalance = bcsub($totalIn, $totalOut, 3);

            // Add Opening cash balance if physical cash drawer
            if ($methodEnum->isPhysicalCash()) {
                $activeShift = CashShift::where('status', 'open')
                    ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                    ->latest('id')
                    ->first();
                if ($activeShift) {
                    $netBalance = bcadd($netBalance, (string)$activeShift->opening_cash_balance, 3);
                }
            }

            $balances[$methodKey] = [
                'enum'          => $methodEnum,
                'key'           => $methodKey,
                'label'         => $methodEnum->label(),
                'short_label'   => $methodEnum->shortLabel(),
                'icon'          => $methodEnum->icon(),
                'badge_class'   => $methodEnum->badgeClass(),
                'inflows'       => $totalIn,
                'outflows'      => $totalOut,
                'balance'       => $netBalance,
            ];

            $totalLiquidity = bcadd($totalLiquidity, $netBalance, 3);
        }

        $balances['total_liquidity'] = $totalLiquidity;
        return $balances;
    }

    /**
     * Execute an inter-account treasury transfer atomically
     */
    public function transfer(array $data): TreasuryTransfer
    {
        $fromMethod = $data['from_method'] ?? null;
        $toMethod   = $data['to_method'] ?? null;
        $amount     = (string)($data['amount'] ?? '0.000');
        $fee        = (string)($data['transfer_fee'] ?? '0.000');
        $storeId    = $data['store_id'] ?? null;
        $notes      = $data['notes'] ?? null;
        $date       = $data['transfer_date'] ?? now()->toDateString();

        if (empty($fromMethod) || empty($toMethod)) {
            throw new Exception("يرجى تحديد الحساب المحول منه والحساب المستلم.");
        }

        if ($fromMethod === $toMethod) {
            throw new Exception("عفواً، لا يمكن التحويل لنفس الحساب أو الخزينة!");
        }

        if (bccomp($amount, '0.000', 3) <= 0) {
            throw new Exception("يرجى إدخال مبلغ تحويل صحيح أكبر من الصفر.");
        }

        if (bccomp($fee, '0.000', 3) < 0) {
            $fee = '0.000';
        }

        // Check source balance sufficiency
        $balances = $this->getBalances(storeId: $storeId, date: $date);
        $sourceBal = $balances[$fromMethod]['balance'] ?? '0.000';
        $totalRequired = bcadd($amount, $fee, 3);

        if (bccomp($totalRequired, $sourceBal, 3) > 0) {
            $fromLabel = PaymentMethod::tryFrom($fromMethod)?->label() ?? $fromMethod;
            throw new Exception("عفواً، رصيد الحساب المحول منه [{$fromLabel}] غير كافٍ لإتمام التحويل (المتاح: " . number_format((float)$sourceBal, 2) . " ج.م - المطلوب بالعمولة: " . number_format((float)$totalRequired, 2) . " ج.م).");
        }

        return DB::transaction(function () use ($fromMethod, $toMethod, $amount, $fee, $storeId, $notes, $date) {
            $transferNumber = 'TRF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $transfer = TreasuryTransfer::create([
                'transfer_number' => $transferNumber,
                'from_method'     => $fromMethod,
                'to_method'       => $toMethod,
                'amount'          => $amount,
                'transfer_fee'    => $fee,
                'store_id'        => $storeId,
                'user_id'         => Auth::id() ?? 1,
                'transfer_date'   => $date,
                'notes'           => $notes,
            ]);

            $fromLabel = PaymentMethod::tryFrom($fromMethod)?->label() ?? $fromMethod;
            $toLabel   = PaymentMethod::tryFrom($toMethod)?->label() ?? $toMethod;

            $this->auditLogService->log(
                action: 'treasury_transfer_created',
                auditable: $transfer,
                oldValues: null,
                newValues: $transfer->toArray()
            );

            $this->activityLogService->log(
                module: 'treasury',
                action: 'transfer',
                description: "تحويل رصيد مالي برقم [{$transferNumber}] بمبلغ " . number_format((float)$amount, 2) . " ج.م من [{$fromLabel}] إلى [{$toLabel}]",
                subject: $transfer,
                storeId: $storeId
            );

            return $transfer;
        });
    }
}
