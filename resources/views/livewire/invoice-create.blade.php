<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
        <div>
            <h2 class="text-lg font-black text-white flex items-center gap-2">
                <span>☕ كاشير ومبيعات مطحنة البن والشاي (POS)</span>
            </h2>
            <p class="text-xs text-slate-400">بيع دقيق بالوزن (إيداع بالكيلو والشكاير والبيع بربع وثمن ونصف كيلو وبالجرام) مع حساب الكسور لحظياً</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-amber-400 font-bold bg-amber-500/10 border border-amber-500/20 px-3 py-1 rounded-xl">
                ⚖️ بيع بالوزن: 1/8 كجم (125جم) | 1/4 كجم (250جم) | 1/2 كجم (500جم) | بالكيلو والجرام
            </span>
        </div>
    </div>

    @if($errorMessage)
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>{{ $errorMessage }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Quick Product Catalog & Active Items Table -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Category Pills & Live Search -->
            <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800 space-y-3">
                <!-- Search Bar -->
                <div class="relative">
                    <input 
                        type="text" 
                        wire:model.live.debounce.150ms="searchQuery" 
                        placeholder="ابحث عن بن برازيلي، بن محوج، شاي، نسكافيه، حبهان..." 
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"
                        autofocus
                    >
                    <div class="absolute left-3 top-2.5 text-slate-500 text-xs">🔍</div>
                </div>

                <!-- Category Filters -->
                <div class="flex flex-wrap items-center gap-1.5 pt-1 text-[11px]">
                    <button wire:click="$set('selectedCategory', 'all')" class="px-3 py-1 rounded-xl font-bold transition-colors {{ $selectedCategory === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800' }}">
                        الكل
                    </button>
                    <button wire:click="$set('selectedCategory', 'بن وتوليفات')" class="px-3 py-1 rounded-xl font-bold transition-colors {{ $selectedCategory === 'بن وتوليفات' ? 'bg-amber-600 text-white' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800' }}">
                        ☕ بن وتوليفات
                    </button>
                    <button wire:click="$set('selectedCategory', 'شاي وأعشاب')" class="px-3 py-1 rounded-xl font-bold transition-colors {{ $selectedCategory === 'شاي وأعشاب' ? 'bg-teal-600 text-white' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800' }}">
                        🍵 شاي وأعشاب
                    </button>
                    <button wire:click="$set('selectedCategory', 'نسكافيه ومشروبات سريعة')" class="px-3 py-1 rounded-xl font-bold transition-colors {{ $selectedCategory === 'نسكافيه ومشروبات سريعة' ? 'bg-indigo-600 text-white' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800' }}">
                        🥤 نسكافيه ومشروبات
                    </button>
                    <button wire:click="$set('selectedCategory', 'تحبيشات وإضافات')" class="px-3 py-1 rounded-xl font-bold transition-colors {{ $selectedCategory === 'تحبيشات وإضافات' ? 'bg-rose-600 text-white' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800' }}">
                        🌿 حبهان ومستكة
                    </button>
                </div>

                <!-- Quick Product Cards Grid (One-touch addition) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 pt-2">
                    @forelse($quickCatalog as $prod)
                    <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800/80 hover:border-emerald-500/50 transition-all flex flex-col justify-between group">
                        <div>
                            <div class="font-bold text-slate-200 text-xs line-clamp-1 group-hover:text-emerald-400 transition-colors">{{ $prod->name }}</div>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                {{ number_format($prod->selling_price, 2) }} ج.م / {{ $prod->unit }}
                            </div>
                        </div>

                        <!-- Quick Weight Select Buttons -->
                        <div class="mt-2.5 pt-2 border-t border-slate-900 flex items-center gap-1">
                            @if($prod->unit === 'كجم')
                                <button type="button" wire:click="addItem({{ $prod->id }}, '0.125')" title="ثمن كيلو (125 جم)" class="flex-1 py-1 rounded bg-slate-800 hover:bg-amber-600 text-[9px] font-bold text-slate-300 hover:text-white transition-colors">
                                    ثمن (125g)
                                </button>
                                <button type="button" wire:click="addItem({{ $prod->id }}, '0.250')" title="ربع كيلو (250 جم)" class="flex-1 py-1 rounded bg-slate-800 hover:bg-amber-600 text-[9px] font-bold text-slate-300 hover:text-white transition-colors">
                                    ربع (250g)
                                </button>
                                <button type="button" wire:click="addItem({{ $prod->id }}, '0.500')" title="نصف كيلو (500 جم)" class="flex-1 py-1 rounded bg-slate-800 hover:bg-amber-600 text-[9px] font-bold text-slate-300 hover:text-white transition-colors">
                                    نصف (500g)
                                </button>
                                <button type="button" wire:click="addItem({{ $prod->id }}, '1.000')" title="كيلو كامل" class="flex-1 py-1 rounded bg-emerald-700 hover:bg-emerald-600 text-[9px] font-black text-white transition-colors">
                                    1كجم
                                </button>
                            @else
                                <button type="button" wire:click="addItem({{ $prod->id }}, '1.000')" class="w-full py-1 rounded bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white text-[10px] font-bold transition-colors">
                                    + إضافة 1 {{ $prod->unit }}
                                </button>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="col-span-3 p-4 text-center text-slate-500 text-xs">
                        لا توجد أصناف مطابقة للبحث
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Current Invoice Items Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-950/60 border-b border-slate-800 flex items-center justify-between text-xs font-bold text-slate-300">
                    <span>محتويات الفاتورة الحالية ({{ count($items) }} بند)</span>
                    <span class="text-[11px] text-amber-400 font-normal">يمكنك إدخال الوزن بالكسور (مثال 0.250 كجم لربع كيلو أو 0.125 لثمن كيلو)</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                            <tr>
                                <th class="p-3">#</th>
                                <th class="p-3">الصنف</th>
                                <th class="p-3 w-52">الوزن / الكمية بالميزان</th>
                                <th class="p-3 w-28">سعر الوحدة</th>
                                <th class="p-3 w-24">الخصم</th>
                                <th class="p-3 w-28">الإجمالي</th>
                                <th class="p-3 text-center w-10">حذف</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($items as $idx => $line)
                            <tr class="hover:bg-slate-800/20">
                                <td class="p-3 text-slate-500 font-mono">{{ $idx + 1 }}</td>
                                <td class="p-3">
                                    <div class="font-bold text-slate-200">{{ $line['name'] }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        الوحدة: <span class="text-amber-400 font-bold">{{ $line['unit'] }}</span> | الرصيد: {{ number_format($line['current_stock'], 3) }}
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="space-y-1.5">
                                        <div class="relative flex items-center">
                                            <input 
                                                type="number" 
                                                step="0.001" 
                                                min="0.001" 
                                                wire:model.live.debounce.250ms="items.{{ $idx }}.quantity" 
                                                class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2 py-1 text-center text-xs font-mono font-bold text-emerald-400 focus:outline-none focus:border-emerald-500"
                                            >
                                            <span class="absolute left-2 text-[10px] text-slate-400 font-bold">{{ $line['unit'] }}</span>
                                        </div>

                                        <!-- Micro weight adjusters if Kg -->
                                        @if($line['unit'] === 'كجم')
                                        <div class="flex items-center gap-1 justify-center">
                                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '0.125')" title="ثمن كيلو (125 جم)" class="px-1.5 py-0.5 bg-slate-800 hover:bg-amber-600 text-[9px] rounded font-mono text-slate-300 hover:text-white">ثمن 125g</button>
                                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '0.250')" title="ربع كيلو (250 جم)" class="px-1.5 py-0.5 bg-slate-800 hover:bg-amber-600 text-[9px] rounded font-mono text-slate-300 hover:text-white">ربع 250g</button>
                                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '0.500')" title="نصف كيلو (500 جم)" class="px-1.5 py-0.5 bg-slate-800 hover:bg-amber-600 text-[9px] rounded font-mono text-slate-300 hover:text-white">نصف 500g</button>
                                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '1.000')" title="كيلو كامل" class="px-1.5 py-0.5 bg-emerald-950 border border-emerald-800 hover:bg-emerald-800 text-[9px] rounded font-mono text-emerald-300 font-bold">1kg</button>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-3">
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        wire:model.live.debounce.250ms="items.{{ $idx }}.unit_price" 
                                        class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2 py-1 text-center text-xs font-mono text-white focus:outline-none focus:border-emerald-500"
                                    >
                                </td>
                                <td class="p-3">
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        wire:model.live.debounce.250ms="items.{{ $idx }}.discount_amount" 
                                        class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2 py-1 text-center text-xs font-mono text-rose-400 focus:outline-none focus:border-rose-500"
                                    >
                                </td>
                                <td class="p-3 font-mono font-bold text-white">
                                    {{ number_format($line['total_price'], 2) }}
                                </td>
                                <td class="p-3 text-center">
                                    <button wire:click="removeItem({{ $idx }})" class="p-1 text-slate-500 hover:text-rose-400 transition-colors">🗑️</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-500">
                                    لم يتم إضافة أصناف بعد. اختر من القائمة بالأعلى أو بالبحث السريع.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Col: Customer & Instant Financial Checkout -->
        <div class="space-y-4">
            <!-- Customer Selection -->
            <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800 space-y-3">
                <label class="block text-xs font-bold text-slate-300">العميل:</label>
                <select wire:model.live="customer_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}">
                        {{ $c->name }} (رصيد: {{ number_format($c->current_balance, 2) }} ج.م)
                    </option>
                    @endforeach
                </select>

                <!-- Payment Type -->
                <label class="block text-xs font-bold text-slate-300 pt-2">طريقة الدفع:</label>
                <div class="grid grid-cols-3 gap-2">
                    <button 
                        type="button" 
                        wire:click="$set('payment_type', 'cash')" 
                        class="py-2 text-xs font-bold rounded-xl border transition-all {{ $payment_type === 'cash' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400' : 'bg-slate-950 border-slate-800 text-slate-400' }}"
                    >
                        كاش (نقدي)
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('payment_type', 'credit')" 
                        class="py-2 text-xs font-bold rounded-xl border transition-all {{ $payment_type === 'credit' ? 'bg-amber-500/20 border-amber-500 text-amber-400' : 'bg-slate-950 border-slate-800 text-slate-400' }}"
                    >
                        آجل (شكك)
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('payment_type', 'partial')" 
                        class="py-2 text-xs font-bold rounded-xl border transition-all {{ $payment_type === 'partial' ? 'bg-indigo-500/20 border-indigo-500 text-indigo-400' : 'bg-slate-950 border-slate-800 text-slate-400' }}"
                    >
                        دفع جزئي
                    </button>
                </div>

                @if($payment_type === 'partial')
                <div class="pt-2">
                    <label class="block text-xs font-bold text-slate-400 mb-1">المبلغ المدفوع مقدماً:</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        wire:model.live.debounce.250ms="paid_amount" 
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-bold text-white focus:outline-none focus:border-emerald-500"
                    >
                </div>
                @endif
            </div>

            <!-- Financial Checkout Card -->
            <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white border-b border-slate-800 pb-2">الحساب المالي والخصومات</h3>

                <!-- Invoice-level Discount -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">نوع الخصم:</label>
                        <select wire:model.live="discount_type" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2 py-1.5 text-xs text-white">
                            <option value="fixed">مبلغ ثابت (ج.م)</option>
                            <option value="percentage">نسبة مئوية (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">قيمة الخصم:</label>
                        <input type="number" step="0.01" min="0" wire:model.live.debounce.250ms="discount_value" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2 py-1.5 text-xs font-mono text-rose-400 text-center">
                    </div>
                </div>

                <!-- Totals Breakdown -->
                <div class="space-y-2 pt-2 text-xs border-t border-slate-800/80">
                    <div class="flex justify-between text-slate-400">
                        <span>المجموع الفرعي:</span>
                        <span class="font-mono font-bold text-white">{{ number_format($subtotal, 2) }} ج.م</span>
                    </div>
                    @if(bccomp($discount_amount, '0.000', 3) > 0)
                    <div class="flex justify-between text-rose-400">
                        <span>إجمالي الخصم:</span>
                        <span class="font-mono font-bold">-{{ number_format($discount_amount, 2) }} ج.م</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-base font-black text-emerald-400 pt-2 border-t border-slate-800">
                        <span>الصافي المطلوب:</span>
                        <span class="font-mono">{{ number_format($net_total, 2) }} ج.م</span>
                    </div>
                    <div class="flex justify-between text-slate-300">
                        <span>المدفوع:</span>
                        <span class="font-mono font-bold">{{ number_format($paid_amount, 2) }} ج.م</span>
                    </div>
                    <div class="flex justify-between font-bold {{ bccomp($remaining_amount, '0.000', 3) > 0 ? 'text-amber-400' : 'text-slate-400' }}">
                        <span>المتبقي (الآجل):</span>
                        <span class="font-mono">{{ number_format($remaining_amount, 2) }} ج.م</span>
                    </div>
                </div>

                <!-- Action Print Buttons -->
                <div class="space-y-2 pt-4">
                    <button 
                        type="button" 
                        wire:click="saveInvoice('print')" 
                        class="w-full py-3 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 transition-all active:scale-95 text-xs"
                    >
                        <span>🖨️ حفظ وطباعة الفاتورة (F9 أو Ctrl+Enter)</span>
                    </button>
                    <button 
                        type="button" 
                        wire:click="saveInvoice" 
                        class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 flex items-center justify-center gap-2 transition-all"
                    >
                        <span>💾 حفظ الفاتورة فقط (F8)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- POS Specific Keydown Shortcuts -->
    <script>
        document.addEventListener('keydown', function(e) {
            // F9 or Ctrl+Enter: Save and Print
            if (e.key === 'F9' || (e.ctrlKey && e.key === 'Enter')) {
                e.preventDefault();
                @this.call('saveInvoice', 'print');
            }
            // F8: Save only
            if (e.key === 'F8') {
                e.preventDefault();
                @this.call('saveInvoice');
            }
        });
    </script>
</div>
