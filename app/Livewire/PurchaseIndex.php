<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Purchase;
use Exception;

class PurchaseIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = 'active'; // active, trashed, all
    public ?string $fromDate = null;
    public ?string $toDate = null;

    public function mount()
    {
        abort_if(!auth()->user()?->can('purchases.view'), 403, 'غير مصرح لك بعرض فواتير المشتريات');
    }

    public function deletePurchase($purchaseId)
    {
        abort_if(!auth()->user()?->can('purchases.delete'), 403, 'غير مصرح لك بأرشفة فواتير المشتريات');

        try {
            $purchase = Purchase::findOrFail($purchaseId);
            $num = $purchase->purchase_number;
            $purchase->delete(); // Soft delete

            session()->flash('success', "تم نقل فاتورة المشتريات رقم {$num} إلى سلة المحذوفات بنجاح.");
            $this->dispatch('swal:toast', [
                'icon'  => 'success',
                'title' => "تم أرشفة فاتورة المشتريات {$num} بنجاح!"
            ]);
        } catch (Exception $e) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $e->getMessage()]);
        }
    }

    public function restorePurchase($purchaseId)
    {
        abort_if(!auth()->user()?->can('trash.access'), 403, 'غير مصرح لك باسترجاع فواتير المشتريات');

        try {
            $purchase = Purchase::onlyTrashed()->findOrFail($purchaseId);
            $purchase->restore();

            session()->flash('success', "تم استعادة فاتورة المشتريات رقم {$purchase->purchase_number} بنجاح.");
            $this->dispatch('swal:toast', [
                'icon'  => 'success',
                'title' => "تم استعادة فاتورة المشتريات {$purchase->purchase_number} بنجاح!"
            ]);
        } catch (Exception $e) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $baseQuery = match ($this->filterStatus) {
            'trashed' => Purchase::onlyTrashed(),
            'all'     => Purchase::withTrashed(),
            default   => Purchase::query(),
        };

        $query = $baseQuery->with(['supplier', 'items.item', 'user', 'store'])
            ->when($this->search, function ($q) {
                $q->where('purchase_number', 'like', "%{$this->search}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('items.item', fn($i) => $i->where('name', 'like', "%{$this->search}%"));
            })
            ->when($this->fromDate, fn($q) => $q->whereDate('purchase_date', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('purchase_date', '<=', $this->toDate))
            ->latest('purchase_date');

        return view('livewire.purchase-index', [
            'purchases'    => $query->paginate(15),
            'trashedCount' => Purchase::onlyTrashed()->count(),
        ])->layout('components.layouts.app', ['title' => 'سجل فواتير المشتريات والتوريد']);
    }
}
