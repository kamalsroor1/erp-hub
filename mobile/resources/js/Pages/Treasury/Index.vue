<script setup>
import { Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';

const props = defineProps({
    treasury: Object,
    balances: Object,
    activeShift: Object,
    activeStore: Object,
});
</script>

<template>
    <MobileLayout>
        <div class="space-y-4 pb-10">
            <!-- Header -->
            <div class="flex items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-500 flex items-center justify-center font-black text-lg">
                        🏦
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">حركة الخزينة والصندوق</h2>
                        <p class="text-xs text-slate-400 font-bold">{{ activeStore?.name || 'الفرع الرئيسي' }}</p>
                    </div>
                </div>

                <div class="text-end">
                    <span class="text-[10px] text-slate-400 font-mono font-bold block">{{ treasury?.date || 'اليوم' }}</span>
                    <span v-if="activeShift" class="text-[10px] px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 font-bold">
                        وردية مفتوحة #{{ activeShift.shift_number }}
                    </span>
                </div>
            </div>

            <!-- Net Cash Today Hero Box -->
            <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 text-white rounded-3xl p-5 shadow-xl shadow-emerald-600/20 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-100">صافي النقدية بالصندوق (اليوم)</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-white/20 font-mono font-bold">LIVE</span>
                </div>

                <div class="text-2xl font-black font-mono">
                    {{ Number(treasury?.net_cash || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} <span class="text-sm font-bold">ج.م</span>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-emerald-500/50 text-xs">
                    <div>
                        <span class="text-[10px] text-emerald-200 block">إجمالي المقبوضات:</span>
                        <span class="font-black font-mono">+{{ Number(treasury?.total_inflow || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-emerald-200 block">إجمالي المصروفات:</span>
                        <span class="font-black font-mono">-{{ Number(treasury?.total_outflow || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م</span>
                    </div>
                </div>
            </div>

            <!-- Quick Inflow / Outflow Breakdown -->
            <div class="grid grid-cols-2 gap-3 text-xs">
                <!-- Inflow Card -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2">
                    <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-bold">
                        <span>📥</span>
                        <span>الوارد والتحصيل</span>
                    </div>
                    <div class="space-y-1 text-slate-600 dark:text-slate-300 font-mono">
                        <div class="flex justify-between text-[11px]">
                            <span class="text-slate-400">مبيعات نقدية:</span>
                            <span class="font-bold">{{ Number(treasury?.cash_collected || 0).toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-[11px]">
                            <span class="text-slate-400">تحصيل ديون:</span>
                            <span class="font-bold">{{ Number(treasury?.customer_receipts || 0).toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Outflow Card -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2">
                    <div class="flex items-center gap-1.5 text-rose-600 dark:text-rose-400 font-bold">
                        <span>📤</span>
                        <span>المنصرف والمدفوع</span>
                    </div>
                    <div class="space-y-1 text-slate-600 dark:text-slate-300 font-mono">
                        <div class="flex justify-between text-[11px]">
                            <span class="text-slate-400">سداد موردين:</span>
                            <span class="font-bold">{{ Number(treasury?.supplier_paid || 0).toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-[11px]">
                            <span class="text-slate-400">مصروفات عامة:</span>
                            <span class="font-bold">{{ Number(treasury?.expenses_total || 0).toFixed(2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Balances (Receivables & Payables) -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <h3 class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                    <span>📊</span>
                    <span>الذمم والمديونيات في السوق</span>
                </h3>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-500/20">
                        <div>
                            <div class="font-bold text-amber-700 dark:text-amber-400">مديونيات العملاء (لنا بالخارج)</div>
                            <div class="text-[10px] text-amber-600/70">مستحقات واجبة التحصيل</div>
                        </div>
                        <div class="font-black font-mono text-sm text-amber-700 dark:text-amber-400">
                            {{ Number(balances?.total_receivable || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-500/20">
                        <div>
                            <div class="font-bold text-rose-700 dark:text-rose-400">مستحقات الموردين (علينا)</div>
                            <div class="text-[10px] text-rose-600/70">فواتير مشتريات آجلة</div>
                        </div>
                        <div class="font-black font-mono text-sm text-rose-700 dark:text-rose-400">
                            {{ Number(balances?.total_payable || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-2 gap-2">
                <Link
                    href="/payments"
                    class="p-3 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 font-black text-xs text-center text-slate-800 dark:text-slate-200 touch-active hover:border-emerald-500"
                >
                    💳 سجل السندات المالية
                </Link>
                <Link
                    href="/invoices"
                    class="p-3 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 font-black text-xs text-center text-slate-800 dark:text-slate-200 touch-active hover:border-emerald-500"
                >
                    🧾 سجل المبيعات
                </Link>
            </div>
        </div>
    </MobileLayout>
</template>
