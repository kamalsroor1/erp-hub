<div class="space-y-6 pb-12" dir="rtl">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl font-bold border border-amber-500/20 shadow-inner">
                    🛡️
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                        سجل العمليات والرقابة الذاتية
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-bold">
                            Live Audit Trail
                        </span>
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        رصد دقيق لكافة الحركات والتعديلات وحركات الخزينة والمخزون في كافة الفروع لحظة بلحظة
                    </p>
                </div>
            </div>
        </div>

        <!-- Header Actions -->
        <div class="flex items-center gap-2">
            <button 
                wire:click="exportCsv" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs md:text-sm font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 transition-colors shadow-sm"
                title="تصدير السجل بتنسيق Excel">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>تصدير إكسيل (CSV)</span>
            </button>

            <button 
                onclick="window.print()" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs md:text-sm font-bold bg-amber-500 hover:bg-amber-600 text-slate-950 font-black transition-colors shadow-md shadow-amber-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>طباعة التقرير</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Today's Total -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">إجمالي عمليات اليوم</p>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($stats['today_total']) }}</h3>
                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">حركات مسجلة</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl font-bold">
                ⚡
            </div>
        </div>

        <!-- Card 2: Critical Actions -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">العمليات الحساسة اليوم</p>
                <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ number_format($stats['today_critical']) }}</h3>
                <span class="text-[11px] text-slate-400">إلغاء / حذف / تنبيه</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl font-bold">
                🚨
            </div>
        </div>

        <!-- Card 3: Active Users -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">المستخدمين النشطين اليوم</p>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($stats['today_users']) }}</h3>
                <span class="text-[11px] text-slate-400">كاشير ومديرين</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl font-bold">
                👥
            </div>
        </div>

        <!-- Card 4: Active Stores -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">الفروع المتصلة اليوم</p>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($stats['today_stores']) }}</h3>
                <span class="text-[11px] text-slate-400">مخازن وعربات توزيع</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl font-bold">
                🏬
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <!-- Date Preset Buttons -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 ml-2">الفترة الزمنية:</span>
                
                <button 
                    wire:click="setDatePreset('all')" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $datePreset === 'all' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    الكل
                </button>
                <button 
                    wire:click="setDatePreset('today')" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $datePreset === 'today' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    اليوم ⚡
                </button>
                <button 
                    wire:click="setDatePreset('yesterday')" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $datePreset === 'yesterday' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    أمس
                </button>
                <button 
                    wire:click="setDatePreset('7days')" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $datePreset === '7days' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    آخر 7 أيام
                </button>
                <button 
                    wire:click="setDatePreset('30days')" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $datePreset === '30days' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    آخر 30 يوم
                </button>
                <button 
                    wire:click="setDatePreset('custom')" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $datePreset === 'custom' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    تاريخ مخصص 📅
                </button>
            </div>

            <!-- View Mode Switcher -->
            <div class="flex items-center bg-slate-100 dark:bg-slate-800 p-1 rounded-xl border border-slate-200 dark:border-slate-700">
                <button 
                    wire:click="$set('viewMode', 'timeline')" 
                    class="flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold transition-all {{ $viewMode === 'timeline' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                    <span>⏱️ الشريط الزمني</span>
                </button>
                <button 
                    wire:click="$set('viewMode', 'table')" 
                    class="flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold transition-all {{ $viewMode === 'table' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                    <span>📊 جدول البيانات</span>
                </button>
            </div>
        </div>

        <!-- Custom Date Range Picker (shown when custom preset active) -->
        @if($datePreset === 'custom')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-amber-500/5 p-3 rounded-xl border border-amber-500/20">
            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">من تاريخ:</label>
                <input type="date" wire:model.live="dateFrom" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">إلى تاريخ:</label>
                <input type="date" wire:model.live="dateTo" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs text-slate-900 dark:text-white">
            </div>
        </div>
        @endif

        <!-- Filter Selects -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search Input -->
            <div class="lg:col-span-2 relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="🔍 ابحث برقم الفاتورة، اسم الصنف، الموظف، الفرع..." 
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-xs md:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                @if($search)
                <button wire:click="$set('search', '')" class="absolute left-3 top-3 text-slate-400 hover:text-slate-600 text-xs">✕</button>
                @endif
            </div>

            <!-- Module Filter -->
            <div>
                <select wire:model.live="module" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-xs md:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                    <option value="">📂 كل الأقسام</option>
                    <option value="sales">🛒 المبيعات و POS</option>
                    <option value="inventory">📦 الأصناف والمخزون</option>
                    <option value="shifts">💵 الخزينة والورديات</option>
                    <option value="purchases">🚚 المشتريات والتوريد</option>
                    <option value="expenses">💸 المصروفات</option>
                    <option value="contacts">👥 العملاء والموردين</option>
                    <option value="auth">🔐 الأمان وتسجيل الدخول</option>
                    <option value="system">⚙️ إدارة النظام</option>
                </select>
            </div>

            <!-- User Filter -->
            <div>
                <select wire:model.live="userId" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-xs md:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                    <option value="">👤 كل الموظفين</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->phone }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Store Filter -->
            <div>
                <select wire:model.live="storeId" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-xs md:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                    <option value="">🏬 كل الفروع والمخازن</option>
                    @foreach($stores as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($search || $module || $action || $userId || $storeId || $datePreset !== 'all')
        <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-800">
            <span>عوامل التصفية مفعلة (عدد النتائج: {{ $logs->total() }})</span>
            <button wire:click="resetFilters" class="text-rose-600 hover:underline font-bold flex items-center gap-1">
                <span>✕ إعادة تعيين الفلاتر</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Main Content View -->
    @if($logs->isEmpty())
    <div class="bg-white dark:bg-slate-900 p-12 rounded-2xl border border-slate-200 dark:border-slate-800 text-center shadow-sm">
        <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-3xl mx-auto mb-3">
            🔍
        </div>
        <h3 class="text-base font-bold text-slate-700 dark:text-slate-200">لا توجد عمليات مسجلة تطابق بحثك</h3>
        <p class="text-xs text-slate-400 mt-1">جرب تغيير عوامل التصفية أو اختيار فترة زمنية أوسع</p>
        @if($search || $module || $userId || $storeId || $datePreset !== 'all')
        <button wire:click="resetFilters" class="mt-4 px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs">
            إعادة تعيين كافة الفلاتر
        </button>
        @endif
    </div>
    @elseif($viewMode === 'timeline')
    <!-- TIMELINE VIEW -->
    <div class="space-y-4">
        @foreach($logs as $log)
        @php
            $mBadge = $log->module_badge;
            $aBadge = $log->action_badge;
        @endphp
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 p-4 md:p-5 shadow-sm hover:shadow-md transition-all relative overflow-hidden group">
            <!-- Left Accent Border -->
            <div class="absolute right-0 top-0 bottom-0 w-1.5 {{ $log->action === 'cancelled' || $log->action === 'deleted' || $log->action === 'login_failed' ? 'bg-rose-500' : ($log->action === 'created' ? 'bg-emerald-500' : ($log->action === 'updated' ? 'bg-amber-500' : 'bg-blue-500')) }}"></div>

            <div class="flex flex-col md:flex-row md:items-start justify-between gap-3">
                <!-- Main Activity Body -->
                <div class="space-y-2 flex-1 pr-2">
                    <!-- Badges Row -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Module Badge -->
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            <span>{{ $mBadge['icon'] }}</span>
                            <span>{{ $mBadge['label'] }}</span>
                        </span>

                        <!-- Action Badge -->
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $aBadge['bg'] }}">
                            {{ $aBadge['label'] }}
                        </span>

                        <!-- Store Badge -->
                        @if($log->store)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20">
                            🏬 {{ $log->store->name }}
                        </span>
                        @endif

                        <!-- Timestamp -->
                        <span class="text-xs text-slate-400 flex items-center gap-1 font-mono mr-auto" title="{{ $log->created_at->format('Y-m-d H:i:s') }}">
                            🕒 {{ $log->created_at->locale('ar')->diffForHumans() }} ({{ $log->created_at->format('h:i A') }})
                        </span>
                    </div>

                    <!-- Arabic Description -->
                    <p class="text-sm md:text-base font-bold text-slate-900 dark:text-white leading-relaxed">
                        {{ $log->description }}
                    </p>

                    <!-- Meta User Footer -->
                    <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-100 dark:border-slate-800/60">
                        <div class="flex items-center gap-1.5">
                            <span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center font-bold text-[10px] text-slate-700 dark:text-slate-300">
                                {{ mb_substr($log->user?->name ?? 'ن', 0, 1) }}
                            </span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $log->user?->name ?? 'النظام التلقائي' }}</span>
                            @if($log->user?->phone && $log->user->phone !== '-')
                            <span class="text-slate-400 text-[11px]">({{ $log->user->phone }})</span>
                            @endif
                        </div>

                        @if($log->ip_address)
                        <div class="flex items-center gap-1 font-mono text-[11px] text-slate-400">
                            <span>🌐 IP: {{ $log->ip_address }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Action Button for Diff -->
                @if(!empty($log->properties))
                <div class="flex md:flex-col items-center justify-end">
                    <button 
                        wire:click="showDetails({{ $log->id }})" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30 transition-colors">
                        <span>👁️ معاينة التفاصيل</span>
                    </button>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <!-- TABLE VIEW -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse text-xs md:text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 font-bold">
                        <th class="p-3.5">الوقت والتاريخ</th>
                        <th class="p-3.5">القسم</th>
                        <th class="p-3.5">الإجراء</th>
                        <th class="p-3.5">الوصف والتفاصيل</th>
                        <th class="p-3.5">الموظف المسؤول</th>
                        <th class="p-3.5">الفرع</th>
                        <th class="p-3.5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($logs as $log)
                    @php
                        $mBadge = $log->module_badge;
                        $aBadge = $log->action_badge;
                    @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="p-3.5 font-mono text-xs whitespace-nowrap text-slate-500">
                            <div>{{ $log->created_at->format('Y-m-d') }}</div>
                            <div class="text-[11px] text-slate-400">{{ $log->created_at->format('h:i:s A') }}</div>
                        </td>
                        <td class="p-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $mBadge['icon'] }} {{ $mBadge['label'] }}
                            </span>
                        </td>
                        <td class="p-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold border {{ $aBadge['bg'] }}">
                                {{ $aBadge['label'] }}
                            </span>
                        </td>
                        <td class="p-3.5 font-semibold text-slate-900 dark:text-white max-w-md">
                            {{ $log->description }}
                        </td>
                        <td class="p-3.5 whitespace-nowrap font-medium text-slate-700 dark:text-slate-300">
                            {{ $log->user?->name ?? 'النظام' }}
                        </td>
                        <td class="p-3.5 whitespace-nowrap text-slate-600 dark:text-slate-400">
                            {{ $log->store?->name ?? 'الرئيسي' }}
                        </td>
                        <td class="p-3.5 text-center whitespace-nowrap">
                            @if(!empty($log->properties))
                            <button 
                                wire:click="showDetails({{ $log->id }})" 
                                class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20 border border-amber-500/30">
                                معاينة
                            </button>
                            @else
                            <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Pagination -->
    <div class="mt-4">
        {{ $logs->links() }}
    </div>

    <!-- DETAILS & DIFF MODAL -->
    @if($selectedLog)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-fadeIn" dir="rtl">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-2xl w-full overflow-hidden max-h-[90vh] flex flex-col">
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-xl font-bold">
                        🔍
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">تفاصيل ومقارنة الحركة (#{{ $selectedLog->id }})</h3>
                        <p class="text-xs text-slate-400 font-mono">{{ $selectedLog->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
                <button wire:click="closeDetails" class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-rose-500 hover:text-white flex items-center justify-center transition-colors text-sm font-bold">
                    ✕
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto space-y-4 text-xs md:text-sm">
                <!-- Description summary -->
                <div class="bg-slate-50 dark:bg-slate-800 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] font-bold text-slate-400 mb-1">البيان المسجل:</span>
                    <p class="font-bold text-slate-900 dark:text-white">{{ $selectedLog->description }}</p>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                        <span class="block text-[11px] text-slate-400 font-bold">الموظف المسؤول</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedLog->user?->name }}</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                        <span class="block text-[11px] text-slate-400 font-bold">الفرع أو المخزن</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedLog->store?->name ?? 'الفرع الرئيسي' }}</span>
                    </div>
                </div>

                <!-- JSON / Diff Properties Breakdown -->
                @if(!empty($selectedLog->properties))
                <div class="space-y-2">
                    <h4 class="font-bold text-slate-800 dark:text-slate-200 text-xs">📊 تفاصيل البيانات والبيانات المرفقة (Data Properties):</h4>
                    <div class="bg-slate-950 text-emerald-400 p-4 rounded-2xl font-mono text-xs overflow-x-auto border border-slate-800 max-h-60" dir="ltr">
                        <pre class="whitespace-pre-wrap">{{ json_encode($selectedLog->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end bg-slate-50 dark:bg-slate-800/50">
                <button wire:click="closeDetails" class="px-5 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 font-bold text-xs text-slate-800 dark:text-slate-200 transition-colors">
                    إغلاق
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
