<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Purchase;

class PurchaseIndex extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $query = Purchase::with(['supplier', 'items.item', 'user'])
            ->when($this->search, function ($q) {
                $q->where('purchase_number', 'like', "%{$this->search}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('items.item', fn($i) => $i->where('name', 'like', "%{$this->search}%"));
            })
            ->latest('purchase_date');

        return view('livewire.purchase-index', [
            'purchases' => $query->paginate(15),
        ])->layout('components.layouts.app', ['title' => 'سجل فواتير المشتريات والتوريد']);
    }
}
