<script setup>
import { Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';

const props = defineProps({
    invoice: Object,
    whatsapp: Object,
});

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <MobileLayout>
        <div class="space-y-4">
            <!-- Header with Back -->
            <div class="flex items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <Link href="/invoices" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center justify-center font-bold text-sm touch-active">
                        ‹
                    </Link>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">تفاصيل الفاتورة</h2>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 font-mono font-bold">#{{ invoice?.invoice_number }}</p>
                    </div>
                </div>

                <!-- Print Actions -->
                <div class="flex items-center gap-1.5">
                    <a
                        :href="'/invoices/' + invoice?.id + '/print/thermal'"
                        target="_blank"
                        class="px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-black touch-active flex items-center gap-1 shadow-sm"
                    >
                        <span>🖨️</span>
                        <span>إيصال 80mm</span>
                    </a>
                    <a
                        :href="'/invoices/' + invoice?.id + '/print/a4'"
                        target="_blank"
                        class="px-2.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-[11px] font-bold touch-active flex items-center gap-1 border border-slate-200 dark:border-slate-700"
                    >
                        <span>📄</span>
                        <span>A4 / PDF</span>
                    </a>
                </div>
            </div>

            <!-- WhatsApp Share Banner -->
            <a
                :href="whatsapp?.whatsapp_url"
                target="_blank"
                class="w-full p-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-lg shadow-emerald-600/25 flex items-center justify-between gap-2 transition touch-active"
            >
                <div class="flex items-center gap-2.5">
                    <span class="text-xl">📲</span>
                    <div>
                        <div>إرسال الفاتورة عبر WhatsApp</div>
                        <div class="text-[10px] text-emerald-100 font-normal">إرسال نص تفصيلي مباشر لرقم العميل</div>
                    </div>
                </div>
                <span class="text-base font-black">‹</span>
            </a>

            <!-- Thermal Invoice Receipt Card (Print Friendly) -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 printable-receipt">
                <!-- Branding -->
                <div class="text-center pb-3 border-b border-dashed border-slate-300 dark:border-slate-700">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">سرور كوفي ERP</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold">لتوريدات خامات ومطاحن البن</p>
                    <div class="text-[10px] text-slate-400 font-mono mt-1">الفرع: {{ invoice?.store?.name }} • {{ invoice?.store?.phone || '' }}</div>
                </div>

                <!-- Meta Info -->
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-[10px] text-slate-400 block">العميل:</span>
                        <span class="font-black text-slate-900 dark:text-white">{{ invoice?.customer?.name || 'عميل نقدي' }}</span>
                    </div>
                    <div class="text-end">
                        <span class="text-[10px] text-slate-400 block">التاريخ:</span>
                        <span class="font-mono font-bold text-slate-900 dark:text-white">{{ invoice?.invoice_date }}</span>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="space-y-2 border-t border-b border-dashed border-slate-300 dark:border-slate-700 py-3">
                    <div class="flex justify-between text-[11px] font-black text-slate-400">
                        <span>الصنف</span>
                        <span>الكمية × السعر</span>
                        <span>الإجمالي</span>
                    </div>

                    <div
                        v-for="item in invoice?.items"
                        :key="item.id"
                        class="flex items-center justify-between text-xs font-bold pt-1 text-slate-800 dark:text-slate-200"
                    >
                        <span class="truncate max-w-[120px]">{{ item.item?.name }}</span>
                        <span class="font-mono text-slate-400 text-[11px]">{{ Number(item.quantity).toFixed(2) }} × {{ Number(item.unit_price).toFixed(2) }}</span>
                        <span class="font-mono font-black">{{ Number(item.total_price).toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Totals Breakdown -->
                <div class="space-y-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <div class="flex justify-between">
                        <span>المجموع الفرعي:</span>
                        <span class="font-mono">{{ Number(invoice?.subtotal || 0).toFixed(2) }} ج.م</span>
                    </div>
                    <div v-if="parseFloat(invoice?.discount_amount) > 0" class="flex justify-between text-amber-500">
                        <span>الخصم:</span>
                        <span class="font-mono">-{{ Number(invoice?.discount_amount).toFixed(2) }} ج.م</span>
                    </div>
                    <div class="flex justify-between text-sm font-black text-slate-900 dark:text-white pt-2 border-t border-slate-200 dark:border-slate-700">
                        <span>صافي الفاتورة:</span>
                        <span class="font-mono text-emerald-500">{{ Number(invoice?.net_total || 0).toFixed(2) }} ج.م</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-500 pt-1">
                        <span>المسدد:</span>
                        <span class="font-mono text-emerald-600">{{ Number(invoice?.paid_amount || 0).toFixed(2) }} ج.م</span>
                    </div>
                    <div v-if="parseFloat(invoice?.remaining_amount) > 0" class="flex justify-between text-xs text-rose-500">
                        <span>المتبقي:</span>
                        <span class="font-mono">{{ Number(invoice?.remaining_amount).toFixed(2) }} ج.م</span>
                    </div>
                </div>

                <!-- Footer Note -->
                <div class="text-center pt-2 text-[10px] text-slate-400 font-semibold border-t border-dashed border-slate-300 dark:border-slate-700">
                    نشكركم لتعاملكم معنا ☕
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
