<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Services\CustomerBalanceService;

class CustomerStatement extends Component
{
    public Customer $customer;
    public $fromDate;
    public $toDate;

    public function mount($id)
    {
        $this->customer = Customer::findOrFail($id);
        $this->fromDate = now()->startOfYear()->toDateString();
        $this->toDate = now()->toDateString();
    }

    public function render(CustomerBalanceService $balanceService)
    {
        $ledger = $balanceService->getCustomerLedger($this->customer, $this->fromDate, $this->toDate);

        return view('livewire.customer-statement', [
            'entries'         => $ledger['entries'],
            'current_balance' => $ledger['current_balance'],
        ])->layout('components.layouts.app', ['title' => "كشف حساب: {$this->customer->name}"]);
    }
}
