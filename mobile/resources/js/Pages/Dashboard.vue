<script setup>
import { Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import { haptic } from '@/Utils/haptics';
import { can } from '@/Utils/permissions';

const props = defineProps({
    user: Object,
    store: Object,
    customersCount: Number,
    suppliersCount: Number,
    totalReceivable: String,
    totalPayable: String,
    todayMetrics: Object,
    currentShift: Object,
    hasActiveShift: Boolean,
    lowStockCount: Number,
    recentInvoices: { type: Array, default: () => [] },
    recentLogs: { type: Array, default: () => [] },
});
</script>

<template>
    <MobileLayout>
        <div class="space-y-4 pb-20 select-none">
            <!-- 1. Executive Welcome & Shift Status Banner -->
            <div class="bg-gradient-to-l from-emerald-700 via-emerald-800 to-slate-900 rounded-3xl p-5 text-white shadow-xl shadow-emerald-950/40 relative overflow-hidden space-y-3">
                <div class="absolute -left-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block px-2.5 py-0.5 rounded-full bg-white/20 text-emerald-100 text-[11px] font-black backdrop-blur-xs">
                                مرحباً بك 👋
                            </span>
                            <span
                                v-if="hasActiveShift"
                                class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black animate-pulse"
                            >
                                الوردية مفتوحة 🟢
                            </span>
                            <span
                                v-else
                                class="px-2.5 py-0.5 rounded-full bg-rose-500/80 text-white text-[10px] font-black"
                            >
                                الوردية مغلقة 🔴
                            </span>
                        </div>
                        <h2 class="text-xl font-black mt-1">{{ user?.name || 'مدير النظام' }}</h2>
                        <p class="text-xs text-emerald-100/90 font-bold mt-0.5">
                            الفرع النشط: <span class="font-black text-amber-300">{{ store?.name || 'الفرع الرئيسي' }}</span>
                        </p>
                    </div>

                    <div class="w-13 h-13 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-3xl border border-white/20 shadow-inner">
                        💼
                    </div>
                </div>

                <!-- Shift Quick Action Bar -->
                <div class="pt-2 border-t border-white/15 flex items-center justify-between text-xs relative z-10">
                    <div v-if="hasActiveShift" class="text-emerald-100 text-[11px] font-bold">
                        رصيد الدرج: <span class="font-mono font-black text-amber-300">{{ Number(currentShift?.opening_balance || 0).toFixed(2) }} ج.م</span>
                    </div>
                    <div v-else class="text-rose-200 text-[11px] font-bold">
                        لم يتم فتح وردية كاشير بعد
                    </div>

                    <Link
                        href="/shifts"
                        @click="haptic.light()"
                        class="px-3 py-1.5 rounded-xl bg-white text-emerald-800 hover:bg-emerald-50 text-[11px] font-black shadow-md flex items-center gap-1 transition touch-active"
                    >
                        <span>🔐</span>
                        <span>{{ hasActiveShift ? 'إدارة وتقفيل الوردية' : 'فتح وردية جديدة' }}</span>
                    </Link>
                </div>
            </div>

            <!-- 2. Low Stock Warning Alert (if any) -->
            <Link
                v-if="lowStockCount > 0"
                href="/items"
                @click="haptic.warning()"
                class="p-3.5 rounded-2xl bg-gradient-to-r from-rose-500/15 via-rose-500/10 to-transparent border border-rose-500/30 flex items-center justify-between text-xs text-rose-600 dark:text-rose-400 font-bold transition hover:bg-rose-500/20 touch-active"
            >
                <div class="flex items-center gap-2.5">
                    <span class="text-lg animate-bounce">⚠️</span>
                    <div>
                        <span class="font-black text-slate-900 dark:text-white">تنبيه رادار النواقص:</span>
                        <span class="text-[11px] block text-rose-500">يوجد {{ lowStockCount }} أصناف قارب رصيدها على النفاد في المخزن</span>
                    </div>
                </div>
                <span class="px-2.5 py-1 bg-rose-600 text-white font-black text-[10px] rounded-xl shadow-xs">
                    عرض النواقص 🔍
                </span>
            </Link>

            <!-- 3. Live Today's Pulse KPIs (نبض أداء اليوم) -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="text-base">📊</span>
                        <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">
                            نبض أداء اليوم (Live Pulse)
                        </h3>
                    </div>
                    <Link href="/reports" class="text-[11px] text-emerald-600 dark:text-emerald-400 font-black flex items-center gap-0.5">
                        <span>التقرير الكامل</span>
                        <span>‹</span>
                    </Link>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <!-- Revenue Today -->
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="text-[10px] text-slate-400 font-bold block mb-0.5">مبيعات اليوم 🛒</span>
                        <div class="text-lg font-black font-mono text-emerald-600 dark:text-emerald-400">
                            {{ Number(todayMetrics?.net_sales || 0).toFixed(2) }} <span class="text-[10px] font-sans">ج.م</span>
                        </div>
                        <span class="text-[9px] text-slate-400 font-mono">{{ todayMetrics?.invoices_count || 0 }} فاتورة</span>
                    </div>

                    <!-- Cash Collected Today -->
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="text-[10px] text-slate-400 font-bold block mb-0.5">التحصيل النقدي 💵</span>
                        <div class="text-lg font-black font-mono text-emerald-600 dark:text-emerald-400">
                            {{ Number(todayMetrics?.total_paid || 0).toFixed(2) }} <span class="text-[10px] font-sans">ج.م</span>
                        </div>
                        <span class="text-[9px] text-slate-400 font-mono">نقداً بالدرج</span>
                    </div>

                    <!-- Today's COGS -->
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="text-[10px] text-slate-400 font-bold block mb-0.5">تكلفة البضاعة 📦</span>
                        <div class="text-lg font-black font-mono text-slate-700 dark:text-slate-300">
                            {{ Number(todayMetrics?.total_cogs || 0).toFixed(2) }} <span class="text-[10px] font-sans">ج.م</span>
                        </div>
                        <span class="text-[9px] text-slate-400">تكلفة البضاعة المباعة</span>
                    </div>

                    <!-- Today's Net Profit -->
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="text-[10px] text-slate-400 font-bold block mb-0.5">صافي ربح اليوم 💎</span>
                        <div class="text-lg font-black font-mono text-emerald-600 dark:text-emerald-400">
                            {{ Number(todayMetrics?.net_profit || 0).toFixed(2) }} <span class="text-[10px] font-sans">ج.م</span>
                        </div>
                        <span class="text-[9px] text-emerald-500 font-bold font-mono">هامش: {{ todayMetrics?.margin_percentage || '0.0' }}%</span>
                    </div>
                </div>
            </div>

            <!-- 4. Quick Action Grid (العمليات السريعة بلمسة واحدة) -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider px-1">
                    ⚡ اختصارات العمليات السريعة
                </h3>

                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <!-- POS -->
                    <Link
                        v-if="can('pos.access')"
                        href="/pos"
                        @click="haptic.medium()"
                        class="p-3 rounded-2xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/20 font-black flex flex-col items-center justify-center gap-1.5 transition touch-active"
                    >
                        <span class="text-2xl">⚡</span>
                        <span class="text-[11px]">كاشير بيع</span>
                    </Link>

                    <!-- Purchases -->
                    <Link
                        v-if="can('purchases.view')"
                        href="/purchases"
                        @click="haptic.light()"
                        class="p-3 rounded-2xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 border border-blue-500/20 font-black flex flex-col items-center justify-center gap-1.5 transition touch-active"
                    >
                        <span class="text-2xl">📦</span>
                        <span class="text-[11px]">فاتورة شراء</span>
                    </Link>

                    <!-- Expenses -->
                    <Link
                        v-if="can('expenses.manage')"
                        href="/expenses"
                        @click="haptic.light()"
                        class="p-3 rounded-2xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 font-black flex flex-col items-center justify-center gap-1.5 transition touch-active"
                    >
                        <span class="text-2xl">💸</span>
                        <span class="text-[11px]">مصروف درج</span>
                    </Link>

                    <!-- Payments -->
                    <Link
                        v-if="can('daily_journal.view')"
                        href="/payments"
                        @click="haptic.light()"
                        class="p-3 rounded-2xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-black flex flex-col items-center justify-center gap-1.5 transition touch-active"
                    >
                        <span class="text-2xl">💰</span>
                        <span class="text-[11px]">سند قبض</span>
                    </Link>

                    <!-- Returns -->
                    <Link
                        v-if="can('returns.manage') || can('invoices.view')"
                        href="/returns"
                        @click="haptic.light()"
                        class="p-3 rounded-2xl bg-purple-500/10 hover:bg-purple-500/20 text-purple-600 dark:text-purple-400 border border-purple-500/20 font-black flex flex-col items-center justify-center gap-1.5 transition touch-active"
                    >
                        <span class="text-2xl">🔄</span>
                        <span class="text-[11px]">مرتجع بضاعة</span>
                    </Link>

                    <!-- Stock Transfers -->
                    <Link
                        v-if="can('items.view')"
                        href="/transfers"
                        @click="haptic.light()"
                        class="p-3 rounded-2xl bg-teal-500/10 hover:bg-teal-500/20 text-teal-600 dark:text-teal-400 border border-teal-500/20 font-black flex flex-col items-center justify-center gap-1.5 transition touch-active"
                    >
                        <span class="text-2xl">🚚</span>
                        <span class="text-[11px]">تحويل مخازن</span>
                    </Link>
                </div>
            </div>

            <!-- 5. Customers & Suppliers Debt Overview -->
            <div class="grid grid-cols-2 gap-2.5">
                <!-- Customers Receivable -->
                <Link
                    href="/customers"
                    @click="haptic.light()"
                    class="bg-white dark:bg-slate-900 p-3.5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition group touch-active space-y-1.5"
                >
                    <div class="flex items-center justify-between">
                        <span class="w-7 h-7 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-xs">👥</span>
                        <span class="text-[10px] font-bold text-slate-400">العملاء ({{ customersCount }})</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">مستحقاتنا لدى العملاء:</span>
                        <div class="text-base font-black font-mono text-rose-500">
                            {{ Number(totalReceivable || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} <span class="text-[10px] font-sans">ج</span>
                        </div>
                    </div>
                </Link>

                <!-- Suppliers Payable -->
                <Link
                    href="/suppliers"
                    @click="haptic.light()"
                    class="bg-white dark:bg-slate-900 p-3.5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-amber-500 transition group touch-active space-y-1.5"
                >
                    <div class="flex items-center justify-between">
                        <span class="w-7 h-7 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-xs">🏭</span>
                        <span class="text-[10px] font-bold text-slate-400">الموردين ({{ suppliersCount }})</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">مستحقات الموردين:</span>
                        <div class="text-base font-black font-mono text-amber-500">
                            {{ Number(totalPayable || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} <span class="text-[10px] font-sans">ج</span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- 6. Recent Sales Invoices Stream -->
            <div v-if="recentInvoices && recentInvoices.length > 0" class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2.5">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>🧾</span>
                        <span>آخر فواتير المبيعات الصادرة</span>
                    </h3>
                    <Link href="/invoices" class="text-[11px] text-emerald-600 font-bold">عرض الكل ‹</Link>
                </div>

                <div class="space-y-1.5">
                    <Link
                        v-for="inv in recentInvoices"
                        :key="inv.id"
                        :href="`/invoices/${inv.id}`"
                        @click="haptic.light()"
                        class="p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 flex items-center justify-between text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition touch-active"
                    >
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">
                                {{ inv.customer_name || 'عميل نقدي' }}
                            </div>
                            <span class="text-[10px] text-slate-400 font-mono">#{{ inv.invoice_number }} • {{ inv.invoice_date }}</span>
                        </div>

                        <div class="text-end">
                            <div class="font-black font-mono text-emerald-600 dark:text-emerald-400">
                                {{ Number(inv.total_amount).toFixed(2) }} ج.م
                            </div>
                            <span
                                class="text-[9px] px-1.5 py-0.2 rounded font-bold"
                                :class="inv.payment_status === 'paid' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500'"
                            >
                                {{ inv.payment_status === 'paid' ? 'مدفوعة نقداً' : 'آجل / متبقي' }}
                            </span>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- 7. Live Activity Feed (سجل الحركات الحية) -->
            <div v-if="recentLogs && recentLogs.length > 0" class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2.5">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>🕵️‍♂️</span>
                        <span>شريط العمليات الحية بالفرع</span>
                    </h3>
                    <Link href="/audit-logs" class="text-[11px] text-indigo-600 font-bold">سجل الرقابة ‹</Link>
                </div>

                <div class="space-y-1.5 text-xs">
                    <div
                        v-for="log in recentLogs"
                        :key="log.id"
                        class="p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 flex items-start justify-between gap-2"
                    >
                        <div class="flex items-start gap-2">
                            <span class="text-sm mt-0.5">{{ log.module_icon || '⚙️' }}</span>
                            <div>
                                <p class="text-[11px] font-bold text-slate-800 dark:text-slate-200 leading-tight">
                                    {{ log.description }}
                                </p>
                                <span class="text-[9px] text-slate-400 font-mono">{{ log.user_name }} • {{ log.time_ago || log.created_at }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
