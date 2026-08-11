<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Store;
use App\Models\User;
use App\Livewire\Traits\RequiresAuth;
use Illuminate\Validation\Rule;

class StoreIndex extends Component
{
    use RequiresAuth;

    public $searchQuery = '';
    public $typeFilter = 'all';
    public $statusFilter = 'active'; // active, trashed, all

    // Create / Edit Modal State
    public $showModal = false;
    public $isEditing = false;
    public $editingStoreId = null;

    public $name;
    public $code;
    public $type = 'retail_shop';
    public $phone;
    public $address;
    public $is_active = true;
    public $is_main = false;

    // User Assignment Modal
    public $showUserModal = false;
    public $targetStore = null;
    public $selectedUsers = [];

    public $successMessage = '';
    public $errorMessage = '';

    protected function rules()
    {
        return [
            'name'      => 'required|string|max:255',
            'code'      => [
                'required',
                'string',
                'max:50',
                Rule::unique('stores', 'code')->whereNull('deleted_at')->ignore($this->editingStoreId),
            ],
            'type'      => 'required|in:retail_shop,wholesale_van,main_warehouse',
            'phone'     => 'nullable|string|max:50',
            'address'   => 'nullable|string',
            'is_active' => 'boolean',
            'is_main'   => 'boolean',
        ];
    }

    public function openCreateModal()
    {
        $this->reset(['name', 'code', 'phone', 'address', 'is_main', 'editingStoreId', 'errorMessage', 'successMessage']);
        $this->type = 'retail_shop';
        $this->is_active = true;
        $this->is_main = false;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $store = Store::withTrashed()->findOrFail($id);
        $this->editingStoreId = $store->id;
        $this->name           = $store->name;
        $this->code           = $store->code;
        $this->type           = $store->type;
        $this->phone          = $store->phone;
        $this->address        = $store->address;
        $this->is_active      = (bool)$store->is_active;
        $this->is_main        = (bool)$store->is_main;
        $this->isEditing      = true;
        $this->showModal      = true;
    }

    public function saveStore()
    {
        $this->validate();

        if ($this->is_main) {
            // Unmark other main stores
            Store::where('id', '!=', $this->editingStoreId)->update(['is_main' => false]);
        }

        if ($this->isEditing) {
            $store = Store::withTrashed()->findOrFail($this->editingStoreId);
            $store->update([
                'name'      => $this->name,
                'code'      => $this->code,
                'type'      => $this->type,
                'phone'     => $this->phone,
                'address'   => $this->address,
                'is_active' => $this->is_active,
                'is_main'   => $this->is_main,
            ]);
            $this->successMessage = 'تم تعديل بيانات الفرع بنجاح.';
        } else {
            $store = Store::create([
                'name'      => $this->name,
                'code'      => $this->code,
                'type'      => $this->type,
                'phone'     => $this->phone,
                'address'   => $this->address,
                'is_active' => $this->is_active,
                'is_main'   => $this->is_main,
            ]);

            // Auto-assign admin
            if (auth()->check()) {
                $store->users()->syncWithoutDetaching([auth()->id()]);
            }

            $this->successMessage = 'تم إنشاء الفرع/عربية التوزيع بنجاح.';
        }

        $this->showModal = false;
    }

    public function deleteStore($id)
    {
        $store = Store::findOrFail($id);
        if ($store->is_main) {
            $this->errorMessage = 'لا يمكن حذف أو أرشفة الفرع / المخزن الرئيسي للمؤسسة.';
            return;
        }

        $name = $store->name;
        $store->delete(); // Soft delete

        $this->successMessage = "تم نقل الفرع [{$name}] إلى سلة المحذوفات بنجاح.";
        session()->flash('success', $this->successMessage);
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => $this->successMessage]);
    }

    public function restoreStore($id)
    {
        $store = Store::onlyTrashed()->findOrFail($id);
        $store->restore();

        $this->successMessage = "تم استعادة الفرع [{$store->name}] بنجاح.";
        session()->flash('success', $this->successMessage);
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => $this->successMessage]);
    }

    public function openUserAssignmentModal($id)
    {
        $this->targetStore = Store::withTrashed()->with('users')->findOrFail($id);
        $this->selectedUsers = $this->targetStore->users->pluck('id')->toArray();
        $this->showUserModal = true;
    }

    public function saveUserAssignment()
    {
        if ($this->targetStore) {
            $this->targetStore->users()->sync($this->selectedUsers);
            $this->successMessage = 'تم تحديث تعيينات الموظفين للفرع بنجاح.';
            $this->showUserModal = false;
        }
    }

    public function switchToStore($id)
    {
        $store = Store::where('id', $id)->where('is_active', true)->first();
        if ($store) {
            session(['current_store_id' => $store->id]);
            $this->successMessage = "تم التحويل إلى [{$store->name}] كفرع نشط.";
        }
    }

    public function render()
    {
        $baseQuery = match ($this->statusFilter) {
            'trashed' => Store::onlyTrashed(),
            'all'     => Store::withTrashed(),
            default   => Store::query(),
        };

        $stores = $baseQuery->withCount(['stocks', 'users', 'invoices'])
            ->when($this->typeFilter !== 'all', fn($q) => $q->where('type', $this->typeFilter))
            ->when(strlen($this->searchQuery) >= 1, function ($q) {
                $q->where('name', 'like', "%{$this->searchQuery}%")
                  ->orWhere('code', 'like', "%{$this->searchQuery}%")
                  ->orWhere('phone', 'like', "%{$this->searchQuery}%");
            })
            ->orderBy('is_main', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        $allUsers = User::where('is_active', true)->get();

        return view('livewire.store-index', [
            'stores'       => $stores,
            'allUsers'     => $allUsers,
            'trashedCount' => Store::onlyTrashed()->count(),
        ])->layout('components.layouts.app', ['title' => 'إدارة الفروع وعربات التوزيع']);
    }
}
