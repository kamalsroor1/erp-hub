<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>🔄 سجل مرتجعات المبيعات والمشتريات</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">سجل حركات إرجاع البضائع ومتابعة استرداد المخزون وتسوية حسابات العملاء والموردين</p>
        </div>
        <a href="{{ route('returns.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition-all cursor-pointer">
            <span>+ تسجيل مرتجع جديد</span>
        </a>
    </div>

    @if (session()->has('success'))
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs flex items-center gap-2">
        <span>✅ {{ session('success') }}</span>
    </div>
    @endif

    <!-- Filters Bar -->
    <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex flex-col sm:flex-row items-center gap-2 w-full lg:w-auto">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="بحث برقم المرتجع أو اسم العميل / المورد..." 
                class="w-full sm:w-72 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500"
            >
            
            <!-- Date Filter Inputs -->
            <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-950 p-1.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs">
                <div class="flex items-center gap-1">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">📅 من:</span>
                    <input 
                        type="date" 
                        wire:model.live="fromDate" 
                        class="h-8 px-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer"
                    >
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">إلى:</span>
                    <input 
                        type="date" 
                        wire:model.live="toDate" 
                        class="h-8 px-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer"
                    >
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-1.5 text-xs">
            <span class="text-slate-500 dark:text-slate-400 text-[11px] hidden sm:inline">الحالة:</span>
            <button wire:click="$set('filterStatus', 'active')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs {{ $filterStatus === 'active' ? 'bg-emerald-600 text-white border-emerald-500' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">النشطة</button>
            <button wire:click="$set('filterStatus', 'trashed')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs flex items-center gap-1 {{ $filterStatus === 'trashed' ? 'bg-rose-600 text-white border-rose-500' : 'border-slate-200 dark:border-slate-800 text-rose-600 dark:text-rose-400' }}">
                <span>سلة المحذوفات</span>
                @if($trashedCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filterStatus === 'trashed' ? 'bg-white text-rose-600' : 'bg-rose-500/20 text-rose-600' }} font-mono font-bold">{{ $trashedCount }}</span>
                @endif
            </button>
            <button wire:click="$set('filterStatus', 'all')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs {{ $filterStatus === 'all' ? 'bg-slate-700 text-white border-slate-600' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">الكل</button>

            <span class="text-slate-300 dark:text-slate-700 mx-1">|</span>

            <span class="text-slate-500 dark:text-slate-400 text-[11px] hidden sm:inline">النوع:</span>
            <button wire:click="$set('type', 'all')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs {{ $type === 'all' ? 'bg-slate-200 dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white' : 'border-transparent text-slate-500 dark:text-slate-400' }}">الكل</button>
            <button wire:click="$set('type', 'sales_return')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs {{ $type === 'sales_return' ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-700 dark:text-emerald-400' : 'border-transparent text-slate-500 dark:text-slate-400' }}">مبيعات</button>
            <button wire:click="$set('type', 'purchase_return')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs {{ $type === 'purchase_return' ? 'bg-amber-500/20 border-amber-500/40 text-amber-700 dark:text-amber-400' : 'border-transparent text-slate-500 dark:text-slate-400' }}">مشتريات</button>
        </div>
    </div>

    <!-- Returns Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-3.5">رقم السند</th>
                        <th class="p-3.5">النوع</th>
                        <th class="p-3.5">الطرف (عميل / مورد)</th>
                        <th class="p-3.5">التاريخ</th>
                        <th class="p-3.5">الأصناف المرتجعة</th>
                        <th class="p-3.5">إجمالي قيمة المرتجع</th>
                        <th class="p-3.5">السبب</th>
                        <th class="p-3.5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @forelse($returns as $r)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="p-3.5 font-mono font-bold text-teal-600 dark:text-teal-400">{{ $r->return_number }}</td>
                        <td class="p-3.5">
                            @if($r->trashed())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">محذوف</span>
                            @elseif($r->return_type === 'sales_return')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">مرتجع مبيعات</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">مرتجع مشتريات</span>
                            @endif
                        </td>
                        <td class="p-3.5 font-bold text-slate-800 dark:text-slate-100">
                            {{ $r->customer->name ?? $r->supplier->name ?? '—' }}
                        </td>
                        <td class="p-3.5 font-mono text-slate-500 dark:text-slate-400">{{ $r->return_date->format('Y-m-d') }}</td>
                        <td class="p-3.5">
                            @foreach($r->items as $it)
                            <div class="text-[11px] text-slate-700 dark:text-slate-300">
                                <span class="font-mono text-emerald-600 dark:text-emerald-400 font-bold">{{ number_format($it->quantity, 3) }}</span> × {{ $it->item->name }}
                            </div>
                            @endforeach
                        </td>
                        <td class="p-3.5 font-mono font-bold text-slate-900 dark:text-white">{{ number_format($r->total_amount, 2) }} ج.م</td>
                        <td class="p-3.5 text-slate-500 dark:text-slate-400">{{ $r->reason ?? '—' }}</td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($r->trashed())
                                <button 
                                    wire:click="restoreReturn({{ $r->id }})" 
                                    class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-600 hover:text-white text-emerald-700 dark:text-emerald-400 font-bold text-[11px] border border-emerald-500/30 transition-colors inline-flex items-center gap-1 cursor-pointer"
                                    title="استعادة المرتجع"
                                >
                                    <span>♻️ استعادة</span>
                                </button>
                                @else
                                @hasrole('admin')
                                <button 
                                    wire:click="deleteReturn({{ $r->id }})" 
                                    wire:confirm="هل أنت متأكد من أرشفة المرتجع رقم {{ $r->return_number }} ونقله لسلة المحذوفات؟"
                                    class="px-2 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-600 hover:text-white text-rose-600 dark:text-rose-400 text-[11px] font-bold border border-rose-500/30 transition-all flex items-center gap-1 cursor-pointer"
                                    title="أرشفة المرتجع"
                                >
                                    <span>🗑️</span>
                                </button>
                                @endhasrole
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-slate-400">لا توجد مرتجعات مسجلة</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $returns->links() }}
        </div>
    </div>
</div>
