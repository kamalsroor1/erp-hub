<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Traits\RequiresAuth;

#[Layout('components.layouts.app')]
#[Title('سجل المصروفات والنثريات | سرور كوفي')]
class ExpenseIndex extends Component
{
    use WithPagination, RequiresAuth;

    public string $search = '';
    public string $filterCategory = 'all';
    public ?string $fromDate = null;
    public ?string $toDate = null;

    // Modal state
    public bool $showModal = false;
    public bool $isEditMode = false;
    public ?int $editExpenseId = null;

    // Form fields
    public string $category = 'شنط وأكياس';
    public string $title = '';
    public string $amount = '0.000';
    public string $expense_date = '';
    public string $payment_method = 'cash';
    public string $notes = '';

    public array $quickCategories = [
        'شنط وأكياس',
        'أكواب ورقية وبلاستيكية',
        'لاصق وشرائط تغليف',
        'بوفيه وضيافة',
        'صيانة مطاحن ومعدات',
        'إيجار وكهرباء ومرافق',
        'نثريات ومصاريف تشغيل',
    ];

    public function mount()
    {
        $this->fromDate = now()->startOfMonth()->toDateString();
        $this->toDate = now()->toDateString();
        $this->expense_date = now()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'category'       => 'required|string|max:100',
            'title'          => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0.01',
            'expense_date'   => 'required|date',
            'payment_method' => 'required|string|max:50',
            'notes'          => 'nullable|string|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [
            'category.required'     => 'يرجى اختيار تصنيف المصروف.',
            'title.required'        => 'يرجى إدخال اسم البند / بيان الصرف (مثل: شراء شنط أو أكواب).',
            'amount.required'       => 'يرجى تحديد المبلغ المصروف.',
            'amount.min'            => 'المبلغ يجب أن يكون أكبر من الصفر.',
            'expense_date.required' => 'يرجى تحديد تاريخ المصروف.',
        ];
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['title', 'notes', 'editExpenseId']);
        $this->isEditMode = false;
        $this->category = 'شنط وأكياس';
        $this->amount = '0.000';
        $this->expense_date = now()->toDateString();
        $this->payment_method = 'cash';
        $this->showModal = true;
    }

    public function selectQuickCategory(string $cat)
    {
        $this->category = $cat;
        if (empty($this->title)) {
            $this->title = 'شراء ' . $cat;
        }
    }

    public function openEditModal(int $id)
    {
        $this->resetValidation();
        $expense = Expense::findOrFail($id);
        $this->isEditMode = true;
        $this->editExpenseId = $expense->id;
        $this->category = $expense->category;
        $this->title = $expense->title;
        $this->amount = (string)$expense->amount;
        $this->expense_date = $expense->expense_date->format('Y-m-d');
        $this->payment_method = $expense->payment_method;
        $this->notes = $expense->notes ?? '';
        $this->showModal = true;
    }

    public function saveExpense()
    {
        $this->validate();

        if ($this->isEditMode && $this->editExpenseId) {
            $expense = Expense::findOrFail($this->editExpenseId);
            $expense->update([
                'category'       => $this->category,
                'title'          => $this->title,
                'amount'         => $this->amount,
                'expense_date'   => $this->expense_date,
                'payment_method' => $this->payment_method,
                'notes'          => $this->notes,
            ]);

            $this->dispatch('swal:toast', [
                'type'  => 'success',
                'title' => 'تم تعديل المصروف!',
                'text'  => "تم تحديث بيان المصروف [{$expense->title}] بنجاح."
            ]);
        } else {
            $prefix = 'EXP-' . date('Ymd');
            $count = Expense::whereDate('created_at', now()->toDateString())->count() + 1;
            $expenseNumber = $prefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $expense = Expense::create([
                'expense_number' => $expenseNumber,
                'category'       => $this->category,
                'title'          => $this->title,
                'amount'         => $this->amount,
                'expense_date'   => $this->expense_date,
                'payment_method' => $this->payment_method,
                'user_id'        => Auth::id() ?? 1,
                'notes'          => $this->notes,
            ]);

            $this->dispatch('swal:toast', [
                'type'  => 'success',
                'title' => 'تم تسجيل المصروف!',
                'text'  => "تم إضافة بند المصروف [{$expense->title}] بمبلغ " . number_format($expense->amount, 2) . " ج.م بنجاح."
            ]);
        }

        $this->showModal = false;
        $this->reset(['editExpenseId', 'title', 'amount', 'notes']);
    }

    public function deleteExpense(int $id)
    {
        $expense = Expense::findOrFail($id);
        $title = $expense->title;
        $expense->delete();

        $this->dispatch('swal:toast', [
            'type'  => 'success',
            'title' => 'تم حذف المصروف!',
            'text'  => "تم إزالة بيان المصروف [{$title}] بنجاح."
        ]);
    }

    public function render()
    {
        $query = Expense::with('user')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('title', 'like', "%{$this->search}%")
                        ->orWhere('expense_number', 'like', "%{$this->search}%")
                        ->orWhere('category', 'like', "%{$this->search}%")
                        ->orWhere('notes', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterCategory !== 'all', fn($q) => $q->where('category', $this->filterCategory))
            ->when($this->fromDate, fn($q) => $q->whereDate('expense_date', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('expense_date', '<=', $this->toDate));

        $totalExpenses = (clone $query)->sum('amount') ?: '0.000';
        $expensesCount = (clone $query)->count();

        return view('livewire.expense-index', [
            'expenses'      => $query->latest('expense_date')->paginate(15),
            'totalExpenses' => $totalExpenses,
            'expensesCount' => $expensesCount,
        ]);
    }
}
