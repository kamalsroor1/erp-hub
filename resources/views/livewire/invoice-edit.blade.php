<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>✏️ تعديل فاتورة مبيعات: {{ $invoice_number }}</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">تعديل الأصناف، الأوزان، الأسعار، أو طريقة السداد مع إعادة موازنة المخزون وحساب العميل تلقائياً</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('invoices.show', $invoice_id) }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-all border border-slate-300 dark:border-slate-700">
                ← إلغاء ورجوع للفاتورة
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
            <span>يرجى تصحيح الأخطاء التالية:</span>
        </div>
        <ul class="list-disc list-inside pr-4 space-y-0.5 font-medium">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Quick Product Catalog & Active Items Table -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Category Pills & Live Search -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3 shadow-sm">
                <!-- Search Bar -->
                <div class="relative">
                    <input 
                        type="text" 
                        wire:model.live.debounce.150ms="searchQuery" 
                        placeholder="ابحث عن بن برازيلي، بن محوج، شاي، نسكافيه، حبهان لإضافته للفاتورة..." 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-amber-500"
                    >
                    <div class="absolute left-3 top-2.5 text-slate-400 text-xs">🔍</div>
                </div>

                <!-- Category Filters -->
                <div class="flex flex-wrap items-center gap-1.5 pt-1 text-[11px]">
                    <button wire:click="$set('selectedCategory', 'all')" class="px-3 py-1 rounded-xl font-bold transition-colors cursor-pointer {{ $selectedCategory === 'all' ? 'bg-amber-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                        الكل
                    </button>
                    <button wire:click="$set('selectedCategory', 'بن وتوليفات')" class="px-3 py-1 rounded-xl font-bold transition-colors cursor-pointer {{ $selectedCategory === 'بن وتوليفات' ? 'bg-amber-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                        ☕ بن وتوليفات
                    </button>
                    <button wire:click="$set('selectedCategory', 'شاي وأعشاب')" class="px-3 py-1 rounded-xl font-bold transition-colors cursor-pointer {{ $selectedCategory === 'شاي وأعشاب' ? 'bg-teal-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                        🍵 شاي وأعشاب
                    </button>
                    <button wire:click="$set('selectedCategory', 'نسكافيه ومشروبات سريعة')" class="px-3 py-1 rounded-xl font-bold transition-colors cursor-pointer {{ $selectedCategory === 'نسكافيه ومشروبات سريعة' ? 'bg-indigo-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                        🥤 نسكافيه ومشروبات
                    </button>
                    <button wire:click="$set('selectedCategory', 'تحبيشات وإضافات')" class="px-3 py-1 rounded-xl font-bold transition-colors cursor-pointer {{ $selectedCategory === 'تحبيشات وإضافات' ? 'bg-rose-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                        🌿 حبهان ومستكة
                    </button>
                </div>

                <!-- Quick Product Cards Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 pt-2">
                    @forelse($quickCatalog as $prod)
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800/80 hover:border-amber-500/50 transition-all flex flex-col justify-between group">
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-200 text-xs line-clamp-1 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">{{ $prod->name }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                                {{ number_format($prod->selling_price, 2) }} ج.م / {{ $prod->unit }}
                            </div>
                        </div>

                        <!-- Quick Weight Select Buttons -->
                        <div class="mt-2.5 pt-2 border-t border-slate-200 dark:border-slate-900 flex items-center gap-1">
                            @if($prod->unit === 'كجم')
                                <button type="button" wire:click="addItem({{ $prod->id }}, '0.125')" title="ثمن كيلو (125 جم)" class="flex-1 py-1 rounded bg-slate-200 dark:bg-slate-800 hover:bg-amber-600 text-[9px] font-bold text-slate-700 dark:text-slate-300 hover:text-white transition-colors cursor-pointer">
                                    ثمن (125g)
                                </button>
                                <button type="button" wire:click="addItem({{ $prod->id }}, '0.250')" title="ربع كيلو (250 جم)" class="flex-1 py-1 rounded bg-slate-200 dark:bg-slate-800 hover:bg-amber-600 text-[9px] font-bold text-slate-700 dark:text-slate-300 hover:text-white transition-colors cursor-pointer">
                                    ربع (250g)
                                </button>
                                <button type="button" wire:click="addItem({{ $prod->id }}, '0.500')" title="نصف كيلو (500 جم)" class="flex-1 py-1 rounded bg-slate-200 dark:bg-slate-800 hover:bg-amber-600 text-[9px] font-bold text-slate-700 dark:text-slate-300 hover:text-white transition-colors cursor-pointer">
                                    نصف (500g)
                                </button>
                                <button type="button" wire:click="addItem({{ $prod->id }}, '1.000')" title="كيلو كامل" class="flex-1 py-1 rounded bg-amber-600 hover:bg-amber-500 text-[9px] font-black text-white transition-colors cursor-pointer">
                                    1كجم
                                </button>
                            @else
                                <button type="button" wire:click="addItem({{ $prod->id }}, '1.000')" class="w-full py-1 rounded bg-amber-500/10 hover:bg-amber-500 text-amber-700 dark:text-amber-300 hover:text-white text-[10px] font-bold transition-colors cursor-pointer">
                                    + إضافة 1 {{ $prod->unit }}
                                </button>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="col-span-3 p-4 text-center text-slate-400 text-xs">
                        لا توجد أصناف مطابقة للبحث
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Current Invoice Items Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>محتويات الفاتورة الحالية ({{ count($items) }} بند)</span>
                    <span class="text-[11px] text-amber-600 dark:text-amber-400 font-normal">تعديل مباشر على الأوزان والكميات والأسعار</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
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
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($items as $idx => $line)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20">
                                <td class="p-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="p-3">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $line['name'] }}</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">
                                        الوحدة: <span class="text-amber-600 dark:text-amber-400 font-bold">{{ $line['unit'] }}</span> | الرصيد: {{ number_format($line['current_stock'], 3) }}
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
                                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-center text-xs font-mono font-bold text-amber-600 dark:text-amber-400 focus:outline-none focus:border-amber-500"
                                            >
                                            <span class="absolute left-2 text-[10px] text-slate-400 font-bold">{{ $line['unit'] }}</span>
                                        </div>

                                        <!-- Micro weight adjusters if Kg -->
                                        @if($line['unit'] === 'كجم')
                                        <div class="flex items-center gap-1 justify-center">
                                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '0.125')" title="ثمن كيلو (125 جم)" class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 hover:bg-amber-600 text-[9px] rounded font-mono text-slate-700 dark:text-slate-300 hover:text-white cursor-pointer">ثمن 125g</button>
                                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '0.250')" title="ربع كيلو (250 جم)" class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 hover:bg-amber-600 text-[9px] rounded font-mono text-slate-700 dark:text-slate-300 hover:text-white cursor-pointer">ربع 250g</button>
                                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '0.500')" title="نصف كيلو (500 جم)" class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 hover:bg-amber-600 text-[9px] rounded font-mono text-slate-700 dark:text-slate-300 hover:text-white cursor-pointer">نصف 500g</button>
                                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '1.000')" title="كيلو كامل" class="px-1.5 py-0.5 bg-amber-100 dark:bg-amber-950 border border-amber-300 dark:border-amber-800 hover:bg-amber-600 text-[9px] rounded font-mono text-amber-800 dark:text-amber-300 hover:text-white font-bold cursor-pointer">1kg</button>
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
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-center text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:border-amber-500"
                                    >
                                </td>
                                <td class="p-3">
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        wire:model.live.debounce.250ms="items.{{ $idx }}.discount_amount" 
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-center text-xs font-mono text-rose-600 dark:text-rose-400 focus:outline-none focus:border-rose-500"
                                    >
                                </td>
                                <td class="p-3 font-mono font-bold text-slate-900 dark:text-white">
                                    {{ number_format($line['total_price'], 2) }}
                                </td>
                                <td class="p-3 text-center">
                                    <button wire:click="removeItem({{ $idx }})" class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors cursor-pointer">🗑️</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">
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
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3 shadow-sm">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">العميل:</label>
                <select wire:model.live="customer_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}">
                        {{ $c->name }} (رصيد: {{ number_format($c->current_balance, 2) }} ج.م)
                    </option>
                    @endforeach
                </select>

                <!-- Payment Type -->
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 pt-2">طريقة الدفع:</label>
                <div class="grid grid-cols-3 gap-2">
                    <button 
                        type="button" 
                        wire:click="$set('payment_type', 'cash')" 
                        class="py-2 text-xs font-bold rounded-xl border transition-all cursor-pointer {{ $payment_type === 'cash' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-700 dark:text-emerald-400' : 'bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}"
                    >
                        كاش (نقدي)
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('payment_type', 'credit')" 
                        class="py-2 text-xs font-bold rounded-xl border transition-all cursor-pointer {{ $payment_type === 'credit' ? 'bg-amber-500/20 border-amber-500 text-amber-700 dark:text-amber-400' : 'bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}"
                    >
                        آجل (شكك)
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('payment_type', 'partial')" 
                        class="py-2 text-xs font-bold rounded-xl border transition-all cursor-pointer {{ $payment_type === 'partial' ? 'bg-indigo-500/20 border-indigo-500 text-indigo-700 dark:text-indigo-400' : 'bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}"
                    >
                        دفع جزئي
                    </button>
                </div>

                @if($payment_type === 'partial')
                <div class="pt-2">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">المبلغ المدفوع مقدماً:</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        wire:model.live.debounce.250ms="paid_amount" 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-amber-500"
                    >
                </div>
                @endif
            </div>

            <!-- Financial Checkout Card -->
            <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-4 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2">الحساب المالي والخصومات</h3>

                <!-- Invoice-level Discount -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">نوع خصم الفاتورة:</label>
                        <select wire:model.live="discount_type" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-2 py-1.5 text-xs text-slate-900 dark:text-white">
                            <option value="fixed">مبلغ ثابت (ج.م)</option>
                            <option value="percentage">نسبة مئوية (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">قيمة الخصم:</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            wire:model.live.debounce.250ms="discount_value" 
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-2 py-1.5 text-xs font-mono text-rose-600 dark:text-rose-400 font-bold text-center"
                        >
                    </div>
                </div>

                <!-- Live Financial Breakdown -->
                <div class="space-y-2 pt-2 border-t border-slate-200 dark:border-slate-800 text-xs">
                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                        <span>المجموع الفرعي:</span>
                        <span class="font-mono text-slate-900 dark:text-white font-bold">{{ number_format($subtotal, 2) }} ج.م</span>
                    </div>

                    @if(bccomp($discount_amount, '0.000', 3) > 0)
                    <div class="flex items-center justify-between text-rose-600 dark:text-rose-400">
                        <span>إجمالي الخصم:</span>
                        <span class="font-mono font-bold">-{{ number_format($discount_amount, 2) }} ج.م</span>
                    </div>
                    @endif

                    <div class="flex items-center justify-between text-sm font-black text-amber-600 dark:text-amber-400 pt-2 border-t border-slate-200 dark:border-slate-800">
                        <span>الصافي المطلوب:</span>
                        <span class="font-mono">{{ number_format($net_total, 2) }} ج.م</span>
                    </div>

                    <div class="flex items-center justify-between text-slate-700 dark:text-slate-300">
                        <span>المدفوع:</span>
                        <span class="font-mono font-bold">{{ number_format($paid_amount, 2) }} ج.م</span>
                    </div>

                    <div class="flex items-center justify-between text-slate-700 dark:text-slate-300">
                        <span>المتبقي في الحساب:</span>
                        <span class="font-mono font-bold {{ bccomp($remaining_amount, '0.000', 3) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ number_format($remaining_amount, 2) }} ج.م
                        </span>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">ملاحظات الفاتورة:</label>
                    <textarea wire:model="notes" rows="2" placeholder="ملاحظات العميل أو تفاصيل الطحن والتسليم..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2 pt-2">
                    <button 
                        type="button" 
                        wire:click="updateInvoice" 
                        wire:loading.attr="disabled"
                        class="w-full py-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-black rounded-xl text-sm shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 transition-all transform active:scale-95 cursor-pointer"
                    >
                        <span wire:loading.remove>💾 حفظ تعديلات الفاتورة</span>
                        <span wire:loading>جاري التحديث وإعادة ضبط المخزون...</span>
                    </button>

                    <button 
                        type="button" 
                        wire:click="updateInvoice(null, 'print')" 
                        wire:loading.attr="disabled"
                        class="w-full py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-colors cursor-pointer border border-slate-300 dark:border-slate-700"
                    >
                        <span>🖨️ حفظ وطباعة الفاتورة (F9 أو Ctrl+Enter)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Specific Keydown Shortcuts -->
    <script>
        document.addEventListener('keydown', function(e) {
            // F9 or Ctrl+Enter: Save and Print
            if (e.key === 'F9' || (e.ctrlKey && e.key === 'Enter')) {
                e.preventDefault();
                @this.call('updateInvoice', null, 'print');
            }
            // F8: Save only
            if (e.key === 'F8') {
                e.preventDefault();
                @this.call('updateInvoice');
            }
        });
    </script>
</div>
