<div class="space-y-4 pb-12 select-none" dir="rtl" x-data="{ 
    showNumpad: false, 
    numpadTarget: 'paid_amount', 
    numpadValue: '',
    pressNum(val) {
        if (val === 'C') {
            this.numpadValue = '';
        } else if (val === 'backspace') {
            this.numpadValue = this.numpadValue.slice(0, -1);
        } else {
            if (val === '.' && this.numpadValue.includes('.')) return;
            this.numpadValue += val;
        }
        if (this.numpadTarget === 'paid_amount') {
            $wire.set('paid_amount', this.numpadValue || '0.000');
        } else if (this.numpadTarget === 'discount_value') {
            $wire.set('discount_value', this.numpadValue || '0.000');
        }
    }
}">

    <!-- Top Touch Command Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Store & Cashier Info -->
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl font-bold border border-amber-500/20 shrink-0 shadow-inner">
                ☕
            </div>
            <div>
                <h1 class="text-lg md:text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span>☕ كاشير ومبيعات مطحنة البن والشاي والتوزيع (Touch POS)</span>
                </h1>
                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">الفرع الحالي:</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20 text-xs font-bold font-mono">
                        @if($currentStore?->type === 'wholesale_van') 🚚 {{ $currentStore->name }} (عربية)
                        @elseif($currentStore?->type === 'main_warehouse') 🏢 {{ $currentStore->name }} (المخزن)
                        @else 🏬 {{ $currentStore?->name ?? 'الفرع الرئيسي' }}
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Top Right Actions & Quick Blender -->
        <div class="flex items-center gap-2 flex-wrap">
            {{-- خلاط التوليفات موقوف مؤقتاً حسب طلب الإدارة --}}
            {{--
            @can('items.create')
            <a href="{{ route('coffee.blender') }}" target="_blank" class="px-3.5 py-2.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 text-amber-700 dark:text-amber-400 text-xs font-bold flex items-center gap-2 transition-all cursor-pointer">
                <span>☕ خلاط التوليفات</span>
                <span class="text-[10px] bg-amber-500/20 px-1.5 py-0.5 rounded">F4</span>
            </a>
            @endcan
            --}}

            <button 
                type="button" 
                @click="showNumpad = !showNumpad" 
                :class="showNumpad ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                class="px-3.5 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 transition-all cursor-pointer"
            >
                <span>🔢 لوحة أرقام اللمس</span>
            </button>
        </div>
    </div>

    <!-- Error Alerts -->
    @if($errorMessage)
    <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs font-bold flex items-center gap-3 shadow-sm">
        <span class="text-xl">⚠️</span>
        <span>{{ $errorMessage }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs space-y-1.5 shadow-sm">
        <div class="font-black flex items-center gap-2 text-sm">
            <span>🚨</span>
            <span>يرجى مراجعة البيانات التالية:</span>
        </div>
        <ul class="list-disc list-inside pr-4 space-y-0.5 font-medium">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Main Grid: Catalog (Left) vs Cart & Checkout (Right) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">
        
        <!-- ========================================== -->
        <!-- 📦 Left Catalog Column (7 Cols on XL)     -->
        <!-- ========================================== -->
        <div class="xl:col-span-7 space-y-4">
            
            <!-- Category Touch Filter Bar -->
            <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <!-- Search & Quick Barcode Scanner -->
                <div class="relative">
                    <input 
                        type="text" 
                        wire:model.live.debounce.150ms="searchQuery" 
                        placeholder="🔍 ابحث بالاسم أو الباركود (بن برازيلي، شاي، نسكافيه، حبهان)..." 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500"
                        autofocus
                    >
                    @if($searchQuery)
                    <button wire:click="$set('searchQuery', '')" class="absolute left-3 top-1/2 -translate-y-1/2 w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs font-bold hover:bg-slate-300 cursor-pointer">✕</button>
                    @endif
                </div>

                <!-- Big Touch Category Pills -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 pt-1">
                    <button 
                        type="button"
                        wire:click="$set('selectedCategory', 'all')" 
                        class="h-12 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-sm active:scale-95 {{ $selectedCategory === 'all' ? 'bg-emerald-600 text-white shadow-emerald-600/30' : 'bg-slate-100 dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
                    >
                        <span>📦</span>
                        <span>الكل</span>
                    </button>

                    <button 
                        type="button"
                        wire:click="$set('selectedCategory', 'بن وتوليفات')" 
                        class="h-12 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-sm active:scale-95 {{ $selectedCategory === 'بن وتوليفات' ? 'bg-amber-600 text-white shadow-amber-600/30' : 'bg-slate-100 dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
                    >
                        <span>☕</span>
                        <span>بن وتوليفات</span>
                    </button>

                    <button 
                        type="button"
                        wire:click="$set('selectedCategory', 'شاي وأعشاب')" 
                        class="h-12 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-sm active:scale-95 {{ $selectedCategory === 'شاي وأعشاب' ? 'bg-teal-600 text-white shadow-teal-600/30' : 'bg-slate-100 dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
                    >
                        <span>🍵</span>
                        <span>شاي وأعشاب</span>
                    </button>

                    <button 
                        type="button"
                        wire:click="$set('selectedCategory', 'نسكافيه ومشروبات سريعة')" 
                        class="h-12 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-sm active:scale-95 {{ $selectedCategory === 'نسكافيه ومشروبات سريعة' ? 'bg-indigo-600 text-white shadow-indigo-600/30' : 'bg-slate-100 dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
                    >
                        <span>🥤</span>
                        <span>نسكافيه</span>
                    </button>

                    <button 
                        type="button"
                        wire:click="$set('selectedCategory', 'تحبيشات وإضافات')" 
                        class="h-12 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-sm active:scale-95 {{ $selectedCategory === 'تحبيشات وإضافات' ? 'bg-rose-600 text-white shadow-rose-600/30' : 'bg-slate-100 dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
                    >
                        <span>🌿</span>
                        <span>حبهان ومستكة</span>
                    </button>
                </div>
            </div>

            <!-- Touch Product Cards Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @forelse($quickCatalog as $prod)
                @php
                    $storeStockQty = $prod->getStockInStore($store_id);
                    $storePrice = $prod->getEffectivePriceForStore($store_id);
                    $hasStock = bccomp($storeStockQty, '0.000', 3) > 0;
                @endphp
                <div class="rounded-2xl bg-white dark:bg-slate-900 border {{ $hasStock ? 'border-slate-200 dark:border-slate-800 hover:border-amber-500/50' : 'border-rose-200 dark:border-rose-900/50 opacity-75' }} shadow-sm transition-all duration-200 flex flex-col justify-between overflow-hidden group">
                    
                    <!-- Tap to Add 1 Unit Top Area -->
                    <button 
                        type="button"
                        wire:click="addItem({{ $prod->id }}, '1.000')" 
                        class="p-3.5 text-right w-full cursor-pointer transition-colors active:bg-amber-500/10 flex-1 flex flex-col justify-between"
                        title="المس للإضافة السريعة (+1 {{ $prod->unit }})"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-1">
                                <h3 class="font-extrabold text-slate-900 dark:text-white text-xs sm:text-sm line-clamp-2 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                    {{ $prod->name }}
                                </h3>
                            </div>
                            <p class="text-[10px] text-slate-400 font-mono mt-0.5">كود: {{ $prod->code }}</p>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-2">
                            <span class="text-xs sm:text-sm font-black font-mono text-emerald-600 dark:text-emerald-400">
                                {{ number_format($storePrice, 2) }} <span class="text-[10px] font-normal">ج.م</span>
                            </span>
                            
                            <span class="text-[10px] px-2 py-0.5 rounded-lg font-mono font-bold {{ $hasStock ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-600 border border-rose-500/20' }}">
                                {{ number_format($storeStockQty, 1) }} {{ $prod->unit }}
                            </span>
                        </div>
                    </button>

                    <!-- Bottom Quick Weight Steppers (For Kg products) -->
                    <div class="p-2 bg-slate-50/80 dark:bg-slate-950/60 border-t border-slate-100 dark:border-slate-800/80 flex items-center gap-1">
                        @if($prod->unit === 'كجم')
                            <button 
                                type="button" 
                                wire:click="addItem({{ $prod->id }}, '0.125')" 
                                class="flex-1 h-9 rounded-xl bg-slate-200/80 dark:bg-slate-800 hover:bg-amber-600 hover:text-white text-[11px] font-bold text-slate-700 dark:text-slate-200 transition-all active:scale-90 cursor-pointer shadow-sm flex items-center justify-center"
                                title="ثمن كيلو (125 جم)"
                            >
                                1/8
                            </button>
                            <button 
                                type="button" 
                                wire:click="addItem({{ $prod->id }}, '0.250')" 
                                class="flex-1 h-9 rounded-xl bg-slate-200/80 dark:bg-slate-800 hover:bg-amber-600 hover:text-white text-[11px] font-bold text-slate-700 dark:text-slate-200 transition-all active:scale-90 cursor-pointer shadow-sm flex items-center justify-center"
                                title="ربع كيلو (250 جم)"
                            >
                                1/4
                            </button>
                            <button 
                                type="button" 
                                wire:click="addItem({{ $prod->id }}, '0.500')" 
                                class="flex-1 h-9 rounded-xl bg-slate-200/80 dark:bg-slate-800 hover:bg-amber-600 hover:text-white text-[11px] font-bold text-slate-700 dark:text-slate-200 transition-all active:scale-90 cursor-pointer shadow-sm flex items-center justify-center"
                                title="نصف كيلو (500 جم)"
                            >
                                1/2
                            </button>
                            <button 
                                type="button" 
                                wire:click="addItem({{ $prod->id }}, '1.000')" 
                                class="flex-1 h-9 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-[11px] font-black text-white transition-all active:scale-90 cursor-pointer shadow-sm flex items-center justify-center"
                                title="كيلو كامل"
                            >
                                1ك
                            </button>
                        @else
                            <button 
                                type="button" 
                                wire:click="addItem({{ $prod->id }}, '1.000')" 
                                class="w-full h-9 rounded-xl bg-emerald-500/15 hover:bg-emerald-600 hover:text-white text-emerald-700 dark:text-emerald-300 text-xs font-bold transition-all active:scale-95 cursor-pointer shadow-sm flex items-center justify-center gap-1.5"
                            >
                                <span>➕ إضافة وحدة</span>
                            </button>
                        @endif
                    </div>
                </div>
                @empty
                <div class="col-span-full py-12 text-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-400 text-sm">
                    لا توجد أصناف مطابقة لبيانات البحث
                </div>
                @endforelse
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 🛒 Right Cart Column (5 Cols on XL)       -->
        <!-- ========================================== -->
        <div class="xl:col-span-5 space-y-4">
            
            <!-- Store & Customer Card -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Store Selector -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">🏬 منفذ البيع / الفرع:</label>
                        <select wire:model.live="store_id" class="w-full h-11 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                            @foreach($stores as $st)
                            <option value="{{ $st->id }}">
                                @if($st->type === 'wholesale_van') 🚚 @elseif($st->type === 'main_warehouse') 🏢 @else 🏬 @endif
                                {{ $st->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Customer Selector -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">👤 العميل والحساب:</label>
                        <select wire:model.live="customer_id" class="w-full h-11 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}">
                                {{ $c->name }} ({{ number_format($c->current_balance, 2) }} ج.م)
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Active Cart Items -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
                <div class="p-3.5 bg-slate-50/80 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-xs sm:text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>🛒 سلة الفاتورة</span>
                        <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-700 dark:text-amber-300 font-mono text-xs font-bold">
                            {{ count($items) }} بنود
                        </span>
                    </span>
                    @if(count($items) > 0)
                    <button wire:click="$set('items', [])" class="text-xs text-rose-500 hover:text-rose-600 font-bold cursor-pointer transition-colors">
                        إفراغ السلة 🗑️
                    </button>
                    @endif
                </div>

                <!-- Items List (Touch Friendly Cards) -->
                <div class="divide-y divide-slate-100 dark:divide-slate-800/80 max-h-[380px] overflow-y-auto p-2 space-y-2">
                    @forelse($items as $idx => $line)
                    <div class="p-3 rounded-xl bg-slate-50/60 dark:bg-slate-950/40 border border-slate-200/80 dark:border-slate-800/80 space-y-2.5">
                        
                        <!-- Line Header: Name + Price + Delete -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="font-black text-slate-900 dark:text-white text-xs sm:text-sm">{{ $line['name'] }}</h4>
                                <div class="text-[10px] text-slate-400 font-mono flex items-center gap-2 mt-0.5">
                                    <span>سعر الوحدة: <b class="text-slate-700 dark:text-slate-300">{{ number_format($line['unit_price'], 2) }} ج.م</b></span>
                                    <span>• المتاح: {{ number_format($line['current_stock'], 2) }} {{ $line['unit'] }}</span>
                                </div>
                            </div>
                            
                            <button 
                                type="button" 
                                wire:click="removeItem({{ $idx }})" 
                                class="w-8 h-8 rounded-lg bg-rose-500/10 hover:bg-rose-500 text-rose-600 hover:text-white flex items-center justify-center transition-all cursor-pointer active:scale-90 shrink-0"
                                title="حذف الصنف من السلة"
                            >
                                ✕
                            </button>
                        </div>

                        <!-- 💡 Last Customer Price Pill -->
                        @if(!empty($line['last_customer_price']))
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-500/10 border border-amber-500/30 text-[10px] font-bold text-amber-700 dark:text-amber-300">
                                <span>💡 آخر سعر:</span>
                                <span class="font-mono font-black">{{ number_format($line['last_customer_price']['unit_price'], 2) }} ج.م</span>
                            </span>
                            @if(bccomp((string)$line['unit_price'], (string)$line['last_customer_price']['unit_price'], 2) !== 0)
                            <button 
                                type="button" 
                                wire:click="applyCustomerLastPrice({{ $idx }})" 
                                class="px-2 py-0.5 rounded-md bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-black cursor-pointer shadow-sm active:scale-95"
                            >
                                ⚡ تطبيق السعر
                            </button>
                            @endif
                        </div>
                        @endif

                        <!-- Stepper & Line Total -->
                        <div class="flex items-center justify-between gap-3 pt-1">
                            <!-- Large Touch Stepper -->
                            <div class="flex items-center gap-1">
                                <button 
                                    type="button" 
                                    wire:click="decrementLineQty({{ $idx }}, '{{ $line['unit'] === 'كجم' ? '0.125' : '1.000' }}')" 
                                    class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-800 dark:text-slate-200 hover:bg-rose-500 hover:text-white text-base font-black flex items-center justify-center active:scale-90 transition-all cursor-pointer shadow-sm"
                                >
                                    -
                                </button>
                                
                                <div class="px-3 h-10 min-w-[70px] bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl flex items-center justify-center font-mono font-black text-sm text-emerald-600 dark:text-emerald-400">
                                    {{ $line['quantity'] }} <span class="text-[10px] text-slate-400 mr-1">{{ $line['unit'] }}</span>
                                </div>

                                <button 
                                    type="button" 
                                    wire:click="incrementLineQty({{ $idx }}, '{{ $line['unit'] === 'كجم' ? '0.125' : '1.000' }}')" 
                                    class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-800 dark:text-slate-200 hover:bg-emerald-600 hover:text-white text-base font-black flex items-center justify-center active:scale-90 transition-all cursor-pointer shadow-sm"
                                >
                                    +
                                </button>
                            </div>

                            <!-- Line Total Price -->
                            <div class="text-left">
                                <span class="text-xs text-slate-400 block">الإجمالي</span>
                                <span class="text-base font-black font-mono text-slate-900 dark:text-white">
                                    {{ number_format($line['total_price'], 2) }} <span class="text-[10px] font-normal">ج.م</span>
                                </span>
                            </div>
                        </div>

                        <!-- Micro Weight Buttons for Kg items in cart -->
                        @if($line['unit'] === 'كجم')
                        <div class="flex items-center gap-1 pt-1">
                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '0.125')" class="flex-1 py-1 bg-slate-200/70 dark:bg-slate-800 hover:bg-amber-600 hover:text-white rounded-lg text-[10px] font-mono text-slate-700 dark:text-slate-300 font-bold transition-colors cursor-pointer">125g</button>
                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '0.250')" class="flex-1 py-1 bg-slate-200/70 dark:bg-slate-800 hover:bg-amber-600 hover:text-white rounded-lg text-[10px] font-mono text-slate-700 dark:text-slate-300 font-bold transition-colors cursor-pointer">250g</button>
                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '0.500')" class="flex-1 py-1 bg-slate-200/70 dark:bg-slate-800 hover:bg-amber-600 hover:text-white rounded-lg text-[10px] font-mono text-slate-700 dark:text-slate-300 font-bold transition-colors cursor-pointer">500g</button>
                            <button type="button" wire:click="setLineWeightPreset({{ $idx }}, '1.000')" class="flex-1 py-1 bg-emerald-100 dark:bg-emerald-950 border border-emerald-300 dark:border-emerald-800 hover:bg-emerald-600 hover:text-white rounded-lg text-[10px] font-mono text-emerald-800 dark:text-emerald-300 font-black transition-colors cursor-pointer">1kg</button>
                        </div>
                        @endif

                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-400 text-xs">
                        🛒 السلة فارغة. المس الأصناف على اليمين لإضافتها فوراً.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Financial Summary & Payment Box -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                
                <!-- Payment Method Big Toggle Buttons -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">طريقة الدفع والسداد:</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button 
                            type="button" 
                            wire:click="quickSetPaymentType('cash')" 
                            class="h-12 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 border transition-all cursor-pointer active:scale-95 {{ $payment_type === 'cash' ? 'bg-emerald-600 border-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-slate-700 dark:text-slate-300' }}"
                        >
                            <span>💵</span>
                            <span>كاش نقدي</span>
                        </button>

                        <button 
                            type="button" 
                            wire:click="quickSetPaymentType('credit')" 
                            class="h-12 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 border transition-all cursor-pointer active:scale-95 {{ $payment_type === 'credit' ? 'bg-amber-600 border-amber-600 text-white shadow-lg shadow-amber-600/30' : 'bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-slate-700 dark:text-slate-300' }}"
                        >
                            <span>💳</span>
                            <span>آجل (ذمم)</span>
                        </button>

                        <button 
                            type="button" 
                            wire:click="quickSetPaymentType('partial')" 
                            class="h-12 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 border transition-all cursor-pointer active:scale-95 {{ $payment_type === 'partial' ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-slate-700 dark:text-slate-300' }}"
                        >
                            <span>⏳</span>
                            <span>دفع جزئي</span>
                        </button>
                    </div>
                </div>

                <!-- Quick Cash Presets Bar -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">⚡ سداد نقدي سريع:</label>
                    <div class="grid grid-cols-4 gap-1.5">
                        <button type="button" wire:click="quickSetPaidExact" class="h-10 rounded-xl bg-emerald-500/15 hover:bg-emerald-600 hover:text-white border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs font-black transition-all active:scale-95 cursor-pointer flex items-center justify-center">
                            المبلغ بالضبط
                        </button>
                        <button type="button" wire:click="quickSetPaidAmount('50.000')" class="h-10 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-amber-600 hover:text-white text-slate-800 dark:text-slate-200 text-xs font-bold font-mono transition-all active:scale-95 cursor-pointer flex items-center justify-center">
                            50 ج.م
                        </button>
                        <button type="button" wire:click="quickSetPaidAmount('100.000')" class="h-10 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-amber-600 hover:text-white text-slate-800 dark:text-slate-200 text-xs font-bold font-mono transition-all active:scale-95 cursor-pointer flex items-center justify-center">
                            100 ج.م
                        </button>
                        <button type="button" wire:click="quickSetPaidAmount('200.000')" class="h-10 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-amber-600 hover:text-white text-slate-800 dark:text-slate-200 text-xs font-bold font-mono transition-all active:scale-95 cursor-pointer flex items-center justify-center">
                            200 ج.م
                        </button>
                    </div>
                </div>

                <!-- Quick Discount Buttons (if allowed) -->
                @can('invoices.discount')
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">🎁 خصم سريع:</label>
                    <div class="grid grid-cols-4 gap-1.5">
                        <button type="button" wire:click="quickSetDiscountPercent('0.000')" class="h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold hover:bg-slate-200 cursor-pointer">بدون خصم</button>
                        <button type="button" wire:click="quickSetDiscountPercent('5.000')" class="h-8 rounded-lg bg-amber-500/10 hover:bg-amber-500 text-amber-700 dark:text-amber-300 hover:text-white text-xs font-bold cursor-pointer">5%</button>
                        <button type="button" wire:click="quickSetDiscountPercent('10.000')" class="h-8 rounded-lg bg-amber-500/10 hover:bg-amber-500 text-amber-700 dark:text-amber-300 hover:text-white text-xs font-bold cursor-pointer">10%</button>
                        <button type="button" wire:click="quickSetDiscountPercent('15.000')" class="h-8 rounded-lg bg-amber-500/10 hover:bg-amber-500 text-amber-700 dark:text-amber-300 hover:text-white text-xs font-bold cursor-pointer">15%</button>
                    </div>
                </div>
                @endcan

                <!-- Interactive Touch Numpad (Collapsible) -->
                <div x-show="showNumpad" x-transition.duration.200ms class="p-3 bg-slate-100 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400">لوحة الأرقام باللمس:</span>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="numpadTarget = 'paid_amount'" :class="numpadTarget === 'paid_amount' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700'" class="px-2 py-0.5 rounded text-[10px] font-bold">المدفوع</button>
                            <button type="button" @click="numpadTarget = 'discount_value'" :class="numpadTarget === 'discount_value' ? 'bg-amber-600 text-white' : 'bg-slate-200 text-slate-700'" class="px-2 py-0.5 rounded text-[10px] font-bold">الخصم</button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="btn in ['1','2','3','4','5','6','7','8','9','.','0','backspace']">
                            <button 
                                type="button" 
                                @click="pressNum(btn)"
                                class="h-12 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 font-mono font-black text-base text-slate-900 dark:text-white flex items-center justify-center active:scale-95 shadow-sm hover:bg-amber-500 hover:text-white transition-all cursor-pointer"
                                x-text="btn === 'backspace' ? '⌫' : btn"
                            ></button>
                        </template>
                    </div>
                </div>

                <!-- Final Calculations (Big Numbers) -->
                <div class="pt-3 border-t border-slate-200 dark:border-slate-800 space-y-2">
                    <div class="flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
                        <span>إجمالي الأصناف:</span>
                        <span class="font-mono font-bold">{{ number_format($subtotal, 2) }} ج.م</span>
                    </div>

                    @if(bccomp($discount_amount, '0.000', 3) > 0)
                    <div class="flex items-center justify-between text-xs text-rose-600 dark:text-rose-400 font-bold">
                        <span>الخصم الممنوح:</span>
                        <span class="font-mono">- {{ number_format($discount_amount, 2) }} ج.م</span>
                    </div>
                    @endif

                    <!-- NET TOTAL HIGHLIGHT -->
                    <div class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-between">
                        <span class="text-sm font-black text-slate-900 dark:text-white">الصافي المطلوب:</span>
                        <span class="text-xl sm:text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400">
                            {{ number_format($net_total, 2) }} <span class="text-xs font-normal">ج.م</span>
                        </span>
                    </div>

                    @if($payment_type === 'partial')
                    <div class="flex items-center justify-between text-xs text-amber-700 dark:text-amber-400 font-bold">
                        <span>المتبقي ذمم:</span>
                        <span class="font-mono font-black text-sm">{{ number_format($remaining_amount, 2) }} ج.م</span>
                    </div>
                    @endif
                </div>

                <!-- Large Checkout Touch Buttons -->
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <button 
                        type="button" 
                        wire:click="saveInvoice" 
                        wire:loading.attr="disabled"
                        class="h-14 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-black shadow-xl shadow-emerald-600/30 transition-all active:scale-95 cursor-pointer flex items-center justify-center gap-2"
                    >
                        <span wire:loading.remove wire:target="saveInvoice">💾 حفظ واعتماد (Enter)</span>
                        <span wire:loading wire:target="saveInvoice">جاري الحفظ...</span>
                    </button>

                    <button 
                        type="button" 
                        wire:click="saveInvoice('print')" 
                        wire:loading.attr="disabled"
                        class="h-14 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-black shadow-xl shadow-indigo-600/30 transition-all active:scale-95 cursor-pointer flex items-center justify-center gap-2"
                    >
                        <span wire:loading.remove wire:target="saveInvoice('print')">🖨️ حفظ وطباعة</span>
                        <span wire:loading wire:target="saveInvoice('print')">جاري التجهيز...</span>
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
