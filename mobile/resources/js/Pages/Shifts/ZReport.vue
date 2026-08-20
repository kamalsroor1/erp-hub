<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    report: { type: Object, default: null },
});

const printReceipt = () => {
    haptic.success();
    window.print();
};

const diff = computed(() => {
    return Number(props.report?.cash_difference || 0);
});

const whatsappText = computed(() => {
    if (!props.report) return '';
    const r = props.report;
    return encodeURIComponent(
        `☕ *تقرير تقفيل وردية الكاشير (Z-Report)*\n` +
        `━━━━━━━━━━━━━━━\n` +
        `📌 *رقم الوردية:* ${r.shift_number}\n` +
        `🏪 *الفرع:* ${r.store_name}\n` +
        `👤 *الكاشير:* ${r.cashier_name}\n` +
        `⏱️ *وقت الفتح:* ${r.opened_at}\n` +
        `⏱️ *وقت الإغلاق:* ${r.closed_at}\n` +
        `━━━━━━━━━━━━━━━\n` +
        `💵 *الرصيد الافتتاحي:* ${Number(r.opening_cash_balance).toFixed(2)} ج.م\n` +
        `⚡ *مبيعات كاش:* ${Number(r.total_cash_sales).toFixed(2)} ج.م\n` +
        `📑 *مبيعات آجل:* ${Number(r.total_credit_sales).toFixed(2)} ج.م\n` +
        `📥 *تحصيلات وسندات:* ${Number(r.total_payments_collected).toFixed(2)} ج.م\n` +
        `💸 *مصروفات ونثريات:* ${Number(r.total_expenses).toFixed(2)} ج.م\n` +
        `━━━━━━━━━━━━━━━\n` +
        `🏦 *النقدية المتوقعة:* ${Number(r.expected_cash_balance).toFixed(2)} ج.م\n` +
        `💰 *النقدية الفعلية:* ${Number(r.actual_cash_balance).toFixed(2)} ج.م\n` +
        `⚖️ *الفارق:* ${diff.value === 0 ? 'مطابقة تماماً ✓' : (diff.value > 0 ? '+' + diff.value.toFixed(2) + ' ج.م زيادة' : diff.value.toFixed(2) + ' ج.م عجز')}\n` +
        `━━━━━━━━━━━━━━━\n` +
        `☕ سرور كوفي ERP`
    );
});
</script>

<template>
    <div class="min-h-screen bg-slate-100 dark:bg-slate-950 p-3 sm:p-6 flex flex-col items-center justify-start text-slate-900 dark:text-slate-100 select-none">
        
        <!-- Action Toolbar (Hidden during browser print) -->
        <div class="w-full max-w-sm mb-4 flex items-center justify-between gap-2 print:hidden">
            <Link
                href="/shifts"
                class="h-10 px-3.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-xs font-bold flex items-center gap-1.5 shadow-xs touch-active"
            >
                <span>⬅️</span>
                <span>الورديات</span>
            </Link>

            <div class="flex items-center gap-2">
                <a
                    :href="'https://wa.me/?text=' + whatsappText"
                    target="_blank"
                    class="h-10 px-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl text-xs font-bold flex items-center gap-1 shadow-md shadow-emerald-500/20 touch-active"
                >
                    <span>💬</span>
                    <span>واتساب</span>
                </a>

                <button
                    @click="printReceipt"
                    type="button"
                    class="h-10 px-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-2xl text-xs font-black flex items-center gap-1.5 shadow-md touch-active"
                >
                    <span>🖨️</span>
                    <span>طباعة حراري</span>
                </button>
            </div>
        </div>

        <!-- 80mm Thermal Slip Card -->
        <div
            v-if="report"
            class="w-full max-w-sm bg-white text-slate-900 rounded-3xl p-5 border border-slate-300 shadow-xl space-y-3 font-mono text-xs print:shadow-none print:border-none print:w-full print:max-w-none print:p-0"
        >
            <!-- Logo / Header -->
            <div class="text-center space-y-1 pb-3 border-b-2 border-dashed border-slate-300">
                <div class="text-2xl font-black font-sans tracking-wide">☕ سرور كوفي</div>
                <div class="text-[11px] font-bold text-slate-600 font-sans">تقرير تقفيل وردية الكاشير اليومي</div>
                <div class="text-xs font-black bg-slate-900 text-white px-2 py-0.5 rounded-md inline-block">
                    Z-REPORT #{{ report.shift_number }}
                </div>
            </div>

            <!-- Meta Data -->
            <div class="text-[11px] space-y-1 py-1 border-b border-slate-200">
                <div class="flex justify-between">
                    <span class="text-slate-500">الفرع:</span>
                    <span class="font-bold">{{ report.store_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">الكاشير:</span>
                    <span class="font-bold">{{ report.cashier_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">وقت الفتح:</span>
                    <span>{{ report.opened_at }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">وقت الإغلاق:</span>
                    <span>{{ report.closed_at }}</span>
                </div>
            </div>

            <!-- Breakdown Financial Table -->
            <div class="space-y-1.5 py-1 text-xs border-b-2 border-dashed border-slate-300">
                <div class="flex justify-between">
                    <span>الرصيد الافتتاحي (الفكة):</span>
                    <span class="font-bold">{{ Number(report.opening_cash_balance).toFixed(2) }} ج.م</span>
                </div>
                <div class="flex justify-between text-emerald-700 font-bold">
                    <span>مبيعات نقدي (كاش):</span>
                    <span>+{{ Number(report.total_cash_sales).toFixed(2) }} ج.م</span>
                </div>
                <div class="flex justify-between text-amber-700">
                    <span>مبيعات آجلة (شكك):</span>
                    <span>{{ Number(report.total_credit_sales).toFixed(2) }} ج.م</span>
                </div>
                <div class="flex justify-between text-teal-700 font-bold">
                    <span>سندات وتحصيلات نقدية:</span>
                    <span>+{{ Number(report.total_payments_collected).toFixed(2) }} ج.م</span>
                </div>
                <div class="flex justify-between text-rose-700">
                    <span>مصروفات ونثريات:</span>
                    <span>-{{ Number(report.total_expenses).toFixed(2) }} ج.م</span>
                </div>
                <div class="flex justify-between text-rose-700">
                    <span>مرتجعات نقدية:</span>
                    <span>-{{ Number(report.total_refunds).toFixed(2) }} ج.م</span>
                </div>
            </div>

            <!-- Drawer Reconciliation Result -->
            <div class="space-y-2 py-2">
                <div class="flex justify-between text-xs font-bold">
                    <span>النقدية المتوقعة بالدرج:</span>
                    <span>{{ Number(report.expected_cash_balance).toFixed(2) }} ج.م</span>
                </div>

                <div class="flex justify-between text-sm font-black bg-slate-100 p-2 rounded-xl border border-slate-200">
                    <span>النقدية الفعلية بالجرد:</span>
                    <span class="text-emerald-700">{{ Number(report.actual_cash_balance).toFixed(2) }} ج.م</span>
                </div>

                <!-- Difference Badge -->
                <div
                    class="p-2 rounded-xl text-center text-xs font-black"
                    :class="diff === 0 ? 'bg-emerald-100 text-emerald-800' : (diff > 0 ? 'bg-sky-100 text-sky-800' : 'bg-rose-100 text-rose-800')"
                >
                    <span v-if="diff === 0">✓ مطابقة تماماً (بدون عجز أو زيادة)</span>
                    <span v-else-if="diff > 0">⚡ زيادة بالدرج: +{{ diff.toFixed(2) }} ج.م</span>
                    <span v-else>⚠️ عجز بالدرج: {{ diff.toFixed(2) }} ج.م</span>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="report.notes" class="text-[10px] text-slate-500 p-2 bg-slate-50 rounded-lg border border-slate-200">
                ملاحظات: {{ report.notes }}
            </div>

            <!-- Footer Signature -->
            <div class="text-center pt-3 border-t border-slate-200 text-[10px] text-slate-400 space-y-1">
                <div>توقيع الكاشير: ........................</div>
                <div>نظام سرور كوفي ERP الذكي</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    body {
        background: #ffffff !important;
    }
}
</style>
