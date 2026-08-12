<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\StockTransfer;
use App\Models\Store;
use App\Services\StockTransferService;
use App\Livewire\Traits\RequiresAuth;
use Exception;

class StockTransferIndex extends Component
{
    use RequiresAuth;

    public $searchQuery = '';
    public $statusFilter = 'all';
    public $storeFilter = 'all';

    // View Details Modal
    public $showDetailsModal = false;
    public $selectedTransfer = null;

    // Cancel Transfer Modal
    public $showCancelModal = false;
    public $transferToCancel = null;
    public $cancelReason = '';

    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        abort_if(!auth()->user()?->can('transfers.view'), 403, 'غير مصرح لك بعرض أذونات تحويل وشحن البضاعة');
    }

    public function viewDetails($id)
    {
        $this->selectedTransfer = StockTransfer::with(['fromStore', 'toStore', 'user', 'items.item'])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function confirmCancel($id)
    {
        abort_if(!auth()->user()?->can('transfers.create'), 403, 'غير مصرح لك بإلغاء أذونات التحويل');
        $this->transferToCancel = StockTransfer::findOrFail($id);
        $this->cancelReason = '';
        $this->showCancelModal = true;
    }

    public function executeCancel(StockTransferService $transferService)
    {
        abort_if(!auth()->user()?->can('transfers.create'), 403, 'غير مصرح لك بإلغاء أذونات التحويل');
        $this->errorMessage = '';
        try {
            if ($this->transferToCancel) {
                $transferService->cancelTransfer($this->transferToCancel, $this->cancelReason);
                $this->successMessage = "تم إلغاء إذن التحويل رقم [{$this->transferToCancel->transfer_number}] وإرجاع البضاعة لمخزن المصدر بنجاح.";
                $this->showCancelModal = false;
            }
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        $stores = Store::active()->get();

        $transfers = StockTransfer::with(['fromStore', 'toStore', 'user', 'items.item'])
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->storeFilter !== 'all', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('from_store_id', $this->storeFilter)
                        ->orWhere('to_store_id', $this->storeFilter);
                });
            })
            ->when(strlen($this->searchQuery) >= 1, function ($q) {
                $q->where('transfer_number', 'like', "%{$this->searchQuery}%")
                  ->orWhere('notes', 'like', "%{$this->searchQuery}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('livewire.stock-transfer-index', [
            'transfers' => $transfers,
            'stores'    => $stores,
        ])->layout('components.layouts.app', ['title' => 'أذونات شحن وتحويل البضاعة']);
    }
}
