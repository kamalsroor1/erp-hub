<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-600 flex items-center justify-center text-white shadow-md shadow-amber-500/20">
                    <span class="text-xl">🧠</span>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-tajawal">
                        مساعد المشتريات الذكي والتنبؤ بالنواقص
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        حساب معدلات السحب اليومي وتوقع الأيام المتبقية لنفاد كل صنف واقتراح كميات التوريد
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a 
                href="{{ route('purchases.index') }}" 
                class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-2xl transition-all flex items-center gap-2"
            >
                📋 فواتير المشتريات
            </a>

            @if(count($selectedItems) > 0)
            <button 
                wire:click="createPurchaseOrder" 
                class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xs rounded-2xl shadow-md shadow-emerald-600/30 transition-all flex items-center gap-2 cursor-pointer"
            >
                <span>➕ إنشاء فاتورة شراء ({{ count($selectedItems) }})</span>
            </button>
            @endif
        </div>
    </div>

    <!-- 3 Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-rose-50 dark:bg-rose-950/40 border-2 border-rose-200 dark:border-rose-900/60 rounded-3xl p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-rose-600 dark:text-rose-400 block mb-1">🚨 أصناف حرجة أوشكت على النفاد (خلال 3 أيام):</span>
                <span class="text-2xl sm:text-3xl font-black font-mono text-rose-700 dark:text-rose-300">{{ $summary['critical_count'] }}</span>
                <span class="text-xs text-rose-500 font-bold">صنف يحتاج طلب فوري</span>
            </div>
            <span class="text-3xl">⚠️</span>
        </div>

        <div class="bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-200 dark:border-amber-900/60 rounded-3xl p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-amber-600 dark:text-amber-400 block mb-1">⚠️ أصناف تحتاج مراقبة (تنتهي خلال أسبوع):</span>
                <span class="text-2xl sm:text-3xl font-black font-mono text-amber-700 dark:text-amber-300">{{ $summary['warning_count'] }}</span>
                <span class="text-xs text-amber-500 font-bold">صنف يقترب من نقطة الطلب</span>
            </div>
            <span class="text-3xl">⏳</span>
        </div>

        <div class="bg-emerald-50 dark:bg-emerald-950/40 border-2 border-emerald-200 dark:border-emerald-900/60 rounded-3xl p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 block mb-1">✅ أصناف في المنطقة الآمنة (> أسبوع):</span>
                <span class="text-2xl sm:text-3xl font-black font-mono text-emerald-700 dark:text-emerald-300">{{ $summary['safe_count'] }}</span>
                <span class="text-xs text-emerald-500 font-bold">رصيد كافٍ ومستقر</span>
            </div>
            <span class="text-3xl">🛡️</span>
        </div>
    </div>

    <!-- Filters & Settings Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">بحث في الأصناف:</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="ابحث باسم الصنف أو الكود..."
                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white text-xs placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:outline-none"
                >
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">الفرع / المخزن:</label>
                <select
                    wire:model.live="selectedStoreId"
                    class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"
                >
                    <option value="all">كل الفروع والمخازن</option>
                    @foreach($stores as $st)
                    <option value="{{ $st->id }}">{{ $st->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">فترة تحليل المبيعات السابقة:</label>
                <select
                    wire:model.live="analysisDays"
                    class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"
                >
                    <option value="7">آخر 7 أيام (أسبوع)</option>
                    <option value="14">آخر 14 يوماً (أسبوعين - موصى به)</option>
                    <option value="30">آخر 30 يوماً (شهر كامل)</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">أيام التغطية المستهدفة للشراء:</label>
                <select
                    wire:model.live="targetCoverDays"
                    class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"
                >
                    <option value="7">تغطية 7 أيام</option>
                    <option value="15">تغطية 15 يوماً (نصف شهر)</option>
                    <option value="30">تغطية 30 يوماً (شهر)</option>
                    <option value="45">تغطية 45 يوماً</option>
                </select>
            </div>
        </div>

        <!-- Quick Urgency Filter Pills & Select All Button -->
        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                <button wire:click="$set('filterUrgency', 'all')" class="px-3 py-1.5 rounded-xl text-xs font-bold cursor-pointer transition-colors {{ $filterUrgency === 'all' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-950 font-black' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">الكل ({{ count($summary['suggestions']) }})</button>
                <button wire:click="$set('filterUrgency', 'critical')" class="px-3 py-1.5 rounded-xl text-xs font-bold cursor-pointer transition-colors {{ $filterUrgency === 'critical' ? 'bg-rose-600 text-white font-black' : 'bg-rose-500/10 text-rose-600' }}">🚨 النواقص الحرجة ({{ $summary['critical_count'] }})</button>
                <button wire:click="$set('filterUrgency', 'warning')" class="px-3 py-1.5 rounded-xl text-xs font-bold cursor-pointer transition-colors {{ $filterUrgency === 'warning' ? 'bg-amber-600 text-white font-black' : 'bg-amber-500/10 text-amber-600' }}">⚠️ قرب النفاد ({{ $summary['warning_count'] }})</button>
                <button wire:click="$set('filterUrgency', 'safe')" class="px-3 py-1.5 rounded-xl text-xs font-bold cursor-pointer transition-colors {{ $filterUrgency === 'safe' ? 'bg-emerald-600 text-white font-black' : 'bg-emerald-500/10 text-emerald-600' }}">✅ الآمنة ({{ $summary['safe_count'] }})</button>
            </div>

            @if(count($criticalItemIds) > 0)
            <button 
                type="button" 
                wire:click="selectAllCritical(@js($criticalItemIds))" 
                class="text-xs text-amber-600 dark:text-amber-400 font-bold hover:underline cursor-pointer flex items-center gap-1"
            >
                <span>⚡ تحديد كافة النواقص الحرجة والتحذيرية</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Suggestions Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950/80 text-xs font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-3.5 text-center w-10">تحديد</th>
                        <th class="p-3.5 text-center">الحالة</th>
                        <th class="p-3.5">الكود</th>
                        <th class="p-3.5">اسم الصنف</th>
                        <th class="p-3.5 text-center">الرصيد الحالي</th>
                        <th class="p-3.5 text-center">السحب اليومي</th>
                        <th class="p-3.5 text-center">الأيام المتبقية</th>
                        <th class="p-3.5 text-center font-black">الكمية المقترح شراؤها</th>
                        <th class="p-3.5 text-center">سعر التكلفة</th>
                        <th class="p-3.5 text-center">إجمالي التكلفة التقديرية</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-sans">
                    @forelse($suggestions as $item)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors {{ in_array($item['id'], $selectedItems) ? 'bg-amber-500/5 dark:bg-amber-500/10' : '' }}">
                        <td class="p-3.5 text-center">
                            <input 
                                type="checkbox" 
                                value="{{ $item['id'] }}" 
                                wire:model.live="selectedItems"
                                class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500 cursor-pointer"
                            >
                        </td>
                        <td class="p-3.5 text-center">
                            @if($item['urgency'] === 'critical')
                                <span class="px-2.5 py-1 rounded-xl bg-rose-600 text-white font-black text-[11px] shadow-sm shadow-rose-600/30">🚨 سينفد فوراً</span>
                            @elseif($item['urgency'] === 'warning')
                                <span class="px-2.5 py-1 rounded-xl bg-amber-500 text-white font-black text-[11px]">⚠️ قرب النفاد</span>
                            @else
                                <span class="px-2.5 py-1 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">✅ رصيد آمن</span>
                            @endif
                        </td>
                        <td class="p-3.5 font-mono text-slate-500" dir="ltr">{{ $item['code'] }}</td>
                        <td class="p-3.5 font-bold text-slate-900 dark:text-white">{{ $item['name'] }}</td>
                        <td class="p-3.5 text-center font-mono font-bold text-slate-700 dark:text-slate-300">
                            {{ number_format((float)$item['current_stock'], 2) }} {{ $item['unit'] }}
                        </td>
                        <td class="p-3.5 text-center font-mono font-bold text-cyan-600 dark:text-cyan-400">
                            {{ number_format((float)$item['daily_consumption'], 2) }} / يوم
                        </td>
                        <td class="p-3.5 text-center font-mono font-black {{ $item['days_remaining'] <= 3 ? 'text-rose-600 dark:text-rose-400' : ($item['days_remaining'] <= 7 ? 'text-amber-600' : 'text-slate-600 dark:text-slate-400') }}">
                            @if($item['days_remaining'] >= 999)
                                ∞ (لا حركة)
                            @else
                                {{ $item['days_remaining'] }} يوم
                            @endif
                        </td>
                        <td class="p-3.5 text-center font-mono font-black text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-50/50 dark:bg-emerald-950/20">
                            @if(bccomp((string)$item['suggested_quantity'], '0.000', 3) > 0)
                                +{{ number_format((float)$item['suggested_quantity'], 2) }} {{ $item['unit'] }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="p-3.5 text-center font-mono text-slate-500 dark:text-slate-400">
                            {{ number_format((float)$item['unit_cost'], 2) }} ج.م
                        </td>
                        <td class="p-3.5 text-center font-mono font-black text-slate-900 dark:text-white">
                            @if(bccomp((string)$item['estimated_cost'], '0.000', 3) > 0)
                                {{ number_format((float)$item['estimated_cost'], 2) }} ج.م
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="p-8 text-center text-slate-400">
                            لا توجد أصناف تطابق خيارات البحث والتصفية الحالية.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
