<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
        <div>
            <h2 class="text-xl font-black text-white flex items-center gap-2">
                <span>🔄 سجل مرتجعات المبيعات والمشتريات</span>
            </h2>
            <p class="text-xs text-slate-400">سجل حركات إرجاع البضائع ومتابعة استرداد المخزون وتسوية حسابات العملاء والموردين</p>
        </div>
        <a href="{{ route('returns.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition-all">
            <span>+ تسجيل مرتجع جديد</span>
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-900 p-4 rounded-2xl border border-slate-800">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="بحث برقم المرتجع أو اسم العميل / المورد..." 
            class="w-full sm:w-80 bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"
        >

        <div class="flex items-center gap-2 text-xs">
            <span class="text-slate-400">نوع المرتجع:</span>
            <button wire:click="$set('type', 'all')" class="px-3 py-1.5 rounded-lg font-bold border transition-colors {{ $type === 'all' ? 'bg-slate-800 border-slate-600 text-white' : 'border-transparent text-slate-400' }}">الكل</button>
            <button wire:click="$set('type', 'sales_return')" class="px-3 py-1.5 rounded-lg font-bold border transition-colors {{ $type === 'sales_return' ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-400' : 'border-transparent text-slate-400' }}">مرتجع مبيعات (من عميل)</button>
            <button wire:click="$set('type', 'purchase_return')" class="px-3 py-1.5 rounded-lg font-bold border transition-colors {{ $type === 'purchase_return' ? 'bg-amber-500/20 border-amber-500/40 text-amber-400' : 'border-transparent text-slate-400' }}">مرتجع مشتريات (لمورد)</button>
        </div>
    </div>

    <!-- Returns Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="p-3.5">رقم السند</th>
                        <th class="p-3.5">النوع</th>
                        <th class="p-3.5">الطرف (عميل / مورد)</th>
                        <th class="p-3.5">التاريخ</th>
                        <th class="p-3.5">الأصناف المرتجعة</th>
                        <th class="p-3.5">إجمالي قيمة المرتجع</th>
                        <th class="p-3.5">السبب</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($returns as $r)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="p-3.5 font-mono font-bold text-teal-400">{{ $r->return_number }}</td>
                        <td class="p-3.5">
                            @if($r->return_type === 'sales_return')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">مرتجع مبيعات</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">مرتجع مشتريات</span>
                            @endif
                        </td>
                        <td class="p-3.5 font-bold text-slate-100">
                            {{ $r->customer->name ?? $r->supplier->name ?? '—' }}
                        </td>
                        <td class="p-3.5 font-mono text-slate-400">{{ $r->return_date->format('Y-m-d') }}</td>
                        <td class="p-3.5">
                            @foreach($r->items as $it)
                            <div class="text-[11px] text-slate-300">
                                <span class="font-mono text-emerald-400 font-bold">{{ number_format($it->quantity, 3) }}</span> × {{ $it->item->name }}
                            </div>
                            @endforeach
                        </td>
                        <td class="p-3.5 font-mono font-bold text-white">{{ number_format($r->total_amount, 2) }} ج.م</td>
                        <td class="p-3.5 text-slate-400">{{ $r->reason ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-500">لا توجد مرتجعات مسجلة</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-800">
            {{ $returns->links() }}
        </div>
    </div>
</div>
