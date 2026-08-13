<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>➕ إنشاء إذن تحويل وشحن عهدة بضاعة</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">نقل بضاعة من المخزن الرئيسي إلى عربية التوزيع أو بين الفروع ذرياً</p>
        </div>
        <div>
            <a 
                href="{{ route('stock-transfers') }}" 
                class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-bold transition-all cursor-pointer"
            >
                ← العودة لسجل التحويلات
            </a>
        </div>
    </div>

    @if($errorMessage)
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs flex items-center gap-2">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>{{ $errorMessage }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs space-y-1">
        <div class="font-bold flex items-center gap-1.5">
            <span>⚠️</span>
            <span>يرجى تصحيح الأخطاء التالية لإنشاء إذن التحويل:</span>
        </div>
        <ul class="list-disc list-inside pr-4 space-y-0.5 font-medium">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Items & Quick Catalog -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Quick Add Catalog -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3 shadow-sm">
                <!-- Search Bar -->
                <div class="relative">
                    <input 
                        type="text" 
                        wire:model.live.debounce.150ms="searchQuery" 
                        placeholder="ابحث عن بن برازيلي، محوج، شاي لإضافته للتحويل..." 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500"
                    >
                    <div class="absolute left-3 top-2 text-slate-400 text-xs">🔍</div>
                </div>

                <!-- Catalog Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 pt-1">
                    @forelse($quickCatalog as $prod)
                    @php
                        $avail = $prod->getStockInStore($from_store_id);
                    @endphp
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800/80 hover:border-emerald-500/50 transition-all flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-200 text-xs line-clamp-1">{{ $prod->name }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                المتاح بالمصدر: <b class="font-mono text-emerald-600 dark:text-emerald-400">{{ number_format($avail, 2) }} {{ $prod->unit }}</b>
                            </div>
                        </div>

                        <div class="mt-2 pt-2 border-t border-slate-200 dark:border-slate-900 flex items-center gap-1">
                            <button type="button" wire:click="addItem({{ $prod->id }}, '5.000')" class="flex-1 py-1 rounded bg-slate-200 dark:bg-slate-800 hover:bg-emerald-600 text-slate-700 dark:text-slate-300 hover:text-white text-[10px] font-bold cursor-pointer">
                                +5
                            </button>
                            <button type="button" wire:click="addItem({{ $prod->id }}, '10.000')" class="flex-1 py-1 rounded bg-slate-200 dark:bg-slate-800 hover:bg-emerald-600 text-slate-700 dark:text-slate-300 hover:text-white text-[10px] font-bold cursor-pointer">
                                +10
                            </button>
                            <button type="button" wire:click="addItem({{ $prod->id }}, '25.000')" class="flex-1 py-1 rounded bg-emerald-600 hover:bg-emerald-500 text-white text-[10px] font-black cursor-pointer">
                                +25 (شيكارة)
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-3 p-4 text-center text-slate-400 text-xs">
                        لا توجد أصناف مطابقة
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Transfer Items Table -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-black text-slate-800 dark:text-slate-200">📦 أصناف التحويل والشحن ({{ count($items) }})</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-100/60 dark:bg-slate-950 text-slate-500 font-bold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3">الصنف</th>
                                <th class="p-3 text-center">المتاح بالمصدر</th>
                                <th class="p-3 text-center w-40">الكمية المحولة</th>
                                <th class="p-3 text-center w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($items as $idx => $line)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/40 transition-colors">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $line['name'] }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">كود: {{ $line['code'] }}</div>
                                </td>
                                <td class="p-3 text-center font-mono text-slate-500">
                                    {{ number_format($line['available_stock'], 2) }} {{ $line['unit'] }}
                                </td>
                                <td class="p-3">
                                    <div class="relative flex items-center">
                                        <input 
                                            type="number" 
                                            step="0.001" 
                                            min="0.001" 
                                            wire:model.live.debounce.250ms="items.{{ $idx }}.quantity" 
                                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-center text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400 focus:outline-none focus:border-emerald-500"
                                        >
                                        <span class="absolute left-2 text-[10px] text-slate-400 font-bold">{{ $line['unit'] }}</span>
                                    </div>
                                </td>
                                <td class="p-3 text-center">
                                    <button type="button" wire:click="removeItem({{ $idx }})" class="p-1 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer">🗑️</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-slate-400">
                                    لم يتم إضافة أصناف للتحويل بعد. اختر من القائمة بالأعلى.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Col: Route & Transfer Confirmation -->
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-4 shadow-sm">
                <h3 class="font-black text-slate-900 dark:text-white text-sm border-b border-slate-100 dark:border-slate-800 pb-2">
                    🔄 مسار التحويل والمخازن
                </h3>

                <!-- From Store -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">المصدر (المخزن المحول منه):</label>
                    <select wire:model.live="from_store_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                        @foreach($stores as $st)
                        <option value="{{ $st->id }}" {{ (int)$st->id === (int)$to_store_id ? 'disabled' : '' }}>
                            @if($st->type === 'wholesale_van') 🚚 @elseif($st->type === 'main_warehouse') 🏢 @else 🏬 @endif
                            {{ $st->name }} ({{ $st->code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- To Store -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الوجهة (الفرع/العربية المستلمة):</label>
                    <select wire:model.live="to_store_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-emerald-600 dark:text-emerald-400 focus:outline-none focus:border-emerald-500">
                        @foreach($stores as $st)
                        <option value="{{ $st->id }}" {{ (int)$st->id === (int)$from_store_id ? 'disabled' : '' }}>
                            @if($st->type === 'wholesale_van') 🚚 @elseif($st->type === 'main_warehouse') 🏢 @else 🏬 @endif
                            {{ $st->name }} ({{ $st->code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">تاريخ التحويل:</label>
                    <x-datepicker wire:model="transfer_date" placeholder="تاريخ التحويل" />
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ملاحظات الإذن:</label>
                    <textarea wire:model="notes" rows="2" placeholder="ملاحظات الشحن أو رقم سيارة التوزيع..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button 
                        type="button" 
                        wire:click="saveTransfer" 
                        wire:loading.attr="disabled"
                        class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-lg shadow-emerald-600/20 transition-all cursor-pointer flex items-center justify-center gap-1.5"
                    >
                        <span wire:loading.remove wire:target="saveTransfer">🚀 اعتماد وتحويل البضاعة فوراً</span>
                        <span wire:loading wire:target="saveTransfer">جاري التحويل...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
