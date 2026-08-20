<?php

namespace App\Actions\Invoices;

use App\DTOs\POSInvoiceDTO;
use App\Models\Invoice;
use App\Services\InvoiceService;

class ProcessPOSInvoiceAction
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    public function execute(POSInvoiceDTO $dto): Invoice
    {
        return $this->invoiceService->confirmInvoice($dto->toArray());
    }
}
