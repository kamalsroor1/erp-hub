<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Invoice;
use App\Services\InvoiceService;

class InvoiceShow extends Component
{
    public Invoice $invoice;

    public function mount($id)
    {
        $this->invoice = Invoice::with(['customer', 'items.item', 'payments', 'user'])->findOrFail($id);
    }

    public function deleteInvoice(InvoiceService $invoiceService)
    {
        $num = $this->invoice->invoice_number;
        $invoiceService->deleteInvoice($this->invoice);

        session()->flash('success', "تم حذف الفاتورة رقم {$num} نهائياً وإرجاع المخزون بنجاح.");
        return redirect()->route('invoices.index');
    }

    public function render()
    {
        return view('livewire.invoice-show', [
            'invoice' => $this->invoice,
        ])->layout('components.layouts.app', ['title' => "فاتورة {$this->invoice->invoice_number}"]);
    }
}
