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
    public ?string $fromDate = null;
    public ?string $toDate = null;

    public function mount()
    {
        abort_if(!auth()->user()?->can('returns.manage'), 403, 'غير مصرح لك بإدارة المرتجعات');
    }

    public function deleteReturn($returnId)
    {
        abort_if(!auth()->user()?->can('returns.manage'), 403, 'غير مصرح لك بحذف أو أرشفة المرتجعات');

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
        abort_if(!auth()->user()?->can('trash.access'), 403, 'غير مصرح لك باسترجاع المرتجعات المحذوفة');

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
            ->when($this->fromDate, fn($q) => $q->whereDate('return_date', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('return_date', '<=', $this->toDate))
            ->latest('return_date');

        return view('livewire.return-index', [
            'returns'      => $query->paginate(15),
            'trashedCount' => ReturnDocument::onlyTrashed()->count(),
        ])->layout('components.layouts.app', ['title' => 'سجل المرتجعات']);
    }
}
