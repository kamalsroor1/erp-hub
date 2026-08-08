<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
        <div>
            <h2 class="text-xl font-black text-white flex items-center gap-2">
                <span>👥 دليل العملاء والحسابات</span>
            </h2>
            <p class="text-xs text-slate-400">إدارة بيانات العملاء، الأرصدة التراكمية، وسندات التحصيل</p>
        </div>
        <button wire:click="openCreateModal" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition-all">
            <span>+ إضافة عميل جديد</span>
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
            placeholder="بحث باسم العميل أو رقم الهاتف أو العنوان..." 
            class="w-full sm:w-80 bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"
        >
    </div>

    <!-- Customers Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="p-3.5">اسم العميل</th>
                        <th class="p-3.5">الهاتف</th>
                        <th class="p-3.5">العنوان</th>
                        <th class="p-3.5">الرصيد الحالي (المستحق)</th>
                        <th class="p-3.5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($customers as $c)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="p-3.5 font-bold text-slate-100">{{ $c->name }}</td>
                        <td class="p-3.5 font-mono text-slate-400">{{ $c->phone ?? '—' }}</td>
                        <td class="p-3.5 text-slate-400">{{ $c->address ?? '—' }}</td>
                        <td class="p-3.5 font-mono font-bold {{ bccomp($c->current_balance, '0.000', 3) > 0 ? 'text-amber-400' : 'text-emerald-400' }}">
                            {{ number_format($c->current_balance, 2) }} ج.م
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button 
                                    wire:click="openEditModal({{ $c->id }})" 
                                    title="تعديل بيانات العميل"
                                    class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg text-xs font-bold border border-slate-700 transition-colors inline-flex items-center gap-1"
                                >
                                    <span>✏️ تعديل</span>
                                </button>
                                <a href="{{ route('customers.statement', $c->id) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg text-xs font-bold border border-slate-700 transition-colors">
                                    📑 كشف حساب
                                </a>
                                @if(bccomp($c->current_balance, '0.000', 3) > 0)
                                <button wire:click="openPaymentModal({{ $c->id }})" class="px-2.5 py-1 bg-emerald-600/20 hover:bg-emerald-600 text-emerald-400 hover:text-white rounded-lg text-xs font-bold border border-emerald-500/30 transition-colors">
                                    💵 تحصيل دفعة
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-500">لا يوجد عملاء مطابقين للبحث</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-800">
            {{ $customers->links() }}
        </div>
    </div>

    <!-- Create & Edit Customer Modal -->
    @if($showCustomerModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-base">
                    {{ $isEditMode ? '✏️ تعديل بيانات العميل' : '👥 إضافة عميل جديد' }}
                </h3>
                <button wire:click="$set('showCustomerModal', false)" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <form wire:submit.prevent="saveCustomer" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">اسم العميل:</label>
                    <input type="text" wire:model="name" placeholder="مثال: كافيه البستان / سوبر ماركت النور" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                    @error('name') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">رقم الهاتف:</label>
                        <input type="text" wire:model="phone" placeholder="010XXXXXXXX" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">الرقم الضريبي (إن وجد):</label>
                        <input type="text" wire:model="tax_number" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">العنوان / المنطقة:</label>
                    <input type="text" wire:model="address" placeholder="مثال: القاهرة - التجمع الخامس" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">ملاحظات إضافية:</label>
                    <input type="text" wire:model="notes" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" wire:click="$set('showCustomerModal', false)" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30">
                        {{ $isEditMode ? '💾 حفظ التعديلات' : '➕ إضافة العميل' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Payment Voucher Modal -->
    @if($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-base">سند قبض وتحصيل دفعة</h3>
                <button wire:click="$set('showPaymentModal', false)" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <form wire:submit.prevent="savePayment" class="space-y-4">
                <div>
                    <span class="text-xs text-slate-400">العميل:</span>
                    <div class="font-bold text-emerald-400 text-sm mt-0.5">{{ $selectedCustomerName }}</div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">المبلغ المحصل (ج.م):</label>
                    <input type="number" step="0.01" min="0.01" wire:model="paymentAmount" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-bold text-emerald-400">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">طريقة التحصيل:</label>
                    <select wire:model="paymentMethod" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                        <option value="cash">نقداً (كاش بالدرج)</option>
                        <option value="instapay">إنستاباي (InstaPay)</option>
                        <option value="vodafone_cash">فودافون كاش ومحافظ إلكترونية</option>
                        <option value="bank_transfer">تحويل بنكي</option>
                        <option value="cheque">شيك بنكي</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">ملاحظات السند:</label>
                    <input type="text" wire:model="paymentNotes" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" wire:click="$set('showPaymentModal', false)" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30">
                        تسجيل سند القبض
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
