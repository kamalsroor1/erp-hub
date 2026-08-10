<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Exception;

class InvoiceIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $status = 'all'; // all, confirmed, cancelled
    public $paymentStatus = 'all'; // all, paid, unpaid, partially_paid

    public $showCancelModal = false;
    public $cancelInvoiceId;
    public $cancelReason = '';
    public $errorMessage = '';

    public function openCancelModal($invoiceId)
    {
        if (!auth()->user()->hasRole('admin')) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => 'عفواً، لا يملك صلاحية إلغاء الفواتير سوى المدير العام.']);
            return;
        }

        $this->cancelInvoiceId = $invoiceId;
        $this->cancelReason = '';
        $this->errorMessage = '';
        $this->showCancelModal = true;
    }

    public function confirmCancel(InvoiceService $invoiceService)
    {
        if (!auth()->user()->hasRole('admin')) {
            $this->errorMessage = 'عفواً، لا يملك صلاحية إلغاء الفواتير سوى المدير العام.';
            return;
        }

        $this->validate([
            'cancelReason' => 'required|string|min:3',
        ]);

        try {
            $invoice = Invoice::findOrFail($this->cancelInvoiceId);
            $invoiceService->cancelInvoice($invoice, $this->cancelReason);

            $this->showCancelModal = false;
            session()->flash('success', "تم إلغاء الفاتورة رقم {$invoice->invoice_number} وعكس أثرها المخزني والمالي بنجاح.");
            $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => "تم إلغاء الفاتورة {$invoice->invoice_number} وإعادة الرصيد للمخزن!"]);
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $e->getMessage()]);
        }
    }

    public function deleteInvoice($invoiceId, InvoiceService $invoiceService)
    {
        if (!auth()->user()->hasRole('admin')) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => 'عفواً، لا يملك صلاحية حذف الفواتير سوى المدير العام.']);
            return;
        }

        try {
            $invoice = Invoice::findOrFail($invoiceId);
            $num = $invoice->invoice_number;
            $invoiceService->deleteInvoice($invoice);

            session()->flash('success', "تم حذف الفاتورة رقم {$num} نهائياً وإرجاع المخزون بنجاح.");
            $this->dispatch('swal:toast', [
                'icon'  => 'success',
                'title' => "تم حذف الفاتورة {$num} نهائياً وإرجاع المخزون!"
            ]);
        } catch (Exception $e) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $query = Invoice::with(['customer', 'user'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('invoice_number', 'like', "%{$this->search}%")
                        ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status !== 'all', fn($q) => $q->where('status', $this->status))
            ->when($this->paymentStatus !== 'all', fn($q) => $q->where('payment_status', $this->paymentStatus))
            ->latest('invoice_date');

        return view('livewire.invoice-index', [
            'invoices' => $query->paginate(15),
        ])->layout('components.layouts.app', ['title' => 'سجل فواتير المبيعات']);
    }
}
