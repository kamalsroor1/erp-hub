<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
        <div>
            <h2 class="text-xl font-black text-white flex items-center gap-2">
                <span>📑 سجل فواتير المبيعات الصادرة</span>
            </h2>
            <p class="text-xs text-slate-400">متابعة الفواتير المعتمدة، حالات السداد والآجل، وإلغاء الفواتير وفق الأصول المحاسبية</p>
        </div>
        <a href="{{ route('invoices.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition-all">
            <span>+ فاتورة بيع جديدة (F2)</span>
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-900 p-4 rounded-2xl border border-slate-800">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="بحث برقم الفاتورة أو اسم العميل..." 
            class="w-full sm:w-80 bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"
        >

        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="text-slate-400">حالة السداد:</span>
            <button wire:click="$set('paymentStatus', 'all')" class="px-3 py-1.5 rounded-lg font-bold border transition-colors {{ $paymentStatus === 'all' ? 'bg-slate-800 border-slate-600 text-white' : 'border-transparent text-slate-400' }}">الكل</button>
            <button wire:click="$set('paymentStatus', 'unpaid')" class="px-3 py-1.5 rounded-lg font-bold border transition-colors {{ $paymentStatus === 'unpaid' ? 'bg-rose-500/20 border-rose-500/40 text-rose-400' : 'border-transparent text-slate-400' }}">آجل (غير مسدد)</button>
            <button wire:click="$set('paymentStatus', 'partially_paid')" class="px-3 py-1.5 rounded-lg font-bold border transition-colors {{ $paymentStatus === 'partially_paid' ? 'bg-amber-500/20 border-amber-500/40 text-amber-400' : 'border-transparent text-slate-400' }}">مسدد جزئياً</button>
            <button wire:click="$set('paymentStatus', 'paid')" class="px-3 py-1.5 rounded-lg font-bold border transition-colors {{ $paymentStatus === 'paid' ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-400' : 'border-transparent text-slate-400' }}">مسدد بالكامل</button>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="p-3.5">رقم الفاتورة</th>
                        <th class="p-3.5">العميل</th>
                        <th class="p-3.5">التاريخ</th>
                        <th class="p-3.5">نوع الدفع</th>
                        <th class="p-3.5">الصافي المطلوب</th>
                        <th class="p-3.5">المدفوع</th>
                        <th class="p-3.5">المتبقي</th>
                        <th class="p-3.5">الحالة</th>
                        <th class="p-3.5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="p-3.5 font-mono font-bold text-emerald-400">{{ $inv->invoice_number }}</td>
                        <td class="p-3.5 font-bold text-slate-100">{{ $inv->customer->name }}</td>
                        <td class="p-3.5 font-mono text-slate-400">{{ $inv->invoice_date->format('Y-m-d') }}</td>
                        <td class="p-3.5">
                            @if($inv->payment_type === 'cash') نقدي
                            @elseif($inv->payment_type === 'credit') آجل
                            @else جزئي
                            @endif
                        </td>
                        <td class="p-3.5 font-mono font-bold text-white">{{ number_format($inv->net_total, 2) }} ج.م</td>
                        <td class="p-3.5 font-mono text-emerald-400">{{ number_format($inv->paid_amount, 2) }}</td>
                        <td class="p-3.5 font-mono font-bold {{ bccomp($inv->remaining_amount, '0.000', 3) > 0 ? 'text-rose-400' : 'text-slate-400' }}">
                            {{ number_format($inv->remaining_amount, 2) }}
                        </td>
                        <td class="p-3.5">
                            @if($inv->status === 'cancelled')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">ملغاة</span>
                            @elseif($inv->payment_status === 'paid')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">مدفوعة</span>
                            @elseif($inv->payment_status === 'partially_paid')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">مسدد جزئي</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">آجل</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-center flex items-center justify-center gap-1.5">
                            <a href="{{ route('invoices.show', $inv->id) }}" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-[11px] transition-colors">
                                تفاصيل / طباعة
                            </a>
                            @if($inv->status !== 'cancelled')
                            <a href="{{ route('invoices.edit', $inv->id) }}" class="px-2 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-600 hover:text-slate-950 text-amber-400 text-[11px] font-bold border border-amber-500/30 transition-all flex items-center gap-1" title="تعديل الفاتورة">
                                <span>✏️ تعديل</span>
                            </a>
                            @endif
                            @hasrole('admin')
                            <button
                                wire:click="deleteInvoice({{ $inv->id }})"
                                wire:confirm="هل أنت متأكد من حذف الفاتورة رقم {{ $inv->invoice_number }} نهائياً؟ سيتم إرجاع البضاعة للمخزن وحذف الفاتورة تماماً."
                                class="px-2 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-600 hover:text-white text-rose-400 text-[11px] font-bold border border-rose-500/30 transition-all flex items-center gap-1 cursor-pointer"
                                title="حذف نهائي للفاتورة (المدير العام فقط)"
                            >
                                <span>🗑️ حذف</span>
                            </button>
                            @endhasrole
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-12 text-center text-slate-500">لا توجد فواتير مسجلة</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-800">
            {{ $invoices->links() }}
        </div>
    </div>

    <!-- Cancel Invoice Modal -->
    @if($showCancelModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-rose-400 text-base">إلغاء فاتورة مبيعات معتمدة</h3>
                <button wire:click="$set('showCancelModal', false)" class="text-slate-400 hover:text-white">✕</button>
            </div>

            @if($errorMessage)
            <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs">
                {{ $errorMessage }}
            </div>
            @endif

            <p class="text-xs text-slate-300">
                تنبيه: إلغاء الفاتورة سيعيد بضاعة الفاتورة بالكامل للمخزن، ويخصم المبلغ من حساب العميل، ولن يتم حذف الفاتورة بل ستحتفظ بسجل الإلغاء.
            </p>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">سبب الإلغاء (إلزامي):</label>
                <textarea wire:model="cancelReason" rows="3" placeholder="اكتب سبب إلغاء الفاتورة..." class="w-full bg-slate-950 border border-slate-700 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-rose-500"></textarea>
                @error('cancelReason') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" wire:click="$set('showCancelModal', false)" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold">إلغاء الأمر</button>
                <button type="button" wire:click="confirmCancel" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-rose-600/30">تأكيد الإلغاء وعكس المخزن</button>
            </div>
        </div>
    </div>
    @endif
</div>
