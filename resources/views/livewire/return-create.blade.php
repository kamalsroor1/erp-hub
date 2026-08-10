<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>🔄 تسجيل مستند مرتجع (مبيعات / مشتريات)</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">إرجاع البضاعة للمخزن بدقة الميزان وعكس الأثر المالي على حساب العميل أو المورد</p>
        </div>
    </div>

    @if($errorMessage)
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs">
        {{ $errorMessage }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Items & Search -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Search Item -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3 shadow-sm">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">ابحث عن الصنف المراد إرجاعه:</label>
                <input 
                    type="text" 
                    wire:model.live.debounce.150ms="searchQuery" 
                    placeholder="ابحث باسم الصنف أو الباركود..." 
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500"
                >

                @if(count($searchResults) > 0)
                <div class="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl overflow-hidden divide-y divide-slate-200 dark:divide-slate-800 shadow-2xl">
                    @foreach($searchResults as $res)
                    <div wire:click="addItem({{ $res->id }})" class="p-3 hover:bg-slate-100 dark:hover:bg-slate-800/80 cursor-pointer flex items-center justify-between transition-colors">
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-200 text-xs">{{ $res->name }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">كود: {{ $res->code }} | رصيد حالي: {{ number_format($res->current_stock, 3) }} {{ $res->unit }}</div>
                        </div>
                        <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                            {{ number_format($res->selling_price, 2) }} ج.م
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Items Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300">
                    أصناف المرتجع ({{ count($items) }} صنف)
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3">#</th>
                                <th class="p-3">اسم الصنف</th>
                                <th class="p-3 w-36">الكمية المرتجعة</th>
                                <th class="p-3 w-36">سعر الوحدة</th>
                                <th class="p-3 w-32">الإجمالي</th>
                                <th class="p-3 text-center w-10">حذف</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($items as $idx => $line)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20">
                                <td class="p-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="p-3">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $line['name'] }}</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">الوحدة: {{ $line['unit'] }}</div>
                                </td>
                                <td class="p-3">
                                    <input 
                                        type="number" 
                                        step="0.001" 
                                        min="0.001" 
                                        wire:model.live.debounce.250ms="items.{{ $idx }}.quantity" 
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1.5 text-center text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400 focus:outline-none focus:border-emerald-500"
                                    >
                                </td>
                                <td class="p-3">
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        wire:model.live.debounce.250ms="items.{{ $idx }}.unit_price" 
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
                                    لم يتم إدراج أصناف مرتجع بعد.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Col: Type, Party & Reason -->
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3 shadow-sm">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">نوع المرتجع:</label>
                <div class="grid grid-cols-2 gap-2">
                    <button 
                        type="button" 
                        wire:click="$set('return_type', 'sales_return')" 
                        class="py-2 text-xs font-bold rounded-xl border transition-all cursor-pointer {{ $return_type === 'sales_return' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-700 dark:text-emerald-400' : 'bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}"
                    >
                        مرتجع مبيعات (من عميل)
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('return_type', 'purchase_return')" 
                        class="py-2 text-xs font-bold rounded-xl border transition-all cursor-pointer {{ $return_type === 'purchase_return' ? 'bg-amber-500/20 border-amber-500 text-amber-700 dark:text-amber-400' : 'bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}"
                    >
                        مرتجع مشتريات (لمورد)
                    </button>
                </div>

                @if($return_type === 'sales_return')
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">العميل المسترجع منه:</label>
                    <select wire:model.live="customer_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white">
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} (رصيد: {{ number_format($c->current_balance, 2) }} ج.م)</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">المورد المسترجع إليه:</label>
                    <select wire:model.live="supplier_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white">
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->company_name ?? 'شركة' }})</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">تاريخ المرتجع:</label>
                    <input type="date" wire:model="return_date" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">سبب المرتجع وملاحظات:</label>
                    <input type="text" wire:model="reason" placeholder="مثال: زيادة في الطلبية أو عيب تغليف..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white">
                </div>

                <button 
                    type="button" 
                    wire:click="saveReturn" 
                    class="w-full mt-3 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 text-xs transition-all active:scale-95 cursor-pointer"
                >
                    🔄 تأكيد المرتجع وتحديث المخزن والرصيد
                </button>
            </div>
        </div>
    </div>
</div>
