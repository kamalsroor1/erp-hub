<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>🚚 أذونات شحن وتحويل البضاعة بين المخازن والعربيات</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">سجل عمليات شحن وتفريغ البضاعة بين المخزن المركزي ومحلات التجزئة وعربات التوزيع</p>
        </div>
        <div>
            <a 
                href="{{ route('stock-transfers.create') }}" 
                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-lg shadow-emerald-600/20 transition-all cursor-pointer flex items-center gap-1.5"
            >
                <span>➕ إنشاء إذن تحويل / شحن عهدة</span>
            </a>
        </div>
    </div>

    @if($successMessage)
    <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold flex items-center justify-between">
        <span>✅ {{ $successMessage }}</span>
        <button wire:click="$set('successMessage', '')" class="text-emerald-500 hover:text-emerald-700 font-black cursor-pointer">✕</button>
    </div>
    @endif

    @if($errorMessage)
    <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-400 text-xs font-bold flex items-center justify-between">
        <span>❌ {{ $errorMessage }}</span>
        <button wire:click="$set('errorMessage', '')" class="text-rose-500 hover:text-rose-700 font-black cursor-pointer">✕</button>
    </div>
    @endif

    <!-- Search & Filters -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-sm">
        <div class="w-full sm:w-72 relative">
            <input 
                type="text" 
                wire:model.live.debounce.200ms="searchQuery" 
                placeholder="ابحث برقم الإذن أو الملاحظات..." 
                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500"
            >
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <select wire:model.live="storeFilter" class="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                <option value="all">كل الفروع والعربيات</option>
                @foreach($stores as $st)
                <option value="{{ $st->id }}">{{ $st->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="statusFilter" class="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                <option value="all">كل الحالات</option>
                <option value="confirmed">معتمد ومحول</option>
                <option value="cancelled">ملغي</option>
            </select>
        </div>
    </div>

    <!-- Transfers Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-100/60 dark:bg-slate-950 text-slate-500 font-bold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-3">رقم الإذن والتاريخ</th>
                        <th class="p-3">من (المصدر)</th>
                        <th class="p-3">إلى (الوجهة)</th>
                        <th class="p-3 text-center">عدد الأصناف</th>
                        <th class="p-3 text-center">الحالة</th>
                        <th class="p-3">المسؤول</th>
                        <th class="p-3 text-center w-24">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($transfers as $trf)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/40 transition-colors">
                        <td class="p-3">
                            <div class="font-mono font-black text-slate-900 dark:text-white text-xs">{{ $trf->transfer_number }}</div>
                            <div class="text-[10px] text-slate-400">{{ $trf->transfer_date->format('Y-m-d') }}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-bold text-slate-800 dark:text-slate-200">
                                @if($trf->fromStore->type === 'wholesale_van') 🚚 @elseif($trf->fromStore->type === 'main_warehouse') 🏢 @else 🏬 @endif
                                {{ $trf->fromStore->name }}
                            </div>
                        </td>
                        <td class="p-3">
                            <div class="font-bold text-emerald-600 dark:text-emerald-400">
                                @if($trf->toStore->type === 'wholesale_van') 🚚 @elseif($trf->toStore->type === 'main_warehouse') 🏢 @else 🏬 @endif
                                {{ $trf->toStore->name }}
                            </div>
                        </td>
                        <td class="p-3 text-center font-mono font-bold">
                            <span class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $trf->items->count() }} صنف
                            </span>
                        </td>
                        <td class="p-3 text-center">
                            @if($trf->status === 'confirmed')
                                <span class="px-2.5 py-1 rounded-xl bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold border border-emerald-500/20">معتمد ومحول</span>
                            @else
                                <span class="px-2.5 py-1 rounded-xl bg-rose-500/10 text-rose-700 dark:text-rose-400 font-bold border border-rose-500/20">ملغي</span>
                            @endif
                        </td>
                        <td class="p-3 text-slate-500 text-[11px]">
                            {{ $trf->user->name }}
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button 
                                    type="button" 
                                    wire:click="viewDetails({{ $trf->id }})" 
                                    class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-indigo-600 hover:text-white text-slate-600 dark:text-slate-400 transition-colors cursor-pointer"
                                    title="عرض التفاصيل والأصناف"
                                >
                                    👁️
                                </button>
                                @if($trf->status === 'confirmed')
                                <button 
                                    type="button" 
                                    wire:click="confirmCancel({{ $trf->id }})" 
                                    class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-rose-600 hover:text-white text-slate-600 dark:text-slate-400 transition-colors cursor-pointer"
                                    title="إلغاء التحويل وإرجاع البضاعة"
                                >
                                    ❌
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">
                            لا توجد أذونات تحويل مسجلة
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $transfers->links() }}
        </div>
    </div>

    <!-- Transfer Details Modal -->
    @if($showDetailsModal && $selectedTransfer)
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 max-w-lg w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="font-black text-slate-900 dark:text-white text-base">
                        📄 إذن تحويل رقم: <span class="font-mono">{{ $selectedTransfer->transfer_number }}</span>
                    </h3>
                    <div class="text-[11px] text-slate-400 mt-0.5">التاريخ: {{ $selectedTransfer->transfer_date->format('Y-m-d') }} | المسؤول: {{ $selectedTransfer->user->name }}</div>
                </div>
                <button wire:click="$set('showDetailsModal', false)" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <!-- Route Info -->
            <div class="grid grid-cols-2 gap-3 bg-slate-50 dark:bg-slate-950 p-3 rounded-xl text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px]">المصدر (المخزن المحول منه):</span>
                    <span class="font-black text-slate-800 dark:text-slate-200">{{ $selectedTransfer->fromStore->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">الوجهة (الفرع/العربية المستلمة):</span>
                    <span class="font-black text-emerald-600 dark:text-emerald-400">{{ $selectedTransfer->toStore->name }}</span>
                </div>
            </div>

            <!-- Items List -->
            <div class="space-y-1.5 max-h-56 overflow-y-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-100 dark:bg-slate-950 text-slate-500 font-bold">
                        <tr>
                            <th class="p-2">الصنف</th>
                            <th class="p-2 text-center">الكمية المحولة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($selectedTransfer->items as $it)
                        <tr>
                            <td class="p-2 font-bold text-slate-800 dark:text-slate-200">
                                {{ $it->item->name }}
                            </td>
                            <td class="p-2 text-center font-mono font-black text-emerald-600 dark:text-emerald-400">
                                {{ number_format($it->quantity, 3) }} {{ $it->item->unit }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($selectedTransfer->notes)
            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 text-xs text-slate-600 dark:text-slate-400">
                <b>ملاحظات:</b> {{ $selectedTransfer->notes }}
            </div>
            @endif

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="button" wire:click="$set('showDetailsModal', false)" class="px-5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold">إغلاق</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Cancel Confirmation Modal -->
    @if($showCancelModal && $transferToCancel)
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-rose-500/30 max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-black text-rose-600 dark:text-rose-400 text-sm">
                    ⚠️ تأكيد إلغاء إذن التحويل رقم [{{ $transferToCancel->transfer_number }}]
                </h3>
                <button wire:click="$set('showCancelModal', false)" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <p class="text-xs text-slate-600 dark:text-slate-400">
                عند إلغاء إذن التحويل، سيتم <b>خصم البضاعة من الفرع/العربية المستلمة وإعادتها فوراً للمخزن المصدر</b>.
            </p>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">سبب الإلغاء (اختياري):</label>
                <input type="text" wire:model="cancelReason" placeholder="مثلاً: خطأ في البنود، رجوع العربية..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-rose-500">
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" wire:click="$set('showCancelModal', false)" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold">تراجع</button>
                <button type="button" wire:click="executeCancel" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-black shadow-md">تأكيد الإلغاء وإرجاع المخزون</button>
            </div>
        </div>
    </div>
    @endif
</div>
