<div class="space-y-6">
    <!-- Header & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900/60 p-4 sm:p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('items.index') }}" class="p-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold transition-colors">
                    ← العودة للأصناف
                </a>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span>📋 كارت حركة الصنف (Stock Card):</span>
                    <span class="text-emerald-600 dark:text-emerald-400">{{ $item->name }}</span>
                </h2>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400 pt-1">
                <span>الكود: <strong class="font-mono text-slate-800 dark:text-slate-200">{{ $item->code }}</strong></span>
                <span>•</span>
                <span>القسم: <strong class="text-slate-800 dark:text-slate-200">{{ $item->category ?: 'عام' }}</strong></span>
                <span>•</span>
                <span>الوحدة: <strong class="text-slate-800 dark:text-slate-200">{{ $item->unit }}</strong></span>
                @can('items.view_cost')
                <span>•</span>
                <span>التكلفة: <strong class="font-mono text-slate-800 dark:text-slate-200">{{ number_format($item->cost_price, 2) }} ج.م</strong></span>
                @endcan
                <span>•</span>
                <span>البيع: <strong class="font-mono text-emerald-600 dark:text-emerald-400 font-bold">{{ number_format($item->selling_price, 2) }} ج.م</strong></span>
            </div>
        </div>

        <!-- Print & Export Buttons -->
        <div class="flex items-center gap-2 self-start md:self-auto">
            <a 
                href="{{ route('items.movements.print', ['id' => $item->id, 'store_id' => $selectedStoreId, 'from' => $fromDate, 'to' => $toDate, 'type' => $filterType]) }}" 
                target="_blank"
                class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 flex items-center gap-1.5 transition-all cursor-pointer"
            >
                <span>🖨️ طباعة A4</span>
            </a>

            <a 
                href="{{ route('items.movements.export', ['id' => $item->id, 'store_id' => $selectedStoreId, 'from' => $fromDate, 'to' => $toDate, 'type' => $filterType]) }}" 
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center gap-1.5 transition-all cursor-pointer"
            >
                <span>📥 تصدير Excel / CSV</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Inbound Total -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-bold">إجمالي الوارد للفترة (+)</span>
                    <span class="text-xl sm:text-2xl font-mono font-black text-emerald-600 dark:text-emerald-400 mt-1 block">
                        +{{ number_format($totalIn, 3) }}
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-lg">
                    📥
                </div>
            </div>
            <span class="text-[10px] text-slate-400 mt-2 block">
                @if($selectedStoreId !== 'all')
                    مشتريات، إيداعات، تحويلات واردة للفرع
                @else
                    مشتريات خارجية، رصيد افتتاحي، إيداعات
                @endif
            </span>
        </div>

        <!-- Outbound Total -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-bold">إجمالي المنصرف للفترة (-)</span>
                    <span class="text-xl sm:text-2xl font-mono font-black text-rose-600 dark:text-rose-400 mt-1 block">
                        -{{ number_format($totalOut, 3) }}
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center text-lg">
                    📤
                </div>
            </div>
            <span class="text-[10px] text-slate-400 mt-2 block">
                @if($selectedStoreId !== 'all')
                    مبيعات الفرع، هالك، تحويلات صادرة
                @else
                    إجمالي مبيعات الشركة، هالك، عجز جرد
                @endif
            </span>
        </div>

        <!-- Net Movement -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-bold">صافي الحركة للفترة</span>
                    <span class="text-xl sm:text-2xl font-mono font-black {{ bccomp($netMovement, '0.000', 3) >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-amber-600 dark:text-amber-400' }} mt-1 block">
                        {{ bccomp($netMovement, '0.000', 3) > 0 ? '+' : '' }}{{ number_format($netMovement, 3) }}
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center text-lg">
                    ⚖️
                </div>
            </div>
            <span class="text-[10px] text-slate-400 mt-2 block">فارق الوارد عن المنصرف</span>
        </div>

        <!-- Current Stock -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-bold">الرصيد الفعلي الحالي</span>
                    <span class="text-xl sm:text-2xl font-mono font-black text-slate-900 dark:text-white mt-1 block">
                        {{ number_format($currentScopeStock, 3) }} <span class="text-xs font-sans font-bold text-slate-500">{{ $item->unit }}</span>
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center text-lg">
                    📦
                </div>
            </div>
            <span class="text-[10px] text-slate-400 mt-2 block">الرصيد الحي بالمخزن حالياً</span>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
        <!-- Presets Buttons & Branch Selection -->
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            <!-- Quick Presets -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <span class="text-slate-500 dark:text-slate-400 text-[11px] font-bold">الفترة:</span>
                <button wire:click="applyDatePreset('today')" class="px-3 py-1.5 rounded-xl font-bold border transition-colors cursor-pointer {{ $datePreset === 'today' ? 'bg-emerald-600 text-white border-emerald-500 shadow-sm' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50' }}">
                    اليوم
                </button>
                <button wire:click="applyDatePreset('this_week')" class="px-3 py-1.5 rounded-xl font-bold border transition-colors cursor-pointer {{ $datePreset === 'this_week' ? 'bg-emerald-600 text-white border-emerald-500 shadow-sm' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50' }}">
                    هذا الأسبوع
                </button>
                <button wire:click="applyDatePreset('this_month')" class="px-3 py-1.5 rounded-xl font-bold border transition-colors cursor-pointer {{ $datePreset === 'this_month' ? 'bg-emerald-600 text-white border-emerald-500 shadow-sm' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50' }}">
                    هذا الشهر
                </button>
                <button wire:click="applyDatePreset('this_year')" class="px-3 py-1.5 rounded-xl font-bold border transition-colors cursor-pointer {{ $datePreset === 'this_year' ? 'bg-emerald-600 text-white border-emerald-500 shadow-sm' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50' }}">
                    هذا العام
                </button>
                <button wire:click="applyDatePreset('all')" class="px-3 py-1.5 rounded-xl font-bold border transition-colors cursor-pointer {{ $datePreset === 'all' ? 'bg-slate-700 text-white border-slate-600 shadow-sm' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50' }}">
                    كل المدة (أول المدة)
                </button>
            </div>

            <!-- Branch / Store Filter -->
            <div class="flex items-center gap-2">
                <select 
                    wire:model.live="selectedStoreId" 
                    class="w-full sm:w-60 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 font-bold [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-slate-100"
                >
                    <option value="all">🏢 كل الفروع والمخازن</option>
                    @foreach($stores as $st)
                        <option value="{{ $st->id }}">{{ $st->type === 'wholesale_van' ? '🚚 ' : ($st->is_main ? '🏢 ' : '🏬 ') }}{{ $st->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Custom Dates & Movement Type Filter Row -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
            <!-- Custom Date Inputs -->
            <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-950 p-1.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs">
                <div class="flex items-center gap-1">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 shrink-0">📅 من:</span>
                    <x-datepicker wire:model.live="fromDate" class="!h-8 !w-32 !py-1 !px-2 !text-xs" placeholder="من تاريخ" />
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 shrink-0">إلى:</span>
                    <x-datepicker wire:model.live="toDate" class="!h-8 !w-32 !py-1 !px-2 !text-xs" placeholder="إلى تاريخ" />
                </div>
            </div>

            <!-- Movement Type Filter -->
            <div class="flex flex-wrap items-center gap-1">
                <span class="text-slate-500 dark:text-slate-400 text-[11px] font-bold">نوع الحركة:</span>
                <button wire:click="$set('filterType', 'all')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs {{ $filterType === 'all' ? 'bg-slate-200 dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white' : 'border-transparent text-slate-500 dark:text-slate-400' }}">الكل</button>
                <button wire:click="$set('filterType', 'in')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs flex items-center gap-1 {{ $filterType === 'in' ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-700 dark:text-emerald-400' : 'border-transparent text-slate-500 dark:text-slate-400' }}">
                    <span>📥 وارد فقط (+)</span>
                </button>
                <button wire:click="$set('filterType', 'out')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs flex items-center gap-1 {{ $filterType === 'out' ? 'bg-rose-500/20 border-rose-500/40 text-rose-700 dark:text-rose-400' : 'border-transparent text-slate-500 dark:text-slate-400' }}">
                    <span>📤 منصرف فقط (-)</span>
                </button>
                <button wire:click="$set('filterType', 'adjustments')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs flex items-center gap-1 {{ $filterType === 'adjustments' ? 'bg-amber-500/20 border-amber-500/40 text-amber-700 dark:text-amber-400' : 'border-transparent text-slate-500 dark:text-slate-400' }}">
                    <span>⚖️ تسويات الجرد</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Movements History Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-3.5">التاريخ والوقت</th>
                        <th class="p-3.5">نوع الحركة</th>
                        <th class="p-3.5">رقم المستند</th>
                        <th class="p-3.5">الفرع / المخزن</th>
                        <th class="p-3.5 text-emerald-600 dark:text-emerald-400">الوارد (+)</th>
                        <th class="p-3.5 text-rose-600 dark:text-rose-400">المنصرف (-)</th>
                        <th class="p-3.5 font-bold text-slate-900 dark:text-white">الرصيد بعد الحركة</th>
                        <th class="p-3.5">المسؤول</th>
                        <th class="p-3.5">البيان والملاحظات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                    @php
                        $inboundTypes = [
                            'purchase_in', 'stock_deposit_in', 'stock_adjustment_in',
                            'cancellation_in', 'transfer_in', 'sales_return_in', 'purchase_restore_in'
                        ];
                    @endphp
                    @forelse($movements as $row)
                    @php
                        $isIn = in_array($row->movement_type, $inboundTypes);
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="p-3.5 font-mono text-slate-500 dark:text-slate-400">
                            {{ $row->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="p-3.5">
                            @if($row->movement_type === 'sales_out')
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">🛒 فاتورة بيع</span>
                            @elseif($row->movement_type === 'purchase_in')
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">📦 توريد مشتريات</span>
                            @elseif($row->movement_type === 'purchase_cancel_out')
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">🚫 إلغاء فاتورة شراء</span>
                            @elseif($row->movement_type === 'purchase_restore_in')
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">♻️ استعادة فاتورة شراء</span>
                            @elseif($row->movement_type === 'cancellation_in')
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">↩️ إلغاء فاتورة بيع</span>
                            @elseif($row->movement_type === 'stock_adjustment_in')
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">⚖️ تسوية جرد (زيادة +)</span>
                            @elseif($row->movement_type === 'stock_adjustment_out')
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">⚖️ تسوية جرد (عجز/هالك -)</span>
                            @elseif($row->movement_type === 'stock_deposit_in')
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">📥 إيداع / أول المدة</span>
                            @elseif($row->movement_type === 'transfer_in')
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">🚚 تحويل وارد</span>
                            @elseif($row->movement_type === 'transfer_out')
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20">🚚 تحويل صادر</span>
                            @elseif($row->movement_type === 'sales_return_in')
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">🔁 مرتجع مبيعات</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700">{{ $row->movement_type }}</span>
                            @endif
                        </td>
                        <td class="p-3.5 font-mono font-bold text-slate-700 dark:text-slate-300">
                            {{ $row->document_number ?: '—' }}
                        </td>
                        <td class="p-3.5 text-slate-700 dark:text-slate-300">
                            {{ $row->store?->name ?? 'المخزن الرئيسي' }}
                        </td>
                        <td class="p-3.5 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            {{ $isIn ? '+' . number_format($row->quantity, 3) : '—' }}
                        </td>
                        <td class="p-3.5 font-mono font-bold text-rose-600 dark:text-rose-400">
                            {{ !$isIn ? '-' . number_format($row->quantity, 3) : '—' }}
                        </td>
                        <td class="p-3.5 font-mono font-black text-slate-900 dark:text-white">
                            {{ number_format($row->stock_after, 3) }} {{ $item->unit }}
                        </td>
                        <td class="p-3.5 text-slate-500 dark:text-slate-400">
                            {{ $row->user?->name ?? 'النظام' }}
                        </td>
                        <td class="p-3.5 text-slate-600 dark:text-slate-300 text-[11px] max-w-xs truncate">
                            {{ $row->notes ?: '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-12 text-center text-slate-400 text-xs">
                            لا توجد حركات مسجلة لهذا الصنف خلال الفترة المحددة
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $movements->links() }}
        </div>
    </div>
</div>
