<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Exception;

class PurchaseIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = 'confirmed'; // confirmed, cancelled, all
    public ?string $fromDate = null;
    public ?string $toDate = null;

    public bool $showDetailsModal = false;
    public ?Purchase $selectedPurchase = null;

    public function mount()
    {
        abort_if(!auth()->user()?->can('purchases.view'), 403, 'غير مصرح لك بعرض فواتير المشتريات');
    }

    public function openDetailsModal($purchaseId)
    {
        $this->selectedPurchase = Purchase::with(['supplier', 'items.item', 'additionalExpenses.payment', 'user', 'store'])
            ->findOrFail($purchaseId);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedPurchase = null;
    }

    public function cancelPurchase($purchaseId, PurchaseService $purchaseService)
    {
        abort_if(!auth()->user()?->can('purchases.delete'), 403, 'غير مصرح لك بإلغاء فواتير المشتريات');

        try {
            $purchase = Purchase::findOrFail($purchaseId);
            $num = $purchase->purchase_number;
            
            $purchaseService->cancelPurchase($purchase, 'إلغاء من قبل المستخدم');

            session()->flash('success', "تم إلغاء فاتورة المشتريات رقم {$num} وعكس الكميات من المخزون بنجاح.");
            $this->dispatch('swal:toast', [
                'icon'  => 'success',
                'title' => "تم إلغاء فاتورة المشتريات {$num} وعكس أثرها المخزني والمالي بنجاح!"
            ]);
        } catch (Exception $e) {
            $this->dispatch('swal:toast', [
                'icon'  => 'error', 
                'title' => $e->getMessage()
            ]);
        }
    }

    public function restorePurchase($purchaseId, PurchaseService $purchaseService)
    {
        abort_if(!auth()->user()?->can('purchases.create'), 403, 'غير مصرح لك باستعادة فواتير المشتريات');

        try {
            $purchase = Purchase::findOrFail($purchaseId);
            $num = $purchase->purchase_number;

            $purchaseService->restorePurchase($purchase);

            session()->flash('success', "تم استعادة فاتورة المشتريات رقم {$num} وإعادة إيداع بضاعتها بالمخزون بنجاح.");
            $this->dispatch('swal:toast', [
                'icon'  => 'success',
                'title' => "تم استعادة فاتورة المشتريات {$num} وإرجاع المخزون بنجاح!"
            ]);
        } catch (Exception $e) {
            $this->dispatch('swal:toast', [
                'icon'  => 'error', 
                'title' => $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        $query = Purchase::query()
            ->with(['supplier', 'items.item', 'user', 'store'])
            ->when($this->filterStatus !== 'all', fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->search, function ($q) {
                $q->where('purchase_number', 'like', "%{$this->search}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('items.item', fn($i) => $i->where('name', 'like', "%{$this->search}%"));
            })
            ->when($this->fromDate, fn($q) => $q->whereDate('purchase_date', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('purchase_date', '<=', $this->toDate))
            ->latest('purchase_date')
            ->latest('id');

        return view('livewire.purchase-index', [
            'purchases'      => $query->paginate(15),
            'confirmedCount' => Purchase::where('status', 'confirmed')->count(),
            'cancelledCount' => Purchase::where('status', 'cancelled')->count(),
        ])->layout('components.layouts.app', ['title' => 'سجل فواتير المشتريات والتوريد']);
    }
}
