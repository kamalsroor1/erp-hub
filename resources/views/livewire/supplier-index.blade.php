<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>🏭 دليل الموردين والشركات</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">إدارة حسابات الشركات والمصانع الموردة ومتابعة مستحقاتهم وسداد الدفعات وتنزيل المديونيات</p>
        </div>
        <button wire:click="openCreateModal" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
            <span>+ إضافة مورد جديد</span>
        </button>
    </div>

    @if (session()->has('success'))
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs flex items-center gap-2">
        <span>✅ {{ session('success') }}</span>
    </div>
    @endif

    <!-- Search Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="بحث باسم المورد أو اسم الشركة أو الهاتف..." 
            class="w-full sm:w-80 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500"
        >
    </div>

    <!-- Mobile Cards View (< 640px) -->
    <div class="sm:hidden space-y-3">
        @forelse($suppliers as $s)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 space-y-3 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-2.5">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">{{ $s->name }}</h3>
                    @if($s->company_name)
                    <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold">{{ $s->company_name }}</p>
                    @endif
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-mono mt-0.5" dir="ltr">{{ $s->phone ?? 'بدون هاتف' }}</p>
                </div>
                <div class="text-left">
                    <span class="text-[10px] text-slate-400 block">المستحق له:</span>
                    <span class="font-mono font-black text-xs {{ bccomp($s->current_balance, '0.000', 3) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ number_format($s->current_balance, 2) }} ج.م
                    </span>
                </div>
            </div>

            @if($s->address)
            <p class="text-xs text-slate-500 dark:text-slate-400">
                📍 {{ $s->address }}
            </p>
            @endif

            <div class="flex items-center gap-1.5 pt-1">
                <button 
                    wire:click="openEditModal({{ $s->id }})" 
                    class="flex-1 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-700 transition-colors text-center cursor-pointer"
                >
                    ✏️ تعديل
                </button>
                <a 
                    href="{{ route('suppliers.statement', $s->id) }}" 
                    class="flex-1 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-700 transition-colors text-center"
                >
                    📑 كشف حساب
                </a>
                @if(bccomp($s->current_balance, '0.000', 3) > 0)
                <button 
                    wire:click="openPaymentModal({{ $s->id }})" 
                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-sm transition-colors text-center shrink-0 cursor-pointer"
                >
                    💵 سداد
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="p-8 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-400 text-xs">
            لا يوجد موردون مطابقون للبحث
        </div>
        @endforelse
    </div>

    <!-- Desktop / Tablet Table View (>= 640px) -->
    <div class="hidden sm:block bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-3.5">اسم المورد</th>
                        <th class="p-3.5">الشركة / المصنع</th>
                        <th class="p-3.5">الهاتف</th>
                        <th class="p-3.5">العنوان</th>
                        <th class="p-3.5">المستحق له (المديونية)</th>
                        <th class="p-3.5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @forelse($suppliers as $s)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="p-3.5 font-bold text-slate-800 dark:text-slate-100">{{ $s->name }}</td>
                        <td class="p-3.5 text-slate-700 dark:text-slate-300">{{ $s->company_name ?? '—' }}</td>
                        <td class="p-3.5 font-mono text-slate-500 dark:text-slate-400">{{ $s->phone ?? '—' }}</td>
                        <td class="p-3.5 text-slate-600 dark:text-slate-400">{{ $s->address ?? '—' }}</td>
                        <td class="p-3.5 font-mono font-bold {{ bccomp($s->current_balance, '0.000', 3) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ number_format($s->current_balance, 2) }} ج.م
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                @if(bccomp($s->current_balance, '0.000', 3) > 0)
                                <button 
                                    wire:click="openPaymentModal({{ $s->id }})" 
                                    title="تسجيل سند صرف وسداد دفعة للمورد لتنزيل المديونية"
                                    class="px-2.5 py-1 bg-emerald-500/10 hover:bg-emerald-600 text-emerald-700 dark:text-emerald-300 hover:text-white rounded-lg text-xs font-bold border border-emerald-500/30 transition-all inline-flex items-center gap-1 cursor-pointer"
                                >
                                    <span>💵 سداد دفعة</span>
                                </button>
                                @endif
                                <button 
                                    wire:click="openEditModal({{ $s->id }})" 
                                    title="تعديل بيانات المورد"
                                    class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-lg text-xs font-bold border border-slate-300 dark:border-slate-700 transition-colors inline-flex items-center gap-1 cursor-pointer"
                                >
                                    <span>✏️ تعديل</span>
                                </button>
                                <a 
                                    href="{{ route('suppliers.statement', $s->id) }}" 
                                    title="كشف حساب المورد"
                                    class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-lg text-xs font-bold border border-slate-300 dark:border-slate-700 transition-colors inline-flex items-center gap-1"
                                >
                                    <span>📑 كشف حساب</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-slate-400">لا يوجد موردون مطابقون للبحث</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $suppliers->links() }}
        </div>
    </div>

    <div class="sm:hidden p-2">
        {{ $suppliers->links() }}
    </div>

    <!-- Create & Edit Supplier Modal -->
    @if($showSupplierModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg p-5 sm:p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-base">
                    {{ $isEditMode ? 'تعديل بيانات المورد' : 'إضافة مورد / شركة جديدة' }}
                </h3>
                <button wire:click="$set('showSupplierModal', false)" class="text-slate-400 hover:text-slate-700 dark:hover:text-white">✕</button>
            </div>

            <form wire:submit.prevent="saveSupplier" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم المورد / المسؤول <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.defer="name" placeholder="اسم الشخص المسؤول..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                        @error('name') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم الشركة / المصنع</label>
                        <input type="text" wire:model.defer="company_name" placeholder="مثال: مطاحن البن الحديثة..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                        @error('company_name') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">رقم الهاتف</label>
                        <input type="text" wire:model.defer="phone" dir="ltr" placeholder="01xxxxxxxxx" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                        @error('phone') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">العنوان / المقر</label>
                        <input type="text" wire:model.defer="address" placeholder="عنوان الشركة أو المصنع..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                        @error('address') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ملاحظات</label>
                    <textarea wire:model.defer="notes" rows="2" placeholder="أي ملاحظات حول شروط التوريد أو الدفع..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" wire:click="$set('showSupplierModal', false)" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold cursor-pointer">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 cursor-pointer">
                        {{ $isEditMode ? 'حفظ التعديلات' : 'إضافة المورد' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Payment Modal (Saddad Daf'a for Supplier) -->
    @if($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-5 sm:p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">سند صرف وسداد دفعة للمورد</h3>
                    <p class="text-xs text-amber-600 dark:text-amber-400 font-bold mt-0.5">المورد: {{ $selectedSupplierName }}</p>
                </div>
                <button wire:click="$set('showPaymentModal', false)" class="text-slate-400 hover:text-slate-700 dark:hover:text-white">✕</button>
            </div>

            <form wire:submit.prevent="savePayment" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">المبلغ المسدد (ج.م) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.001" wire:model.defer="paymentAmount" placeholder="0.000" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-3 text-sm font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                    @error('paymentAmount') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">طريقة الصرف</label>
                        <select wire:model.defer="paymentMethod" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                            <option value="cash">نقداً (من الدرج/الخزينة)</option>
                            <option value="bank_transfer">تحويل بنكي / فودافون كاش</option>
                            <option value="cheque">شيك</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">البيان / ملاحظات</label>
                        <input type="text" wire:model.defer="paymentNotes" placeholder="سداد دفعة توريد..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" wire:click="$set('showPaymentModal', false)" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold cursor-pointer">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 cursor-pointer">
                        تسجيل السند وخصم الخزينة
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
