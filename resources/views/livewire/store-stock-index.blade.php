<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>📦 جرد وأسعار الفروع وعربات التوزيع</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">متابعة رصيد كل صنف في كل محل أو عربية وتعيين أسعار مخصصة لكل فرع</p>
        </div>

        <!-- Store Selector Dropdown -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-600 dark:text-slate-400 hidden sm:inline">الفرع المحدد:</span>
            <select wire:model.live="selectedStoreId" class="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                @foreach($stores as $st)
                <option value="{{ $st->id }}">
                    @if($st->type === 'wholesale_van') 🚚 @elseif($st->type === 'main_warehouse') 🏢 @else 🏬 @endif
                    {{ $st->name }} ({{ $st->code }})
                </option>
                @endforeach
            </select>
        </div>
    </div>

    @if($successMessage)
    <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold flex items-center justify-between">
        <span>✅ {{ $successMessage }}</span>
        <button wire:click="$set('successMessage', '')" class="text-emerald-500 hover:text-emerald-700 font-black cursor-pointer">✕</button>
    </div>
    @endif

    <!-- Search & Filter -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 shadow-sm">
        <div class="w-full lg:w-80 relative">
            <input 
                type="text" 
                wire:model.live.debounce.200ms="searchQuery" 
                placeholder="ابحث باسم الصنف أو الكود..." 
                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500"
            >
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Filter Pills -->
            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-950 p-1 rounded-xl border border-slate-200 dark:border-slate-800 text-xs">
                <button 
                    type="button" 
                    wire:click="$set('stockFilter', 'all')" 
                    class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer {{ $stockFilter === 'all' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm border border-slate-200 dark:border-slate-700' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}"
                >
                    🏢 الكل
                </button>
                <button 
                    type="button" 
                    wire:click="$set('stockFilter', 'in_stock')" 
                    class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer {{ $stockFilter === 'in_stock' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400' }}"
                >
                    📦 متوفر فقط (>0)
                </button>
                <button 
                    type="button" 
                    wire:click="$set('stockFilter', 'low')" 
                    class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer {{ $stockFilter === 'low' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-amber-600 dark:hover:text-amber-400' }}"
                >
                    ⚠️ قرب النفاد
                </button>
                <button 
                    type="button" 
                    wire:click="$set('stockFilter', 'out')" 
                    class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer {{ $stockFilter === 'out' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400' }}"
                >
                    🚫 الرصيد صفر
                </button>
            </div>

            @can('transfers.create')
            <a 
                href="{{ route('stock-transfers.create') }}?to_store_id={{ $selectedStoreId }}" 
                class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black transition-all cursor-pointer flex items-center gap-1 shrink-0"
            >
                <span>➕ شحن بضاعة لهذا الفرع</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Stock Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-100/60 dark:bg-slate-950 text-slate-500 font-bold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-3">الصنف والتصنيف</th>
                        <th class="p-3 text-center">الرصيد في هذا الفرع</th>
                        <th class="p-3 text-center">حد الإنذار</th>
                        <th class="p-3 text-center">السعر الأساسي</th>
                        <th class="p-3 text-center">سعر الفرع المخصص</th>
                        <th class="p-3 text-center">السعر الفعلي للبيع</th>
                        <th class="p-3 text-center w-20">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($stocks as $stk)
                    @php
                        $isLow = $stk->isLowStock();
                    @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/40 transition-colors {{ $isLow ? 'bg-rose-500/5' : '' }}">
                        <td class="p-3">
                            <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $stk->item->name }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">كود: {{ $stk->item->code }} | {{ $stk->item->category }}</div>
                        </td>
                        <td class="p-3 text-center font-mono">
                            <span class="px-2.5 py-1 rounded-xl text-xs font-black {{ $isLow ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400' }}">
                                {{ number_format($stk->quantity, 3) }} {{ $stk->item->unit }}
                            </span>
                        </td>
                        <td class="p-3 text-center font-mono text-slate-500">
                            {{ number_format($stk->min_stock, 1) }} {{ $stk->item->unit }}
                        </td>
                        <td class="p-3 text-center font-mono text-slate-500">
                            {{ number_format($stk->item->selling_price, 2) }} ج.م
                        </td>
                        <td class="p-3 text-center font-mono">
                            @if($stk->custom_selling_price !== null && bccomp((string)$stk->custom_selling_price, '0.000', 3) > 0)
                                <span class="px-2 py-0.5 rounded-lg bg-amber-500/10 text-amber-700 dark:text-amber-300 font-bold border border-amber-500/20">
                                    {{ number_format($stk->custom_selling_price, 2) }} ج.م
                                </span>
                            @else
                                <span class="text-slate-400 text-[10px]">افتراضي (سعر المحل)</span>
                            @endif
                        </td>
                        <td class="p-3 text-center font-mono font-black text-emerald-600 dark:text-emerald-400">
                            {{ number_format($stk->effective_selling_price, 2) }} ج.م
                        </td>
                        <td class="p-3 text-center">
                            @can('items.edit')
                            <button 
                                type="button" 
                                wire:click="openEditModal({{ $stk->id }})" 
                                class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-600 hover:text-white text-slate-700 dark:text-slate-300 text-[11px] font-bold transition-colors cursor-pointer"
                            >
                                ⚙️ ضبط
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">
                            لا توجد أصناف مسجلة في هذا الفرع
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Stock Settings Modal -->
    @if($showEditModal)
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-black text-slate-900 dark:text-white text-sm">
                    ⚙️ ضبط إعدادات وسعر [{{ $editingItemName }}] في [{{ $currentStore?->name }}]
                </h3>
                <button wire:click="$set('showEditModal', false)" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <form wire:submit.prevent="saveStockSettings" class="space-y-3">
                <div class="bg-slate-50 dark:bg-slate-950 p-3 rounded-xl space-y-1 text-xs">
                    <div class="flex justify-between text-slate-500">
                        <span>الرصيد الحالي بالمخزن:</span>
                        <span class="font-mono font-bold text-slate-900 dark:text-white">{{ number_format($editingQuantity, 3) }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">السعر المخصص لهذا الفرع/العربية (ج.م):</label>
                    <input type="number" step="0.01" min="0" wire:model="editingCustomPrice" placeholder="اتركه فارغاً لاستخدام سعر المحل الافتراضي" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                    <span class="text-[10px] text-slate-400">إذا تركت الحقل فارغاً، سيتم بيع الصنف بسعر المحل العام.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">حد إنذار النواقص لهذا الفرع:</label>
                    <input type="number" step="0.1" min="0" wire:model="editingMinStock" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" wire:click="$set('showEditModal', false)" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-md">حفظ الإعدادات</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
