<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\Payment;
use App\Models\ReturnDocument;

class SupplierStatement extends Component
{
    public Supplier $supplier;
    public $fromDate;
    public $toDate;

    public function mount($id)
    {
        $this->supplier = Supplier::findOrFail($id);
        $this->fromDate = now()->startOfYear()->toDateString();
        $this->toDate = now()->toDateString();
    }

    public function render()
    {
        $entries = collect();

        // 1. Purchases (Debit for supplier balance)
        $purchases = Purchase::where('supplier_id', $this->supplier->id)
            ->where('status', 'confirmed')
            ->when($this->fromDate, fn($q) => $q->whereDate('purchase_date', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('purchase_date', '<=', $this->toDate))
            ->get();

        foreach ($purchases as $pur) {
            $entries->push([
                'date'        => $pur->purchase_date->format('Y-m-d'),
                'type'        => 'فاتورة مشتريات وتوريد',
                'ref_number'  => $pur->purchase_number,
                'debit'       => $pur->net_total, // مستحق للمورد
                'credit'      => '0.000',
                'notes'       => $pur->notes,
                'timestamp'   => $pur->created_at->timestamp,
            ]);
        }

        // 2. Payments (Credit for supplier balance)
        $payments = Payment::where('supplier_id', $this->supplier->id)
            ->when($this->fromDate, fn($q) => $q->whereDate('payment_date', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('payment_date', '<=', $this->toDate))
            ->get();

        foreach ($payments as $pay) {
            $entries->push([
                'date'        => $pay->payment_date->format('Y-m-d'),
                'type'        => 'سند صرف نقدي',
                'ref_number'  => $pay->payment_number,
                'debit'       => '0.000',
                'credit'      => $pay->amount, // سداد للمورد
                'notes'       => $pay->notes,
                'timestamp'   => $pay->created_at->timestamp,
            ]);
        }

        // 3. Purchase Returns (Credit)
        $returns = ReturnDocument::where('supplier_id', $this->supplier->id)
            ->where('return_type', 'purchase_return')
            ->when($this->fromDate, fn($q) => $q->whereDate('return_date', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('return_date', '<=', $this->toDate))
            ->get();

        foreach ($returns as $ret) {
            $entries->push([
                'date'        => $ret->return_date->format('Y-m-d'),
                'type'        => 'مرتجع مشتريات',
                'ref_number'  => $ret->return_number,
                'debit'       => '0.000',
                'credit'      => $ret->total_amount,
                'notes'       => $ret->reason,
                'timestamp'   => $ret->created_at->timestamp,
            ]);
        }

        // Sort chronologically and compute running balance
        $sorted = $entries->sortBy('timestamp')->values();
        $runningBalance = '0.000';
        $ledger = [];

        foreach ($sorted as $row) {
            $runningBalance = bcadd($runningBalance, $row['debit'], 3);
            $runningBalance = bcsub($runningBalance, $row['credit'], 3);
            $ledger[] = array_merge($row, ['balance_after' => $runningBalance]);
        }

        return view('livewire.supplier-statement', [
            'entries'         => $ledger,
            'current_balance' => $this->supplier->current_balance,
        ])->layout('components.layouts.app', ['title' => "كشف حساب المورد: {$this->supplier->name}"]);
    }
}
