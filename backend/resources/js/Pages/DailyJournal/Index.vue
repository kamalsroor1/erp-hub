<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DatePicker from '@/Components/DatePicker.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { useMoney } from '@/Composables/useMoney';
import { trans } from '@/helpers/trans';

const props = defineProps({
    date: { type: String, required: true },
    active_shift: { type: Object, default: null },
    summary: { type: Object, required: true },
    invoices: { type: Array, default: () => [] },
    expenses: { type: Array, default: () => [] },
});

const { formatMoney } = useMoney();

const selectedDate = ref(props.date);

watch(selectedDate, (newDate) => {
    if (newDate && newDate !== props.date) {
        router.get('/daily-journal', { date: newDate }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }
});

// Shift Open Modal
const showOpenShiftModal = ref(false);
const openShiftForm = useForm({
    opening_cash_balance: '0.00',
    notes: '',
});

const submitOpenShift = () => {
    openShiftForm.post('/daily-journal/open-shift', {
        preserveScroll: true,
        onSuccess: () => {
            showOpenShiftModal.value = false;
        }
    });
};

// Shift Close Modal (Z-Report)
const showCloseShiftModal = ref(false);
const closeShiftForm = useForm({
    actual_cash_balance: '',
    notes: '',
});

const submitCloseShift = () => {
    if (!props.active_shift) return;
    closeShiftForm.post(`/daily-journal/close-shift/${props.active_shift.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showCloseShiftModal.value = false;
        }
    });
};

// Quick Expense Modal
const showExpenseModal = ref(false);
const expenseForm = useForm({
    title: '',
    amount: '',
    cost_center: 'operational',
    payment_method: 'cash',
    notes: '',
});

const costCenterOptions = computed(() => [
    { id: 'operational', name: `${trans('expenses.cc_operational') || 'مصاريف تشغيلية ونثريات عامة'} ☕` },
    { id: 'salaries', name: `${trans('expenses.cc_salaries') || 'رواتب وعمالة وإكراميات'} 👥` },
    { id: 'utilities', name: `${trans('expenses.cc_utilities') || 'كهرباء ومياه وغاز ومرافق'} ⚡` },
    { id: 'rent', name: `${trans('expenses.cc_rent') || 'إيجارات مقرات وفروع'} 🏬` },
    { id: 'packaging', name: `${trans('expenses.cc_packaging') || 'مطبوعات وكراتين وتعبئة'} 📦` },
    { id: 'hospitality', name: `${trans('expenses.cc_hospitality') || 'ضيافة ونظافة وبوفيه'} 🧹` },
    { id: 'maintenance', name: `${trans('expenses.cc_maintenance') || 'صيانة معدات وديكورات'} 🔧` },
    { id: 'vehicles', name: `${trans('expenses.cc_vehicles') || 'وقود وزيوت وصيانة سيارات'} 🚚` },
    { id: 'shipping', name: `${trans('expenses.cc_shipping') || 'شحن ونولون وتوصيل خارجي'} ✈️` },
]);

const paymentMethodOptions = computed(() => [
    { id: 'cash', name: `${trans('treasury.cash_drawer') || 'نقدي من درج الخزينة'} 💵` },
    { id: 'instapay', name: `${trans('treasury.instapay') || 'إنستاباي'} ⚡` },
    { id: 'wallet', name: `${trans('treasury.e_wallet') || 'محفظة إلكترونية'} 📱` },
    { id: 'bank', name: `${trans('treasury.bank_transfer') || 'حساب بنكي'} 🏦` },
]);

const submitExpense = () => {
    expenseForm.post('/daily-journal/expense', {
        preserveScroll: true,
        onSuccess: () => {
            expenseForm.reset();
            showExpenseModal.value = false;
        }
    });
};

const printJournal = () => {
    window.print();
};
</script>

<template>
    <Head :title="$t('treasury.daily_journal')" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 no-print">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">📒</span>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                            {{ $t('treasury.daily_journal') }}
                        </h1>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-bold">
                        {{ $t('treasury.live_balances') }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Date Picker -->
                    <div class="w-44">
                        <DatePicker v-model="selectedDate" :placeholder="$t('treasury.select_date_placeholder')" />
                    </div>

                    <!-- Add Expense Button -->
                    <button
                        @click="showExpenseModal = true"
                        type="button"
                        class="h-10 px-4 rounded-2xl bg-rose-600/90 hover:bg-rose-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-rose-600/20 transition cursor-pointer"
                    >
                        <span>💸</span>
                        <span>{{ $t('treasury.record_expense_modal') }}</span>
                    </button>

                    <!-- Shift Control Button -->
                    <button
                        v-if="!active_shift"
                        @click="showOpenShiftModal = true"
                        type="button"
                        class="h-10 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-emerald-600/20 transition cursor-pointer"
                    >
                        <span>🟢</span>
                        <span>{{ $t('treasury.open_shift') }}</span>
                    </button>
                    <button
                        v-else
                        @click="showCloseShiftModal = true"
                        type="button"
                        class="h-10 px-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-md shadow-amber-500/20 transition cursor-pointer"
                    >
                        <span>🔒</span>
                        <span>{{ $t('treasury.close_shift') }}</span>
                    </button>

                    <button
                        @click="printJournal"
                        type="button"
                        class="h-10 px-3.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center gap-1 transition cursor-pointer border border-slate-200 dark:border-transparent"
                    >
                        <span>🖨️</span>
                        <span>{{ $t('reports.print_report') }}</span>
                    </button>
                </div>
            </div>

            <!-- Active Shift Card -->
            <div
                class="rounded-3xl p-5 border flex flex-col md:flex-row items-center justify-between gap-4 shadow-xs"
                :class="active_shift ? 'bg-white dark:bg-slate-900 border-emerald-500/30' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800'"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl font-black"
                        :class="active_shift ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500'"
                    >
                        {{ active_shift ? '🟢' : '🔒' }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-slate-900 dark:text-white text-base">
                                {{ active_shift ? `${$t('nav.active_shift')}: #${active_shift.shift_number || active_shift.id}` : $t('treasury.shift_closed_now') }}
                            </span>
                            <span
                                class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                :class="active_shift ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
                            >
                                {{ active_shift ? $t('treasury.shift_active_desc') : $t('treasury.no_active_shift_desc') }}
                            </span>
                        </div>
                        <p v-if="active_shift" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ $t('treasury.cashier_label') }}: <strong class="text-slate-900 dark:text-white">{{ active_shift.user_name }}</strong> | {{ $t('treasury.opened_at_label') }}: <span class="font-mono text-amber-600 dark:text-amber-400">{{ active_shift.opened_at }}</span>
                        </p>
                    </div>
                </div>

                <div v-if="active_shift" class="text-left font-mono">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-tajawal">{{ $t('treasury.opening_cash') }}</span>
                    <div class="text-lg font-black text-emerald-600 dark:text-emerald-400">
                        {{ formatMoney(active_shift.opening_cash_balance) }} <span class="text-xs text-slate-700 dark:text-white">{{ $t('common.currency') }}</span>
                    </div>
                </div>
            </div>

            <!-- Financial Summary Matrix Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Inflow -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-2">
                    <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">{{ $t('treasury.inflow_cash') }}</span>
                    <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400">
                        {{ formatMoney(summary.total_cash_in) }} <span class="text-xs text-slate-700 dark:text-white">{{ $t('common.currency') }}</span>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-bold space-y-0.5 pt-1 border-t border-slate-100 dark:border-slate-800/80">
                        <div>{{ $t('treasury.cash_sales') }}: <span class="font-mono text-slate-900 dark:text-white">{{ formatMoney(summary.cash_sales) }}</span></div>
                        <div>{{ $t('treasury.customer_collections') }}: <span class="font-mono text-slate-900 dark:text-white">{{ formatMoney(summary.customer_payments) }}</span></div>
                    </div>
                </div>

                <!-- Total Outflow -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-2">
                    <span class="text-xs text-rose-600 dark:text-rose-400 font-bold">{{ $t('treasury.outflow_cash') }}</span>
                    <div class="text-2xl font-black font-mono text-rose-600 dark:text-rose-400">
                        {{ formatMoney(summary.total_cash_out) }} <span class="text-xs text-slate-700 dark:text-white">{{ $t('common.currency') }}</span>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-bold space-y-0.5 pt-1 border-t border-slate-100 dark:border-slate-800/80">
                        <div>{{ $t('treasury.supplier_payments') }}: <span class="font-mono text-slate-900 dark:text-white">{{ formatMoney(summary.supplier_payments) }}</span></div>
                        <div>{{ $t('treasury.operating_expenses') }}: <span class="font-mono text-slate-900 dark:text-white">{{ formatMoney(summary.total_expenses) }}</span></div>
                    </div>
                </div>

                <!-- Net Day Cash -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $t('treasury.net_cash_today') }}</span>
                    <div
                        class="text-2xl font-black font-mono"
                        :class="summary.net_cash_today >= 0 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400'"
                    >
                        {{ formatMoney(summary.net_cash_today) }} <span class="text-xs text-slate-700 dark:text-white">{{ $t('common.currency') }}</span>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-bold pt-1 border-t border-slate-100 dark:border-slate-800/80">
                        {{ $t('treasury.recorded_credit_sales') }}: <span class="font-mono text-amber-600 dark:text-amber-300">{{ formatMoney(summary.credit_sales) }} {{ $t('common.currency') }}</span>
                    </div>
                </div>

                <!-- Expected Drawer Balance -->
                <div class="bg-white dark:bg-slate-900 border border-amber-500/30 rounded-3xl p-5 shadow-xs space-y-2 dark:bg-gradient-to-br dark:from-slate-900 dark:to-amber-950/20">
                    <span class="text-xs text-amber-600 dark:text-amber-400 font-black">{{ $t('treasury.expected_in_drawer_now') }}</span>
                    <div class="text-3xl font-black font-mono text-amber-600 dark:text-amber-400">
                        {{ formatMoney(summary.expected_cash_in_drawer) }} <span class="text-xs text-slate-700 dark:text-white">{{ $t('common.currency') }}</span>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-bold pt-1 border-t border-slate-100 dark:border-slate-800/80">
                        {{ $t('treasury.including_opening_cash') }}: <span class="font-mono text-slate-900 dark:text-white">{{ formatMoney(summary.opening_cash_balance) }}</span>
                    </div>
                </div>
            </div>

            <!-- Two Columns: Invoices of the Day & Expenses of the Day -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Invoices Log of Date -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span>🧾</span>
                            <h3 class="font-black text-sm text-slate-900 dark:text-white">{{ $t('treasury.today_invoices') }} ({{ invoices.length }})</h3>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                    <th class="pb-2">{{ $t('invoices.invoice_number') }}</th>
                                    <th class="pb-2">{{ $t('invoices.customer') }}</th>
                                    <th class="pb-2 font-mono">{{ $t('common.total') }}</th>
                                    <th class="pb-2">{{ $t('pos.payment_method') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                                <tr v-for="inv in invoices" :key="inv.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                    <td class="py-2.5 font-mono font-bold">
                                        <Link :href="`/invoices/${inv.id}`" class="text-amber-600 dark:text-amber-400 hover:underline">
                                            {{ inv.invoice_number }}
                                        </Link>
                                    </td>
                                    <td class="py-2.5 text-slate-800 dark:text-slate-200 font-tajawal">{{ inv.customer_name }}</td>
                                    <td class="py-2.5 font-mono font-bold text-slate-900 dark:text-white">{{ formatMoney(inv.net_total) }}</td>
                                    <td class="py-2.5">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-tajawal">
                                            {{ inv.payment_method === 'cash' ? $t('pos.payment_cash') : (inv.payment_method === 'credit' ? $t('pos.payment_credit') : $t('pos.payment_partial')) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="invoices.length === 0" class="py-8 text-center text-slate-400 dark:text-slate-500 text-xs font-bold font-tajawal">
                            {{ $t('treasury.empty_today_invoices') }}
                        </div>
                    </div>
                </div>

                <!-- Expenses Log of Date -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span>💸</span>
                            <h3 class="font-black text-sm text-slate-900 dark:text-white">{{ $t('treasury.today_expenses') }} ({{ expenses.length }})</h3>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                    <th class="pb-2">{{ $t('expenses.expense_item') }}</th>
                                    <th class="pb-2">{{ $t('expenses.cost_center') }}</th>
                                    <th class="pb-2 font-mono">{{ $t('common.amount') }}</th>
                                    <th class="pb-2">{{ $t('pos.payment_method') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                                <tr v-for="exp in expenses" :key="exp.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                    <td class="py-2.5 font-bold text-slate-800 dark:text-slate-200 font-tajawal">{{ exp.title }}</td>
                                    <td class="py-2.5 text-slate-500 dark:text-slate-400 font-tajawal text-[11px]">{{ exp.cost_center_label }}</td>
                                    <td class="py-2.5 font-mono font-bold text-rose-600 dark:text-rose-400">{{ formatMoney(exp.amount) }} {{ $t('common.currency') }}</td>
                                    <td class="py-2.5 text-slate-500 dark:text-slate-400 font-tajawal text-[11px]">{{ exp.payment_method }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="expenses.length === 0" class="py-8 text-center text-slate-400 dark:text-slate-500 text-xs font-bold font-tajawal">
                            {{ $t('treasury.empty_today_expenses') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Open Shift Modal -->
        <div
            v-if="showOpenShiftModal"
            @click="showOpenShiftModal = false"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal"
            dir="rtl"
        >
            <div @click.stop class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-black text-base text-white">{{ $t('treasury.open_shift_modal_title') }}</h3>
                    <button @click="showOpenShiftModal = false" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 text-xs hover:text-white">✕</button>
                </div>

                <form @submit.prevent="submitOpenShift" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('treasury.opening_cash_field') }}</label>
                        <input
                            v-model="openShiftForm.opening_cash_balance"
                            type="number"
                            step="0.01"
                            required
                            placeholder="0.00"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-sm text-emerald-400 font-mono font-black focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('treasury.open_notes_field') }}</label>
                        <input
                            v-model="openShiftForm.notes"
                            type="text"
                            :placeholder="$t('invoices.notes')"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                        <button
                            @click="showOpenShiftModal = false"
                            type="button"
                            class="px-4 py-2.5 rounded-2xl border border-slate-700 text-slate-300 text-xs font-bold hover:bg-slate-800 transition cursor-pointer"
                        >
                            {{ $t('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="openShiftForm.processing"
                            class="px-5 py-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black shadow-lg shadow-emerald-500/20 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            {{ openShiftForm.processing ? $t('common.save') + '...' : $t('treasury.start_shift_btn') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Close Shift Modal (Z-Report) -->
        <div
            v-if="showCloseShiftModal"
            @click="showCloseShiftModal = false"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal"
            dir="rtl"
        >
            <div @click.stop class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="font-black text-base text-white">{{ $t('treasury.close_shift_modal_title') }}</h3>
                        <p class="text-xs text-amber-400 font-mono mt-0.5">{{ active_shift?.shift_number }}</p>
                    </div>
                    <button @click="showCloseShiftModal = false" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 text-xs hover:text-white">✕</button>
                </div>

                <div class="bg-slate-950/90 rounded-2xl p-4 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">{{ $t('treasury.expected_cash_calculated') }}:</span>
                        <span class="font-mono font-black text-amber-400">{{ formatMoney(summary.expected_cash_in_drawer) }} {{ $t('common.currency') }}</span>
                    </div>
                </div>

                <form @submit.prevent="submitCloseShift" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('treasury.actual_cash_field') }}</label>
                        <input
                            v-model="closeShiftForm.actual_cash_balance"
                            type="number"
                            step="0.01"
                            required
                            placeholder="0.00"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-sm text-emerald-400 font-mono font-black focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('treasury.close_notes_field') }}</label>
                        <input
                            v-model="closeShiftForm.notes"
                            type="text"
                            :placeholder="$t('invoices.notes')"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                        <button
                            @click="showCloseShiftModal = false"
                            type="button"
                            class="px-4 py-2.5 rounded-2xl border border-slate-700 text-slate-300 text-xs font-bold hover:bg-slate-800 transition cursor-pointer"
                        >
                            {{ $t('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="closeShiftForm.processing"
                            class="px-5 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black shadow-lg shadow-amber-500/20 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            {{ closeShiftForm.processing ? $t('common.save') + '...' : $t('treasury.confirm_close_shift_btn') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Expense Modal -->
        <div
            v-if="showExpenseModal"
            @click="showExpenseModal = false"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal"
            dir="rtl"
        >
            <div @click.stop class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-black text-base text-white">{{ $t('treasury.record_expense_modal') }}</h3>
                    <button @click="showExpenseModal = false" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 text-xs hover:text-white">✕</button>
                </div>

                <form @submit.prevent="submitExpense" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('treasury.expense_title_field') }}</label>
                        <input
                            v-model="expenseForm.title"
                            type="text"
                            required
                            placeholder="مثال: فاتورة كهرباء / بوفيه ومشروبات..."
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('expenses.amount') }} *</label>
                            <input
                                v-model="expenseForm.amount"
                                type="number"
                                step="0.01"
                                required
                                placeholder="0.00"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-rose-400 font-mono font-black focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('treasury.payment_method_field') }}</label>
                            <SearchableSelect
                                v-model="expenseForm.payment_method"
                                :options="paymentMethodOptions"
                                :placeholder="$t('treasury.payment_method')"
                            />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('treasury.cost_center_field') }}</label>
                        <SearchableSelect
                            v-model="expenseForm.cost_center"
                            :options="costCenterOptions"
                            :placeholder="$t('expenses.cost_center')"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('invoices.notes') }}</label>
                        <input
                            v-model="expenseForm.notes"
                            type="text"
                            :placeholder="$t('invoices.notes')"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                        <button
                            @click="showExpenseModal = false"
                            type="button"
                            class="px-4 py-2.5 rounded-2xl border border-slate-700 text-slate-300 text-xs font-bold hover:bg-slate-800 transition cursor-pointer"
                        >
                            {{ $t('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="expenseForm.processing"
                            class="px-5 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-black shadow-lg shadow-rose-600/20 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            {{ expenseForm.processing ? $t('common.save') + '...' : $t('treasury.record_expense_modal') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>