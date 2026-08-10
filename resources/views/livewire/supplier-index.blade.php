<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
        <div>
            <h2 class="text-xl font-black text-white flex items-center gap-2">
                <span>🏭 دليل الموردين والشركات</span>
            </h2>
            <p class="text-xs text-slate-400">إدارة حسابات الشركات والمصانع الموردة ومتابعة مستحقاتهم وسداد الدفعات وتنزيل المديونيات</p>
        </div>
        <button wire:click="openCreateModal" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition-all">
            <span>+ إضافة مورد جديد</span>
        </button>
    </div>

    @if (session()->has('success'))
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
        <span>✅ {{ session('success') }}</span>
    </div>
    @endif

    <!-- Search Bar -->
    <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="بحث باسم المورد أو اسم الشركة أو الهاتف..." 
            class="w-full sm:w-80 bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"
        >
    </div>

    <!-- Suppliers Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="p-3.5">اسم المورد</th>
                        <th class="p-3.5">الشركة / المصنع</th>
                        <th class="p-3.5">الهاتف</th>
                        <th class="p-3.5">العنوان</th>
                        <th class="p-3.5">المستحق له (المديونية)</th>
                        <th class="p-3.5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($suppliers as $s)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="p-3.5 font-bold text-slate-100">{{ $s->name }}</td>
                        <td class="p-3.5 text-slate-300">{{ $s->company_name ?? '—' }}</td>
                        <td class="p-3.5 font-mono text-slate-400">{{ $s->phone ?? '—' }}</td>
                        <td class="p-3.5 text-slate-400">{{ $s->address ?? '—' }}</td>
                        <td class="p-3.5 font-mono font-bold {{ bccomp($s->current_balance, '0.000', 3) > 0 ? 'text-amber-400' : 'text-emerald-400' }}">
                            {{ number_format($s->current_balance, 2) }} ج.م
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                @if(bccomp($s->current_balance, '0.000', 3) > 0)
                                <button 
                                    wire:click="openPaymentModal({{ $s->id }})" 
                                    title="تسجيل سند صرف وسداد دفعة للمورد لتنزيل المديونية"
                                    class="px-2.5 py-1 bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white rounded-lg text-xs font-bold border border-emerald-500/30 transition-all inline-flex items-center gap-1"
                                >
                                    <span>💵 سداد دفعة</span>
                                </button>
                                @endif
                                <button 
                                    wire:click="openEditModal({{ $s->id }})" 
                                    title="تعديل بيانات المورد"
                                    class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg text-xs font-bold border border-slate-700 transition-colors inline-flex items-center gap-1"
                                >
                                    <span>✏️ تعديل</span>
                                </button>
                                <a href="{{ route('suppliers.statement', $s->id) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg text-xs font-bold border border-slate-700 transition-colors">
                                    📑 كشف حساب
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-slate-500">لا يوجد موردون مطابقون للبحث</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-800">
            {{ $suppliers->links() }}
        </div>
    </div>

    <!-- Create & Edit Supplier Modal -->
    @if($showSupplierModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-base">
                    {{ $isEditMode ? '✏️ تعديل بيانات المورد' : '🏭 إضافة مورد جديد' }}
                </h3>
                <button wire:click="$set('showSupplierModal', false)" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <form wire:submit.prevent="saveSupplier" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">اسم المورد / المسؤول:</label>
                    <input type="text" wire:model="name" placeholder="مثال: م. أحمد رضوان" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                    @error('name') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">اسم الشركة / المصنع:</label>
                        <input type="text" wire:model="company_name" placeholder="مثال: شركة الأهرام لاستيراد البن" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">رقم الهاتف:</label>
                        <input type="text" wire:model="phone" placeholder="010XXXXXXXX" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">العنوان / الميناء:</label>
                    <input type="text" wire:model="address" placeholder="مثال: الإسكندرية - ميناء الدخيلة" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">ملاحظات والتوريدات:</label>
                    <input type="text" wire:model="notes" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" wire:click="$set('showSupplierModal', false)" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30">
                        {{ $isEditMode ? '💾 حفظ التعديلات' : '➕ إضافة المورد' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Supplier Payment Voucher Modal (سند صرف سداد مديونية) -->
    @if($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-emerald-400 text-base flex items-center gap-2">
                    <span>💵 سند صرف / سداد مديونية مورد</span>
                </h3>
                <button wire:click="$set('showPaymentModal', false)" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800 text-xs">
                <span class="text-slate-400">المورد:</span>
                <strong class="text-white text-sm block mt-0.5">{{ $selectedSupplierName }}</strong>
            </div>

            <form wire:submit.prevent="savePayment" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">المبلغ المسدد للمورد (ج.م):</label>
                    <input type="number" step="0.001" wire:model="paymentAmount" required class="w-full bg-slate-950 border border-slate-700 rounded-xl p-3 text-sm text-white font-mono font-bold focus:outline-none focus:border-emerald-500">
                    @error('paymentAmount') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">طريقة الدفع:</label>
                    <select wire:model="paymentMethod" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs text-white">
                        <option value="cash">💵 نقدي (خزينة الكاشير)</option>
                        <option value="bank_transfer">🏦 تحويل بنكي / فودافون كاش / إنستاباي</option>
                        <option value="cheque">📝 شيك بنكي</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">ملاحظات / رقم الإيصال:</label>
                    <textarea wire:model="paymentNotes" rows="2" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs text-white" placeholder="مثال: سداد دفعة توريد شكاير بن أخضر"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" wire:click="$set('showPaymentModal', false)" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30">
                        💾 حفظ السند وخصم المديونية
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
