<div class="space-y-6">
    <!-- Header & Date Navigation -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
        <div>
            <h2 class="text-xl font-black text-white flex items-center gap-2">
                <span>📅 يومية المبيعات وحركة الدرج (يوم بيوم)</span>
            </h2>
            <p class="text-xs text-slate-400">متابعة دقيقة لمبيعات اليوم، النقدية المقبوضة، المصروفات، وحساب صافي الدرج وتقفيل الوردية</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Date Filter Presets -->
            <button wire:click="setDate('today')" class="px-3 py-1.5 rounded-xl font-bold text-xs border transition-colors {{ $selectedDate === now()->toDateString() ? 'bg-amber-600 text-slate-950 border-amber-500' : 'bg-slate-900 border-slate-800 text-slate-300 hover:bg-slate-800' }}">
                اليوم
            </button>
            <button wire:click="setDate('yesterday')" class="px-3 py-1.5 rounded-xl font-bold text-xs border transition-colors {{ $selectedDate === now()->subDay()->toDateString() ? 'bg-amber-600 text-slate-950 border-amber-500' : 'bg-slate-900 border-slate-800 text-slate-300 hover:bg-slate-800' }}">
                أمس
            </button>

            <!-- Date Picker -->
            <div class="flex items-center gap-1.5 bg-slate-950 border border-slate-700 rounded-xl px-3 py-1 text-xs">
                <span class="text-slate-400">التاريخ:</span>
                <input type="date" wire:model.live="selectedDate" class="bg-transparent text-white font-mono font-bold focus:outline-none">
            </div>

            <!-- Print Daily Summary Button -->
            <button onclick="window.print()" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs rounded-xl border border-slate-700 flex items-center gap-1.5 transition-colors">
                <span>🖨️ طباعة اليومية</span>
            </button>
        </div>
    </div>

    @if($successMessage)
    <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-bold flex items-center gap-2">
        <span>✓</span> {{ $successMessage }}
    </div>
    @endif

    @if($errorMessage)
    <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-bold flex items-center gap-2">
        <span>✕</span> {{ $errorMessage }}
    </div>
    @endif

    <!-- Shift / Drawer Action Bar -->
    <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-3 h-3 rounded-full {{ $activeShift ? 'bg-emerald-400 animate-pulse' : 'bg-slate-600' }}"></div>
            <div>
                <div class="text-xs font-bold text-white flex items-center gap-2">
                    <span>حالة اليومية / الوردية الحالية:</span>
                    @if($activeShift)
                        <span class="px-2 py-0.5 rounded-full text-[11px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">مفتوحة (وردية #{{ $activeShift->shift_number }})</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[11px] bg-slate-800 text-slate-400">مغلقة / غير مفتوحة</span>
                    @endif
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    @if($activeShift)
                        فُتحت بواسطة: <span class="text-slate-200 font-bold">{{ $activeShift->user->name ?? 'الكاشير' }}</span> الساعة {{ $activeShift->opened_at->format('h:i A') }} (رصيد افتتاحي: {{ number_format($activeShift->opening_cash_balance, 2) }} ج.م)
                    @else
                        يمكنك فتح اليومية وبدء تسجيل الرصيد الافتتاحي للدرج
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($activeShift)
                <button wire:click="openCloseModal" class="px-4 py-2 bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/30 flex items-center gap-1.5 transition-all">
                    <span>🔴 تقفيل اليومية (Z-Report)</span>
                </button>
            @else
                <button wire:click="openShiftModal" class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center gap-1.5 transition-all">
                    <span>🟢 فتح يومية جديدة</span>
                </button>
            @endif
        </div>
    </div>

    <!-- 5 Daily High-Level Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- 1. Total Sales Invoices -->
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl space-y-1">
            <div class="text-xs font-bold text-slate-400">إجمالي مبيعات اليوم</div>
            <div class="text-xl font-black text-white font-mono mt-2">{{ number_format($totalSales, 2) }} <span class="text-xs text-amber-400">ج.م</span></div>
            <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-800/80 flex justify-between">
                <span>{{ $invoicesCount }} فاتورة</span>
                <span>نقدي: {{ number_format($cashSales + $partialSales, 1) }}</span>
            </div>
        </div>

        <!-- 2. Cash Inflow Collected -->
        <div class="bg-slate-900 border border-emerald-500/30 p-4 rounded-2xl space-y-1 bg-gradient-to-b from-slate-900 to-emerald-950/20">
            <div class="text-xs font-bold text-emerald-400">النقدية المقبوضة (داخل الدرج)</div>
            <div class="text-xl font-black text-emerald-300 font-mono mt-2">{{ number_format($totalCashCollected, 2) }} <span class="text-xs text-emerald-400">ج.م</span></div>
            <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-800/80">
                مبيعات كاش + سندات قبض
            </div>
        </div>

        <!-- 3. Expenses Paid Out -->
        <div class="bg-slate-900 border border-amber-500/30 p-4 rounded-2xl space-y-1 bg-gradient-to-b from-slate-900 to-amber-950/20">
            <div class="text-xs font-bold text-amber-400 flex items-center justify-between">
                <span>المصروفات والنثريات</span>
                <a href="{{ route('expenses.index') }}" class="text-[10px] hover:underline">عرض</a>
            </div>
            <div class="text-xl font-black text-amber-300 font-mono mt-2">{{ number_format($totalExpenses, 2) }} <span class="text-xs text-amber-400">ج.م</span></div>
            <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-800/80">
                {{ $expenses->count() }} مصروف (شنط، أكواب، صيانة)
            </div>
        </div>

        <!-- 4. Purchases & Supplier Payments -->
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl space-y-1">
            <div class="text-xs font-bold text-slate-400">المشتريات وسداد الموردين</div>
            <div class="text-xl font-black text-indigo-300 font-mono mt-2">{{ number_format($totalPurchases, 2) }} <span class="text-xs text-indigo-400">ج.م</span></div>
            <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-800/80">
                مسدد للموردين: {{ number_format($totalSupplierPaid, 2) }} ج.م
            </div>
        </div>

        <!-- 5. Net Cash In Drawer Today -->
        <div class="bg-slate-900 border border-amber-500/40 p-4 rounded-2xl space-y-1 relative overflow-hidden bg-gradient-to-b from-slate-900 to-amber-950/30">
            <div class="text-xs font-bold text-amber-400">صافي حركة الدرج لليوم</div>
            <div class="text-xl font-black {{ bccomp($netCashToday, '0.000', 3) >= 0 ? 'text-emerald-400' : 'text-rose-400' }} font-mono mt-2">
                {{ number_format($netCashToday, 2) }} <span class="text-xs text-amber-400">ج.م</span>
            </div>
            <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-800/80">
                المقبوض نقداً - المنصرف نقداً
            </div>
        </div>
    </div>

    <!-- Daily Operations Details Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ tab: 'invoices' }">
        <!-- Main Feed (2 cols) -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Navigation Tabs -->
            <div class="flex items-center gap-2 border-b border-slate-800 pb-2 text-xs font-bold">
                <button @click="tab = 'invoices'" :class="tab === 'invoices' ? 'bg-amber-600 text-slate-950' : 'bg-slate-900 text-slate-400 hover:text-white'" class="px-3 py-1.5 rounded-xl transition-all">
                    🧾 فواتير المبيعات ({{ $invoicesCount }})
                </button>
                <button @click="tab = 'expenses'" :class="tab === 'expenses' ? 'bg-amber-600 text-slate-950' : 'bg-slate-900 text-slate-400 hover:text-white'" class="px-3 py-1.5 rounded-xl transition-all">
                    💸 المصروفات والنثريات ({{ $expenses->count() }})
                </button>
                <button @click="tab = 'payments'" :class="tab === 'payments' ? 'bg-amber-600 text-slate-950' : 'bg-slate-900 text-slate-400 hover:text-white'" class="px-3 py-1.5 rounded-xl transition-all">
                    💵 سندات القبض ({{ $customerPayments->count() }})
                </button>
                <button @click="tab = 'purchases'" :class="tab === 'purchases' ? 'bg-amber-600 text-slate-950' : 'bg-slate-900 text-slate-400 hover:text-white'" class="px-3 py-1.5 rounded-xl transition-all">
                    🛒 المشتريات ({{ $purchases->count() }})
                </button>
            </div>

            <!-- Tab 1: Invoices Table -->
            <div x-show="tab === 'invoices'" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-950/60 border-b border-slate-800 font-bold text-xs text-white">
                    فواتير مبيعات يوم {{ $selectedDate }}
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                            <tr>
                                <th class="p-3">رقم الفاتورة</th>
                                <th class="p-3">العميل</th>
                                <th class="p-3">الوقت</th>
                                <th class="p-3">النوع</th>
                                <th class="p-3">الإجمالي</th>
                                <th class="p-3">المدفوع</th>
                                <th class="p-3 text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($invoices as $inv)
                            <tr class="hover:bg-slate-800/30">
                                <td class="p-3 font-mono font-bold text-amber-400">
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="hover:underline">{{ $inv->invoice_number }}</a>
                                </td>
                                <td class="p-3 font-bold text-slate-200">{{ $inv->customer->name ?? 'عميل نقدي' }}</td>
                                <td class="p-3 text-slate-400 font-mono text-[11px]">{{ $inv->created_at->format('h:i A') }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $inv->payment_type === 'cash' ? 'bg-emerald-500/10 text-emerald-400' : ($inv->payment_type === 'credit' ? 'bg-rose-500/10 text-rose-400' : 'bg-amber-500/10 text-amber-400') }}">
                                        {{ $inv->payment_type === 'cash' ? 'كاش' : ($inv->payment_type === 'credit' ? 'آجل' : 'جزئي') }}
                                    </span>
                                </td>
                                <td class="p-3 font-mono font-bold text-white">{{ number_format($inv->net_total, 2) }}</td>
                                <td class="p-3 font-mono text-emerald-400">{{ number_format($inv->paid_amount, 2) }}</td>
                                <td class="p-3 text-center flex items-center justify-center gap-1">
                                    <a href="{{ route('invoices.edit', $inv->id) }}" class="px-2 py-1 bg-amber-500/10 hover:bg-amber-500 hover:text-slate-950 text-amber-400 rounded text-[10px] font-bold transition-colors">
                                        ✏️ تعديل
                                    </a>
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded text-[10px] font-bold transition-colors">
                                        عرض
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-500">لا توجد فواتير مبيعات مسجلة في هذا اليوم</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Expenses Table -->
            <div x-show="tab === 'expenses'" x-cloak class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-950/60 border-b border-slate-800 font-bold text-xs text-white">
                    مصروفات ونثريات يوم {{ $selectedDate }}
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                            <tr>
                                <th class="p-3">التصنيف</th>
                                <th class="p-3">بيان المصروف</th>
                                <th class="p-3">الوقت</th>
                                <th class="p-3">طريقة الدفع</th>
                                <th class="p-3">المبلغ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($expenses as $exp)
                            <tr class="hover:bg-slate-800/30">
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">{{ $exp->category }}</span>
                                </td>
                                <td class="p-3 font-bold text-slate-200">{{ $exp->title }}</td>
                                <td class="p-3 text-slate-400 font-mono text-[11px]">{{ $exp->created_at->format('h:i A') }}</td>
                                <td class="p-3 text-slate-400">{{ $exp->payment_method === 'cash' ? 'نقدي (كاش)' : $exp->payment_method }}</td>
                                <td class="p-3 font-mono font-bold text-rose-400">{{ number_format($exp->amount, 2) }} ج.م</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">لا توجد مصروفات مسجلة في هذا اليوم</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 3: Customer Payments Table -->
            <div x-show="tab === 'payments'" x-cloak class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-950/60 border-b border-slate-800 font-bold text-xs text-white">
                    سندات القبض وتحصيلات العملاء في يوم {{ $selectedDate }}
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                            <tr>
                                <th class="p-3">رقم السند</th>
                                <th class="p-3">العميل</th>
                                <th class="p-3">الوقت</th>
                                <th class="p-3">طريقة التحصيل</th>
                                <th class="p-3">المبلغ المقبوض</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($customerPayments as $pay)
                            <tr class="hover:bg-slate-800/30">
                                <td class="p-3 font-mono font-bold text-slate-300">{{ $pay->payment_number }}</td>
                                <td class="p-3 font-bold text-slate-200">{{ $pay->customer->name ?? 'عميل' }}</td>
                                <td class="p-3 text-slate-400 font-mono text-[11px]">{{ $pay->created_at->format('h:i A') }}</td>
                                <td class="p-3 text-slate-400">{{ $pay->payment_method }}</td>
                                <td class="p-3 font-mono font-bold text-emerald-400">{{ number_format($pay->amount, 2) }} ج.م</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">لا توجد سندات قبض في هذا اليوم</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 4: Purchases Table -->
            <div x-show="tab === 'purchases'" x-cloak class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-950/60 border-b border-slate-800 font-bold text-xs text-white">
                    مشتريات وتوريدات يوم {{ $selectedDate }}
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                            <tr>
                                <th class="p-3">رقم الفاتورة</th>
                                <th class="p-3">المورد</th>
                                <th class="p-3">الوقت</th>
                                <th class="p-3">الإجمالي</th>
                                <th class="p-3">المسدد</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($purchases as $pur)
                            <tr class="hover:bg-slate-800/30">
                                <td class="p-3 font-mono font-bold text-amber-400">{{ $pur->purchase_number }}</td>
                                <td class="p-3 font-bold text-slate-200">{{ $pur->supplier->name ?? 'مورد' }}</td>
                                <td class="p-3 text-slate-400 font-mono text-[11px]">{{ $pur->created_at->format('h:i A') }}</td>
                                <td class="p-3 font-mono font-bold text-white">{{ number_format($pur->net_total, 2) }}</td>
                                <td class="p-3 font-mono text-emerald-400">{{ number_format($pur->paid_amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">لا توجد مشتريات مسجلة في هذا اليوم</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Col: Daily Shifts Log & Drawer Reconciliation -->
        <div class="space-y-4">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4">
                <h3 class="text-sm font-bold text-white border-b border-slate-800 pb-2">
                    ⏱️ سجل الورديات وتقفيل الـ Z-Report لليوم
                </h3>

                @forelse($shiftsOnDate as $sh)
                <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800 space-y-2 text-xs">
                    <div class="flex items-center justify-between font-bold">
                        <span class="text-amber-400">وردية رقم #{{ $sh->shift_number }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] {{ $sh->status === 'closed' ? 'bg-slate-800 text-slate-400' : 'bg-emerald-500/10 text-emerald-400' }}">
                            {{ $sh->status === 'closed' ? 'مقفلة' : 'جارية' }}
                        </span>
                    </div>
                    <div class="text-[11px] text-slate-400">
                        الكاشير: <span class="text-slate-200 font-bold">{{ $sh->user->name ?? 'مستخدم' }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-900 text-[11px]">
                        <div>الافتتاحي: <span class="font-mono text-white font-bold">{{ number_format($sh->opening_cash_balance, 2) }}</span></div>
                        <div>الفعلي: <span class="font-mono text-white font-bold">{{ number_format($sh->actual_cash_balance, 2) }}</span></div>
                    </div>
                    @if($sh->status === 'closed' && bccomp((string)$sh->cash_difference, '0.000', 3) != 0)
                    <div class="pt-1 text-[11px] font-bold {{ bccomp((string)$sh->cash_difference, '0.000', 3) < 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                        {{ bccomp((string)$sh->cash_difference, '0.000', 3) < 0 ? 'عجز في الدرج:' : 'زيادة في الدرج:' }}
                        <span class="font-mono">{{ number_format($sh->cash_difference, 2) }} ج.م</span>
                    </div>
                    @endif
                </div>
                @empty
                <div class="p-4 text-center text-slate-500 text-xs">
                    لا توجد ورديات مسجلة في هذا التاريخ
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Open Shift Modal -->
    @if($showOpenModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-base">🟢 فتح يومية / وردية عمل جديدة</h3>
                <button wire:click="$set('showOpenModal', false)" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">الرصيد الافتتاحي للدرج (الفكة / العهدة):</label>
                    <input type="number" step="0.001" wire:model="opening_cash_balance" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-bold text-emerald-400 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">ملاحظات الفتح:</label>
                    <textarea wire:model="open_notes" rows="2" placeholder="ملاحظات تسليم الدرج أو اسم الكاشير..." class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" wire:click="$set('showOpenModal', false)" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold">إلغاء</button>
                <button type="button" wire:click="startShift" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30">تأكيد فتح اليومية</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Close Shift Modal (Z-Report) -->
    @if($showCloseModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-rose-400 text-base">🔴 تقفيل اليومية (Z-Report)</h3>
                <button wire:click="$set('showCloseModal', false)" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-300 mb-1">النقدية الفعلية الموجودة في الدرج بعد العد والفرز:</label>
                    <input type="number" step="0.001" wire:model="actual_cash_balance" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-bold text-white focus:outline-none focus:border-rose-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-300 mb-1">ملاحظات التقفيل:</label>
                    <textarea wire:model="close_notes" rows="2" placeholder="ملاحظات تسليم الدرج أو أسباب العجز/الزيادة إن وجدت..." class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-rose-500"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" wire:click="$set('showCloseModal', false)" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold">إلغاء</button>
                <button type="button" wire:click="submitCloseShift" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-rose-600/30">تأكيد التقفيل وإصدار Z-Report</button>
            </div>
        </div>
    </div>
    @endif
</div>
