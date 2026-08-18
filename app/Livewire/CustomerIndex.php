<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use App\Services\PaymentService;
use Exception;

class CustomerIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = 'active'; // active, trashed, all

    // Quick Add / Edit Customer Modal
    public $showCustomerModal = false;
    public $isEditMode = false;
    public $editCustomerId = null;

    public $name = '';
    public $phone = '';
    public $address = '';
    public $tax_number = '';
    public $opening_balance = '0.000';
    public $notes = '';

    // Quick Payment Voucher Modal
    public $showPaymentModal = false;
    public $selectedCustomerId;
    public $selectedCustomerName = '';
    public $paymentAmount = '0.000';
    public $paymentMethod = 'cash';
    public $paymentNotes = '';

    public $errorMessage = '';
    public $successMessage = '';

    protected $rules = [
        'name'            => 'required|string|max:255',
        'phone'           => 'nullable|string|max:50',
        'address'         => 'nullable|string|max:500',
        'tax_number'      => 'nullable|string|max:50',
        'opening_balance' => 'nullable|numeric|min:0',
        'notes'           => 'nullable|string',
    ];

    public function mount()
    {
        abort_if(!auth()->user()?->can('customers.manage'), 403, 'غير مصرح لك بإدارة العملاء والحسابات');
    }

    public function openCreateModal()
    {
        abort_if(!auth()->user()?->can('customers.manage'), 403, 'غير مصرح لك بإضافة عملاء جدد');
        $this->reset(['name', 'phone', 'address', 'tax_number', 'opening_balance', 'notes', 'editCustomerId', 'errorMessage', 'successMessage']);
        $this->opening_balance = '0.000';
        $this->isEditMode = false;
        $this->showCustomerModal = true;
    }

    public function closeCustomerModal()
    {
        $this->showCustomerModal = false;
        $this->errorMessage = '';
    }

    public function openEditModal($id)
    {
        $this->reset(['errorMessage', 'successMessage']);
        $customer = Customer::withTrashed()->findOrFail($id);
        $this->isEditMode = true;
        $this->editCustomerId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone ?? '';
        $this->address = $customer->address ?? '';
        $this->tax_number = $customer->tax_number ?? '';
        $this->opening_balance = (string)$customer->current_balance;
        $this->notes = $customer->notes ?? '';
        $this->showCustomerModal = true;
    }

    public function saveCustomer()
    {
        $this->errorMessage = '';
        $this->validate();

        try {
            if ($this->isEditMode && $this->editCustomerId) {
                $customer = Customer::withTrashed()->findOrFail($this->editCustomerId);
                $customer->update([
                    'name'       => $this->name,
                    'phone'      => $this->phone ?: null,
                    'address'    => $this->address ?: null,
                    'tax_number' => $this->tax_number ?: null,
                    'notes'      => $this->notes ?: null,
                ]);

                $this->successMessage = "تم تعديل بيانات العميل [{$customer->name}] بنجاح.";
                session()->flash('success', $this->successMessage);
                $this->dispatch('swal:toast', ['icon' => 'success', 'title' => $this->successMessage]);
            } else {
                $initialBalance = ($this->opening_balance && is_numeric($this->opening_balance)) 
                    ? (string)$this->opening_balance 
                    : '0.000';

                $customer = Customer::create([
                    'name'            => $this->name,
                    'phone'           => $this->phone ?: null,
                    'address'         => $this->address ?: null,
                    'tax_number'      => $this->tax_number ?: null,
                    'current_balance' => $initialBalance,
                    'is_active'       => true,
                    'notes'           => $this->notes ?: null,
                ]);

                $this->successMessage = "تم إضافة العميل [{$customer->name}] بنجاح.";
                session()->flash('success', $this->successMessage);
                $this->dispatch('swal:toast', ['icon' => 'success', 'title' => $this->successMessage]);
            }

            $this->showCustomerModal = false;
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function toggleActive($id)
    {
        abort_if(!auth()->user()?->can('customers.manage'), 403, 'غير مصرح لك بتعديل بيانات العملاء');
        $customer = Customer::withTrashed()->findOrFail($id);
        $customer->is_active = !$customer->is_active;
        $customer->save();

        $state = $customer->is_active ? 'تفعيل' : 'تعطيل';
        $this->dispatch('swal:toast', [
            'icon'  => 'info',
            'title' => "تم {$state} العميل [{$customer->name}] بنجاح."
        ]);
    }

    public function deleteCustomer($id)
    {
        abort_if(!auth()->user()?->can('customers.manage'), 403, 'غير مصرح لك بحذف العملاء');
        $customer = Customer::findOrFail($id);
        $name = $customer->name;

        $blockers = $customer->getDeletionBlockers();
        if (!empty($blockers)) {
            $reasons = implode(' • ', $blockers);
            $this->dispatch('swal:toast', [
                'icon'  => 'warning',
                'title' => "⚠️ لا يمكن حذف العميل [{$name}] لوجود معاملات مالية!\nيمكنك تعطيله بدلاً من حذفه."
            ]);
            $this->errorMessage = "لا يمكن حذف العميل [{$name}] لوجود قيود: " . implode(' ، ', $blockers) . ". يمكنك تعطيل الحساب بدلاً من الحذف.";
            return;
        }

        $customer->delete(); // Soft delete only if clean

        $this->successMessage = "تم نقل العميل [{$name}] إلى سلة المحذوفات بنجاح.";
        session()->flash('success', $this->successMessage);
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => $this->successMessage]);
    }

    public function restoreCustomer($id)
    {
        abort_if(!auth()->user()?->can('trash.access'), 403, 'غير مصرح لك باسترجاع العملاء المحذوفين');
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->restore();

        $this->successMessage = "تم استعادة العميل [{$customer->name}] بنجاح.";
        session()->flash('success', $this->successMessage);
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => $this->successMessage]);
    }

    public function openPaymentModal($customerId)
    {
        $customer = Customer::withTrashed()->findOrFail($customerId);
        $this->selectedCustomerId = $customer->id;
        $this->selectedCustomerName = $customer->name;
        $this->paymentAmount = (string)$customer->current_balance;
        $this->paymentMethod = 'cash';
        $this->paymentNotes = 'سداد دفعة من الحساب';
        $this->errorMessage = '';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->errorMessage = '';
    }

    public function savePayment(PaymentService $paymentService)
    {
        $this->errorMessage = '';
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
        ]);

        try {
            $paymentService->recordCustomerPayment([
                'customer_id'    => $this->selectedCustomerId,
                'amount'         => $this->paymentAmount,
                'payment_method' => $this->paymentMethod,
                'notes'          => $this->paymentNotes,
            ]);

            $this->showPaymentModal = false;
            $this->successMessage = "تم تسجيل سند القبض بنجاح وتحديث رصيد العميل.";
            session()->flash('success', $this->successMessage);
            $this->dispatch('swal:toast', ['icon' => 'success', 'title' => $this->successMessage]);
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        $baseQuery = match ($this->filterStatus) {
            'trashed' => Customer::onlyTrashed(),
            'all'     => Customer::withTrashed(),
            default   => Customer::active(),
        };

        $query = $baseQuery
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
                  ->orWhere('address', 'like', "%{$this->search}%");
            })
            ->orderBy('name');

        return view('livewire.customer-index', [
            'customers'    => $query->paginate(15),
            'trashedCount' => Customer::onlyTrashed()->count(),
        ])->layout('components.layouts.app', ['title' => 'دليل العملاء والحسابات']);
    }
}
