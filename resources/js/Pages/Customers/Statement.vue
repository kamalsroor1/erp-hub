<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    customer: { type: Object, required: true },
    ledger: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const { formatMoney } = useMoney();

const dateFrom = ref(props.filters.from || '');
const dateTo = ref(props.filters.to || '');

const filterStatement = () => {
    router.get(`/customers/${props.customer.id}/statement`, {
        from: dateFrom.value || undefined,
        to: dateTo.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const printStatement = () => {
    window.print();
};
</script>

<template>
    <Head :title="`كشف حساب: ${customer.name}`" />

    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6 font-tajawal">
            <!-- Header & Action Bar -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 no-print">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link href="/customers" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                            →
                        </Link>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            كشف حساب تفصيلي: <span class="text-amber-400">{{ customer.name }}</span>
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400">
                        سجل العمليات المالية والمبيعات وسندات التحصيل
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="printStatement"
                        type="button"
                        class="px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-indigo-600/20 transition cursor-pointer"
                    >
                        <span>📄</span>
                        <span>طباعة كشف الحساب A4</span>
                    </button>
                </div>
            </div>

            <!-- Customer Summary Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <span class="text-xs text-slate-500 font-bold">اسم العميل / الشركة</span>
                        <div class="text-base font-black text-white mt-0.5">{{ customer.name }}</div>
                        <div v-if="customer.phone" class="text-xs text-slate-400 font-mono" dir="ltr">📱 {{ customer.phone }}</div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-500 font-bold">العنوان والسجل</span>
                        <div class="text-xs text-slate-300 font-bold mt-1">{{ customer.address || '—' }}</div>
                        <div v-if="customer.tax_number" class="text-[11px] text-slate-400 font-mono">الرقم الضريبي: {{ customer.tax_number }}</div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-500 font-bold">الرصيد الحالي المستحق</span>
                        <div
                            class="text-xl font-black font-mono mt-0.5"
                            :class="customer.current_balance > 0 ? 'text-rose-400' : (customer.current_balance < 0 ? 'text-indigo-400' : 'text-slate-400')"
                        >
                            {{ formatMoney(customer.current_balance) }} <span class="text-xs text-white">ج.م</span>
                        </div>
                    </div>
                </div>

                <!-- Date Range Filter -->
                <div class="pt-3 border-t border-slate-800 flex flex-wrap items-center gap-3 no-print">
                    <span class="text-xs font-bold text-slate-400">تصفية التاريخ:</span>
                    <div class="w-40">
                        <DatePicker v-model="dateFrom" placeholder="من تاريخ..." />
                    </div>
                    <div class="w-40">
                        <DatePicker v-model="dateTo" placeholder="إلى تاريخ..." />
                    </div>
                    <button
                        @click="filterStatement"
                        type="button"
                        class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition cursor-pointer"
                    >
                        تطبيق
                    </button>
                </div>
            </div>

            <!-- Ledger Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">التاريخ</th>
                                <th class="pb-3">البيان / الحركة</th>
                                <th class="pb-3 font-mono text-rose-400">مدين (+)</th>
                                <th class="pb-3 font-mono text-emerald-400">دائن (-)</th>
                                <th class="pb-3 font-mono text-amber-400">الرصيد التراكمي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="row in ledger" :key="row.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3 font-mono text-slate-400 text-[11px]">{{ row.date }}</td>
                                <td class="py-3 font-bold text-white font-tajawal">
                                    <Link v-if="row.type === 'invoice'" :href="`/invoices/${row.reference_id}`" class="hover:text-amber-400 text-slate-200">
                                        {{ row.description }}
                                    </Link>
                                    <span v-else class="text-slate-300">{{ row.description }}</span>
                                </td>
                                <td class="py-3 font-mono font-bold text-rose-400">
                                    {{ row.debit > 0 ? formatMoney(row.debit) : '—' }}
                                </td>
                                <td class="py-3 font-mono font-bold text-emerald-400">
                                    {{ row.credit > 0 ? formatMoney(row.credit) : '—' }}
                                </td>
                                <td class="py-3 font-mono font-black text-amber-400 text-sm">
                                    {{ formatMoney(row.balance) }} ج.م
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="ledger.length === 0" class="py-12 text-center text-slate-500 text-xs font-bold font-tajawal">
                        لا توجد حركات مسجلة في هذا النطاق الزمني
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
