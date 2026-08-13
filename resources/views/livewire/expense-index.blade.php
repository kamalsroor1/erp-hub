<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900/60 p-4 sm:p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-tajawal flex items-center gap-2.5">
                <span>💸 المصروفات والنثريات وتكلفة التشغيل</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                سجل مشتريات الخامات الاستهلاكية (شنط، أكواب، لاصق)، والمصاريف الإدارية، والإيجار، والنثريات
            </p>
        </div>
        <button
            wire:click="openCreateModal"
            class="px-5 py-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold rounded-2xl text-xs sm:text-sm shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 transition-all transform active:scale-95 cursor-pointer"
        >
            <span>➕ تسجيل مصروف جديد</span>
        </button>
    </div>

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">إجمالي المصروفات (الفترة المحددة)</p>
                    <p class="text-2xl sm:text-3xl font-black text-rose-600 dark:text-rose-400 font-mono mt-2" dir="ltr">
                        {{ number_format($totalExpenses, 2) }} <span class="text-xs text-slate-500 dark:text-slate-400 font-sans">ج.م</span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl">
                    💸
                </div>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full mt-4 overflow-hidden">
                <div class="bg-rose-500 h-full w-full"></div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">عدد عمليات الصرف</p>
                    <p class="text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400 font-mono mt-2" dir="ltr">
                        {{ number_format($expensesCount) }} <span class="text-xs text-slate-500 dark:text-slate-400 font-sans">سند</span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl">
                    📝
                </div>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full mt-4 overflow-hidden">
                <div class="bg-amber-500 h-full w-full"></div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm sm:col-span-2 lg:col-span-1 flex flex-col justify-center">
            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-2">أصناف وخامات سريعة شائعة</p>
            <div class="flex flex-wrap gap-1.5">
                <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-[11px] text-slate-700 dark:text-slate-300 font-bold border border-slate-200 dark:border-slate-700">🛍️ شنط وأكياس</span>
                <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-[11px] text-slate-700 dark:text-slate-300 font-bold border border-slate-200 dark:border-slate-700">☕ أكواب ورقية</span>
                <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-[11px] text-slate-700 dark:text-slate-300 font-bold border border-slate-200 dark:border-slate-700">📦 لاصق وتغليف</span>
                <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-[11px] text-slate-700 dark:text-slate-300 font-bold border border-slate-200 dark:border-slate-700">⚙️ صيانة مطاحن</span>
            </div>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 sm:p-5 shadow-sm space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">بحث في المصروفات:</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="ابحث باسم البند، رقم السند، أو الملاحظات..."
                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white text-xs placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:outline-none"
                >
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">الحالة والأرشيف:</label>
                <div class="flex items-center gap-1">
                    <button wire:click="$set('filterStatus', 'active')" class="flex-1 py-2 rounded-xl font-bold transition-colors cursor-pointer text-xs {{ $filterStatus === 'active' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">النشطة</button>
                    <button wire:click="$set('filterStatus', 'trashed')" class="flex-1 py-2 rounded-xl font-bold transition-colors cursor-pointer text-xs flex items-center justify-center gap-1 {{ $filterStatus === 'trashed' ? 'bg-rose-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-rose-600 dark:text-rose-400' }}">
                        <span>المحذوفات</span>
                        @if($trashedCount > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filterStatus === 'trashed' ? 'bg-white text-rose-600' : 'bg-rose-500/20 text-rose-600' }} font-mono font-bold">{{ $trashedCount }}</span>
                        @endif
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">التصنيف:</label>
                <select
                    wire:model.live="filterCategory"
                    class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-slate-100"
                >
                    <option class="bg-white dark:bg-slate-900 text-slate-900 dark:text-white" value="all">كل التصنيفات</option>
                    @foreach($quickCategories as $cat)
                        <option class="bg-white dark:bg-slate-900 text-slate-900 dark:text-white" value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">📅 من تاريخ:</label>
                <input
                    type="date"
                    wire:model.live="fromDate"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white text-xs font-mono focus:ring-2 focus:ring-amber-500 focus:outline-none cursor-pointer"
                >
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">📅 إلى تاريخ:</label>
                <input
                    type="date"
                    wire:model.live="toDate"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white text-xs font-mono focus:ring-2 focus:ring-amber-500 focus:outline-none cursor-pointer"
                >
            </div>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950/80 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-4">رقم السند</th>
                        <th class="px-5 py-4">التاريخ</th>
                        <th class="px-5 py-4">التصنيف</th>
                        <th class="px-5 py-4">بيان الصرف / الصنف</th>
                        <th class="px-5 py-4">المبلغ المسدد</th>
                        <th class="px-5 py-4">طريقة الدفع</th>
                        <th class="px-5 py-4">المستخدم</th>
                        <th class="px-5 py-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                    @forelse($expenses as $exp)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-4 font-mono font-bold text-amber-600 dark:text-amber-400" dir="ltr">
                            {{ $exp->expense_number }}
                        </td>
                        <td class="px-5 py-4 font-mono text-slate-600 dark:text-slate-300">
                            {{ $exp->expense_date->format('Y-m-d') }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-[11px] font-bold border border-slate-200 dark:border-slate-700">
                                {{ $exp->category }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $exp->title }}</div>
                            @if($exp->notes)
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $exp->notes }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-mono font-black text-rose-600 dark:text-rose-400 text-sm" dir="ltr">
                            {{ number_format($exp->amount, 2) }} ج.م
                        </td>
                        <td class="px-5 py-4 text-slate-700 dark:text-slate-300">
                            @if($exp->payment_method === 'cash')
                                💵 نقدي (كاشير)
                            @elseif($exp->payment_method === 'bank_transfer')
                                🏦 تحويل / فودافون كاش
                            @else
                                📝 شيك
                            @endif
                        </td>
                        <td class="px-5 py-4 text-slate-500 dark:text-slate-400">
                            {{ $exp->user->name ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($exp->trashed())
                                <button
                                    wire:click="restoreExpense({{ $exp->id }})"
                                    class="px-2.5 py-1.5 bg-emerald-500/10 hover:bg-emerald-600 text-emerald-700 dark:text-emerald-400 hover:text-white rounded-xl text-xs font-bold border border-emerald-500/30 transition-colors inline-flex items-center gap-1 cursor-pointer"
                                    title="استعادة المصروف"
                                >
                                    <span>♻️ استعادة</span>
                                </button>
                                @else
                                <button
                                    wire:click="openEditModal({{ $exp->id }})"
                                    class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-amber-600 dark:text-amber-400 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-700 transition-colors cursor-pointer"
                                    title="تعديل المصروف"
                                >
                                    ✏️ تعديل
                                </button>
                                <button
                                    wire:click="deleteExpense({{ $exp->id }})"
                                    wire:confirm="هل أنت متأكد من أرشفة بيان المصروف [{{ $exp->title }}] ونقله لسلة المحذوفات؟"
                                    class="px-2.5 py-1.5 bg-rose-500/10 hover:bg-rose-600 text-rose-600 hover:text-white rounded-xl text-xs font-bold border border-rose-500/30 transition-all cursor-pointer"
                                    title="أرشفة المصروف"
                                >
                                    🗑️
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            لا توجد مصروفات مسجلة تطابق خيارات البحث.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($expenses->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-950/40">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    <!-- Create / Edit Expense Modal -->
    @if ($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-5 relative">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white font-tajawal flex items-center gap-2">
                    <span>{{ $isEditMode ? '✏️ تعديل بيان المصروف' : '➕ تسجيل مصروف / نثريات جديدة' }}</span>
                </h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-lg">
                    ✕
                </button>
            </div>

            <!-- Quick Category Touch Tiles -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">اختر التصنيف بلمسة واحدة:</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($quickCategories as $cat)
                        <button
                            type="button"
                            wire:click="selectQuickCategory('{{ $cat }}')"
                            class="h-11 px-3 rounded-2xl text-xs font-black transition-all border cursor-pointer flex items-center justify-center text-center {{ $category === $cat ? 'bg-amber-500 text-white border-amber-400 shadow-md shadow-amber-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:bg-slate-200' }}"
                        >
                            {{ $cat }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Modal Form -->
            <form wire:submit.prevent="saveExpense" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        بيان الصرف / الصنف المشترى <span class="text-rose-500">*</span>
                    </label>
                    <input
                        wire:model="title"
                        type="text"
                        required
                        placeholder="مثال: شراء 500 كيس شنط مقاس وسط + 2 بكرة لاصق"
                        class="w-full h-11 px-4 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-amber-500 focus:outline-none"
                    >
                    @error('title') <span class="text-xs text-rose-500 mt-1 block font-bold">{{ $message }}</span> @enderror
                </div>

                <!-- Touch Quick Amounts & Amount Input -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        المبلغ المدفوع (ج.م) <span class="text-rose-500">*</span>
                    </label>

                    <!-- Quick Touch Amount Buttons -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                        @foreach([50, 100, 200, 500, 1000, 2000] as $amt)
                        <button 
                            type="button" 
                            wire:click="selectQuickAmount({{ $amt }})" 
                            class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-amber-600 hover:text-white text-xs font-mono font-bold text-slate-700 dark:text-slate-300 transition-colors cursor-pointer border border-slate-200 dark:border-slate-700"
                        >
                            {{ $amt }}
                        </button>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <input
                                wire:model="amount"
                                type="number"
                                step="0.001"
                                required
                                placeholder="0.00"
                                class="w-full h-11 px-4 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white text-sm font-mono font-bold focus:ring-2 focus:ring-amber-500 focus:outline-none"
                            >
                            @error('amount') <span class="text-xs text-rose-500 mt-1 block font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <input
                                wire:model="expense_date"
                                type="date"
                                required
                                class="w-full h-11 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white text-xs font-mono font-bold focus:ring-2 focus:ring-amber-500 focus:outline-none"
                            >
                            @error('expense_date') <span class="text-xs text-rose-500 mt-1 block font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">طريقة الدفع:</label>
                    <select
                        wire:model="payment_method"
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"
                    >
                        <option value="cash">💵 نقدي من خزينة الكاشير</option>
                        <option value="bank_transfer">🏦 تحويل بنكي / فودافون كاش / إنستاباي</option>
                        <option value="cheque">📝 شيك بنكي</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">ملاحظات إضافية:</label>
                    <textarea
                        wire:model="notes"
                        rows="2"
                        placeholder="أي تفاصيل أو اسم المحل / المورد..."
                        class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button
                        type="button"
                        wire:click="$set('showModal', false)"
                        class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-all cursor-pointer"
                    >
                        إلغاء
                    </button>
                    <button
                        type="submit"
                        class="px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-black rounded-xl text-xs shadow-lg shadow-amber-500/20 transition-all transform active:scale-95 cursor-pointer"
                    >
                        {{ $isEditMode ? '💾 حفظ التعديلات' : '➕ إضافة المصروف' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
