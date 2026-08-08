<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ReturnDocument;

class ReturnIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $type = 'all'; // all, sales_return, purchase_return

    public function render()
    {
        $query = ReturnDocument::with(['customer', 'supplier', 'items.item', 'user'])
            ->when($this->search, function ($q) {
                $q->where('return_number', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$this->search}%"));
            })
            ->when($this->type !== 'all', fn($q) => $q->where('return_type', $this->type))
            ->latest('return_date');

        return view('livewire.return-index', [
            'returns' => $query->paginate(15),
        ])->layout('components.layouts.app', ['title' => 'سجل المرتجعات']);
    }
}
