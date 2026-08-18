<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supplier;
use App\Services\PaymentService;

class SupplierIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = 'active'; // active, trashed, all

    // Quick Add / Edit Supplier Modal
    public $showSupplierModal = false;
    public $isEditMode = false;
    public $editSupplierId = null;

    public $name = '';
    public $company_name = '';
    public $phone = '';
    public $address = '';
    public $notes = '';

    // Quick Supplier Payment Voucher Modal (سند صرف سداد مديونية)
    public $showPaymentModal = false;
    public $selectedSupplierId;
    public $selectedSupplierName = '';
    public $paymentAmount = '0.000';
    public $paymentMethod = 'cash';
    public $paymentNotes = '';

    protected $rules = [
        'name' => 'required|string|max:255',
    ];

    public function mount()
    {
        abort_if(!auth()->user()?->can('suppliers.manage'), 403, 'غير مصرح لك بإدارة الموردين');
    }

    public function openCreateModal()
    {
        abort_if(!auth()->user()?->can('suppliers.manage'), 403, 'غير مصرح لك بإضافة موردين جدد');
        $this->reset(['name', 'company_name', 'phone', 'address', 'notes', 'editSupplierId']);
        $this->isEditMode = false;
        $this->showSupplierModal = true;
    }

    public function openEditModal($id)
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);
        $this->isEditMode = true;
        $this->editSupplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->company_name = $supplier->company_name ?? '';
        $this->phone = $supplier->phone ?? '';
        $this->address = $supplier->address ?? '';
        $this->notes = $supplier->notes ?? '';
        $this->showSupplierModal = true;
    }

    public function saveSupplier()
    {
        $this->validate();

        if ($this->isEditMode && $this->editSupplierId) {
            $supplier = Supplier::withTrashed()->findOrFail($this->editSupplierId);
            $supplier->update([
                'name'         => $this->name,
                'company_name' => $this->company_name,
                'phone'        => $this->phone,
                'address'      => $this->address,
                'notes'        => $this->notes,
            ]);

            session()->flash('success', "تم تعديل بيانات المورد [{$supplier->name}] بنجاح.");
            $this->dispatch('swal:toast', ['icon' => 'success', 'title' => "تم تعديل بيانات المورد [{$supplier->name}] بنجاح!"]);
        } else {
            $supplier = Supplier::create([
                'name'            => $this->name,
                'company_name'    => $this->company_name,
                'phone'           => $this->phone,
                'address'         => $this->address,
                'current_balance' => '0.000',
                'is_active'       => true,
                'notes'           => $this->notes,
            ]);

            session()->flash('success', "تم إضافة المورد [{$supplier->name}] بنجاح.");
            $this->dispatch('swal:toast', ['icon' => 'success', 'title' => "تم إضافة المورد [{$supplier->name}] بنجاح!"]);
        }

        $this->showSupplierModal = false;
    }

    public function toggleActive($id)
    {
        abort_if(!auth()->user()?->can('suppliers.manage'), 403, 'غير مصرح لك بتعديل بيانات الموردين');
        $supplier = Supplier::withTrashed()->findOrFail($id);
        $supplier->is_active = !$supplier->is_active;
        $supplier->save();

        $state = $supplier->is_active ? 'تفعيل' : 'تعطيل';
        $this->dispatch('swal:toast', [
            'icon'  => 'info',
            'title' => "تم {$state} المورد [{$supplier->name}] بنجاح."
        ]);
    }

    public function deleteSupplier($id)
    {
        abort_if(!auth()->user()?->can('suppliers.manage'), 403, 'غير مصرح لك بحذف الموردين');
        $supplier = Supplier::findOrFail($id);
        $name = $supplier->name;

        $blockers = $supplier->getDeletionBlockers();
        if (!empty($blockers)) {
            $reasons = implode(' • ', $blockers);
            $this->dispatch('swal:toast', [
                'icon'  => 'warning',
                'title' => "⚠️ لا يمكن حذف المورد [{$name}] لوجود معاملات مالية ومشتريات!\nيمكنك تعطيله بدلاً من حذفه."
            ]);
            session()->flash('error', "لا يمكن حذف المورد [{$name}] لوجود قيود: " . implode(' ، ', $blockers) . ". يمكنك تعطيل حسابه بدلاً من الحذف.");
            return;
        }

        $supplier->delete(); // Soft delete only if clean

        session()->flash('success', "تم نقل المورد [{$name}] إلى سلة المحذوفات بنجاح.");
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => "تم نقل المورد [{$name}] إلى سلة المحذوفات بنجاح."]);
    }

    public function restoreSupplier($id)
    {
        abort_if(!auth()->user()?->can('trash.access'), 403, 'غير مصرح لك باسترجاع الموردين المحذوفين');
        $supplier = Supplier::onlyTrashed()->findOrFail($id);
        $supplier->restore();

        session()->flash('success', "تم استعادة المورد [{$supplier->name}] بنجاح.");
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => "تم استعادة المورد [{$supplier->name}] بنجاح!"]);
    }

    public function openPaymentModal($supplierId)
    {
        abort_if(!auth()->user()?->can('suppliers.manage'), 403, 'غير مصرح لك بتسجيل سندات صرف للموردين');
        $supplier = Supplier::withTrashed()->findOrFail($supplierId);
        $this->selectedSupplierId = $supplier->id;
        $this->selectedSupplierName = $supplier->name;
        $this->paymentAmount = $supplier->current_balance;
        $this->paymentMethod = 'cash';
        $this->paymentNotes = 'سداد دفعة من الحساب للمورد';
        $this->showPaymentModal = true;
    }

    public function savePayment(PaymentService $paymentService)
    {
        abort_if(!auth()->user()?->can('suppliers.manage'), 403, 'غير مصرح لك بتسجيل سندات صرف للموردين');
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
        ]);

        $paymentService->recordSupplierPayment([
            'supplier_id'    => $this->selectedSupplierId,
            'amount'         => $this->paymentAmount,
            'payment_method' => $this->paymentMethod,
            'notes'          => $this->paymentNotes,
        ]);

        $this->showPaymentModal = false;
        session()->flash('success', "تم تسجيل سند الصرف بنجاح وتخفيض مديونية المورد.");
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => "تم تسجيل سند الصرف وتخفيض مديونية المورد بنجاح!"]);
    }

    public function render()
    {
        $baseQuery = match ($this->filterStatus) {
            'trashed' => Supplier::onlyTrashed(),
            'all'     => Supplier::withTrashed(),
            default   => Supplier::active(),
        };

        $query = $baseQuery
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('company_name', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%");
            })
            ->orderBy('name');

        return view('livewire.supplier-index', [
            'suppliers'    => $query->paginate(15),
            'trashedCount' => Supplier::onlyTrashed()->count(),
        ])->layout('components.layouts.app', ['title' => 'دليل الموردين']);
    }
}
