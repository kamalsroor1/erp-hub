<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>🛒 فواتير المشتريات وتوريد البضاعة للمخزن</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">سجل استلام الشحنات وتوريد المخزن وتكلفة الواردات بالكمية والأوزان</p>
        </div>
        <a href="{{ route('purchases.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition-all cursor-pointer">
            <span>+ فاتورة شراء وتوريد جديدة</span>
        </a>
    </div>

    <!-- Search Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="بحث برقم الفاتورة أو اسم المورد أو الصنف..." 
            class="w-full sm:w-80 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500"
        >
    </div>

    <!-- Purchases Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-3.5">رقم الفاتورة</th>
                        <th class="p-3.5">المورد</th>
                        <th class="p-3.5">التاريخ</th>
                        <th class="p-3.5">الأصناف والكميات الموردة</th>
                        <th class="p-3.5">إجمالي التوريد</th>
                        <th class="p-3.5">المدفوع</th>
                        <th class="p-3.5">المتبقي للمورد</th>
                        <th class="p-3.5">حالة السداد</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @forelse($purchases as $p)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="p-3.5 font-mono font-bold text-teal-600 dark:text-teal-400">{{ $p->purchase_number }}</td>
                        <td class="p-3.5 font-bold text-slate-800 dark:text-slate-100">{{ $p->supplier->name }}</td>
                        <td class="p-3.5 font-mono text-slate-500 dark:text-slate-400">{{ $p->purchase_date->format('Y-m-d') }}</td>
                        
                        <!-- Items & Quantities Column -->
                        <td class="p-3.5">
                            <div class="space-y-1">
                                @foreach($p->items as $itemLine)
                                <div class="flex items-center gap-1.5 text-[11px]">
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ number_format($itemLine->quantity, 3) }} {{ $itemLine->item->unit ?? 'كجم' }}</span>
                                    <span class="text-slate-700 dark:text-slate-300">× {{ $itemLine->item->name }}</span>
                                </div>
                                @endforeach
                            </div>
                        </td>

                        <td class="p-3.5 font-mono font-bold text-slate-900 dark:text-white">{{ number_format($p->net_total, 2) }} ج.م</td>
                        <td class="p-3.5 font-mono text-emerald-600 dark:text-emerald-400">{{ number_format($p->paid_amount, 2) }}</td>
                        <td class="p-3.5 font-mono font-bold {{ bccomp($p->remaining_amount, '0.000', 3) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400' }}">
                            {{ number_format($p->remaining_amount, 2) }}
                        </td>
                        <td class="p-3.5">
                            @if($p->payment_status === 'paid')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">مسددة</span>
                            @elseif($p->payment_status === 'partially_paid')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">مسدد جزئي</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">آجل</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-slate-400">لا توجد فواتير مشتريات مسجلة</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $purchases->links() }}
        </div>
    </div>
</div>
