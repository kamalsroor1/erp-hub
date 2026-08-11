<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Item;
use App\Services\StockService;
use App\Livewire\Traits\RequiresAuth;
use Illuminate\Validation\Rule;

class ItemIndex extends Component
{
    use WithPagination, RequiresAuth;

    public $search = '';
    public $filterStock = 'all'; // all, low, out
    public $filterCategory = 'all';
    public $filterStatus = 'active'; // active, trashed, all

    // Modal Properties (Create & Edit)
    public $showModal = false;
    public $isEditMode = false;
    public $editItemId = null;

    public $code = '';
    public $name = '';
    public $category = 'بن وتوليفات';
    public $unit = 'كجم';
    public $current_stock = '0.000';
    public $cost_price = '0.000';
    public $selling_price = '0.000';
    public $min_stock_level = '5.000';
    public $notes = '';

    protected function rules()
    {
        return [
            'code'            => [
                'required',
                'max:50',
                Rule::unique('items', 'code')->whereNull('deleted_at')->ignore($this->editItemId),
            ],
            'name'            => 'required|string|max:255',
            'category'        => 'nullable|string|max:100',
            'unit'            => 'nullable|string|max:50',
            'current_stock'   => 'required|numeric|min:0',
            'cost_price'      => 'required|numeric|min:0',
            'selling_price'   => 'required|numeric|min:0',
            'min_stock_level' => 'required|numeric|min:0',
        ];
    }

    public function openCreateModal()
    {
        $this->reset(['code', 'name', 'notes', 'editItemId']);
        $this->isEditMode = false;
        $this->code = 'ITM-' . rand(1000, 9999);
        $this->category = 'بن وتوليفات';
        $this->unit = 'كجم';
        $this->current_stock = '0.000';
        $this->cost_price = '0.000';
        $this->selling_price = '0.000';
        $this->min_stock_level = '5.000';
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $item = Item::withTrashed()->findOrFail($id);
        $this->isEditMode = true;
        $this->editItemId = $item->id;
        $this->code = $item->code;
        $this->name = $item->name;
        $this->category = $item->category ?: 'بن وتوليفات';
        $this->unit = $item->unit ?: 'كجم';
        $this->current_stock = $item->current_stock;
        $this->cost_price = $item->cost_price;
        $this->selling_price = $item->selling_price;
        $this->min_stock_level = $item->min_stock_level;
        $this->notes = $item->notes ?? '';
        $this->showModal = true;
    }

    public function saveItem(StockService $stockService)
    {
        $this->validate();

        if ($this->isEditMode && $this->editItemId) {
            $item = Item::withTrashed()->findOrFail($this->editItemId);
            $item->update([
                'code'            => $this->code,
                'name'            => $this->name,
                'category'        => $this->category,
                'unit'            => $this->unit,
                'cost_price'      => $this->cost_price,
                'selling_price'   => $this->selling_price,
                'min_stock_level' => $this->min_stock_level,
                'notes'           => $this->notes,
            ]);

            session()->flash('success', "تم تعديل بيانات الصنف [{$item->name}] بنجاح.");
            $this->dispatch('swal:toast', ['icon' => 'success', 'title' => "تم تعديل بيانات الصنف [{$item->name}] بنجاح!"]);
        } else {
            $initialStock = $this->current_stock;

            $item = Item::create([
                'code'              => $this->code,
                'name'              => $this->name,
                'category'          => $this->category,
                'unit'              => $this->unit,
                'current_stock'     => '0.000',
                'cost_price'        => $this->cost_price,
                'weighted_avg_cost' => $this->cost_price,
                'selling_price'     => $this->selling_price,
                'min_stock_level'   => $this->min_stock_level,
                'is_active'         => true,
                'notes'             => $this->notes,
            ]);

            if (bccomp($initialStock, '0.000', 3) > 0) {
                $stockService->depositStock(
                    item: $item,
                    quantity: $initialStock,
                    costPrice: $this->cost_price,
                    depositType: 'opening_balance',
                    reason: 'رصيد أول المدة عند تعريف الصنف'
                );
            }

            session()->flash('success', "تم إضافة الصنف [{$item->name}] بنجاح.");
            $this->dispatch('swal:toast', ['icon' => 'success', 'title' => "تم إضافة الصنف [{$item->name}] بنجاح للمخزون!"]);
        }

        $this->showModal = false;
    }

    public function deleteItem($id)
    {
        $item = Item::findOrFail($id);
        $name = $item->name;
        $item->delete(); // Soft delete

        session()->flash('success', "تم نقل الصنف [{$name}] إلى سلة المحذوفات بنجاح.");
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => "تم أرشفة الصنف [{$name}] بنجاح."]);
    }

    public function restoreItem($id)
    {
        $item = Item::onlyTrashed()->findOrFail($id);
        $item->restore();

        session()->flash('success', "تم استعادة الصنف [{$item->name}] بنجاح.");
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => "تم استعادة الصنف [{$item->name}] بنجاح!"]);
    }

    public function render()
    {
        $baseQuery = match ($this->filterStatus) {
            'trashed' => Item::onlyTrashed(),
            'all'     => Item::withTrashed(),
            default   => Item::active(),
        };

        $query = $baseQuery
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%")
                        ->orWhere('category', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterCategory !== 'all', fn($q) => $q->where('category', $this->filterCategory))
            ->when($this->filterStock === 'low', fn($q) => $q->lowStock())
            ->when($this->filterStock === 'out', fn($q) => $q->where('current_stock', '<=', '0.000'))
            ->orderBy('name');

        return view('livewire.item-index', [
            'items'        => $query->paginate(15),
            'trashedCount' => Item::onlyTrashed()->count(),
        ])->layout('components.layouts.app', ['title' => 'دليل الأصناف والمخزون']);
    }
}
