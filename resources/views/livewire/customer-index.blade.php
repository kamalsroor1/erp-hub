<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>👥 دليل العملاء والحسابات</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">إدارة بيانات العملاء، الأرصدة التراكمية، وسندات التحصيل</p>
        </div>
        @can('customers.manage')
        <button wire:click="openCreateModal" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
            <span>➕ إضافة عميل جديد</span>
        </button>
        @endcan
    </div>

    @if (session()->has('success') || $successMessage)
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs flex items-center justify-between">
        <span>✅ {{ session('success') ?? $successMessage }}</span>
        <button wire:click="$set('successMessage', '')" class="text-emerald-500 hover:text-emerald-700 font-bold">✕</button>
    </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="w-full sm:w-80">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="بحث باسم العميل أو رقم الهاتف أو العنوان..." 
                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500"
            >
        </div>
        <div class="flex flex-wrap items-center gap-1.5 text-xs">
            <span class="text-slate-500 dark:text-slate-400 text-[11px] hidden sm:inline">الحالة:</span>
            <button wire:click="$set('filterStatus', 'active')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs {{ $filterStatus === 'active' ? 'bg-emerald-600 text-white border-emerald-500' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">النشطين</button>
            <button wire:click="$set('filterStatus', 'trashed')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs flex items-center gap-1 {{ $filterStatus === 'trashed' ? 'bg-rose-600 text-white border-rose-500' : 'border-slate-200 dark:border-slate-800 text-rose-600 dark:text-rose-400' }}">
                <span>سلة المحذوفات</span>
                @if($trashedCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filterStatus === 'trashed' ? 'bg-white text-rose-600' : 'bg-rose-500/20 text-rose-600' }} font-mono font-bold">{{ $trashedCount }}</span>
                @endif
            </button>
            <button wire:click="$set('filterStatus', 'all')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs {{ $filterStatus === 'all' ? 'bg-slate-700 text-white border-slate-600' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">الكل</button>
        </div>
    </div>

    <!-- Mobile Cards View (< 640px) -->
    <div class="sm:hidden space-y-3">
        @forelse($customers as $c)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 space-y-3 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-2.5">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">{{ $c->name }}</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-mono mt-0.5" dir="ltr">{{ $c->phone ?? 'بدون هاتف' }}</p>
                </div>
                <div class="text-left">
                    <span class="text-[10px] text-slate-400 block">الرصيد:</span>
                    <span class="font-mono font-black text-xs {{ bccomp($c->current_balance, '0.000', 3) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ number_format($c->current_balance, 2) }} ج.م
                    </span>
                </div>
            </div>

            @if($c->address)
            <p class="text-xs text-slate-500 dark:text-slate-400">
                📍 {{ $c->address }}
            </p>
            @endif

            <div class="flex items-center gap-1.5 pt-1">
                @can('customers.manage')
                <button 
                    wire:click="openEditModal({{ $c->id }})" 
                    class="flex-1 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-700 transition-colors text-center cursor-pointer"
                >
                    ✏️ تعديل
                </button>
                @endcan
                @can('customers.statement')
                <a 
                    href="{{ route('customers.statement', $c->id) }}" 
                    class="flex-1 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-700 transition-colors text-center"
                >
                    📑 كشف حساب
                </a>
                @endcan
                @if(bccomp($c->current_balance, '0.000', 3) > 0)
                @can('customers.manage')
                <button 
                    wire:click="openPaymentModal({{ $c->id }})" 
                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-sm transition-colors text-center shrink-0 cursor-pointer"
                >
                    💵 تحصيل
                </button>
                @endcan
                @endif
            </div>
        </div>
        @empty
        <div class="p-8 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-400 text-xs">
            لا يوجد عملاء مطابقين للبحث
        </div>
        @endforelse
    </div>

    <!-- Desktop / Tablet Table View (>= 640px) -->
    <div class="hidden sm:block bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-3.5">اسم العميل</th>
                        <th class="p-3.5">الهاتف</th>
                        <th class="p-3.5">العنوان</th>
                        <th class="p-3.5">الرصيد الحالي (المستحق)</th>
                        <th class="p-3.5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @forelse($customers as $c)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="p-3.5 font-bold text-slate-800 dark:text-slate-100">{{ $c->name }}</td>
                        <td class="p-3.5 font-mono text-slate-500 dark:text-slate-400">{{ $c->phone ?? '—' }}</td>
                        <td class="p-3.5 text-slate-600 dark:text-slate-400">{{ $c->address ?? '—' }}</td>
                        <td class="p-3.5 font-mono font-bold {{ bccomp($c->current_balance, '0.000', 3) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ number_format($c->current_balance, 2) }} ج.م
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($c->trashed())
                                    @can('trash.access')
                                    <button 
                                        wire:click="restoreCustomer({{ $c->id }})" 
                                        title="استعادة العميل"
                                        class="px-2.5 py-1 bg-emerald-500/10 hover:bg-emerald-600 text-emerald-700 dark:text-emerald-400 hover:text-white rounded-lg text-xs font-bold border border-emerald-500/30 transition-colors inline-flex items-center gap-1 cursor-pointer"
                                    >
                                        <span>♻️ استعادة</span>
                                    </button>
                                    @endcan
                                @else
                                    @can('customers.manage')
                                    <button 
                                        wire:click="openEditModal({{ $c->id }})" 
                                        title="تعديل بيانات العميل"
                                        class="px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-bold border border-slate-300 dark:border-slate-700 transition-colors inline-flex items-center gap-1 cursor-pointer"
                                    >
                                        <span>✏️</span>
                                    </button>
                                    @endcan

                                    @can('customers.statement')
                                    <a href="{{ route('customers.statement', $c->id) }}" title="كشف حساب" class="px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-bold border border-slate-300 dark:border-slate-700 transition-colors">
                                        📑
                                    </a>
                                    @endcan

                                    @if(bccomp($c->current_balance, '0.000', 3) > 0)
                                    @can('customers.manage')
                                    <button wire:click="openPaymentModal({{ $c->id }})" title="تحصيل دفعة" class="px-2 py-1 bg-emerald-500/10 hover:bg-emerald-600 text-emerald-700 dark:text-emerald-400 hover:text-white rounded-lg text-xs font-bold border border-emerald-500/30 transition-colors cursor-pointer">
                                        💵
                                    </button>
                                    @endcan
                                    @endif

                                    @can('customers.manage')
                                    <button 
                                        wire:click="deleteCustomer({{ $c->id }})" 
                                        wire:confirm="هل أنت متأكد من نقل هذا العميل لسلة المحذوفات؟"
                                        title="أرشفة العميل"
                                        class="px-2 py-1 bg-rose-500/10 hover:bg-rose-600 text-rose-600 dark:text-rose-400 hover:text-white rounded-lg text-xs font-bold border border-rose-500/20 transition-colors inline-flex items-center gap-1 cursor-pointer"
                                    >
                                        <span>🗑️</span>
                                    </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-400">لا يوجد عملاء مطابقين للبحث</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $customers->links() }}
        </div>
    </div>

    <div class="sm:hidden p-2">
        {{ $customers->links() }}
    </div>

    <!-- Create & Edit Customer Modal -->
    @if($showCustomerModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg p-5 sm:p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-base">
                    {{ $isEditMode ? 'تعديل بيانات العميل' : 'إضافة عميل جديد' }}
                </h3>
                <button wire:click="closeCustomerModal" class="text-slate-400 hover:text-slate-700 dark:hover:text-white font-bold cursor-pointer">✕</button>
            </div>

            @if($errorMessage)
            <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs">
                {{ $errorMessage }}
            </div>
            @endif

            <form wire:submit.prevent="saveCustomer" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم العميل <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="name" placeholder="اسم العميل أو المحل..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                    @error('name') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">رقم الهاتف</label>
                        <input type="text" wire:model="phone" dir="ltr" placeholder="01xxxxxxxxx" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                        @error('phone') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الرصيد الافتتاحي (ج.م)</label>
                        <input type="number" step="0.001" wire:model="opening_balance" {{ $isEditMode ? 'disabled' : '' }} placeholder="0.000" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 disabled:opacity-50">
                        @error('opening_balance') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">العنوان / المنطقة</label>
                    <input type="text" wire:model="address" placeholder="عنوان العميل أو اسم المنطقة..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                    @error('address') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ملاحظات إضافية</label>
                    <textarea wire:model="notes" rows="2" placeholder="أي ملاحظات تخص التعامل مع العميل..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" wire:click="closeCustomerModal" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold cursor-pointer">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 cursor-pointer">
                        {{ $isEditMode ? 'حفظ التعديلات' : 'إضافة العميل' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Payment Collection Modal -->
    @if($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-5 sm:p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">سند تحصيل وقبض نقدية</h3>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">العميل: {{ $selectedCustomerName }}</p>
                </div>
                <button wire:click="closePaymentModal" class="text-slate-400 hover:text-slate-700 dark:hover:text-white font-bold cursor-pointer">✕</button>
            </div>

            @if($errorMessage)
            <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs">
                {{ $errorMessage }}
            </div>
            @endif

            <form wire:submit.prevent="savePayment" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">المبلغ المحصل (ج.م) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.001" wire:model="paymentAmount" placeholder="0.000" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-3 text-sm font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                    @error('paymentAmount') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">طريقة القبض</label>
                        <select wire:model="paymentMethod" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                            <option value="cash">نقداً (في الدرج)</option>
                            <option value="bank_transfer">تحويل بنكي / فودافون كاش</option>
                            <option value="cheque">شيك</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">البيان / ملاحظات السند</label>
                        <input type="text" wire:model="paymentNotes" placeholder="دفعة تحت الحساب / سداد فاتورة..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" wire:click="closePaymentModal" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold cursor-pointer">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 cursor-pointer">
                        تسجيل السند وإيداع الخزينة
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
