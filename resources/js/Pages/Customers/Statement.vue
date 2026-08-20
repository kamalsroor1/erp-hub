<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    customer: { type: Object, required: true },
    ledger: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({ total_debit: 0, total_credit: 0, current_balance: 0 }) },
    filters: { type: Object, default: () => ({}) },
});

const { formatMoney } = useMoney();

const dateFrom = ref(props.filters.from || '');
const dateTo = ref(props.filters.to || '');

const applyDatePreset = (preset) => {
    const now = new Date();
    const formatDate = (d) => d.toISOString().split('T')[0];

    if (preset === 'today') {
        dateFrom.value = formatDate(now);
        dateTo.value = formatDate(now);
    } else if (preset === 'this_month') {
        const start = new Date(now.getFullYear(), now.getMonth(), 1);
        const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        dateFrom.value = formatDate(start);
        dateTo.value = formatDate(end);
    } else if (preset === 'this_year') {
        const start = new Date(now.getFullYear(), 0, 1);
        const end = new Date(now.getFullYear(), 11, 31);
        dateFrom.value = formatDate(start);
        dateTo.value = formatDate(end);
    } else if (preset === 'all') {
        dateFrom.value = '';
        dateTo.value = '';
    }
    filterStatement();
};

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
    <Head :title="`${$t('contacts.ledger_title')}: ${customer.name}`" />

    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6 font-tajawal">
            <!-- Header & Action Bar -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 no-print">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link href="/customers" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                            →
                        </Link>
                        <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                            <span>{{ $t('contacts.ledger_title') }}:</span>
                            <span class="text-amber-400">{{ customer.name }}</span>
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400">
                        {{ $t('contacts.ledger_subtitle') }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="printStatement"
                        type="button"
                        class="px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-indigo-600/20 transition cursor-pointer"
                    >
                        <span>📄</span>
                        <span>{{ $t('contacts.print_statement') }}</span>
                    </button>
                </div>
            </div>

            <!-- Customer Summary Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <span class="text-xs text-slate-500 font-bold">{{ $t('contacts.customer_name') }}</span>
                        <div class="text-base font-black text-white mt-0.5">{{ customer.name }}</div>
                        <div v-if="customer.phone" class="text-xs text-slate-400 font-mono" dir="ltr">📱 {{ customer.phone }}</div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-500 font-bold">{{ $t('common.address') }}</span>
                        <div class="text-xs text-slate-300 font-bold mt-1">{{ customer.address || '—' }}</div>
                        <div v-if="customer.tax_number" class="text-[11px] text-slate-400 font-mono">{{ $t('invoices.tax_number') || 'الرقم الضريبي' }}: {{ customer.tax_number }}</div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-500 font-bold">{{ $t('contacts.closing_balance') }}</span>
                        <div
                            class="text-xl font-black font-mono mt-0.5"
                            :class="customer.current_balance > 0 ? 'text-rose-400' : (customer.current_balance < 0 ? 'text-indigo-400' : 'text-slate-400')"
                        >
                            {{ formatMoney(customer.current_balance) }} <span class="text-xs text-white">{{ $t('common.currency') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Date Range Filter & Presets -->
                <div class="pt-3 border-t border-slate-800 space-y-3 no-print">
                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="text-slate-500 font-bold text-[11px] ml-1">{{ $t('contacts.report_period') }}:</span>
                        <button @click="applyDatePreset('today')" type="button" class="px-2.5 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold transition">{{ $t('dashboard.today') || 'اليوم' }}</button>
                        <button @click="applyDatePreset('this_month')" type="button" class="px-2.5 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold transition">{{ $t('dashboard.this_month') || 'هذا الشهر' }}</button>
                        <button @click="applyDatePreset('this_year')" type="button" class="px-2.5 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold transition">{{ $t('dashboard.this_year') || 'هذا العام' }}</button>
                        <button @click="applyDatePreset('all')" type="button" class="px-2.5 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold transition">{{ $t('common.all') }}</button>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="w-44">
                            <DatePicker v-model="dateFrom" :placeholder="$t('contacts.from_date')" />
                        </div>
                        <div class="w-44">
                            <DatePicker v-model="dateTo" :placeholder="$t('contacts.to_date')" />
                        </div>
                        <button
                            @click="filterStatement"
                            type="button"
                            class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition cursor-pointer"
                        >
                            {{ $t('common.filter') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3 Summary KPI Cards for Statement Period -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 space-y-1">
                    <span class="text-[11px] text-slate-400 font-bold block">{{ $t('contacts.period_debit') }}</span>
                    <div class="text-xl font-black font-mono text-rose-400">
                        {{ formatMoney(summary.total_debit) }} <span class="text-xs text-white">{{ $t('common.currency') }}</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 space-y-1">
                    <span class="text-[11px] text-slate-400 font-bold block">{{ $t('contacts.period_credit') }}</span>
                    <div class="text-xl font-black font-mono text-emerald-400">
                        {{ formatMoney(summary.total_credit) }} <span class="text-xs text-white">{{ $t('common.currency') }}</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 space-y-1">
                    <span class="text-[11px] text-slate-400 font-bold block">{{ $t('contacts.closing_balance') }}</span>
                    <div class="text-xl font-black font-mono text-amber-400">
                        {{ formatMoney(summary.current_balance) }} <span class="text-xs text-white">{{ $t('common.currency') }}</span>
                    </div>
                </div>
            </div>

            <!-- Ledger Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">{{ $t('common.date') }}</th>
                                <th class="pb-3">{{ $t('contacts.transaction_type') }}</th>
                                <th class="pb-3">{{ $t('contacts.reference_no') }}</th>
                                <th class="pb-3 font-mono text-rose-400">{{ $t('contacts.period_debit') }}</th>
                                <th class="pb-3 font-mono text-emerald-400">{{ $t('contacts.period_credit') }}</th>
                                <th class="pb-3 font-mono text-amber-400">{{ $t('contacts.closing_balance') }}</th>
                                <th class="pb-3">{{ $t('common.notes') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="(row, idx) in ledger" :key="idx" class="hover:bg-slate-800/30 transition">
                                <td class="py-3 font-mono text-slate-400 text-[11px]">{{ row.date }}</td>

                                <td class="py-3 font-bold font-tajawal">
                                    <span
                                        class="px-2 py-0.5 rounded-lg text-[10.5px] font-bold border"
                                        :class="row.type.includes('فاتورة') ? 'bg-indigo-500/15 text-indigo-400 border-indigo-500/30' : (row.type.includes('قبض') ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/15 text-amber-400 border-amber-500/30')"
                                    >
                                        {{ row.type }}
                                    </span>
                                </td>

                                <td class="py-3 font-mono text-white font-bold">
                                    {{ row.ref_number || '—' }}
                                </td>

                                <td class="py-3 font-mono font-bold text-rose-400">
                                    {{ row.debit > 0 ? formatMoney(row.debit) : '—' }}
                                </td>

                                <td class="py-3 font-mono font-bold text-emerald-400">
                                    {{ row.credit > 0 ? formatMoney(row.credit) : '—' }}
                                </td>

                                <td class="py-3 font-mono font-black text-amber-400 text-sm">
                                    {{ formatMoney(row.balance_after) }} {{ $t('common.currency') }}
                                </td>

                                <td class="py-3 text-slate-400 text-[11px] font-tajawal">
                                    {{ row.notes || '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="ledger.length === 0" class="py-12 text-center text-slate-500 text-xs font-bold font-tajawal">
                        {{ $t('contacts.statement_empty') }}
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
