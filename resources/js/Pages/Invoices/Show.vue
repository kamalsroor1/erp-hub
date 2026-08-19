<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useMoney } from '@/Composables/useMoney';
import { trans } from '@/helpers/trans';

const props = defineProps({
    invoice: { type: Object, required: true },
});

const { formatMoney } = useMoney();

const printThermal = () => {
    window.open(`/invoices/${props.invoice.id}/print/thermal`, '_blank', 'width=400,height=600');
};

const printA4 = () => {
    window.open(`/invoices/${props.invoice.id}/print/a4`, '_blank', 'width=900,height=800');
};

const cancelInvoice = () => {
    if (confirm('هل أنت متأكد من رغبتك في إلغاء الفاتورة وعكس الأثر المخزني والمالي؟')) {
        router.post(`/invoices/${props.invoice.id}/cancel`, {}, {
            preserveScroll: true,
        });
    }
};

const getPaymentBadge = computed(() => {
    const inv = props.invoice;
    if (inv.payment_type === 'cash') {
        if (inv.payment_method === 'instapay') return { label: 'إستاباي ⚡', class: 'bg-indigo-500/15 text-indigo-400 border-indigo-500/30' };
        if (inv.payment_method === 'wallet') return { label: 'محفظة 📱', class: 'bg-teal-500/15 text-teal-400 border-teal-500/30' };
        return { label: 'كاش 💵', class: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' };
    }
    if (inv.payment_type === 'credit') return { label: 'آجل', class: 'bg-rose-500/15 text-rose-400 border-rose-500/30' };
    if (inv.payment_type === 'partial') return { label: 'جزئي', class: 'bg-amber-500/15 text-amber-400 border-amber-500/30' };
    return { label: inv.payment_type, class: 'bg-slate-800 text-slate-400' };
});
</script>

<template>
    <Head :title="`فاتورة #${invoice.invoice_number}`" />

    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6 font-tajawal">
            <!-- Header & Action Bar -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link href="/invoices" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                            →
                        </Link>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            فاتورة مبيعات #<span class="font-mono text-amber-400">{{ invoice.invoice_number }}</span>
                        </h1>
                        <span class="px-2.5 py-1 rounded-xl text-xs font-bold border" :class="getPaymentBadge.class">
                            {{ getPaymentBadge.label }}
                        </span>
                        <span v-if="invoice.status === 'cancelled'" class="px-2.5 py-1 rounded-xl text-xs font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                            ملغاة
                        </span>
                    </div>
                    <p class="text-xs text-slate-400">
                        التاريخ: <span class="font-mono">{{ invoice.formatted_created_at || invoice.invoice_date }}</span> • الفرع: <span class="text-slate-200">{{ invoice.store.name }}</span> • الكاشير: <span class="text-slate-200">{{ invoice.cashier_name }}</span>
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    <button
                        @click="printThermal"
                        type="button"
                        class="px-4 py-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-md shadow-emerald-500/20 transition cursor-pointer"
                    >
                        <span>🖨️</span>
                        <span>إيصال حراري (80mm)</span>
                    </button>

                    <button
                        @click="printA4"
                        type="button"
                        class="px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-indigo-600/20 transition cursor-pointer"
                    >
                        <span>📄</span>
                        <span>فاتورة رسمية A4</span>
                    </button>

                    <button
                        v-if="invoice.status !== 'cancelled'"
                        @click="cancelInvoice"
                        type="button"
                        class="px-3 py-2.5 rounded-2xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 font-bold text-xs transition cursor-pointer"
                        title="إلغاء الفاتورة"
                    >
                        <span>🗑️</span>
                    </button>
                </div>
            </div>

            <!-- Customer & Invoice Overview Card -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Customer Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-2">
                    <span class="text-xs text-slate-500 font-bold">بيانات العميل</span>
                    <div class="text-base font-black text-white">
                        {{ invoice.customer?.name || 'عميل نقدي' }}
                    </div>
                    <div v-if="invoice.customer?.phone" class="text-xs text-slate-400 font-mono" dir="ltr">
                        📱 {{ invoice.customer.phone }}
                    </div>
                    <div v-if="invoice.customer" class="text-xs text-slate-400">
                        رصيد الحساب الحالي: <span class="font-mono font-bold text-amber-400">{{ formatMoney(invoice.customer.balance) }} ج.م</span>
                    </div>
                </div>

                <!-- Financial Card 1 -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-2">
                    <span class="text-xs text-slate-500 font-bold">إجمالي الفاتورة والخصم</span>
                    <div class="text-xl font-black font-mono text-white">
                        {{ formatMoney(invoice.total_amount) }} <span class="text-xs text-slate-400">ج.م</span>
                    </div>
                    <div class="text-xs text-slate-400">
                        الخصم: <span class="font-mono text-rose-400">{{ formatMoney(invoice.discount_amount) }} ج.م</span>
                    </div>
                </div>

                <!-- Financial Card 2 (Net & Paid) -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-2">
                    <span class="text-xs text-slate-500 font-bold">الصافي المطلوب والمدفوع</span>
                    <div class="text-2xl font-black font-mono text-emerald-400">
                        {{ formatMoney(invoice.net_total) }} <span class="text-xs text-white">ج.م</span>
                    </div>
                    <div class="text-xs text-slate-300 flex items-center justify-between">
                        <span>المدفوع: <b class="font-mono">{{ formatMoney(invoice.paid_amount) }}</b></span>
                        <span v-if="Number(invoice.remaining_amount) > 0" class="text-rose-400 font-bold">
                            متبقي: <b class="font-mono">{{ formatMoney(invoice.remaining_amount) }}</b>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                <h3 class="text-sm font-black text-white flex items-center gap-2">
                    <span>📦</span>
                    <span>الأصناف المسجلة في الفاتورة</span>
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">#</th>
                                <th class="pb-3">اسم الصنف</th>
                                <th class="pb-3">الكمية</th>
                                <th class="pb-3">سعر الوحدة</th>
                                <th class="pb-3 text-left">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="(item, idx) in invoice.items" :key="item.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3 text-slate-500 font-mono">{{ idx + 1 }}</td>
                                <td class="py-3 font-bold text-white font-tajawal">
                                    {{ item.item_name }}
                                    <span v-if="item.item_code" class="text-[10px] text-slate-500 font-mono block">كود: {{ item.item_code }}</span>
                                </td>
                                <td class="py-3 font-mono font-bold text-slate-200">
                                    {{ item.quantity }} {{ item.unit }}
                                </td>
                                <td class="py-3 font-mono text-slate-300">
                                    {{ formatMoney(item.unit_price) }}
                                </td>
                                <td class="py-3 font-mono font-bold text-emerald-400 text-left text-sm">
                                    {{ formatMoney(item.total_price) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Notes -->
                <div v-if="invoice.notes" class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800 text-xs text-slate-300">
                    <span class="font-bold text-amber-400">ملاحظات:</span> {{ invoice.notes }}
                </div>
            </div>
        </div>
    </AppLayout>
</template>
