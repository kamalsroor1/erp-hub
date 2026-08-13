<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>📑 كشف حساب تفصيلي: {{ $customer->name }}</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">سجل حركة الفواتير وسندات القبض والرصيد التراكمي المتحرك</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.export.csv', $customer->id) }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition-all">
                📊 تصدير إكسيل (CSV)
            </a>
            <button onclick="window.print()" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 flex items-center gap-2 cursor-pointer">
                🖨️ طباعة كشف الحساب
            </button>
            <a href="{{ route('customers.index') }}" class="px-3 py-2.5 bg-slate-100 dark:bg-slate-950 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-800">
                ← رجوع
            </a>
        </div>
    </div>

    <!-- Customer Overview Bar & Date Filters -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs shadow-sm items-center">
        <div>
            <span class="text-slate-500">رقم الهاتف:</span>
            <div class="font-bold text-slate-900 dark:text-white font-mono mt-0.5">{{ $customer->phone ?? '—' }}</div>
        </div>
        <div>
            <span class="text-slate-500">العنوان:</span>
            <div class="font-bold text-slate-900 dark:text-white mt-0.5">{{ $customer->address ?? '—' }}</div>
        </div>
        <div>
            <span class="text-slate-500">الرصيد المدين النهائي المطلوب:</span>
            <div class="font-black text-base font-mono mt-0.5 {{ bccomp($current_balance, '0.000', 3) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                {{ number_format($current_balance, 2) }} ج.م
            </div>
        </div>
        <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-950 p-2 rounded-xl border border-slate-300 dark:border-slate-700">
            <div class="flex items-center gap-1">
                <span class="text-[10px] font-bold text-slate-500">من:</span>
                <input type="date" wire:model.live="fromDate" class="h-7 px-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none cursor-pointer">
            </div>
            <div class="flex items-center gap-1">
                <span class="text-[10px] font-bold text-slate-500">إلى:</span>
                <input type="date" wire:model.live="toDate" class="h-7 px-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none cursor-pointer">
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-3.5">التاريخ</th>
                        <th class="p-3.5">نوع الحركة</th>
                        <th class="p-3.5">رقم السند / الفاتورة</th>
                        <th class="p-3.5 text-rose-600 dark:text-rose-400">مدين (+) فاتورة</th>
                        <th class="p-3.5 text-emerald-600 dark:text-emerald-400">دائن (-) سداد</th>
                        <th class="p-3.5 font-bold text-slate-900 dark:text-white">الرصيد بعد الحركة</th>
                        <th class="p-3.5">البيان / ملاحظات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @forelse($entries as $row)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="p-3.5 font-mono text-slate-500 dark:text-slate-400">{{ $row['date'] }}</td>
                        <td class="p-3.5 font-bold text-slate-800 dark:text-slate-200">{{ $row['type'] }}</td>
                        <td class="p-3.5 font-mono text-slate-700 dark:text-slate-300">{{ $row['ref_number'] }}</td>
                        <td class="p-3.5 font-mono font-bold text-rose-600 dark:text-rose-400">
                            {{ bccomp($row['debit'], '0.000', 3) > 0 ? number_format($row['debit'], 2) : '—' }}
                        </td>
                        <td class="p-3.5 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            {{ bccomp($row['credit'], '0.000', 3) > 0 ? number_format($row['credit'], 2) : '—' }}
                        </td>
                        <td class="p-3.5 font-mono font-black text-slate-900 dark:text-white">
                            {{ number_format($row['balance_after'], 2) }} ج.م
                        </td>
                        <td class="p-3.5 text-slate-500 dark:text-slate-400">{{ $row['notes'] ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-400">لا توجد حركات مسجلة لهذا العميل</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
