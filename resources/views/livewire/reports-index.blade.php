<div class="space-y-6">
    <!-- Header & Period Filter Toolbar -->
    <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2 font-tajawal">
                    <span>📈 التقارير المالية ومجمل الأرباح ومؤشرات المبيعات</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    لوحة تحكم إدارية متكاملة لمتابعة المبيعات، الإيرادات، التكاليف، أداء الفروع وعربيات التوزيع، وحسابات الأرباح
                </p>
            </div>

            <!-- Store Selector -->
            <!-- Store Selector -->
            <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 py-2 shrink-0">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">🏬 نطاق الفرع:</span>
                <select wire:model.live="selectedStoreId" class="bg-transparent text-xs font-black text-slate-900 dark:text-white focus:outline-none cursor-pointer [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-slate-100">
                    <option class="bg-white dark:bg-slate-900 text-slate-900 dark:text-white" value="all">🏢 إجمالي كافة الفروع والمخازن وعربات التوزيع</option>
                    @foreach($stores as $st)
                    <option class="bg-white dark:bg-slate-900 text-slate-900 dark:text-white" value="{{ $st->id }}">
                        @if($st->type === 'wholesale_van') 🚚 @elseif($st->type === 'main_warehouse') 🏢 @else 🏬 @endif
                        {{ $st->name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Date Filters Buttons & Custom Date Pickers -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <!-- Preset Period Buttons -->
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                <button 
                    type="button"
                    wire:click="setFilter('today')" 
                    class="px-3.5 py-2 rounded-xl text-xs font-black transition-all cursor-pointer {{ $dateFilter === 'today' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200' }}"
                >
                    ☀️ مبيعات اليوم
                </button>
                <button 
                    type="button"
                    wire:click="setFilter('this_week')" 
                    class="px-3.5 py-2 rounded-xl text-xs font-black transition-all cursor-pointer {{ $dateFilter === 'this_week' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200' }}"
                >
                    📅 هذا الأسبوع
                </button>
                <button 
                    type="button"
                    wire:click="setFilter('this_month')" 
                    class="px-3.5 py-2 rounded-xl text-xs font-black transition-all cursor-pointer {{ $dateFilter === 'this_month' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200' }}"
                >
                    🗓️ هذا الشهر
                </button>
                <button 
                    type="button"
                    wire:click="setFilter('this_year')" 
                    class="px-3.5 py-2 rounded-xl text-xs font-black transition-all cursor-pointer {{ $dateFilter === 'this_year' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200' }}"
                >
                    📊 هذا العام ({{ date('Y') }})
                </button>
                <button 
                    type="button"
                    wire:click="setFilter('custom')" 
                    class="px-3.5 py-2 rounded-xl text-xs font-black transition-all cursor-pointer {{ $dateFilter === 'custom' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200' }}"
                >
                    🎯 فترة مخصصة
                </button>
            </div>

            <!-- Custom Date Inputs -->
            <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-950 p-1.5 rounded-2xl border border-slate-300 dark:border-slate-700 text-xs">
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 shrink-0">📅 من:</span>
                    <x-datepicker wire:model.live="fromDate" class="!h-8 !w-32 !py-1 !px-2 !text-xs" placeholder="من تاريخ" />
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 shrink-0">إلى:</span>
                    <x-datepicker wire:model.live="toDate" class="!h-8 !w-32 !py-1 !px-2 !text-xs" placeholder="إلى تاريخ" />
                </div>
            </div>
        </div>
    </div>

    <!-- 📑 Navigation Tabs Bar -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200 dark:border-slate-800">
        <button 
            type="button"
            wire:click="setTab('sales')" 
            class="px-4 py-3 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap transition-all cursor-pointer flex items-center gap-2 {{ $activeTab === 'sales' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-950 shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900' }}"
        >
            <span>📊 مبيعات وإيرادات الفترة</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $activeTab === 'sales' ? 'bg-amber-500 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                {{ $periodic['invoice_count'] }} فواتير
            </span>
        </button>

        <button 
            type="button"
            wire:click="setTab('items')" 
            class="px-4 py-3 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap transition-all cursor-pointer flex items-center gap-2 {{ $activeTab === 'items' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-950 shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900' }}"
        >
            <span>📦 حركة وربحية الأصناف</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $activeTab === 'items' ? 'bg-amber-500 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                {{ count($itemProfits) }} صنف
            </span>
        </button>

        <button 
            type="button"
            wire:click="setTab('stores')" 
            class="px-4 py-3 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap transition-all cursor-pointer flex items-center gap-2 {{ $activeTab === 'stores' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-950 shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900' }}"
        >
            <span>🏬 مقارنة أداء الفروع والعربيات</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $activeTab === 'stores' ? 'bg-amber-500 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                {{ count($storeBreakdown) }} فرع
            </span>
        </button>

        <button 
            type="button"
            wire:click="setTab('customers')" 
            class="px-4 py-3 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap transition-all cursor-pointer flex items-center gap-2 {{ $activeTab === 'customers' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-950 shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900' }}"
        >
            <span>👥 مبيعات وحسابات العملاء</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $activeTab === 'customers' ? 'bg-amber-500 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                {{ count($customerSales) }} عميل
            </span>
        </button>

        <button 
            type="button"
            wire:click="setTab('expenses')" 
            class="px-4 py-3 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap transition-all cursor-pointer flex items-center gap-2 {{ $activeTab === 'expenses' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-950 shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900' }}"
        >
            <span>💸 المصروفات وصافي الدخل</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $activeTab === 'expenses' ? 'bg-amber-500 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                {{ number_format($totalExpenses, 0) }} ج.م
            </span>
        </button>

        <button 
            type="button"
            wire:click="setTab('inventory')" 
            class="px-4 py-3 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap transition-all cursor-pointer flex items-center gap-2 {{ $activeTab === 'inventory' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-950 shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900' }}"
        >
            <span>🏢 تقييم بضاعة المخزن</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $activeTab === 'inventory' ? 'bg-amber-500 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                {{ count($allItems) }} صنف
            </span>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- 📊 TAB 1: مؤشرات المبيعات والإيرادات (Sales Dashboard)     -->
    <!-- ======================================================== -->
    @if($activeTab === 'sales')
    <div class="space-y-6">
        <!-- 6 Main KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3.5">
            <!-- 1. Total Sales -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-3xl shadow-sm space-y-2">
                <div class="flex items-center justify-between text-xs font-bold text-slate-500 dark:text-slate-400">
                    <span>إجمالي المبيعات</span>
                    <span class="text-emerald-500">💰</span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-mono">
                    {{ number_format($periodic['total_sales'], 2) }}
                    <span class="text-xs font-normal text-emerald-600">ج.م</span>
                </div>
                <div class="text-[11px] text-slate-400 font-bold">
                    {{ $periodic['invoice_count'] }} فاتورة معتمدة
                </div>
            </div>

            <!-- 2. Cash Collected -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-3xl shadow-sm space-y-2">
                <div class="flex items-center justify-between text-xs font-bold text-slate-500 dark:text-slate-400">
                    <span>التحصيل النقدي</span>
                    <span class="text-indigo-500">💵</span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-indigo-600 dark:text-indigo-400 font-mono">
                    {{ number_format($periodic['total_paid'], 2) }}
                    <span class="text-xs font-normal">ج.م</span>
                </div>
                <div class="text-[11px] text-slate-400 font-bold">
                    تم سدادها واستلامها في الدرج
                </div>
            </div>

            <!-- 3. Credit / Receivables in Period -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-3xl shadow-sm space-y-2">
                <div class="flex items-center justify-between text-xs font-bold text-slate-500 dark:text-slate-400">
                    <span>المبيعات الآجلة (المتبقي)</span>
                    <span class="text-amber-500">⏳</span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 font-mono">
                    {{ number_format($periodic['total_remaining'], 2) }}
                    <span class="text-xs font-normal">ج.م</span>
                </div>
                <div class="text-[11px] text-slate-400 font-bold">
                    ذمم على العملاء في هذه الفترة
                </div>
            </div>

            <!-- 4. COGS -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-3xl shadow-sm space-y-2">
                <div class="flex items-center justify-between text-xs font-bold text-slate-500 dark:text-slate-400">
                    <span>تكلفة البضاعة المباعة</span>
                    <span class="text-rose-500">📦</span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">
                    {{ number_format($periodic['total_cost'], 2) }}
                    <span class="text-xs font-normal">ج.م</span>
                </div>
                <div class="text-[11px] text-slate-400 font-bold">
                    تكلفة شراء الأصناف المباعة
                </div>
            </div>

            <!-- 5. Gross Profit -->
            <div class="bg-white dark:bg-slate-900 border border-emerald-500/40 p-4 rounded-3xl shadow-sm space-y-2 bg-gradient-to-b from-white dark:from-slate-900 to-emerald-500/5">
                <div class="flex items-center justify-between text-xs font-bold text-emerald-600 dark:text-emerald-400">
                    <span>مجمل أرباح المبيعات</span>
                    <span>📈</span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                    {{ number_format($periodic['gross_profit'], 2) }}
                    <span class="text-xs font-normal">ج.م</span>
                </div>
                <div class="text-[11px] text-emerald-700 dark:text-emerald-300 font-bold font-mono">
                    هامش الربح: {{ $periodic['margin_percentage'] }}%
                </div>
            </div>

            <!-- 6. Net Profit After Expenses -->
            <div class="bg-white dark:bg-slate-900 border border-indigo-500/40 p-4 rounded-3xl shadow-sm space-y-2 bg-gradient-to-b from-white dark:from-slate-900 to-indigo-500/5">
                <div class="flex items-center justify-between text-xs font-bold text-indigo-600 dark:text-indigo-400">
                    <span>صافي دخل النشاط</span>
                    <span>🏆</span>
                </div>
                <div class="text-xl sm:text-2xl font-black {{ bccomp($netProfitAfterExpenses, '0.000', 3) >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-600' }} font-mono">
                    {{ number_format($netProfitAfterExpenses, 2) }}
                    <span class="text-xs font-normal">ج.م</span>
                </div>
                <div class="text-[11px] text-slate-400 font-bold">
                    بعد خصم مصروفات ({{ number_format($totalExpenses, 2) }} ج.م)
                </div>
            </div>
        </div>

        <!-- Quick Store Sales Summary Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span>🏬 مقارنة أداء ومبيعات الفروع وعربيات التوزيع</span>
                </h3>
                <button wire:click="setTab('stores')" class="text-xs font-bold text-amber-600 hover:underline cursor-pointer">
                    عرض المقارنة التفصيلية ←
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($storeBreakdown as $sb)
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                            <span>{{ $sb['store']->type === 'wholesale_van' ? '🚚' : ($sb['store']->is_main ? '🏢' : '🏬') }}</span>
                            <span>{{ $sb['store']->name }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-bold mt-1">
                            {{ $sb['invoice_count'] }} فاتورة | مساهمة: {{ $sb['share_pct'] }}%
                        </div>
                    </div>
                    <div class="text-left">
                        <div class="text-sm font-black font-mono text-emerald-600 dark:text-emerald-400">
                            {{ number_format($sb['total_sales'], 2) }} ج.م
                        </div>
                        <div class="text-[10px] text-slate-500 font-bold">
                            ربح: {{ number_format($sb['gross_profit'], 2) }} ج.م
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- ======================================================== -->
    <!-- 📦 TAB 2: حركة وربحية الأصناف (Items Profitability)        -->
    <!-- ======================================================== -->
    @if($activeTab === 'items')
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>📦 تحليل مبيعات وربحية الأصناف مرتبة حسب الأعلى إيراداً</span>
            </h3>
            <span class="text-xs text-slate-500">{{ count($itemProfits) }} صنف مباع</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 font-bold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-3.5">#</th>
                        <th class="p-3.5">الصنف</th>
                        <th class="p-3.5 text-center">الكمية المباعة</th>
                        <th class="p-3.5">إجمالي المبيعات (الإيراد)</th>
                        <th class="p-3.5">تكلفة الشراء (COGS)</th>
                        <th class="p-3.5">مجمل الربح</th>
                        <th class="p-3.5 text-center">هامش الربح %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($itemProfits as $index => $row)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="p-3.5 font-bold font-mono text-slate-400">{{ $index + 1 }}</td>
                        <td class="p-3.5">
                            <span class="font-extrabold text-slate-900 dark:text-white text-xs sm:text-sm">{{ $row['item']->name ?? 'صنف غير معروف' }}</span>
                            @if($row['item']?->code)
                            <span class="block text-[10px] text-slate-400 font-mono">كود: {{ $row['item']->code }}</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-center font-black font-mono text-slate-900 dark:text-white">
                            {{ number_format($row['total_qty'], 2) }} {{ $row['item']?->unit }}
                        </td>
                        <td class="p-3.5 font-black font-mono text-emerald-600 dark:text-emerald-400">
                            {{ number_format($row['total_revenue'], 2) }} ج.م
                        </td>
                        <td class="p-3.5 font-bold font-mono text-rose-600 dark:text-rose-400">
                            {{ number_format($row['total_cogs'], 2) }} ج.م
                        </td>
                        <td class="p-3.5 font-black font-mono text-slate-900 dark:text-white">
                            {{ number_format($row['profit'], 2) }} ج.م
                        </td>
                        <td class="p-3.5 text-center">
                            <span class="px-2.5 py-1 rounded-xl text-[11px] font-black font-mono {{ (float)$row['margin'] >= 20 ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-600 border border-amber-500/20' }}">
                                {{ $row['margin'] }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">لا توجد مبيعات أصناف مسجلة في هذه الفترة</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- ======================================================== -->
    <!-- 🏬 TAB 3: مقارنة أداء الفروع والتوزيع (Stores Performance)  -->
    <!-- ======================================================== -->
    @if($activeTab === 'stores')
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>🏬 مقارنة الأداء والمبيعات عبر الفروع وعربات التوزيع</span>
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 font-bold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-3.5">الفرع / النقطة</th>
                        <th class="p-3.5 text-center">النوع</th>
                        <th class="p-3.5 text-center">عدد الفواتير</th>
                        <th class="p-3.5">إجمالي المبيعات</th>
                        <th class="p-3.5">التحصيل النقدي</th>
                        <th class="p-3.5">الآجل (المتبقي)</th>
                        <th class="p-3.5">مجمل الربح</th>
                        <th class="p-3.5 text-center">هامش الربح %</th>
                        <th class="p-3.5 text-center">نسبة المساهمة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach($storeBreakdown as $sb)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="p-3.5 font-black text-slate-900 dark:text-white text-xs sm:text-sm">
                            {{ $sb['store']->name }}
                        </td>
                        <td class="p-3.5 text-center">
                            <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold {{ $sb['store']->type === 'wholesale_van' ? 'bg-amber-500/10 text-amber-600' : ($sb['store']->is_main ? 'bg-indigo-500/10 text-indigo-600' : 'bg-emerald-500/10 text-emerald-600') }}">
                                {{ $sb['store']->type === 'wholesale_van' ? '🚚 سيارة توزيع' : ($sb['store']->is_main ? '🏢 رئيسي' : '🏬 فرع') }}
                            </span>
                        </td>
                        <td class="p-3.5 text-center font-bold font-mono">{{ $sb['invoice_count'] }}</td>
                        <td class="p-3.5 font-black font-mono text-emerald-600 dark:text-emerald-400">
                            {{ number_format($sb['total_sales'], 2) }} ج.م
                        </td>
                        <td class="p-3.5 font-bold font-mono text-indigo-600 dark:text-indigo-400">
                            {{ number_format($sb['total_paid'], 2) }} ج.م
                        </td>
                        <td class="p-3.5 font-bold font-mono text-amber-600 dark:text-amber-400">
                            {{ number_format($sb['total_remaining'], 2) }} ج.م
                        </td>
                        <td class="p-3.5 font-black font-mono text-slate-900 dark:text-white">
                            {{ number_format($sb['gross_profit'], 2) }} ج.م
                        </td>
                        <td class="p-3.5 text-center font-black font-mono text-emerald-600">
                            {{ $sb['margin'] }}%
                        </td>
                        <td class="p-3.5 text-center font-black font-mono text-indigo-600">
                            {{ $sb['share_pct'] }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- ======================================================== -->
    <!-- 👥 TAB 4: حسابات وديون العملاء (Customers Analytics)        -->
    <!-- ======================================================== -->
    @if($activeTab === 'customers')
    <div class="space-y-4">
        <!-- Customer Debt Banner -->
        <div class="bg-gradient-to-r from-amber-600 to-amber-500 rounded-3xl p-5 text-white shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="text-base sm:text-lg font-black">إجمالي مديونيات وحسابات كافة العملاء المسجلة بالنظام</h3>
                <p class="text-xs text-amber-100 mt-0.5">مجموع أرصدة الذمم المستحقة على العملاء حتى هذه اللحظة</p>
            </div>
            <div class="text-2xl sm:text-3xl font-black font-mono bg-white/20 px-4 py-2 rounded-2xl backdrop-blur-sm">
                {{ number_format($totalAllCustomersDebt, 2) }} <span class="text-xs font-normal">ج.م</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
            <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">
                    👥 كبار العملاء الأكثر شراءً وتعاملات خلال الفترة
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 font-bold border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="p-3.5">العميل</th>
                            <th class="p-3.5">رقم الهاتف</th>
                            <th class="p-3.5 text-center">عدد الفواتير</th>
                            <th class="p-3.5">إجمالي المشتريات</th>
                            <th class="p-3.5">المسدد نقداً</th>
                            <th class="p-3.5">المتبقي بالفترة</th>
                            <th class="p-3.5">الرصيد الإجمالي الحالي</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($customerSales as $cs)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="p-3.5 font-black text-slate-900 dark:text-white text-xs sm:text-sm">
                                {{ $cs->customer?->name ?? 'عميل غير مسجل' }}
                            </td>
                            <td class="p-3.5 font-mono text-slate-500" dir="ltr">
                                {{ $cs->customer?->phone ?? '-' }}
                            </td>
                            <td class="p-3.5 text-center font-bold font-mono">{{ $cs->total_invoices }}</td>
                            <td class="p-3.5 font-black font-mono text-emerald-600 dark:text-emerald-400">
                                {{ number_format($cs->total_bought, 2) }} ج.م
                            </td>
                            <td class="p-3.5 font-bold font-mono text-indigo-600 dark:text-indigo-400">
                                {{ number_format($cs->total_paid, 2) }} ج.م
                            </td>
                            <td class="p-3.5 font-bold font-mono text-amber-600 dark:text-amber-400">
                                {{ number_format($cs->total_debt_in_period, 2) }} ج.م
                            </td>
                            <td class="p-3.5 font-black font-mono text-slate-900 dark:text-white">
                                {{ number_format($cs->customer?->current_balance ?? 0, 2) }} ج.م
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">لا توجد حركات مبيعات عملاء في هذه الفترة</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- ======================================================== -->
    <!-- 💸 TAB 5: المصروفات وصافي الدخل (Expenses & Net Income)     -->
    <!-- ======================================================== -->
    @if($activeTab === 'expenses')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Expenses Summary Cards -->
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl shadow-sm space-y-3">
                <div class="text-xs font-bold text-slate-500">إجمالي مجمل الربح من المبيعات</div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                    {{ number_format($periodic['gross_profit'], 2) }} ج.م
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-rose-500/30 p-5 rounded-3xl shadow-sm space-y-3 bg-gradient-to-b from-white dark:from-slate-900 to-rose-500/5">
                <div class="text-xs font-bold text-rose-500">إجمالي المصروفات والنثريات التشغيلية</div>
                <div class="text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">
                    -{{ number_format($totalExpenses, 2) }} ج.م
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-indigo-500/40 p-5 rounded-3xl shadow-sm space-y-3 bg-gradient-to-b from-white dark:from-slate-900 to-indigo-500/10">
                <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">الصافي الفعلي للأرباح (Net Income)</div>
                <div class="text-3xl font-black {{ bccomp($netProfitAfterExpenses, '0.000', 3) >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-600' }} font-mono">
                    {{ number_format($netProfitAfterExpenses, 2) }} <span class="text-xs font-normal">ج.م</span>
                </div>
                <p class="text-[11px] text-slate-500">صافي الأرباح الصريحة بعد استبعاد كافة النفقات والرواتب والمصروفات</p>
            </div>
        </div>

        <!-- Expenses by Category Table -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
            <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">
                    💸 تفصيل المصروفات حسب بنود الصرف خلال الفترة
                </h3>
                <a href="{{ route('expenses.index') }}" class="text-xs font-bold text-amber-600 hover:underline">
                    إدارة المصروفات ←
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 font-bold border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="p-3.5">بند / تصنيف المصروف</th>
                            <th class="p-3.5 text-center">عدد الحركات</th>
                            <th class="p-3.5">إجمالي المبلغ المنصرف</th>
                            <th class="p-3.5 text-center">النسبة من إجمالي المصاريف</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($expensesByCategory as $ec)
                        @php
                            $catPct = '0.0';
                            if (bccomp($totalExpenses, '0.000', 3) > 0) {
                                $catPct = bcmul(bcdiv($ec->total_amount, $totalExpenses, 4), '100', 1);
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="p-3.5 font-bold text-slate-900 dark:text-white">{{ $ec->category }}</td>
                            <td class="p-3.5 text-center font-bold font-mono">{{ $ec->count }}</td>
                            <td class="p-3.5 font-black font-mono text-rose-600 dark:text-rose-400">
                                {{ number_format($ec->total_amount, 2) }} ج.م
                            </td>
                            <td class="p-3.5 text-center font-black font-mono text-slate-700 dark:text-slate-300">
                                {{ $catPct }}%
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400">لا توجد مصروفات مسجلة في هذه الفترة</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- ======================================================== -->
    <!-- 🏢 TAB 6: تقييم المخزون (Inventory Valuation)              -->
    <!-- ======================================================== -->
    @if($activeTab === 'inventory')
    <div class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl shadow-sm space-y-2">
                <div class="text-xs font-bold text-slate-500">
                    قيمة البضاعة بسعر التكلفة 
                    @if($selectedStore) ({{ $selectedStore->name }}) @endif
                </div>
                <div class="text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400 font-mono">
                    {{ number_format($stockCostValuation, 2) }} <span class="text-xs font-normal">ج.م</span>
                </div>
                <div class="text-[11px] text-slate-400">رأس المال المستثمر في البضاعة حالياً</div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl shadow-sm space-y-2">
                <div class="text-xs font-bold text-slate-500">
                    قيمة البضاعة بسعر البيع المتوقع
                    @if($selectedStore) ({{ $selectedStore->name }}) @endif
                </div>
                <div class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                    {{ number_format($stockSellingValuation, 2) }} <span class="text-xs font-normal">ج.م</span>
                </div>
                <div class="text-[11px] text-slate-400">المردود المتوقع عند بيع كامل المخزون</div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-indigo-500/40 p-5 rounded-3xl shadow-sm space-y-2 bg-gradient-to-b from-white dark:from-slate-900 to-indigo-500/5">
                <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                    الأرباح المتوقعة في المخزن
                    @if($selectedStore) ({{ $selectedStore->name }}) @endif
                </div>
                <div class="text-2xl sm:text-3xl font-black text-indigo-600 dark:text-indigo-400 font-mono">
                    {{ number_format($expectedStockProfit, 2) }} <span class="text-xs font-normal">ج.م</span>
                </div>
                <div class="text-[11px] text-slate-400">فارق سعر البيع عن سعر التكلفة</div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
            <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex flex-wrap items-center gap-2">
                    <span>🏢 جرد وتقييم الأصناف:</span>
                    @if($selectedStore)
                        <span class="px-2.5 py-0.5 rounded-xl bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20 text-xs font-bold">
                            @if($selectedStore->type === 'wholesale_van') 🚚 @elseif($selectedStore->type === 'main_warehouse') 🏢 @else 🏬 @endif {{ $selectedStore->name }}
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-xl bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 text-xs font-bold">
                            🏢 إجمالي كافة الفروع والمخازن
                        </span>
                    @endif
                </h3>

                <!-- Stock Quantity Filter Buttons -->
                <div class="flex flex-wrap items-center gap-1.5 bg-slate-100 dark:bg-slate-950 p-1 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs">
                    <button 
                        type="button" 
                        wire:click="$set('inventoryStockFilter', 'all')" 
                        class="px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $inventoryStockFilter === 'all' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm border border-slate-200 dark:border-slate-700' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}"
                    >
                        <span>🏢 كل الأصناف</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold bg-slate-200 dark:bg-slate-700">{{ $totalInventoryCount }}</span>
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('inventoryStockFilter', 'in_stock')" 
                        class="px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $inventoryStockFilter === 'in_stock' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400' }}"
                    >
                        <span>📦 متوفر كمية فقط (> 0)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold {{ $inventoryStockFilter === 'in_stock' ? 'bg-white text-emerald-700' : 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300' }}">{{ $inStockCount }}</span>
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('inventoryStockFilter', 'zero_stock')" 
                        class="px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $inventoryStockFilter === 'zero_stock' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30' : 'text-slate-600 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400' }}"
                    >
                        <span>🚫 الرصيد صفر (0)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold {{ $inventoryStockFilter === 'zero_stock' ? 'bg-white text-rose-700' : 'bg-rose-500/20 text-rose-700 dark:text-rose-300' }}">{{ $zeroStockCount }}</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 font-bold border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="p-3.5">#</th>
                            <th class="p-3.5">الصنف</th>
                            <th class="p-3.5 text-center">{{ $selectedStore ? 'الرصيد في الفرع' : 'الرصيد الكلي' }}</th>
                            <th class="p-3.5">سعر التكلفة</th>
                            <th class="p-3.5">سعر البيع</th>
                            <th class="p-3.5">القيمة بالتكلفة</th>
                            <th class="p-3.5">القيمة بسعر البيع</th>
                            <th class="p-3.5">الربح المتوقع</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($inventoryItems as $index => $item)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="p-3.5 font-bold font-mono text-slate-400">{{ $index + 1 }}</td>
                            <td class="p-3.5">
                                <span class="font-extrabold text-slate-900 dark:text-white text-xs sm:text-sm">{{ $item->name }}</span>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[10px] text-slate-400 font-mono">كود: {{ $item->code }}</span>
                                    @if(!empty($item->has_custom_price))
                                    <span class="text-[9px] px-1.5 py-0.5 bg-amber-500/15 text-amber-700 dark:text-amber-400 rounded font-bold">سعر مخصص للفرع</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3.5 text-center font-black font-mono {{ bccomp($item->current_stock, '0.000', 3) <= 0 ? 'text-rose-600' : 'text-slate-900 dark:text-white' }}">
                                {{ number_format($item->current_stock, 2) }} {{ $item->unit }}
                            </td>
                            <td class="p-3.5 font-mono text-slate-600 dark:text-slate-400">
                                {{ number_format($item->cost_price, 2) }} ج.م
                            </td>
                            <td class="p-3.5 font-bold font-mono text-slate-900 dark:text-white">
                                {{ number_format($item->selling_price, 2) }} ج.م
                            </td>
                            <td class="p-3.5 font-black font-mono text-amber-600 dark:text-amber-400">
                                {{ number_format($item->cost_val, 2) }} ج.م
                            </td>
                            <td class="p-3.5 font-black font-mono text-emerald-600 dark:text-emerald-400">
                                {{ number_format($item->sell_val, 2) }} ج.م
                            </td>
                            <td class="p-3.5 font-black font-mono {{ bccomp($item->profit, '0.000', 3) >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-600' }}">
                                {{ number_format($item->profit, 2) }} ج.م
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
