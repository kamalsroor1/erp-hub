<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CashShift;
use App\Services\ShiftService;
use Exception;

class CashShiftManager extends Component
{
    public ?CashShift $activeShift = null;

    // Open Shift Form
    public $opening_cash_balance = '500.000';
    public $open_notes = '';

    // Close Shift Form
    public $actual_cash_balance = '0.000';
    public $close_notes = '';
    public $showCloseModal = false;

    public $errorMessage = '';
    public $successMessage = '';

    public function mount(ShiftService $shiftService)
    {
        $this->loadActiveShift($shiftService);
    }

    public function loadActiveShift(ShiftService $shiftService)
    {
        $this->activeShift = $shiftService->getActiveShift();
    }

    public function startShift(ShiftService $shiftService)
    {
        abort_if(!auth()->user()?->can('daily_journal.close_shift'), 403, 'غير مصرح لك بفتح وردية العمل');
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            $this->activeShift = $shiftService->openShift(
                openingCash: $this->opening_cash_balance ?: '0.000',
                notes: $this->open_notes
            );
            $this->successMessage = "تم فتح وردية العمل رقم {$this->activeShift->shift_number} بنجاح.";
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function openCloseModal(ShiftService $shiftService)
    {
        abort_if(!auth()->user()?->can('daily_journal.close_shift'), 403, 'غير مصرح لك بإغلاق وردية العمل');
        if (!$this->activeShift) return;
        $totals = $shiftService->calculateShiftTotals($this->activeShift);
        $this->actual_cash_balance = $totals['expected_cash_balance'];
        $this->showCloseModal = true;
    }

    public function submitCloseShift(ShiftService $shiftService)
    {
        abort_if(!auth()->user()?->can('daily_journal.close_shift'), 403, 'غير مصرح لك بإغلاق وتقفيل وردية العمل');
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
            $this->successMessage = "تم إغلاق الوردية رقم {$closed->shift_number} بنجاح وتسجيل تقرير الـ Z-Report.";
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render(ShiftService $shiftService)
    {
        $liveMetrics = [];
        if ($this->activeShift) {
            $liveMetrics = $shiftService->calculateShiftTotals($this->activeShift);
        }

        $pastShifts = CashShift::with('user')->latest()->paginate(10);

        return view('livewire.cash-shift-manager', [
            'liveMetrics' => $liveMetrics,
            'pastShifts'  => $pastShifts,
        ])->layout('components.layouts.app', ['title' => 'إدارة الورديات وإغلاق درج النقدية (Z-Report)']);
    }
}
