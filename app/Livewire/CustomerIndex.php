<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use App\Services\PaymentService;

class CustomerIndex extends Component
{
    use WithPagination;

    public $search = '';

    // Quick Add / Edit Customer Modal
    public $showCustomerModal = false;
    public $isEditMode = false;
    public $editCustomerId = null;

    public $name = '';
    public $phone = '';
    public $address = '';
    public $tax_number = '';
    public $notes = '';

    // Quick Payment Voucher Modal
    public $showPaymentModal = false;
    public $selectedCustomerId;
    public $selectedCustomerName = '';
    public $paymentAmount = '0.000';
    public $paymentMethod = 'cash';
    public $paymentNotes = '';

    protected $rules = [
        'name'  => 'required|string|max:255',
        'phone' => 'nullable|string|max:50',
    ];

    public function openCreateModal()
    {
        $this->reset(['name', 'phone', 'address', 'tax_number', 'notes', 'editCustomerId']);
        $this->isEditMode = false;
        $this->showCustomerModal = true;
    }

    public function openEditModal($id)
    {
        $customer = Customer::findOrFail($id);
        $this->isEditMode = true;
        $this->editCustomerId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone ?? '';
        $this->address = $customer->address ?? '';
        $this->tax_number = $customer->tax_number ?? '';
        $this->notes = $customer->notes ?? '';
        $this->showCustomerModal = true;
    }

    public function saveCustomer()
    {
        $this->validate();

        if ($this->isEditMode && $this->editCustomerId) {
            $customer = Customer::findOrFail($this->editCustomerId);
            $customer->update([
                'name'       => $this->name,
                'phone'      => $this->phone,
                'address'    => $this->address,
                'tax_number' => $this->tax_number,
                'notes'      => $this->notes,
            ]);

            session()->flash('success', "تم تعديل بيانات العميل [{$customer->name}] بنجاح.");
            $this->dispatch('swal:toast', ['icon' => 'success', 'title' => "تم تعديل بيانات العميل [{$customer->name}] بنجاح!"]);
        } else {
            $customer = Customer::create([
                'name'            => $this->name,
                'phone'           => $this->phone,
                'address'         => $this->address,
                'tax_number'      => $this->tax_number,
                'current_balance' => '0.000',
                'is_active'       => true,
                'notes'           => $this->notes,
            ]);

            session()->flash('success', "تم إضافة العميل [{$customer->name}] بنجاح.");
            $this->dispatch('swal:toast', ['icon' => 'success', 'title' => "تم إضافة العميل [{$customer->name}] بنجاح!"]);
        }

        $this->showCustomerModal = false;
    }

    public function openPaymentModal($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $this->selectedCustomerId = $customer->id;
        $this->selectedCustomerName = $customer->name;
        $this->paymentAmount = $customer->current_balance;
        $this->paymentMethod = 'cash';
        $this->paymentNotes = 'سداد دفعة من الحساب';
        $this->showPaymentModal = true;
    }

    public function savePayment(PaymentService $paymentService)
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
        ]);

        $paymentService->recordCustomerPayment([
            'customer_id'    => $this->selectedCustomerId,
            'amount'         => $this->paymentAmount,
            'payment_method' => $this->paymentMethod,
            'notes'          => $this->paymentNotes,
        ]);

        $this->showPaymentModal = false;
        session()->flash('success', "تم تسجيل سند القبض بنجاح وتحديث رصيد العميل.");
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => "تم تسجيل سند القبض بنجاح!"]);
    }

    public function render()
    {
        $query = Customer::active()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
                  ->orWhere('address', 'like', "%{$this->search}%");
            })
            ->orderBy('name');

        return view('livewire.customer-index', [
            'customers' => $query->paginate(15),
        ])->layout('components.layouts.app', ['title' => 'دليل العملاء والحسابات']);
    }
}
