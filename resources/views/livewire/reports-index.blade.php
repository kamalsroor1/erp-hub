<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
        <div>
            <h2 class="text-xl font-black text-white flex items-center gap-2">
                <span>📈 التقارير المالية ومجمل الأرباح وتقييم المخزون</span>
            </h2>
            <p class="text-xs text-slate-400">تحليل الأرباح المحققة، تكلفة البضاعة المباعة (COGS)، ربحية كل صنف، وقيمة بضاعة المخزن</p>
        </div>

        <!-- Date Presets -->
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <button wire:click="setFilter('today')" class="px-3 py-1.5 rounded-xl font-bold border transition-colors {{ $dateFilter === 'today' ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-slate-900 border-slate-800 text-slate-400' }}">اليوم</button>
            <button wire:click="setFilter('this_week')" class="px-3 py-1.5 rounded-xl font-bold border transition-colors {{ $dateFilter === 'this_week' ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-slate-900 border-slate-800 text-slate-400' }}">هذا الأسبوع</button>
            <button wire:click="setFilter('this_month')" class="px-3 py-1.5 rounded-xl font-bold border transition-colors {{ $dateFilter === 'this_month' ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-slate-900 border-slate-800 text-slate-400' }}">هذا الشهر</button>
        </div>
    </div>

    <!-- 5 High Level Financial Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- Revenue -->
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl">
            <div class="text-xs font-bold text-slate-400">إجمالي المبيعات (الإيراد)</div>
            <div class="text-xl font-black text-white font-mono mt-2">{{ number_format($periodic['total_sales'], 2) }} <span class="text-xs text-emerald-400">ج.م</span></div>
            <div class="text-[11px] text-slate-400 mt-1">{{ $periodic['invoice_count'] }} فاتورة معتمدة</div>
        </div>

        <!-- COGS -->
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl">
            <div class="text-xs font-bold text-slate-400">تكلفة البضاعة المباعة (COGS)</div>
            <div class="text-xl font-black text-rose-400 font-mono mt-2">{{ number_format($periodic['total_cost'], 2) }} <span class="text-xs text-rose-300">ج.م</span></div>
            <div class="text-[11px] text-slate-400 mt-1">تكلفة الشراء للبضاعة المباعة</div>
        </div>

        <!-- Operational Expenses -->
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl">
            <div class="text-xs font-bold text-slate-400 flex items-center justify-between">
                <span>المصروفات والنثريات</span>
                <a href="{{ route('expenses.index') }}" class="text-[10px] text-amber-400 hover:underline">عرض</a>
            </div>
            <div class="text-xl font-black text-amber-400 font-mono mt-2">{{ number_format($totalExpenses, 2) }} <span class="text-xs text-amber-300">ج.م</span></div>
            <div class="text-[11px] text-slate-400 mt-1">شنط، أكواب، لاصق، تشغيل</div>
        </div>

        <!-- Net Profit After Expenses -->
        <div class="bg-slate-900 border border-emerald-500/40 p-4 rounded-2xl relative overflow-hidden bg-gradient-to-b from-slate-900 to-emerald-950/30">
            <div class="text-xs font-bold text-emerald-400">صافي الربح بعد المصاريف</div>
            <div class="text-xl font-black {{ bccomp($netProfitAfterExpenses, '0.000', 3) >= 0 ? 'text-emerald-300' : 'text-rose-400' }} font-mono mt-2">
                {{ number_format($netProfitAfterExpenses, 2) }} <span class="text-xs text-emerald-400">ج.م</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">الربح الصافي الفعلي بعد النفقات</div>
        </div>

        <!-- Stock Valuation -->
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl">
            <div class="text-xs font-bold text-slate-400">تقييم بضاعة المخزن (التكلفة)</div>
            <div class="text-xl font-black text-amber-300 font-mono mt-2">{{ number_format($stockCostValuation, 2) }} <span class="text-xs text-amber-300">ج.م</span></div>
            <div class="text-[11px] text-slate-400 mt-1">بسعر البيع: <span class="text-white font-mono">{{ number_format($stockSellingValuation, 2) }}</span></div>
        </div>
    </div>

    <!-- Item-level Profitability Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>تحليل أرباح كل صنف مباع خلال الفترة المحددة</span>
            </h3>
            <button onclick="window.print()" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-lg border border-slate-700">
                🖨️ طباعة التقرير
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="p-3.5">الصنف</th>
                        <th class="p-3.5">القسم</th>
                        <th class="p-3.5 text-center">الكمية المباعة</th>
                        <th class="p-3.5">إجمالي الإيراد</th>
                        <th class="p-3.5">إجمالي التكلفة</th>
                        <th class="p-3.5">صافي الربح</th>
                        <th class="p-3.5 text-center">نسبة هامش الربح</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($itemProfits as $row)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="p-3.5 font-bold text-slate-100">{{ $row['item']->name ?? '—' }}</td>
                        <td class="p-3.5 text-slate-400">{{ $row['item']->category ?? 'عام' }}</td>
                        <td class="p-3.5 text-center font-mono font-bold text-emerald-400">
                            {{ number_format($row['total_qty'], 3) }} {{ $row['item']->unit ?? 'كجم' }}
                        </td>
                        <td class="p-3.5 font-mono text-white">{{ number_format($row['total_revenue'], 2) }} ج.م</td>
                        <td class="p-3.5 font-mono text-slate-400">{{ number_format($row['total_cogs'], 2) }} ج.م</td>
                        <td class="p-3.5 font-mono font-black text-emerald-400">{{ number_format($row['profit'], 2) }} ج.م</td>
                        <td class="p-3.5 text-center font-mono font-bold text-slate-200">
                            <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                {{ $row['margin'] }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-500">لا توجد مبيعات مسجلة في هذه الفترة الزمنية</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
