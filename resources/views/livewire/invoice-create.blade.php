<div class="space-y-4">
    <!-- Header with Active Store Indicator -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>☕ كاشير ومبيعات مطحنة البن والشاي والتوزيع (POS)</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">البيع بالوزن والكميات مع تسعير ذكي وتخصيص الأسعار حسب الفرع/العربية</p>
        </div>
        <div class="flex items-center gap-2">
            <!-- Active Store Badge -->
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 text-xs font-black">
                @if($currentStore?->type === 'wholesale_van')
                    <span>🚚 {{ $currentStore->name }} (عربية توزيع)</span>
                @elseif($currentStore?->type === 'main_warehouse')
                    <span>🏢 {{ $currentStore->name }} (المخزن الرئيسي)</span>
                @else
                    <span>🏬 {{ $currentStore?->name ?? 'الفرع الرئيسي' }}</span>
                @endif
            </div>

            <span class="hidden sm:inline-flex text-[11px] sm:text-xs text-amber-700 dark:text-amber-400 font-bold bg-amber-500/10 border border-amber-500/20 px-3 py-1.5 rounded-xl">
                ⚖️ 1/8 | 1/4 | 1/2 كجم والبيع بالجرام
            </span>
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
            <span>يرجى تصحيح الأخطاء التالية لحفظ الفاتورة:</span>
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
                        placeholder="ابحث عن بن برازيلي، بن محوج، شاي، نسكافيه، حبهان..." 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500"
                        autofocus
                    >
                    <div class="absolute left-3 top-2.5 text-slate-400 text-xs">🔍</div>
                </div>

                <!-- Category Filters -->
                <div class="flex flex-wrap items-center gap-1.5 pt-1 text-[11px]">
                    <button wire:click="$set('selectedCategory', 'all')" class="px-3 py-1 rounded-xl font-bold transition-colors cursor-pointer {{ $selectedCategory === 'all' ? 'bg-emerald-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800' }}">
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

                <!-- Quick Product Cards Grid (One-touch addition) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 pt-2">
                    @forelse($quickCatalog as $prod)
                    @php
                        $storeStockQty = $prod->getStockInStore($store_id);
                        $storePrice = $prod->getEffectivePriceForStore($store_id);
                    @endphp
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800/80 hover:border-emerald-500/50 transition-all flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between gap-1">
                                <div class="font-bold text-slate-800 dark:text-slate-200 text-xs line-clamp-1 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">{{ $prod->name }}</div>
                                <span class="text-[9px] px-1.5 py-0.5 rounded font-mono font-bold {{ bccomp($storeStockQty, '0.000', 3) > 0 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400' }}">
                                    {{ number_format($storeStockQty, 1) }} {{ $prod->unit }}
                                </span>
                            </div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                                {{ number_format($storePrice, 2) }} ج.م / {{ $prod->unit }}
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
                                <button type="button" wire:click="addItem({{ $prod->id }}, '1.000')" title="كيلو كامل" class="flex-1 py-1 rounded bg-emerald-600 hover:bg-emerald-500 text-[9px] font-black text-white transition-colors cursor-pointer">
                                    1كجم
                                </button>
                            @else
                                <button type="button" wire:click="addItem({{ $prod->id }}, '1.000')" class="w-full py-1 rounded bg-emerald-500/10 hover:bg-emerald-600 text-emerald-700 dark:text-emerald-300 hover:text-white text-[10px] font-bold transition-colors cursor-pointer">
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

            <!-- Active Invoice Items Table -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-black text-slate-800 dark:text-slate-200">🛒 بنود الفاتورة ({{ count($items) }})</span>
                    @if(count($items) > 0)
                    <button wire:click="$set('items', [])" class="text-[11px] text-rose-500 hover:underline cursor-pointer font-bold">إفراغ السلة</button>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-100/50 dark:bg-slate-950 text-slate-500 font-bold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3">الصنف والتسعير</th>
                                <th class="p-3 text-center w-36">الكمية والوزن</th>
                                <th class="p-3 text-center w-28">السعر (ج.م)</th>
                                <th class="p-3 text-center w-24">الخصم</th>
                                <th class="p-3 text-center w-28">الإجمالي</th>
                                <th class="p-3 text-center w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($items as $idx => $line)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/40 transition-colors">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $line['name'] }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono flex items-center gap-2 mt-0.5">
                                        <span>كود: {{ $line['code'] }}</span>
                                        <span>• المتاح بالفرع: <b class="text-slate-700 dark:text-slate-300">{{ number_format($line['current_stock'], 2) }} {{ $line['unit'] }}</b></span>
                                    </div>

                                    <!-- 💡 Smart Last Customer Price Badge with 1-Click Apply Button -->
                                    @if(!empty($line['last_customer_price']))
                                    <div class="mt-1.5 flex items-center gap-1.5 flex-wrap">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-500/10 border border-amber-500/30 text-[10px] font-bold text-amber-700 dark:text-amber-300">
                                            <span>💡 آخر سعر للعميل:</span>
                                            <span class="font-mono font-black">{{ number_format($line['last_customer_price']['unit_price'], 2) }} ج.م</span>
                                            <span class="text-[9px] text-slate-400 font-normal">({{ $line['last_customer_price']['invoice_date'] }})</span>
                                        </span>
                                        @if(bccomp((string)$line['unit_price'], (string)$line['last_customer_price']['unit_price'], 2) !== 0)
                                        <button 
                                            type="button" 
                                            wire:click="applyCustomerLastPrice({{ $idx }})" 
                                            class="px-2 py-0.5 rounded bg-amber-500 hover:bg-amber-600 text-white text-[9px] font-black cursor-pointer shadow-sm transition-all active:scale-95"
                                            title="تطبيق آخر سعر أخذ به العميل تلقائياً"
                                        >
                                            ⚡ تطبيق السعر
                                        </button>
                                        @endif
                                    </div>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <div class="space-y-1.5">
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

                                        <!-- Micro weight adjusters if Kg -->
                                        @if($line['unit'] === 'كجم')
                                        <div class="flex items-center gap-1 justify-center">
                                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '0.125')" title="ثمن كيلو (125 جم)" class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 hover:bg-amber-600 text-[9px] rounded font-mono text-slate-700 dark:text-slate-300 hover:text-white cursor-pointer">ثمن 125g</button>
                                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '0.250')" title="ربع كيلو (250 جم)" class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 hover:bg-amber-600 text-[9px] rounded font-mono text-slate-700 dark:text-slate-300 hover:text-white cursor-pointer">ربع 250g</button>
                                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '0.500')" title="نصف كيلو (500 جم)" class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 hover:bg-amber-600 text-[9px] rounded font-mono text-slate-700 dark:text-slate-300 hover:text-white cursor-pointer">نصف 500g</button>
                                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '1.000')" title="كيلو كامل" class="px-1.5 py-0.5 bg-emerald-100 dark:bg-emerald-950 border border-emerald-300 dark:border-emerald-800 hover:bg-emerald-600 text-[9px] rounded font-mono text-emerald-800 dark:text-emerald-300 hover:text-white font-bold cursor-pointer">1kg</button>
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
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-center text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500"
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
                                <td class="p-3 text-center font-mono font-bold text-slate-900 dark:text-white">
                                    {{ number_format($line['total_price'], 2) }}
                                </td>
                                <td class="p-3 text-center">
                                    <button wire:click="removeItem({{ $idx }})" class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors cursor-pointer">🗑️</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">
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
            <!-- Customer & Store Selection Card -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3 shadow-sm">
                <!-- Store/Van Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الفرع / عربية التوزيع:</label>
                    <select wire:model.live="store_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                        @foreach($stores as $st)
                        <option value="{{ $st->id }}">
                            @if($st->type === 'wholesale_van') 🚚 @elseif($st->type === 'main_warehouse') 🏢 @else 🏬 @endif
                            {{ $st->name }} ({{ $st->code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Customer Selection -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">العميل:</label>
                    <select wire:model.live="customer_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}">
                            {{ $c->name }} (رصيد: {{ number_format($c->current_balance, 2) }} ج.م)
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Type -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">طريقة الدفع:</label>
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
                            آجل (ذمم)
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('payment_type', 'partial')" 
                            class="py-2 text-xs font-bold rounded-xl border transition-all cursor-pointer {{ $payment_type === 'partial' ? 'bg-indigo-500/20 border-indigo-500 text-indigo-700 dark:text-indigo-400' : 'bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}"
                        >
                            جزئي (عربون)
                        </button>
                    </div>
                </div>

                <!-- Invoice Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">تاريخ الفاتورة:</label>
                    <input type="date" wire:model="invoice_date" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <!-- Financial Summary Box -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3 shadow-sm">
                <div class="flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
                    <span>إجمالي البنود:</span>
                    <span class="font-mono font-bold">{{ number_format($subtotal, 2) }} ج.م</span>
                </div>

                <!-- Invoice Level Discount -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 dark:text-slate-400">خصم إضافي:</span>
                        <div class="flex items-center gap-1">
                            <select wire:model.live="discount_type" class="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-[10px] px-1.5 py-0.5">
                                <option value="fixed">مبلغ ثابت</option>
                                <option value="percentage">نسبة %</option>
                            </select>
                            <input type="number" step="0.01" min="0" wire:model.live.debounce.250ms="discount_value" class="w-20 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-0.5 text-center text-xs font-mono text-rose-600 dark:text-rose-400 font-bold">
                        </div>
                    </div>
                </div>

                <!-- Net Total (Large Highlight) -->
                <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-sm font-black text-slate-900 dark:text-white">الصافي المطلوب:</span>
                    <span class="text-lg font-black font-mono text-emerald-600 dark:text-emerald-400">{{ number_format($net_total, 2) }} ج.م</span>
                </div>

                <!-- Partial Paid & Remaining -->
                @if($payment_type === 'partial')
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-700 dark:text-slate-300">المدفوع الآن:</span>
                        <input type="number" step="0.01" min="0" wire:model.live.debounce.250ms="paid_amount" class="w-28 bg-emerald-500/10 border border-emerald-500/30 rounded-lg px-2 py-1 text-center text-xs font-mono font-bold text-emerald-700 dark:text-emerald-300">
                    </div>
                    <div class="flex items-center justify-between text-xs text-amber-700 dark:text-amber-400 font-bold">
                        <span>المتبقي ذمم:</span>
                        <span class="font-mono">{{ number_format($remaining_amount, 2) }} ج.م</span>
                    </div>
                </div>
                @endif

                <!-- Notes -->
                <div>
                    <input type="text" wire:model="notes" placeholder="ملاحظات اختيارية..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Action Buttons -->
                <div class="pt-2 grid grid-cols-2 gap-2">
                    <button 
                        type="button" 
                        wire:click="saveInvoice" 
                        wire:loading.attr="disabled"
                        class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-lg shadow-emerald-600/20 transition-all cursor-pointer flex items-center justify-center gap-1.5"
                    >
                        <span wire:loading.remove wire:target="saveInvoice">💾 حفظ واعتماد</span>
                        <span wire:loading wire:target="saveInvoice">جاري الحفظ...</span>
                    </button>

                    <button 
                        type="button" 
                        wire:click="saveInvoice('print')" 
                        wire:loading.attr="disabled"
                        class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black shadow-lg shadow-indigo-600/20 transition-all cursor-pointer flex items-center justify-center gap-1.5"
                    >
                        <span wire:loading.remove wire:target="saveInvoice('print')">🖨️ حفظ وطباعة</span>
                        <span wire:loading wire:target="saveInvoice('print')">جاري التجهيز...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
