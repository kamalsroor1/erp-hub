<div class="space-y-6">
    <!-- Header & Date Navigation -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>📅 يومية المبيعات وحركة الدرج (يوم بيوم)</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">متابعة دقيقة لمبيعات اليوم، النقدية المقبوضة، المصروفات، وحساب صافي الدرج وتقفيل الوردية</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Store Selector -->
            @hasrole('admin')
            <div class="flex items-center gap-1 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-2.5 py-1 text-xs">
                <span class="text-slate-500 dark:text-slate-400 font-bold">الفرع:</span>
                <select wire:model.live="selectedStoreId" class="bg-transparent text-slate-900 dark:text-white font-bold focus:outline-none cursor-pointer [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-slate-100">
                    <option class="bg-white dark:bg-slate-900 text-slate-900 dark:text-white" value="all">كل الفروع والعربيات</option>
                    @foreach($stores as $st)
                    <option class="bg-white dark:bg-slate-900 text-slate-900 dark:text-white" value="{{ $st->id }}">
                        @if($st->type === 'wholesale_van') 🚚 @elseif($st->type === 'main_warehouse') 🏢 @else 🏬 @endif
                        {{ $st->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @else
                @if($currentStore)
                <div class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-bold text-xs rounded-xl">
                    <span>@if($currentStore->type === 'wholesale_van') 🚚 @elseif($currentStore->type === 'main_warehouse') 🏢 @else 🏬 @endif {{ $currentStore->name }}</span>
                </div>
                @endif
            @endhasrole

            <!-- Date Filter Presets -->
            <button wire:click="setDate('today')" class="px-3 py-1.5 rounded-xl font-bold text-xs border transition-colors cursor-pointer {{ $selectedDate === now()->toDateString() ? 'bg-amber-600 text-white border-amber-500' : 'bg-slate-100 dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                اليوم
            </button>
            <button wire:click="setDate('yesterday')" class="px-3 py-1.5 rounded-xl font-bold text-xs border transition-colors cursor-pointer {{ $selectedDate === now()->subDay()->toDateString() ? 'bg-amber-600 text-white border-amber-500' : 'bg-slate-100 dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                أمس
            </button>

            <!-- Modern Date Picker -->
            <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-2 py-0.5 text-xs">
                <span class="text-slate-500 dark:text-slate-400 font-bold">📅</span>
                <x-datepicker wire:model.live="selectedDate" class="w-32 !py-1 !px-2 !text-xs !bg-transparent !border-none focus:!ring-0" placeholder="اختر التاريخ" />
            </div>

            <!-- Print Daily Summary Button -->
            <a href="{{ route('daily.journal.print', ['date' => $selectedDate, 'store_id' => $selectedStoreId, 'autoprint' => 1]) }}" target="_blank" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs rounded-xl shadow-sm flex items-center gap-1.5 transition-colors cursor-pointer font-tajawal">
                <span>🖨️ طباعة تقرير A4 رسمي</span>
            </a>
        </div>
    </div>

    @if($successMessage)
    <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs font-bold flex items-center gap-2">
        <span>✓</span> {{ $successMessage }}
    </div>
    @endif

    @if($errorMessage)
    <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs font-bold flex items-center gap-2">
        <span>✕</span> {{ $errorMessage }}
    </div>
    @endif

    <!-- Shift / Drawer Action Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl flex flex-col lg:flex-row lg:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-start sm:items-center gap-3">
            <div class="w-3.5 h-3.5 rounded-full mt-1 sm:mt-0 shrink-0 {{ $activeShift ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400 dark:bg-slate-600' }}"></div>
            <div>
                <div class="text-xs font-bold text-slate-900 dark:text-white flex flex-wrap items-center gap-2">
                    <span>حالة الوردية / اليومية:</span>
                    @if($activeShift)
                        @php
                            $isOverdue = $activeShift->opened_at && (now()->diffInHours($activeShift->opened_at) >= 24 || $activeShift->opened_at->diffInDays(now()) >= 1);
                        @endphp
                        @if($isOverdue)
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] bg-rose-500/20 text-rose-700 dark:text-rose-300 border border-rose-500/40 font-bold animate-pulse">
                            🚨 مفتوحة ومتأخرة (+24h) (#{{ $activeShift->shift_number }})
                        </span>
                        @else
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 font-bold">
                            🟢 مفتوحة وشغالة (#{{ $activeShift->shift_number }})
                        </span>
                        @endif
                        <span class="text-slate-500 dark:text-slate-400 font-normal">| الكاشير: <strong class="text-slate-900 dark:text-white">{{ $activeShift->user->name ?? 'الكاشير' }}</strong></span>
                        <span class="text-slate-500 dark:text-slate-400 font-normal">| تم الفتح: <strong class="text-slate-900 dark:text-white font-mono">{{ $activeShift->opened_at ? $activeShift->opened_at->translatedFormat('d F - h:i A') : '—' }}</strong></span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-slate-700">
                            مغلقة / غير مفتوحة
                        </span>
                    @endif
                </div>

                @if($activeShift)
                <div class="text-xs text-slate-600 dark:text-slate-300 mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 font-mono">
                    <span>الافتتاحي: <strong class="text-slate-900 dark:text-white">{{ number_format($activeShift->opening_cash_balance, 2) }}</strong> ج.م</span>
                    <span>+ المقبوض: <strong class="text-emerald-600 dark:text-emerald-400">{{ number_format($totalCashCollected, 2) }}</strong> ج.م</span>
                    @if(bccomp($totalExpenses, '0.000', 3) > 0)
                    <span>- المصروفات: <strong class="text-rose-600 dark:text-rose-400">{{ number_format($totalExpenses, 2) }}</strong> ج.م</span>
                    @endif
                    <span class="bg-amber-500/20 text-amber-800 dark:text-amber-300 px-2 py-0.5 rounded-lg border border-amber-500/30 font-bold">
                        💰 المفروض بالدرج الآن: {{ number_format($expectedCashInDrawer, 2) }} ج.م
                    </span>
                </div>
                @else
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                    يمكنك فتح اليومية وبدء تسجيل الرصيد الافتتاحي (العهدة/الفكة) للدرج.
                </div>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @if($activeShift)
                <button wire:click="openCloseModal" class="px-4 py-2.5 bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/30 flex items-center gap-1.5 transition-all cursor-pointer">
                    <span>🔴 تقفيل اليومية (Z-Report)</span>
                </button>
            @else
                <button wire:click="openShiftModal" class="px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center gap-1.5 transition-all cursor-pointer">
                    <span>🟢 فتح يومية جديدة</span>
                </button>
            @endif
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 🏛️ Live Treasury & Multi-Account Balances -->
    <!-- ========================================== -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>🏛️ أرصدة الخزن وحسابات الدفع الفعلية الحالية</span>
                </h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">متابعة دقيقة لرصيد الكاش، إنستاباي، والمحافظ مع إمكانية التحويل المباشر بين الحسابات</p>
            </div>
            <button 
                type="button" 
                wire:click="openTransferModal" 
                class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs font-bold rounded-xl shadow-md shadow-purple-600/20 flex items-center gap-1.5 transition-all cursor-pointer self-start sm:self-auto"
            >
                <span>🔄 تحويل رصيد بين الخزن</span>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- 1. Cash Drawer -->
            @php $cashBal = $treasuryBalances['cash'] ?? null; @endphp
            <div class="p-3.5 rounded-xl border border-emerald-500/20 bg-emerald-500/5 space-y-1">
                <div class="flex items-center justify-between text-xs font-bold text-emerald-700 dark:text-emerald-400">
                    <span class="flex items-center gap-1">💵 درج النقدية (الكاش)</span>
                </div>
                <div class="text-xl font-black text-emerald-700 dark:text-emerald-300 font-mono">
                    {{ number_format($cashBal['balance'] ?? 0, 2) }} <span class="text-xs font-normal">ج.م</span>
                </div>
                <div class="text-[10px] text-slate-500 dark:text-slate-400 pt-1 border-t border-emerald-500/10 flex justify-between">
                    <span>وارد: +{{ number_format($cashBal['inflows'] ?? 0, 1) }}</span>
                    <span>صادر: -{{ number_format($cashBal['outflows'] ?? 0, 1) }}</span>
                </div>
            </div>

            <!-- 2. InstaPay -->
            @php $instaBal = $treasuryBalances['instapay'] ?? null; @endphp
            <div class="p-3.5 rounded-xl border border-purple-500/20 bg-purple-500/5 space-y-1">
                <div class="flex items-center justify-between text-xs font-bold text-purple-700 dark:text-purple-400">
                    <span class="flex items-center gap-1">⚡ حساب إنستاباي (InstaPay)</span>
                </div>
                <div class="text-xl font-black text-purple-700 dark:text-purple-300 font-mono">
                    {{ number_format($instaBal['balance'] ?? 0, 2) }} <span class="text-xs font-normal">ج.م</span>
                </div>
                <div class="text-[10px] text-slate-500 dark:text-slate-400 pt-1 border-t border-purple-500/10 flex justify-between">
                    <span>وارد: +{{ number_format($instaBal['inflows'] ?? 0, 1) }}</span>
                    <span>صادر: -{{ number_format($instaBal['outflows'] ?? 0, 1) }}</span>
                </div>
            </div>

            <!-- 3. E-Wallet -->
            @php $walletBal = $treasuryBalances['e_wallet'] ?? null; @endphp
            <div class="p-3.5 rounded-xl border border-rose-500/20 bg-rose-500/5 space-y-1">
                <div class="flex items-center justify-between text-xs font-bold text-rose-700 dark:text-rose-400">
                    <span class="flex items-center gap-1">📲 المحفظة الذكية (كاش)</span>
                </div>
                <div class="text-xl font-black text-rose-700 dark:text-rose-300 font-mono">
                    {{ number_format($walletBal['balance'] ?? 0, 2) }} <span class="text-xs font-normal">ج.م</span>
                </div>
                <div class="text-[10px] text-slate-500 dark:text-slate-400 pt-1 border-t border-rose-500/10 flex justify-between">
                    <span>وارد: +{{ number_format($walletBal['inflows'] ?? 0, 1) }}</span>
                    <span>صادر: -{{ number_format($walletBal['outflows'] ?? 0, 1) }}</span>
                </div>
            </div>

            <!-- 4. Total Liquidity -->
            <div class="p-3.5 rounded-xl border-2 border-indigo-500/40 bg-indigo-500/5 space-y-1">
                <div class="flex items-center justify-between text-xs font-black text-indigo-700 dark:text-indigo-400">
                    <span class="flex items-center gap-1">💰 إجمالي السيولة النقدية</span>
                </div>
                <div class="text-xl font-black text-indigo-700 dark:text-indigo-300 font-mono">
                    {{ number_format($treasuryBalances['total_liquidity'] ?? 0, 2) }} <span class="text-xs font-normal">ج.م</span>
                </div>
                <div class="text-[10px] text-slate-500 dark:text-slate-400 pt-1 border-t border-indigo-500/10">
                    مجموع كل الخزائن والحسابات
                </div>
            </div>
        </div>
    </div>

    <!-- 5 Daily High-Level Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- 1. Opening Cash Balance -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl space-y-1 shadow-sm">
            <div class="text-xs font-bold text-slate-500 dark:text-slate-400">الرصيد الافتتاحي للدرج (الفكة)</div>
            <div class="text-xl font-black text-slate-900 dark:text-slate-200 font-mono mt-2">{{ number_format($openingCashBalance, 2) }} <span class="text-xs text-slate-400">ج.م</span></div>
            <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-800/80">
                العهدة الافتتاحية لبداية اليوم
            </div>
        </div>

        <!-- 2. Cash Inflow Collected -->
        <div class="bg-white dark:bg-slate-900 border border-emerald-500/30 p-4 rounded-2xl space-y-1 bg-gradient-to-b from-white dark:from-slate-900 to-emerald-50/50 dark:to-emerald-950/20 shadow-sm">
            <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400">النقدية المقبوضة لليوم (كاش)</div>
            <div class="text-xl font-black text-emerald-600 dark:text-emerald-300 font-mono mt-2">{{ number_format($totalCashCollected, 2) }} <span class="text-xs text-emerald-500 dark:text-emerald-400">ج.م</span></div>
            <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-800/80">
                مبيعات كاش + سندات قبض
            </div>
        </div>

        <!-- 3. Expenses Paid Out -->
        <div class="bg-white dark:bg-slate-900 border border-rose-500/30 p-4 rounded-2xl space-y-1 bg-gradient-to-b from-white dark:from-slate-900 to-rose-50/50 dark:to-rose-950/20 shadow-sm">
            <div class="text-xs font-bold text-rose-600 dark:text-rose-400 flex items-center justify-between">
                <span>المصروفات والنثريات</span>
                <a href="{{ route('expenses.index') }}" class="text-[10px] text-amber-600 dark:text-amber-400 hover:underline">عرض</a>
            </div>
            <div class="text-xl font-black text-rose-600 dark:text-rose-300 font-mono mt-2">{{ number_format($totalExpenses, 2) }} <span class="text-xs text-rose-500 dark:text-rose-400">ج.م</span></div>
            <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-800/80">
                {{ $expenses->count() }} مصروف (شنط، أكواب، صيانة)
            </div>
        </div>

        <!-- 4. Total Sales Volume -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl space-y-1 shadow-sm">
            <div class="text-xs font-bold text-slate-500 dark:text-slate-400">إجمالي مبيعات اليوم</div>
            <div class="text-xl font-black text-slate-900 dark:text-white font-mono mt-2">{{ number_format($totalSales, 2) }} <span class="text-xs text-amber-600 dark:text-amber-400">ج.م</span></div>
            <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-800/80 flex justify-between">
                <span>{{ $invoicesCount }} فاتورة</span>
                <span>آجل: {{ number_format($creditSales, 1) }}</span>
            </div>
        </div>

        <!-- 5. Total Expected Cash Physically in Drawer Right Now (CRITICAL) -->
        <div class="bg-white dark:bg-slate-900 border-2 border-amber-500/60 p-4 rounded-2xl space-y-1 relative overflow-hidden bg-gradient-to-b from-white dark:from-slate-900 to-amber-50/60 dark:to-amber-950/40 shadow-lg shadow-amber-500/10">
            <div class="text-xs font-black text-amber-600 dark:text-amber-400 flex items-center justify-between">
                <span>💰 المفروض في الدرج الآن</span>
                <span class="text-[10px] bg-amber-500/20 text-amber-800 dark:text-amber-300 px-1.5 py-0.5 rounded font-mono font-bold">الافتتاحي + المقبوض - المصروف</span>
            </div>
            <div class="text-2xl font-black text-amber-600 dark:text-amber-300 font-mono mt-2">
                {{ number_format($expectedCashInDrawer, 2) }} <span class="text-xs text-amber-500 dark:text-amber-400">ج.م</span>
            </div>
            <div class="text-[10px] text-slate-500 dark:text-slate-300 pt-1 border-t border-amber-500/30">
                النقدية الفعلية المفترض تسليمها في الدرج
            </div>
        </div>
    </div>

    <!-- Daily Operations Details Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ tab: 'invoices' }">
        <!-- Main Feed (2 cols) -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Navigation Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 border-b border-slate-200 dark:border-slate-800 pb-2 text-[11px] sm:text-xs font-bold">
                <button @click="tab = 'invoices'" :class="tab === 'invoices' ? 'bg-amber-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-2.5 sm:px-3 py-1.5 rounded-xl transition-all cursor-pointer">
                    🧾 الفواتير ({{ $invoicesCount }})
                </button>
                <button @click="tab = 'expenses'" :class="tab === 'expenses' ? 'bg-amber-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-2.5 sm:px-3 py-1.5 rounded-xl transition-all cursor-pointer">
                    💸 المصروفات ({{ $expenses->count() }})
                </button>
                <button @click="tab = 'payments'" :class="tab === 'payments' ? 'bg-amber-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-2.5 sm:px-3 py-1.5 rounded-xl transition-all cursor-pointer">
                    💵 المقبوضات ({{ $customerPayments->count() }})
                </button>
                <button @click="tab = 'purchases'" :class="tab === 'purchases' ? 'bg-amber-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-2.5 sm:px-3 py-1.5 rounded-xl transition-all cursor-pointer">
                    🛒 المشتريات ({{ $purchases->count() }})
                </button>
                <button @click="tab = 'transfers'" :class="tab === 'transfers' ? 'bg-purple-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-2.5 sm:px-3 py-1.5 rounded-xl transition-all cursor-pointer">
                    🔄 التحويلات بين الخزن ({{ $transfers->count() }})
                </button>
            </div>

            <!-- Tab 1: Invoices Table -->
            <div x-show="tab === 'invoices'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 font-bold text-xs text-slate-900 dark:text-white">
                    فواتير مبيعات يوم {{ $selectedDate }}
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3">رقم الفاتورة</th>
                                <th class="p-3">العميل</th>
                                <th class="p-3">الوقت</th>
                                <th class="p-3">النوع</th>
                                <th class="p-3">الإجمالي</th>
                                <th class="p-3">المدفوع</th>
                                <th class="p-3 text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($invoices as $inv)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="p-3 font-mono font-bold text-amber-600 dark:text-amber-400">
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="hover:underline">{{ $inv->invoice_number }}</a>
                                </td>
                                <td class="p-3 font-bold text-slate-800 dark:text-slate-200">{{ $inv->customer->name ?? 'عميل نقدي' }}</td>
                                <td class="p-3 text-slate-500 dark:text-slate-400 font-mono text-[11px]">{{ $inv->created_at->format('h:i A') }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $inv->payment_type === 'cash' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : ($inv->payment_type === 'credit' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400') }}">
                                        {{ $inv->payment_type === 'cash' ? 'كاش' : ($inv->payment_type === 'credit' ? 'آجل' : 'جزئي') }}
                                    </span>
                                </td>
                                <td class="p-3 font-mono font-bold text-slate-900 dark:text-white">{{ number_format($inv->net_total, 2) }}</td>
                                <td class="p-3 font-mono text-emerald-600 dark:text-emerald-400">{{ number_format($inv->paid_amount, 2) }}</td>
                                <td class="p-3 text-center flex items-center justify-center gap-1">
                                    <a href="{{ route('invoices.edit', $inv->id) }}" class="px-2 py-1 bg-amber-500/10 hover:bg-amber-500 hover:text-slate-950 text-amber-600 dark:text-amber-400 rounded text-[10px] font-bold transition-colors">
                                        ✏️ تعديل
                                    </a>
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-[10px] font-bold transition-colors border border-slate-200 dark:border-slate-700">
                                        عرض
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">لا توجد فواتير مبيعات مسجلة في هذا اليوم</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Expenses Table -->
            <div x-show="tab === 'expenses'" x-cloak class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 font-bold text-xs text-slate-900 dark:text-white">
                    مصروفات ونثريات يوم {{ $selectedDate }}
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3">التصنيف</th>
                                <th class="p-3">بيان المصروف</th>
                                <th class="p-3">الوقت</th>
                                <th class="p-3">طريقة الدفع</th>
                                <th class="p-3">المبلغ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($expenses as $exp)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">{{ $exp->category }}</span>
                                </td>
                                <td class="p-3 font-bold text-slate-800 dark:text-slate-200">{{ $exp->title }}</td>
                                <td class="p-3 text-slate-500 dark:text-slate-400 font-mono text-[11px]">{{ $exp->created_at->format('h:i A') }}</td>
                                <td class="p-3 text-slate-600 dark:text-slate-400">{{ $exp->payment_method === 'cash' ? 'نقدي (كاش)' : $exp->payment_method }}</td>
                                <td class="p-3 font-mono font-bold text-rose-600 dark:text-rose-400">{{ number_format($exp->amount, 2) }} ج.م</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">لا توجد مصروفات مسجلة في هذا اليوم</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 3: Customer Payments Table -->
            <div x-show="tab === 'payments'" x-cloak class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 font-bold text-xs text-slate-900 dark:text-white">
                    سندات القبض وتحصيلات العملاء في يوم {{ $selectedDate }}
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3">رقم السند</th>
                                <th class="p-3">العميل</th>
                                <th class="p-3">الوقت</th>
                                <th class="p-3">طريقة التحصيل</th>
                                <th class="p-3">المبلغ المقبوض</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($customerPayments as $pay)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="p-3 font-mono font-bold text-slate-800 dark:text-slate-300">{{ $pay->payment_number }}</td>
                                <td class="p-3 font-bold text-slate-800 dark:text-slate-200">{{ $pay->customer->name ?? 'عميل' }}</td>
                                <td class="p-3 text-slate-500 dark:text-slate-400 font-mono text-[11px]">{{ $pay->created_at->format('h:i A') }}</td>
                                <td class="p-3 text-slate-600 dark:text-slate-400">{{ $pay->payment_method }}</td>
                                <td class="p-3 font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($pay->amount, 2) }} ج.م</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">لا توجد سندات قبض في هذا اليوم</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 4: Purchases Table -->
            <div x-show="tab === 'purchases'" x-cloak class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 font-bold text-xs text-slate-900 dark:text-white">
                    مشتريات وتوريدات يوم {{ $selectedDate }}
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3">رقم الفاتورة</th>
                                <th class="p-3">المورد</th>
                                <th class="p-3">الوقت</th>
                                <th class="p-3">الإجمالي</th>
                                <th class="p-3">المسدد</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($purchases as $pur)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="p-3 font-mono font-bold text-amber-600 dark:text-amber-400">{{ $pur->purchase_number }}</td>
                                <td class="p-3 font-bold text-slate-800 dark:text-slate-200">{{ $pur->supplier->name ?? 'مورد' }}</td>
                                <td class="p-3 text-slate-500 dark:text-slate-400 font-mono text-[11px]">{{ $pur->created_at->format('h:i A') }}</td>
                                <td class="p-3 font-mono font-bold text-slate-900 dark:text-white">{{ number_format($pur->net_total, 2) }}</td>
                                <td class="p-3 font-mono text-emerald-600 dark:text-emerald-400">{{ number_format($pur->paid_amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">لا توجد مشتريات مسجلة في هذا اليوم</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 5: Treasury Transfers Table -->
            <div x-show="tab === 'transfers'" x-cloak class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 font-bold text-xs text-slate-900 dark:text-white flex items-center justify-between">
                    <span>حركات التحويل بين الخزن وحسابات الدفع يوم {{ $selectedDate }}</span>
                    <button type="button" wire:click="openTransferModal" class="px-2.5 py-1 bg-purple-600 hover:bg-purple-500 text-white rounded-lg text-[10px] font-bold cursor-pointer">
                        + تحويل جديد
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3">رقم التحويل</th>
                                <th class="p-3">من الخزينة</th>
                                <th class="p-3">إلى الخزينة</th>
                                <th class="p-3">الوقت</th>
                                <th class="p-3">المبلغ المحول</th>
                                <th class="p-3">الرسوم / العمولة</th>
                                <th class="p-3">المسؤول والبيان</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($transfers as $trf)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="p-3 font-mono font-bold text-purple-600 dark:text-purple-400">{{ $trf->transfer_number }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $trf->from_method_icon }} {{ $trf->from_method_label }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">
                                        {{ $trf->to_method_icon }} {{ $trf->to_method_label }}
                                    </span>
                                </td>
                                <td class="p-3 text-slate-500 dark:text-slate-400 font-mono text-[11px]">{{ $trf->created_at->format('h:i A') }}</td>
                                <td class="p-3 font-mono font-black text-slate-900 dark:text-white">{{ number_format($trf->amount, 2) }} ج.م</td>
                                <td class="p-3 font-mono text-rose-600 dark:text-rose-400">{{ bccomp($trf->transfer_fee, '0.000', 3) > 0 ? number_format($trf->transfer_fee, 2) . ' ج.م' : '—' }}</td>
                                <td class="p-3 text-slate-600 dark:text-slate-400 text-[11px]">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $trf->user->name ?? 'مستخدم' }}</span>
                                    @if($trf->notes)
                                        <div class="text-[10px] text-slate-400">{{ $trf->notes }}</div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">لا توجد تحويلات مسجلة بين الخزن في هذا اليوم</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Col: Daily Shifts Log & Drawer Reconciliation -->
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 space-y-4 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2 flex items-center justify-between">
                    <span>⏱️ سجل الورديات وتقفيل الـ Z-Report</span>
                </h3>

                @forelse($shiftsOnDate as $sh)
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 space-y-2 text-xs">
                    <div class="flex items-center justify-between font-bold">
                        <span class="text-amber-600 dark:text-amber-400">وردية رقم #{{ $sh->shift_number }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] {{ $sh->status === 'closed' ? 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-400' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' }}">
                            {{ $sh->status === 'closed' ? 'مقفلة' : '🟢 جارية الآن' }}
                        </span>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">
                        الكاشير: <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $sh->user->name ?? 'مستخدم' }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1.5 border-t border-slate-200 dark:border-slate-900 text-[11px]">
                        <div>الافتتاحي: <span class="font-mono text-slate-900 dark:text-white font-bold">{{ number_format($sh->opening_cash_balance, 2) }}</span></div>
                        <div>
                            @if($sh->status === 'open')
                                <span class="text-amber-600 dark:text-amber-400 font-bold">المتوقع: {{ number_format($expectedCashInDrawer, 2) }}</span>
                            @else
                                الفعلي: <span class="font-mono text-slate-900 dark:text-white font-bold">{{ number_format($sh->actual_cash_balance, 2) }}</span>
                            @endif
                        </div>
                    </div>
                    @if($sh->status === 'closed' && bccomp((string)$sh->cash_difference, '0.000', 3) != 0)
                    <div class="pt-1 text-[11px] font-bold {{ bccomp((string)$sh->cash_difference, '0.000', 3) < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ bccomp((string)$sh->cash_difference, '0.000', 3) < 0 ? 'عجز في الدرج:' : 'زيادة في الدرج:' }}
                        <span class="font-mono">{{ number_format($sh->cash_difference, 2) }} ج.م</span>
                    </div>
                    @endif
                </div>
                @empty
                <div class="p-4 text-center text-slate-400 text-xs">
                    لا توجد ورديات مسجلة في هذا التاريخ
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Open Shift Modal -->
    @if($showOpenModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-base">🟢 فتح يومية / وردية عمل جديدة</h3>
                <button wire:click="$set('showOpenModal', false)" class="text-slate-400 hover:text-slate-700 dark:hover:text-white">✕</button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الرصيد الافتتاحي للدرج (الفكة / العهدة):</label>
                    <input type="number" step="0.001" wire:model="opening_cash_balance" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ملاحظات الفتح:</label>
                    <textarea wire:model="open_notes" rows="2" placeholder="ملاحظات تسليم الدرج أو اسم الكاشير..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" wire:click="$set('showOpenModal', false)" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold cursor-pointer">إلغاء</button>
                <button type="button" wire:click="startShift" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 cursor-pointer">تأكيد فتح اليومية</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Close Shift Modal (Z-Report) -->
    @if($showCloseModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-rose-600 dark:text-rose-400 text-base">🔴 تقفيل اليومية (Z-Report)</h3>
                <button wire:click="$set('showCloseModal', false)" class="text-slate-400 hover:text-slate-700 dark:hover:text-white">✕</button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 font-mono space-y-1">
                    <div class="flex justify-between text-slate-500 dark:text-slate-400">
                        <span>الرصيد الافتتاحي:</span>
                        <span class="text-slate-900 dark:text-white">{{ number_format($activeShift->opening_cash_balance, 2) }} ج.م</span>
                    </div>
                    <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                        <span>+ النقدية المقبوضة:</span>
                        <span>{{ number_format($totalCashCollected, 2) }} ج.م</span>
                    </div>
                    @if(bccomp($totalExpenses, '0.000', 3) > 0)
                    <div class="flex justify-between text-rose-600 dark:text-rose-400">
                        <span>- المصروفات:</span>
                        <span>{{ number_format($totalExpenses, 2) }} ج.م</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-amber-600 dark:text-amber-400 font-bold pt-1 border-t border-slate-200 dark:border-slate-800 text-sm">
                        <span>💰 المفروض بالدرج:</span>
                        <span>{{ number_format($expectedCashInDrawer, 2) }} ج.م</span>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">النقدية الفعلية الموجودة في الدرج بعد العد والفرز:</label>
                    <input type="number" step="0.001" wire:model="actual_cash_balance" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-rose-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">ملاحظات التقفيل:</label>
                    <textarea wire:model="close_notes" rows="2" placeholder="ملاحظات تسليم الدرج أو أسباب العجز/الزيادة إن وجدت..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-rose-500"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" wire:click="$set('showCloseModal', false)" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold cursor-pointer">إلغاء</button>
                <button type="button" wire:click="submitCloseShift" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-rose-600/30 cursor-pointer">تأكيد التقفيل وإصدار Z-Report</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Treasury Transfer Modal -->
    @if($showTransferModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                    <span>🔄 تحويل رصيد مالي بين الخزائن والحسابات</span>
                </h3>
                <button wire:click="$set('showTransferModal', false)" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-sm cursor-pointer">✕</button>
            </div>

            @if($errorMessage)
            <div class="p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 text-xs font-bold">
                {{ $errorMessage }}
            </div>
            @endif

            <div class="space-y-3.5 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- From Method -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">التحويل من (الخزينة المحول منها):</label>
                        <select wire:model.live="transfer_from_method" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                            @foreach(\App\Enums\PaymentMethod::activeMethods() as $m)
                            @php $b = $treasuryBalances[$m->value]['balance'] ?? '0.000'; @endphp
                            <option value="{{ $m->value }}">{{ $m->icon() }} {{ $m->label() }} (رصيد: {{ number_format($b, 2) }} ج.م)</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- To Method -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">التحويل إلى (الخزينة المستلمة):</label>
                        <select wire:model.live="transfer_to_method" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                            @foreach(\App\Enums\PaymentMethod::activeMethods() as $m)
                            @php $b = $treasuryBalances[$m->value]['balance'] ?? '0.000'; @endphp
                            <option value="{{ $m->value }}">{{ $m->icon() }} {{ $m->label() }} (رصيد: {{ number_format($b, 2) }} ج.م)</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Amount & Fees -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">المبلغ المراد تحويله (ج.م):</label>
                        <input 
                            type="number" 
                            step="0.001" 
                            min="0.001" 
                            wire:model="transfer_amount" 
                            placeholder="0.00" 
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-bold text-purple-600 dark:text-purple-400 focus:ring-2 focus:ring-purple-500"
                        >
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">رسوم / عمولة التحويل (اختياري):</label>
                        <input 
                            type="number" 
                            step="0.001" 
                            min="0" 
                            wire:model="transfer_fee" 
                            placeholder="0.00" 
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-purple-500"
                        >
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">البيان / سبب التحويل:</label>
                    <input 
                        type="text" 
                        wire:model="transfer_notes" 
                        placeholder="مثال: سحب كاش من الـ ATM لتغذية درج المحل..." 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                    >
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" wire:click="$set('showTransferModal', false)" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold cursor-pointer">إلغاء</button>
                <button type="button" wire:click="executeTransfer" class="px-5 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-purple-600/30 cursor-pointer">تأكيد وتنفيذ التحويل</button>
            </div>
        </div>
    </div>
    @endif
</div>
