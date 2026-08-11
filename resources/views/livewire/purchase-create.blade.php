<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>📥 فاتورة شراء بضاعة وتوريد للمخزن</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">إضافة البضاعة المشتراة بالميزان (كجم / جرام) أو القطعة، تحديث متوسط التكلفة، وإثبات حساب المورد</p>
        </div>
    </div>

    @if($errorMessage)
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs">
        {{ $errorMessage }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Item Search and Items Table -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Search & Add Item -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3 shadow-sm">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">بحث سريع عن صنف لإضافته للتوريد:</label>
                <div class="relative">
                    <input 
                        type="text" 
                        wire:model.live.debounce.150ms="searchQuery" 
                        placeholder="ابحث عن بن أخضر، شاي، نسكافيه، حبهان..." 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500"
                    >
                </div>

                @if(count($searchResults) > 0)
                <div class="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl overflow-hidden divide-y divide-slate-200 dark:divide-slate-800 shadow-2xl">
                    @foreach($searchResults as $res)
                    <div wire:click="addItem({{ $res->id }})" class="p-3 hover:bg-slate-100 dark:hover:bg-slate-800/80 cursor-pointer flex items-center justify-between transition-colors">
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-200 text-xs">{{ $res->name }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">كود: {{ $res->code }} | الوحدة: {{ $res->unit }} | رصيد حالي: {{ number_format($res->current_stock, 3) }}</div>
                        </div>
                        <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                            سعر التكلفة الحالي: {{ number_format($res->cost_price, 2) }} ج.م
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Items Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>بنود التوريد ({{ count($items) }} صنف)</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 font-normal">أدخل الكمية المشتراة بالميزان أو الوحدة بدقة</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3">#</th>
                                <th class="p-3">الصنف</th>
                                <th class="p-3 w-48">الكمية المشتراة بالميزان (الوحدة)</th>
                                <th class="p-3 w-32">سعر تكلفة الوحدة</th>
                                <th class="p-3 w-28">إجمالي السطر</th>
                                <th class="p-3 text-center w-10">حذف</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($items as $idx => $line)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20">
                                <td class="p-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="p-3">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $line['name'] }}</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">الوحدة المعتمدة: <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $line['unit'] }}</span></div>
                                </td>
                                <td class="p-3">
                                    <div class="space-y-1">
                                        <div class="relative">
                                            <input 
                                                type="number" 
                                                step="0.001" 
                                                min="0.001" 
                                                wire:model.live.debounce.250ms="items.{{ $idx }}.quantity" 
                                                placeholder="الكمية المشتراة" 
                                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1.5 text-center text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400 focus:outline-none focus:border-emerald-500"
                                            >
                                            <span class="absolute left-2 top-1.5 text-[10px] text-slate-400 font-bold">{{ $line['unit'] }}</span>
                                        </div>

                                        <!-- Quick Bulk Weight Presets for Coffee & Tea -->
                                        @if($line['unit'] === 'كجم')
                                        <div class="flex items-center gap-1 justify-center">
                                            <button type="button" wire:click="setLineQuantity({{ $idx }}, '5.000')" class="px-1 py-0.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-[9px] rounded font-mono text-slate-700 dark:text-slate-300 cursor-pointer">5kg</button>
                                            <button type="button" wire:click="setLineQuantity({{ $idx }}, '10.000')" class="px-1 py-0.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-[9px] rounded font-mono text-slate-700 dark:text-slate-300 cursor-pointer">10kg</button>
                                            <button type="button" wire:click="setLineQuantity({{ $idx }}, '25.000')" class="px-1 py-0.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-[9px] rounded font-mono text-slate-700 dark:text-slate-300 cursor-pointer">25kg</button>
                                            <button type="button" wire:click="setLineQuantity({{ $idx }}, '50.000')" title="شيكارة / جوال بن 50 كجم" class="px-1.5 py-0.5 bg-emerald-100 dark:bg-emerald-950 border border-emerald-300 dark:border-emerald-800 hover:bg-emerald-600 text-[9px] rounded font-mono text-emerald-800 dark:text-emerald-300 hover:text-white font-bold cursor-pointer">50kg شكارة</button>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-3">
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        wire:model.live.debounce.250ms="items.{{ $idx }}.cost_price" 
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1.5 text-center text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500"
                                    >
                                </td>
                                <td class="p-3 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($line['total_price'], 2) }}
                                </td>
                                <td class="p-3 text-center">
                                    <button wire:click="removeItem({{ $idx }})" class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors cursor-pointer">🗑️</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">
                                    لم يتم إضافة أصناف بعد. ابحث في الخانة بالأعلى لإدراج الأصناف الموردة.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Col: Supplier and Calculations -->
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3 shadow-sm">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">المخزن / الفرع المستلم:</label>
                    <select wire:model.live="store_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white">
                        @foreach($stores as $st)
                        <option value="{{ $st->id }}">
                            @if($st->type === 'wholesale_van') 🚚 @elseif($st->type === 'main_warehouse') 🏢 @else 🏬 @endif
                            {{ $st->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">المورد:</label>
                    <select wire:model.live="supplier_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white">
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->company_name ?? 'شركة' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">رقم فاتورة المورد (المرجع):</label>
                    <input type="text" wire:model="supplier_invoice_ref" placeholder="مثال: فاتورة المورد #982" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الخصم المكتسب من المورد:</label>
                    <input type="number" step="0.01" min="0" wire:model.live.debounce.250ms="discount_amount" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-rose-600 dark:text-rose-400">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">المبلغ المسدد للمورد:</label>
                    <input type="number" step="0.01" min="0" wire:model.live.debounce.250ms="paid_amount" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-900 dark:text-white">
                </div>
            </div>

            <!-- Financial Totals Card -->
            <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3 shadow-sm">
                <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span>إجمالي التوريد:</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ number_format($subtotal, 2) }} ج.م</span>
                </div>
                <div class="flex justify-between text-xs text-rose-600 dark:text-rose-400">
                    <span>الخصم المكتسب:</span>
                    <span class="font-mono font-bold">-{{ number_format($discount_amount, 2) }} ج.م</span>
                </div>
                <div class="flex justify-between text-base font-black text-emerald-600 dark:text-emerald-400 pt-2 border-t border-slate-200 dark:border-slate-800">
                    <span>الصافي المطلوب للمورد:</span>
                    <span class="font-mono">{{ number_format($net_total, 2) }} ج.م</span>
                </div>
                <div class="flex justify-between text-xs font-bold {{ bccomp($remaining_amount, '0.000', 3) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400' }}">
                    <span>المتبقي للمورد (آجل):</span>
                    <span class="font-mono">{{ number_format($remaining_amount, 2) }} ج.م</span>
                </div>

                <button 
                    type="button" 
                    wire:click="savePurchase" 
                    class="w-full mt-4 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 text-xs transition-all active:scale-95 cursor-pointer"
                >
                    📥 تأكيد التوريد وإضافة الكمية للمخزون
                </button>
            </div>
        </div>
    </div>
</div>
