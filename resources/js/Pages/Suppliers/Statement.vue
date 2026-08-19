<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    supplier: { type: Object, required: true },
    ledger: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const { formatMoney } = useMoney();

const dateFrom = ref(props.filters.from || '');
const dateTo = ref(props.filters.to || '');

const applyFilters = () => {
    router.get(`/suppliers/${props.supplier.id}/statement`, {
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

// Quick Payment Modal
const showPaymentModal = ref(false);
const paymentForm = useForm({
    amount: props.supplier.current_balance > 0 ? props.supplier.current_balance : '',
    payment_method: 'cash',
    payment_date: new Date().toISOString().split('T')[0],
    notes: 'سداد دفعة نقدية للمورد من كشف الحساب',
});

const savePayment = () => {
    paymentForm.post(`/suppliers/${props.supplier.id}/pay`, {
        preserveScroll: true,
        onSuccess: () => {
            showPaymentModal.value = false;
        }
    });
};
</script>

<template>
    <Head :title="`كشف حساب المورد: ${supplier.name}`" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header (Hidden on print) -->
            <div class="print:hidden flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link href="/suppliers" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                            →
                        </Link>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                                <span>كشف حساب المورد:</span>
                                <span class="text-amber-400">{{ supplier.name }}</span>
                                <span v-if="supplier.company_name" class="text-xs text-slate-400 font-normal">({{ supplier.company_name }})</span>
                            </h1>
                            <p class="text-xs text-slate-400 font-bold mt-0.5">
                                هاتف: {{ supplier.phone || 'غير مسجل' }} | العنوان: {{ supplier.address || 'غير محدد' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <button
                        @click="showPaymentModal = true"
                        type="button"
                        class="h-11 px-5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-600/30 transition transform active:scale-95 cursor-pointer"
                    >
                        <span>💸</span>
                        <span>سند صرف وسداد للمورد</span>
                    </button>

                    <button
                        @click="printStatement"
                        type="button"
                        class="h-11 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold flex items-center gap-2 transition cursor-pointer"
                    >
                        <span>🖨️</span>
                        <span>طباعة كشف الحساب (A4)</span>
                    </button>
                </div>
            </div>

            <!-- Printable Header (Visible only on print) -->
            <div class="hidden print:block text-center border-b pb-4 mb-6">
                <h1 class="text-2xl font-black">سرور كوفي - كشف حساب مورد تفصيلي</h1>
                <div class="text-sm font-bold mt-1">المورد: {{ supplier.name }} {{ supplier.company_name ? '(' + supplier.company_name + ')' : '' }}</div>
                <div class="text-xs text-gray-600">الفترة من: {{ filters.from }} إلى: {{ filters.to }}</div>
            </div>

            <!-- Financial Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">إجمالي المشتريات والتوريدات (دائن)</span>
                    <div class="text-2xl font-black font-mono text-white">
                        {{ formatMoney(summary.total_purchases) }} <span class="text-xs text-amber-400">ج.م</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">إجمالي المدفوعات وسندات الصرف (مدين)</span>
                    <div class="text-2xl font-black font-mono text-emerald-400">
                        {{ formatMoney(summary.total_payments) }} <span class="text-xs text-white">ج.م</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">الرصيد المتبقي مستحق للمورد (صافي المديونية)</span>
                    <div class="text-2xl font-black font-mono text-rose-400">
                        {{ formatMoney(summary.net_balance) }} <span class="text-xs text-white">ج.م</span>
                    </div>
                </div>
            </div>

            <!-- Filter Controls (Hidden on print) -->
            <div class="print:hidden bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-36">
                        <DatePicker v-model="dateFrom" placeholder="من تاريخ..." />
                    </div>
                    <span class="text-slate-500 text-xs">←</span>
                    <div class="w-36">
                        <DatePicker v-model="dateTo" placeholder="إلى تاريخ..." />
                    </div>
                    <button
                        @click="applyFilters"
                        type="button"
                        class="h-10 px-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition cursor-pointer"
                    >
                        تصفية التاريخ
                    </button>
                </div>
            </div>

            <!-- Ledger Statement Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">التاريخ</th>
                                <th class="pb-3">البيان والحركة</th>
                                <th class="pb-3 font-mono">مدين (سداد للمورد)</th>
                                <th class="pb-3 font-mono">دائن (شراء بضاعة)</th>
                                <th class="pb-3 font-mono">الرصيد التراكمي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="(row, rIdx) in ledger" :key="rIdx" class="hover:bg-slate-800/30 transition">
                                <td class="py-3.5 font-mono text-slate-400 text-[11px]">
                                    {{ row.date }}
                                </td>

                                <td class="py-3.5 font-tajawal font-bold text-white">
                                    <span v-if="row.type === 'payment'" class="text-emerald-400 ml-1">💸</span>
                                    <span v-else class="text-amber-400 ml-1">📦</span>
                                    {{ row.description }}
                                </td>

                                <td class="py-3.5 font-mono font-bold text-emerald-400">
                                    {{ row.debit > 0 ? formatMoney(row.debit) + ' ج.م' : '—' }}
                                </td>

                                <td class="py-3.5 font-mono font-bold text-white">
                                    {{ row.credit > 0 ? formatMoney(row.credit) + ' ج.م' : '—' }}
                                </td>

                                <td class="py-3.5 font-mono font-black text-rose-400 text-sm">
                                    {{ formatMoney(row.running_balance) }} ج.م
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="ledger.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">📜</span>
                        <p class="text-xs font-bold text-slate-400 font-tajawal">لا توجد حركات مسجلة لهذا المورد في الفترة المحددة</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Payment Modal -->
        <div
            v-if="showPaymentModal"
            @click="showPaymentModal = false"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal"
            dir="rtl"
        >
            <div @click.stop class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-black text-base text-white">تسجيل سند صرف وسداد للمورد 💸</h3>
                    <button @click="showPaymentModal = false" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 text-xs hover:text-white">✕</button>
                </div>

                <form @submit.prevent="savePayment" class="space-y-4">
                    <div class="p-3 bg-slate-950/80 rounded-2xl border border-slate-800 flex items-center justify-between">
                        <span class="text-xs text-slate-400 font-bold">المديونية المستحقة حالياً:</span>
                        <span class="font-mono font-black text-rose-400 text-base">{{ formatMoney(supplier.current_balance) }} ج.م</span>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">المبلغ المدفوع (ج.م) *</label>
                        <input
                            v-model.number="paymentForm.amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            required
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs font-mono font-black text-emerald-400 focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">طريقة السداد *</label>
                            <select
                                v-model="paymentForm.payment_method"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                            >
                                <option value="cash">نقداً كاش 💵</option>
                                <option value="instapay">انستاباي ⚡</option>
                                <option value="wallet">محفظة إلكترونية 📱</option>
                                <option value="bank">تحويل بنكي 🏦</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">تاريخ السند *</label>
                            <DatePicker v-model="paymentForm.payment_date" placeholder="تاريخ السند..." />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">ملاحظات السند</label>
                        <input
                            v-model="paymentForm.notes"
                            type="text"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                        <button
                            @click="showPaymentModal = false"
                            type="button"
                            class="px-4 py-2.5 rounded-2xl border border-slate-700 text-slate-300 text-xs font-bold hover:bg-slate-800 transition cursor-pointer"
                        >
                            إلغاء
                        </button>
                        <button
                            type="submit"
                            :disabled="paymentForm.processing"
                            class="px-5 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black shadow-lg shadow-amber-500/20 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            {{ paymentForm.processing ? 'جاري القيد...' : 'اعتماد سند الصرف' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>