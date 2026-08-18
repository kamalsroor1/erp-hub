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

    @if (session()->has('success'))
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs flex items-center gap-2">
        <span>✅ {{ session('success') }}</span>
    </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex flex-col sm:flex-row items-center gap-2 w-full lg:w-auto">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="بحث برقم الفاتورة أو اسم المورد أو الصنف..." 
                class="w-full sm:w-72 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500"
            >
            
            <!-- Date Filter Inputs -->
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
        </div>

        <div class="flex flex-wrap items-center gap-1.5 text-xs">
            <span class="text-slate-500 dark:text-slate-400 text-[11px] hidden sm:inline">الحالة:</span>
            <button wire:click="$set('filterStatus', 'confirmed')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs flex items-center gap-1 {{ $filterStatus === 'confirmed' ? 'bg-emerald-600 text-white border-emerald-500' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">
                <span>المؤكدة (النشطة)</span>
                @if($confirmedCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filterStatus === 'confirmed' ? 'bg-white text-emerald-700 font-bold' : 'bg-emerald-500/20 text-emerald-600' }} font-mono">{{ $confirmedCount }}</span>
                @endif
            </button>
            <button wire:click="$set('filterStatus', 'cancelled')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs flex items-center gap-1 {{ $filterStatus === 'cancelled' ? 'bg-rose-600 text-white border-rose-500' : 'border-slate-200 dark:border-slate-800 text-rose-600 dark:text-rose-400' }}">
                <span>الملغاة</span>
                @if($cancelledCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filterStatus === 'cancelled' ? 'bg-white text-rose-700 font-bold' : 'bg-rose-500/20 text-rose-600' }} font-mono">{{ $cancelledCount }}</span>
                @endif
            </button>
            <button wire:click="$set('filterStatus', 'all')" class="px-2.5 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer text-xs {{ $filterStatus === 'all' ? 'bg-slate-700 text-white border-slate-600' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">الكل</button>
        </div>
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
                        <th class="p-3.5">حالة الفاتورة</th>
                        <th class="p-3.5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @forelse($purchases as $p)
                    <tr class="transition-colors {{ $p->status === 'cancelled' ? 'bg-rose-50/50 dark:bg-rose-950/20 opacity-75' : 'hover:bg-slate-50 dark:hover:bg-slate-800/30' }}">
                        <td class="p-3.5 font-mono font-bold {{ $p->status === 'cancelled' ? 'line-through text-slate-400' : 'text-teal-600 dark:text-teal-400' }}">
                            {{ $p->purchase_number }}
                        </td>
                        <td class="p-3.5 font-bold text-slate-800 dark:text-slate-100">
                            {{ $p->supplier->name }}
                        </td>
                        <td class="p-3.5 font-mono text-slate-500 dark:text-slate-400">{{ $p->purchase_date->format('Y-m-d') }}</td>
                        
                        <!-- Items & Quantities Column -->
                        <td class="p-3.5">
                            <div class="space-y-1">
                                @foreach($p->items as $itemLine)
                                <div class="flex items-center gap-1.5 text-[11px]">
                                    <span class="font-bold {{ $p->status === 'cancelled' ? 'text-slate-400 line-through' : 'text-emerald-600 dark:text-emerald-400' }} font-mono">
                                        {{ number_format($itemLine->quantity, 3) }} {{ $itemLine->item->unit ?? 'كجم' }}
                                    </span>
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
                            @if($p->status === 'cancelled')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center gap-1 w-fit">
                                    <span>🚫</span>
                                    <span>ملغاة وعُكس المخزن</span>
                                </span>
                            @elseif($p->payment_status === 'paid')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">مسددة</span>
                            @elseif($p->payment_status === 'partially_paid')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">مسدد جزئي</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">آجل</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button 
                                    wire:click="openDetailsModal({{ $p->id }})" 
                                    class="px-2 py-1 rounded-lg bg-indigo-500/10 hover:bg-indigo-600 hover:text-white text-indigo-600 dark:text-indigo-400 font-bold text-[11px] border border-indigo-500/20 transition-colors inline-flex items-center gap-1 cursor-pointer"
                                    title="عرض تفاصيل الفاتورة والمصاريف"
                                >
                                    <span>👁️ تفاصيل</span>
                                </button>

                                @if($p->status === 'cancelled')
                                    @hasrole('admin')
                                    <button 
                                        wire:click="restorePurchase({{ $p->id }})" 
                                        wire:confirm="هل تريد استعادة فاتورة المشتريات رقم {{ $p->purchase_number }} وإعادة إيداع بضاعتها بالمخزون؟"
                                        class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-600 hover:text-white text-emerald-700 dark:text-emerald-400 font-bold text-[11px] border border-emerald-500/30 transition-colors inline-flex items-center gap-1 cursor-pointer"
                                        title="استعادة الفاتورة وإرجاع البضاعة للمخزن"
                                    >
                                        <span>♻️ استعادة</span>
                                    </button>
                                    @endhasrole
                                @else
                                    @hasrole('admin')
                                    <button 
                                        wire:click="cancelPurchase({{ $p->id }})" 
                                        wire:confirm="⚠️ تنبيه هام: هل أنت متأكد من إلغاء فاتورة المشتريات رقم {{ $p->purchase_number }}؟ سيتم خصم وعكس كافة الكميات الموردة من المخزون فوراً وضبط رصيد المورد."
                                        class="px-2 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-600 hover:text-white text-rose-600 dark:text-rose-400 text-[11px] font-bold border border-rose-500/30 transition-all flex items-center gap-1 cursor-pointer"
                                        title="إلغاء الفاتورة وعكس المخزون"
                                    >
                                        <span>🚫 إلغاء</span>
                                    </button>
                                    @endhasrole
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-12 text-center text-slate-400">لا توجد فواتير مشتريات مسجلة</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $purchases->links() }}
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 👁️ Purchase Details & Expenses Modal       -->
    <!-- ========================================== -->
    @if($showDetailsModal && $selectedPurchase)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm animate-fade-in">
        <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header -->
            <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-950">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>📦 تفاصيل فاتورة مشتريات:</span>
                        <span class="font-mono text-emerald-600 dark:text-emerald-400">{{ $selectedPurchase->purchase_number }}</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        المورد: <span class="font-bold text-slate-700 dark:text-slate-300">{{ $selectedPurchase->supplier?->name }}</span> • 
                        التاريخ: <span class="font-mono">{{ $selectedPurchase->purchase_date?->format('Y-m-d') }}</span> • 
                        الفرع: <span class="font-bold">{{ $selectedPurchase->store?->name ?? 'الرئيسي' }}</span>
                    </p>
                </div>
                <button 
                    wire:click="closeDetailsModal" 
                    class="w-9 h-9 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-rose-500 hover:text-white text-slate-600 dark:text-slate-400 flex items-center justify-center text-sm font-bold transition-colors cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <!-- Content Body -->
            <div class="p-4 sm:p-5 overflow-y-auto space-y-4 text-xs">
                <!-- Items Table -->
                <div>
                    <h4 class="font-bold text-slate-800 dark:text-slate-200 mb-2">📋 أصناف الفاتورة والتكلفة:</h4>
                    <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-2xl">
                        <table class="w-full text-right">
                            <thead class="bg-slate-50 dark:bg-slate-950 text-[11px] text-slate-500 font-bold border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="p-2.5">الصنف</th>
                                    <th class="p-2.5 text-center">الكمية</th>
                                    <th class="p-2.5 text-center">السعر الأساسي</th>
                                    <th class="p-2.5 text-center">نصيب المصاريف</th>
                                    <th class="p-2.5 text-center">التكلفة المحملة</th>
                                    <th class="p-2.5 text-left">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($selectedPurchase->items as $it)
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-900 dark:text-white">
                                        {{ $it->item?->name }}
                                        <span class="block text-[10px] text-slate-400 font-mono">{{ $it->item?->code }}</span>
                                    </td>
                                    <td class="p-2.5 text-center font-mono font-bold">{{ number_format($it->quantity, 3) }} {{ $it->item?->unit ?? 'كجم' }}</td>
                                    <td class="p-2.5 text-center font-mono">{{ number_format($it->base_cost_price ?? $it->cost_price, 2) }}</td>
                                    <td class="p-2.5 text-center font-mono text-amber-600 dark:text-amber-400">
                                        +{{ number_format($it->allocated_expense ?? 0, 2) }}
                                    </td>
                                    <td class="p-2.5 text-center font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ number_format($it->cost_price, 2) }} ج.م
                                    </td>
                                    <td class="p-2.5 text-left font-mono font-black text-slate-900 dark:text-white">
                                        {{ number_format($it->total_price, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Additional Expenses Breakdown (Landed Costs) -->
                @if($selectedPurchase->additionalExpenses && $selectedPurchase->additionalExpenses->count() > 0)
                <div>
                    <h4 class="font-bold text-slate-800 dark:text-slate-200 mb-2">🚚 تفاصيل المصاريف الملحقة بالفاتورة:</h4>
                    <div class="overflow-x-auto border border-amber-500/20 rounded-2xl bg-amber-500/5">
                        <table class="w-full text-right">
                            <thead class="bg-amber-500/10 text-[11px] text-amber-900 dark:text-amber-300 font-bold border-b border-amber-500/20">
                                <tr>
                                    <th class="p-2.5">بند المصروف</th>
                                    <th class="p-2.5 text-center">المبلغ</th>
                                    <th class="p-2.5 text-center">طريقة التوزيع</th>
                                    <th class="p-2.5 text-center">طريقة السداد</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-500/10 text-[11px]">
                                @foreach($selectedPurchase->additionalExpenses as $exp)
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-900 dark:text-white">{{ $exp->title }}</td>
                                    <td class="p-2.5 text-center font-mono font-black text-amber-600 dark:text-amber-400">+{{ number_format($exp->amount, 2) }} ج.م</td>
                                    <td class="p-2.5 text-center text-slate-600 dark:text-slate-400">{{ $exp->allocation_method_label }}</td>
                                    <td class="p-2.5 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $exp->paid_by === 'supplier_account' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' }}">
                                            {{ $exp->paid_by_label }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Totals Breakdown Card -->
                <div class="p-3.5 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800 grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">المجموع الفرعي:</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200 text-sm mt-0.5 block">{{ number_format($selectedPurchase->subtotal, 2) }} ج.م</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">إجمالي المصاريف الملحقة:</span>
                        <span class="font-mono font-bold text-amber-600 dark:text-amber-400 text-sm mt-0.5 block">+{{ number_format($selectedPurchase->additional_expenses_total ?? 0, 2) }} ج.م</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">الصافي الإجمالي:</span>
                        <span class="font-mono font-black text-emerald-600 dark:text-emerald-400 text-base mt-0.5 block">{{ number_format($selectedPurchase->net_total, 2) }} ج.م</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">المتبقي (آجل):</span>
                        <span class="font-mono font-black text-amber-600 dark:text-amber-400 text-sm mt-0.5 block">{{ number_format($selectedPurchase->remaining_amount, 2) }} ج.م</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex justify-end">
                <button 
                    wire:click="closeDetailsModal" 
                    class="px-5 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 transition-colors cursor-pointer"
                >
                    إغلاق
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
