<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\ReturnDocument;
use App\Models\CashShift;
use App\Models\Store;
use App\Models\TreasuryTransfer;
use App\Services\ShiftService;
use App\Services\TreasuryService;
use Illuminate\Support\Facades\Auth;
use Exception;

class DailyJournalIndex extends Component
{
    public $selectedDate;
    public $selectedStoreId = 'all';
    
    // Shift Management
    public ?CashShift $activeShift = null;
    public $opening_cash_balance = '0.000';
    public $open_notes = '';
    public $actual_cash_balance = '0.000';
    public $close_notes = '';
    public $showOpenModal = false;
    public $showCloseModal = false;

    // Treasury Transfer Modal
    public $showTransferModal = false;
    public $transfer_from_method = 'instapay';
    public $transfer_to_method = 'cash';
    public $transfer_amount = '';
    public $transfer_fee = '';
    public $transfer_notes = '';

    public $errorMessage = '';
    public $successMessage = '';

    public function mount(ShiftService $shiftService)
    {
        abort_if(!auth()->user()?->can('daily_journal.view'), 403, 'غير مصرح لك بعرض دفتر اليومية');

        $this->selectedDate = now()->toDateString();
        
        $currentStore = session('current_store_id') 
            ?? auth()->user()?->getCurrentStore()?->id;

        if (!auth()->user()?->hasRole('admin') && $currentStore) {
            $this->selectedStoreId = (string)$currentStore;
        } else {
            $this->selectedStoreId = $currentStore ? (string)$currentStore : 'all';
        }

        $this->loadActiveShift($shiftService);
    }

    public function updatedSelectedStoreId(ShiftService $shiftService)
    {
        $this->loadActiveShift($shiftService);
    }

    public function setDate($datePreset)
    {
        if ($datePreset === 'today') {
            $this->selectedDate = now()->toDateString();
        } elseif ($datePreset === 'yesterday') {
            $this->selectedDate = now()->subDay()->toDateString();
        }
    }

    public function loadActiveShift(ShiftService $shiftService)
    {
        $storeId = ($this->selectedStoreId && $this->selectedStoreId !== 'all') 
            ? (int)$this->selectedStoreId 
            : (session('current_store_id') ?? auth()->user()?->getCurrentStore()?->id);

        $this->activeShift = $shiftService->getActiveShift(storeId: $storeId);
    }

    public function openShiftModal()
    {
        abort_if(!auth()->user()?->can('daily_journal.close_shift'), 403, 'غير مصرح لك بإدارة ورديات اليومية');
        $this->opening_cash_balance = '0.000';
        $this->open_notes = '';
        $this->showOpenModal = true;
    }

    public function startShift(ShiftService $shiftService)
    {
        abort_if(!auth()->user()?->can('daily_journal.close_shift'), 403, 'غير مصرح لك بفتح يومية العمل');
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            $storeId = ($this->selectedStoreId && $this->selectedStoreId !== 'all') 
                ? (int)$this->selectedStoreId 
                : null;

            $this->activeShift = $shiftService->openShift(
                openingCash: $this->opening_cash_balance ?: '0.000',
                notes: $this->open_notes,
                storeId: $storeId
            );
            $this->showOpenModal = false;
            $this->successMessage = "تم فتح يومية العمل رقم {$this->activeShift->shift_number} بنجاح.";
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function openCloseModal(ShiftService $shiftService)
    {
        abort_if(!auth()->user()?->can('daily_journal.close_shift'), 403, 'غير مصرح لك بإغلاق يومية العمل');
        if (!$this->activeShift) return;
        $totals = $shiftService->calculateShiftTotals($this->activeShift);
        $this->actual_cash_balance = $totals['expected_cash_balance'];
        $this->showCloseModal = true;
    }

    public function submitCloseShift(ShiftService $shiftService)
    {
        abort_if(!auth()->user()?->can('daily_journal.close_shift'), 403, 'غير مصرح لك بإغلاق وتقفيل يومية العمل');
        $this->errorMessage = '';
        $this->successMessage = '';

        if (!$this->activeShift) return;

        try {
            $closed = $shiftService->closeShift(
                shift: $this->activeShift,
                actualCash: $this->actual_cash_balance ?: '0.000',
                notes: $this->close_notes
            );

            $this->showCloseModal = false;
            $this->activeShift = null;
            $this->successMessage = "تم تقفيل اليومية رقم {$closed->shift_number} بنجاح وتسجيل تقرير الـ Z-Report.";
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function openTransferModal()
    {
        $this->transfer_amount = '';
        $this->transfer_fee = '';
        $this->transfer_notes = '';
        $this->errorMessage = '';
        $this->showTransferModal = true;
    }

    public function executeTransfer(TreasuryService $treasuryService)
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        $this->validate([
            'transfer_from_method' => 'required|in:cash,instapay,e_wallet,visa,bank_transfer',
            'transfer_to_method'   => 'required|in:cash,instapay,e_wallet,visa,bank_transfer|different:transfer_from_method',
            'transfer_amount'      => 'required|numeric|min:0.001',
            'transfer_fee'         => 'nullable|numeric|min:0',
            'transfer_notes'       => 'nullable|string|max:500',
        ], [
            'transfer_to_method.different' => 'عفواً، لا يمكن التحويل لنفس الحساب أو الخزينة!',
            'transfer_amount.required'     => 'يرجى كتابة المبلغ المراد تحويله.',
            'transfer_amount.min'          => 'يجب أن يكون المبلغ أكبر من الصفر.',
        ]);

        try {
            $storeId = ($this->selectedStoreId && $this->selectedStoreId !== 'all') 
                ? (int)$this->selectedStoreId 
                : (session('current_store_id') ?? auth()->user()?->getCurrentStore()?->id);

            $transfer = $treasuryService->transfer([
                'from_method'   => $this->transfer_from_method,
                'to_method'     => $this->transfer_to_method,
                'amount'        => $this->transfer_amount,
                'transfer_fee'  => $this->transfer_fee ?: '0.000',
                'store_id'      => $storeId,
                'transfer_date' => $this->selectedDate ?: now()->toDateString(),
                'notes'         => $this->transfer_notes,
            ]);

            $this->showTransferModal = false;
            $fromName = $transfer->from_method_label;
            $toName   = $transfer->to_method_label;
            $this->successMessage = "تم تحويل مبلغ {$transfer->amount} ج.م بنجاح من [{$fromName}] إلى [{$toName}] برقم قيد {$transfer->transfer_number}.";
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render(ShiftService $shiftService, TreasuryService $treasuryService)
    {
        $date = $this->selectedDate ?: now()->toDateString();
        $storeFilter = ($this->selectedStoreId && $this->selectedStoreId !== 'all') 
            ? (int)$this->selectedStoreId 
            : null;

        $stores = Store::active()->get();

        // 1. Invoices on this day
        $invoices = Invoice::with(['customer', 'store'])
            ->whereDate('invoice_date', $date)
            ->where('status', 'confirmed')
            ->when($storeFilter, fn($q) => $q->where('store_id', $storeFilter))
            ->latest('id')
            ->get();

        $invoicesCount = $invoices->count();
        $totalSales = $invoices->sum('net_total');
        $cashSales = $invoices->where('payment_type', 'cash')->sum('net_total');
        $creditSales = $invoices->where('payment_type', 'credit')->sum('net_total');
        $partialSales = $invoices->where('payment_type', 'partial')->sum('paid_amount');

        // Payment Method Breakdown
        $instapaySales     = (string)($invoices->where('payment_method', 'instapay')->sum('paid_amount') ?: '0.000');
        $walletSales       = (string)($invoices->where('payment_method', 'e_wallet')->sum('paid_amount') ?: '0.000');
        $visaSales         = (string)($invoices->where('payment_method', 'visa')->sum('paid_amount') ?: '0.000');
        $bankSales         = (string)($invoices->where('payment_method', 'bank_transfer')->sum('paid_amount') ?: '0.000');
        $physicalCashSales = (string)($invoices->where('payment_method', 'cash')->whereIn('payment_type', ['cash', 'partial'])->sum('paid_amount') ?: '0.000');

        // 2. Total Cash Inflows
        $customerPayments = Payment::with('customer')
            ->whereDate('payment_date', $date)
            ->whereNotNull('customer_id')
            ->get();
        $totalCashCollected = (string)($customerPayments->sum('amount') ?: ($cashSales + $partialSales));

        // 3. Operational Expenses on this day
        $expenses = Expense::whereDate('expense_date', $date)
            ->when($storeFilter, fn($q) => $q->where('store_id', $storeFilter))
            ->latest('id')
            ->get();
        $totalExpenses = (string)($expenses->sum('amount') ?: '0.000');

        // 4. Purchases on this day
        $purchases = Purchase::with('supplier')
            ->whereDate('purchase_date', $date)
            ->where('status', 'confirmed')
            ->when($storeFilter, fn($q) => $q->where('store_id', $storeFilter))
            ->latest('id')
            ->get();
        $totalPurchases = (string)($purchases->sum('net_total') ?: '0.000');

        // 5. Supplier Payments on this day
        $supplierPayments = Payment::with('supplier')
            ->whereDate('payment_date', $date)
            ->whereNotNull('supplier_id')
            ->get();
        $totalSupplierPaid = (string)($supplierPayments->sum('amount') ?: '0.000');

        // 6. Net Movement for this day
        $totalOutflows = bcadd($totalExpenses, $totalSupplierPaid, 3);
        $netCashToday = bcsub((string)$totalCashCollected, $totalOutflows, 3);

        // 7. Shifts on this day & Opening Balance
        $shiftsOnDate = CashShift::with(['user', 'store'])
            ->whereDate('opened_at', $date)
            ->when($storeFilter, fn($q) => $q->where('store_id', $storeFilter))
            ->latest('id')
            ->get();

        $openingCashBalance = '0.000';
        if ($this->activeShift && $this->activeShift->opened_at->format('Y-m-d') === $date) {
            $openingCashBalance = (string)$this->activeShift->opening_cash_balance;
        } elseif ($shiftsOnDate->count() > 0) {
            $openingCashBalance = (string)$shiftsOnDate->first()->opening_cash_balance;
        }

        // 8. Expected Total Cash Physically in Drawer Right Now
        $expectedCashInDrawer = bcadd($openingCashBalance, $netCashToday, 3);

        // Calculate live shift stats if active
        $activeShiftTotals = null;
        if ($this->activeShift) {
            $activeShiftTotals = $shiftService->calculateShiftTotals($this->activeShift);
        }

        // 9. Treasury & Multi-Account Balances
        $treasuryBalances = $treasuryService->getBalances(storeId: $storeFilter, date: $date);

        // 10. Treasury Transfers on this day
        $transfers = TreasuryTransfer::with(['user', 'store'])
            ->whereDate('transfer_date', $date)
            ->when($storeFilter, fn($q) => $q->where('store_id', $storeFilter))
            ->latest('id')
            ->get();

        return view('livewire.daily-journal-index', [
            'stores'               => $stores,
            'currentStore'         => $storeFilter ? Store::find($storeFilter) : null,
            'invoices'             => $invoices,
            'invoicesCount'        => $invoicesCount,
            'totalSales'           => $totalSales,
            'cashSales'            => $cashSales,
            'creditSales'          => $creditSales,
            'partialSales'         => $partialSales,
            'physicalCashSales'    => $physicalCashSales,
            'instapaySales'        => $instapaySales,
            'walletSales'          => $walletSales,
            'visaSales'            => $visaSales,
            'bankSales'            => $bankSales,
            'customerPayments'     => $customerPayments,
            'totalCashCollected'   => $totalCashCollected,
            'expenses'             => $expenses,
            'totalExpenses'        => $totalExpenses,
            'purchases'            => $purchases,
            'totalPurchases'       => $totalPurchases,
            'supplierPayments'     => $supplierPayments,
            'totalSupplierPaid'    => $totalSupplierPaid,
            'netCashToday'         => $netCashToday,
            'openingCashBalance'   => $openingCashBalance,
            'expectedCashInDrawer' => $expectedCashInDrawer,
            'shiftsOnDate'         => $shiftsOnDate,
            'activeShiftTotals'    => $activeShiftTotals,
            'treasuryBalances'     => $treasuryBalances,
            'transfers'            => $transfers,
        ])->layout('components.layouts.app', ['title' => "يومية المبيعات وحركة الدرج - {$date}"]);
    }
}
