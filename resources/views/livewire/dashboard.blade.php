<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 backdrop-blur-sm shadow-sm">
        <div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>مرحباً بك في نظام سرور لإدارة الفواتير</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30">Phase 1 Live</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">نظرة عامة على المبيعات، رصيد الخزينة، المخزون، وحسابات العملاء</p>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">
            <a href="{{ route('invoices.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>فاتورة بيع سريعة (POS)</span>
            </a>
            <a href="{{ route('purchases.create') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 flex items-center justify-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>فاتورة شراء (توريد)</span>
            </a>
        </div>
    </div>

    <!-- 4 Key Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Today Sales -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm relative overflow-hidden group hover:border-emerald-500/40 transition-all">
            <div class="absolute -top-10 -left-10 w-28 h-28 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">مبيعات اليوم</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">{{ number_format($todaySales, 2) }} <span class="text-xs text-emerald-600 dark:text-emerald-400">ج.م</span></div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $todayInvoicesCount }} فاتورة معتمدة اليوم</div>
            </div>
        </div>

        <!-- Monthly Gross Profit -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm relative overflow-hidden group hover:border-teal-500/40 transition-all">
            <div class="absolute -top-10 -left-10 w-28 h-28 bg-teal-500/10 rounded-full blur-2xl group-hover:bg-teal-500/20 transition-all"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">مجمل أرباح الشهر</span>
                <span class="p-2 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-teal-600 dark:text-teal-400 font-mono">{{ number_format($monthlyGrossProfit, 2) }} <span class="text-xs text-teal-500 dark:text-teal-300">ج.م</span></div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">هامش ربح: <span class="text-slate-900 dark:text-white font-bold font-mono">{{ $monthlyMargin }}%</span></div>
            </div>
        </div>

        <!-- Outstanding Debts -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm relative overflow-hidden group hover:border-amber-500/40 transition-all">
            <div class="absolute -top-10 -left-10 w-28 h-28 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-all"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">إجمالي ديون العملاء (الآجل)</span>
                <span class="p-2 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono">{{ number_format($totalCustomersDebt, 2) }} <span class="text-xs text-amber-500 dark:text-amber-300">ج.م</span></div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">مستحقات واجبة التحصيل</div>
            </div>
        </div>

        <!-- Monthly Sales -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm relative overflow-hidden group hover:border-indigo-500/40 transition-all">
            <div class="absolute -top-10 -left-10 w-28 h-28 bg-indigo-500/10 rounded-full blur-2xl group-hover:bg-indigo-500/20 transition-all"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">مبيعات الشهر الحالي</span>
                <span class="p-2 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-indigo-600 dark:text-indigo-300 font-mono">{{ number_format($monthlySales, 2) }} <span class="text-xs text-indigo-500 dark:text-indigo-400">ج.م</span></div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">إجمالي تعاملات الشهر</div>
            </div>
        </div>
    </div>

    <!-- 2 Column Section: Recent Invoices & Low Stock Alert -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Invoices (2 Cols) -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>آخر فواتير المبيعات الصادرة</span>
                </h3>
                <a href="{{ route('invoices.index') }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline font-bold">عرض الكل ←</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="p-3">رقم الفاتورة</th>
                            <th class="p-3">العميل</th>
                            <th class="p-3">التاريخ</th>
                            <th class="p-3">الإجمالي</th>
                            <th class="p-3">الحالة</th>
                            <th class="p-3 text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                        @forelse($recentInvoices as $inv)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="p-3 font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $inv->invoice_number }}</td>
                            <td class="p-3 font-bold text-slate-800 dark:text-slate-200">{{ $inv->customer->name }}</td>
                            <td class="p-3 text-slate-500 dark:text-slate-400 font-mono">{{ $inv->invoice_date->format('Y-m-d') }}</td>
                            <td class="p-3 font-mono font-bold text-slate-900 dark:text-white">{{ number_format($inv->net_total, 2) }} ج.م</td>
                            <td class="p-3">
                                @if($inv->status === 'cancelled')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">ملغاة</span>
                                @elseif($inv->payment_status === 'paid')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">مدفوعة</span>
                                @elseif($inv->payment_status === 'partially_paid')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">جزئي</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">آجل</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <a href="{{ route('invoices.show', $inv->id) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-[11px] transition-colors border border-slate-200 dark:border-slate-700">
                                    معاينة / طباعة
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-400">لا توجد فواتير مسجلة حتى الآن</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Alerts (1 Col) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    <span>تنبيهات النواقص بالمخزن</span>
                </h3>
                <a href="{{ route('items.index') }}" class="text-xs text-slate-500 dark:text-slate-400 hover:underline">إدارة الأصناف ←</a>
            </div>

            <div class="p-3 divide-y divide-slate-200 dark:divide-slate-800/60">
                @forelse($lowStockItems as $item)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-slate-800 dark:text-slate-200 text-xs">{{ $item->name }}</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">كود: {{ $item->code }}</div>
                    </div>
                    <div class="text-left">
                        <span class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                            {{ number_format($item->current_stock, 2) }} {{ $item->unit }}
                        </span>
                        <div class="text-[10px] text-slate-400 mt-0.5">الحد الأدنى: {{ number_format($item->min_stock_level, 2) }}</div>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-slate-400 text-xs">
                    جميع الأصناف متوفرة فوق الحد الأدنى 👍
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
