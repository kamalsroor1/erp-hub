<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ReturnDocument;
use Exception;

class ReturnIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $type = 'all'; // all, sales_return, purchase_return
    public $filterStatus = 'active'; // active, trashed, all

    public function deleteReturn($returnId)
    {
        if (!auth()->user()->hasRole('admin')) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => 'عفواً، لا يملك صلاحية أرشفة المرتجعات سوى المدير العام.']);
            return;
        }

        try {
            $returnDoc = ReturnDocument::findOrFail($returnId);
            $num = $returnDoc->return_number;
            $returnDoc->delete(); // Soft delete

            session()->flash('success', "تم نقل مستند المرتجع رقم {$num} إلى سلة المحذوفات بنجاح.");
            $this->dispatch('swal:toast', [
                'icon'  => 'success',
                'title' => "تم أرشفة المرتجع {$num} بنجاح!"
            ]);
        } catch (Exception $e) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $e->getMessage()]);
        }
    }

    public function restoreReturn($returnId)
    {
        if (!auth()->user()->hasRole('admin')) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => 'عفواً، لا يملك صلاحية استعادة المرتجعات سوى المدير العام.']);
            return;
        }

        try {
            $returnDoc = ReturnDocument::onlyTrashed()->findOrFail($returnId);
            $returnDoc->restore();

            session()->flash('success', "تم استعادة مستند المرتجع رقم {$returnDoc->return_number} بنجاح.");
            $this->dispatch('swal:toast', [
                'icon'  => 'success',
                'title' => "تم استعادة المرتجع {$returnDoc->return_number} بنجاح!"
            ]);
        } catch (Exception $e) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $baseQuery = match ($this->filterStatus) {
            'trashed' => ReturnDocument::onlyTrashed(),
            'all'     => ReturnDocument::withTrashed(),
            default   => ReturnDocument::query(),
        };

        $query = $baseQuery->with(['customer', 'supplier', 'items.item', 'user', 'store'])
            ->when($this->search, function ($q) {
                $q->where('return_number', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$this->search}%"));
            })
            ->when($this->type !== 'all', fn($q) => $q->where('return_type', $this->type))
            ->latest('return_date');

        return view('livewire.return-index', [
            'returns'      => $query->paginate(15),
            'trashedCount' => ReturnDocument::onlyTrashed()->count(),
        ])->layout('components.layouts.app', ['title' => 'سجل المرتجعات']);
    }
}
