<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Invoice;

class InvoiceShow extends Component
{
    public Invoice $invoice;

    public function mount($id)
    {
        $this->invoice = Invoice::with(['customer', 'items.item', 'payments', 'user'])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.invoice-show', [
            'invoice' => $this->invoice,
        ])->layout('components.layouts.app', ['title' => "فاتورة {$this->invoice->invoice_number}"]);
    }
}
