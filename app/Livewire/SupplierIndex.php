<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supplier;

class SupplierIndex extends Component
{
    use WithPagination;

    public $search = '';

    public $showSupplierModal = false;
    public $isEditMode = false;
    public $editSupplierId = null;

    public $name = '';
    public $company_name = '';
    public $phone = '';
    public $address = '';
    public $notes = '';

    protected $rules = [
        'name' => 'required|string|max:255',
    ];

    public function openCreateModal()
    {
        $this->reset(['name', 'company_name', 'phone', 'address', 'notes', 'editSupplierId']);
        $this->isEditMode = false;
        $this->showSupplierModal = true;
    }

    public function openEditModal($id)
    {
        $supplier = Supplier::findOrFail($id);
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
            $supplier = Supplier::findOrFail($this->editSupplierId);
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

    public function render()
    {
        $query = Supplier::active()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('company_name', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%");
            })
            ->orderBy('name');

        return view('livewire.supplier-index', [
            'suppliers' => $query->paginate(15),
        ])->layout('components.layouts.app', ['title' => 'دليل الموردين']);
    }
}
