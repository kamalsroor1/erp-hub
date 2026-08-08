<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
        <div>
            <h2 class="text-xl font-black text-white flex items-center gap-2">
                <span>🔐 إدارة ورديات الكاشير وإغلاق درج النقدية (Z-Report)</span>
            </h2>
            <p class="text-xs text-slate-400">متابعة النقدية المستلمة، مبيعات الشفت، إجمالي التحصيلات، وتقفيل الدرج اليومي</p>
        </div>

        @if($activeShift)
        <button wire:click="openCloseModal" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/30 flex items-center gap-2 transition-all">
            <span>🔒 تقفيل الوردية الحالية (Z-Report)</span>
        </button>
        @endif
    </div>

    @if($errorMessage)
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs">
        {{ $errorMessage }}
    </div>
    @endif

    @if($successMessage)
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs">
        {{ $successMessage }}
    </div>
    @endif

    <!-- Active Shift Status Card OR Open Shift Prompt -->
    @if($activeShift)
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950/20 border border-emerald-500/30 rounded-2xl p-6 space-y-6 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-emerald-400 animate-ping"></span>
                <div>
                    <h3 class="font-bold text-white text-base">الوردية الحالية: {{ $activeShift->shift_number }}</h3>
                    <span class="text-xs text-slate-400">فُتحت بتاريخ: {{ $activeShift->opened_at->translatedFormat('l, d F Y - h:i A') }}</span>
                </div>
            </div>
            <div class="text-xs font-mono text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-xl border border-emerald-500/20">
                درج النقدية الافتتاحي: {{ number_format($activeShift->opening_cash_balance, 2) }} ج.م
            </div>
        </div>

        <!-- Live Shift Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-slate-950/70 p-4 rounded-xl border border-slate-800">
                <span class="text-slate-400 text-xs">مبيعات كاش (نقدي):</span>
                <div class="text-xl font-black text-emerald-400 font-mono mt-1">
                    {{ number_format($liveMetrics['total_cash_sales'] ?? 0, 2) }} ج.م
                </div>
            </div>

            <div class="bg-slate-950/70 p-4 rounded-xl border border-slate-800">
                <span class="text-slate-400 text-xs">مبيعات آجلة (شكك):</span>
                <div class="text-xl font-black text-amber-400 font-mono mt-1">
                    {{ number_format($liveMetrics['total_credit_sales'] ?? 0, 2) }} ج.م
                </div>
            </div>

            <div class="bg-slate-950/70 p-4 rounded-xl border border-slate-800">
                <span class="text-slate-400 text-xs">سندات تحصيل من العملاء:</span>
                <div class="text-xl font-black text-teal-400 font-mono mt-1">
                    {{ number_format($liveMetrics['total_payments_collected'] ?? 0, 2) }} ج.م
                </div>
            </div>

            <div class="bg-slate-950/70 p-4 rounded-xl border border-emerald-500/40 bg-emerald-950/10">
                <span class="text-emerald-300 text-xs font-bold">النقدية المتوقعة بالدرج الآن:</span>
                <div class="text-2xl font-black text-white font-mono mt-1">
                    {{ number_format($liveMetrics['expected_cash_balance'] ?? 0, 2) }} ج.م
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Open New Shift Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 max-w-xl">
        <h3 class="font-bold text-white text-base flex items-center gap-2">
            <span>🔓 بدء وفتح وردية عمل جديدة</span>
        </h3>
        <p class="text-xs text-slate-400">أدخل عهدة النقدية الافتتاحية الموجودة في درج الكاشير قبل بدء عمليات البيع اليومية</p>

        <form wire:submit.prevent="startShift" class="space-y-4 pt-2">
            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">الرصيد الافتتاحي للدرج (الفكة / العهدة):</label>
                <input 
                    type="number" 
                    step="0.01" 
                    min="0" 
                    wire:model="opening_cash_balance" 
                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm font-mono font-bold text-emerald-400 focus:outline-none focus:border-emerald-500"
                >
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">ملاحظات الافتتاح:</label>
                <input 
                    type="text" 
                    wire:model="open_notes" 
                    placeholder="مثال: استلام درج الكاشير من شفت الصباح..." 
                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white"
                >
            </div>

            <button 
                type="submit" 
                class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs shadow-lg shadow-emerald-600/30 transition-all active:scale-95"
            >
                🔓 فتح الوردية وبدء تسجيل المبيعات
            </button>
        </form>
    </div>
    @endif

    <!-- Past Shifts Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 bg-slate-950/60 border-b border-slate-800 text-xs font-bold text-slate-300">
            سجل الورديات السابقة وإغلاقات الأدراج (Z-Reports History)
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="p-3.5">رقم الوردية</th>
                        <th class="p-3.5">الكاشير</th>
                        <th class="p-3.5">وقت الفتح</th>
                        <th class="p-3.5">وقت الإغلاق</th>
                        <th class="p-3.5">المبيعات النقدية</th>
                        <th class="p-3.5">المتوقع بالدرج</th>
                        <th class="p-3.5">الفعلي بالعد</th>
                        <th class="p-3.5">العجز / الزيادة</th>
                        <th class="p-3.5">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($pastShifts as $s)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="p-3.5 font-mono font-bold text-teal-400">{{ $s->shift_number }}</td>
                        <td class="p-3.5 font-bold text-slate-200">{{ $s->user->name ?? 'الكاشير' }}</td>
                        <td class="p-3.5 font-mono text-slate-400">{{ $s->opened_at->format('Y-m-d H:i') }}</td>
                        <td class="p-3.5 font-mono text-slate-400">{{ $s->closed_at ? $s->closed_at->format('Y-m-d H:i') : '—' }}</td>
                        <td class="p-3.5 font-mono text-white">{{ number_format($s->total_cash_sales, 2) }} ج.م</td>
                        <td class="p-3.5 font-mono text-slate-300">{{ number_format($s->expected_cash_balance, 2) }}</td>
                        <td class="p-3.5 font-mono font-bold text-emerald-400">{{ number_format($s->actual_cash_balance, 2) }}</td>
                        <td class="p-3.5 font-mono font-bold {{ bccomp($s->cash_difference, '0.000', 3) < 0 ? 'text-rose-400' : (bccomp($s->cash_difference, '0.000', 3) > 0 ? 'text-emerald-400' : 'text-slate-400') }}">
                            {{ number_format($s->cash_difference, 2) }}
                        </td>
                        <td class="p-3.5">
                            @if($s->status === 'open')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">جارية (مفتوحة)</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">مغلقة (Z-Done)</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-12 text-center text-slate-500">لا توجد ورديات مسجلة بعد</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-800">
            {{ $pastShifts->links() }}
        </div>
    </div>

    <!-- Close Shift Modal -->
    @if($showCloseModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-base">إغلاق وردية العمل وتقفيل درج النقدية</h3>
                <button wire:click="$set('showCloseModal', false)" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <form wire:submit.prevent="submitCloseShift" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">المبلغ الفعلي الموجود بالدرج بعد الجرد:</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        wire:model="actual_cash_balance" 
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-base font-mono font-bold text-emerald-400 focus:outline-none focus:border-emerald-500 text-center"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">ملاحظات الإغلاق والتسليم:</label>
                    <input 
                        type="text" 
                        wire:model="close_notes" 
                        placeholder="أي ملاحظات حول العجز أو الفكة..." 
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white"
                    >
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" wire:click="$set('showCloseModal', false)" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-rose-600/30">
                        🔒 تأكيد إغلاق الوردية وطباعة Z-Report
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
