<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    invoice: { type: Object, required: true },
    customers: { type: Array, default: () => [] },
    items_catalog: { type: Array, default: () => [] },
});

const { formatMoney } = useMoney();

const form = useForm({
    customer_id: props.invoice.customer_id,
    invoice_date: props.invoice.invoice_date,
    payment_type: props.invoice.payment_type,
    discount_type: props.invoice.discount_type || 'fixed',
    discount_value: props.invoice.discount_value || 0,
    paid_amount: props.invoice.paid_amount || 0,
    shipping_cost: 0,
    notes: props.invoice.notes || '',
    items: props.invoice.items.map(item => ({ ...item })),
    additional_expenses: props.invoice.additional_expenses ? [...props.invoice.additional_expenses] : [],
});

const customerOptions = props.customers.map(c => ({
    id: c.id,
    name: `${c.name} (${c.phone || 'بدون هاتف'})`
}));

const itemCatalogOptions = props.items_catalog.map(i => ({
    id: i.id,
    name: `${i.name} [${i.code || '—'}] - ${i.selling_price} ج.م (مخزون: ${i.current_stock})`,
    raw: i,
}));

const selectedItemId = ref(null);

const addItemToInvoice = () => {
    if (!selectedItemId.value) return;
    const selectedOption = itemCatalogOptions.find(o => o.id === selectedItemId.value);
    if (!selectedOption) return;

    const rawItem = selectedOption.raw;
    const existing = form.items.find(i => i.item_id === rawItem.id);
    if (existing) {
        existing.quantity += 1;
        calculateLineTotal(existing);
    } else {
        const newLine = {
            item_id: rawItem.id,
            name: rawItem.name,
            code: rawItem.code,
            unit: rawItem.unit,
            current_stock: rawItem.current_stock,
            quantity: 1,
            unit_price: rawItem.selling_price,
            discount_amount: 0,
            total_price: rawItem.selling_price,
        };
        form.items.push(newLine);
    }
    selectedItemId.value = null;
};

const calculateLineTotal = (item) => {
    const gross = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
    const disc = parseFloat(item.discount_amount) || 0;
    item.total_price = Math.max(0, gross - disc);
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

// Expenses
const addExpenseRow = () => {
    form.additional_expenses.push({ title: '', amount: 0 });
};

const removeExpenseRow = (idx) => {
    form.additional_expenses.splice(idx, 1);
};

// Computed Totals
const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (parseFloat(item.total_price) || 0), 0);
});

const discountTotal = computed(() => {
    if (form.discount_type === 'percentage') {
        return (subtotal.value * (parseFloat(form.discount_value) || 0)) / 100;
    }
    return parseFloat(form.discount_value) || 0;
});

const expensesTotal = computed(() => {
    return form.additional_expenses.reduce((sum, exp) => sum + (parseFloat(exp.amount) || 0), 0);
});

const netTotal = computed(() => {
    return Math.max(0, subtotal.value - discountTotal.value + expensesTotal.value + (parseFloat(form.shipping_cost) || 0));
});

const remainingAmount = computed(() => {
    return Math.max(0, netTotal.value - (parseFloat(form.paid_amount) || 0));
});

const submitUpdate = () => {
    form.put(`/invoices/${props.invoice.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`تعديل فاتورة مبيعات: ${invoice.invoice_number}`" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link :href="`/invoices/${invoice.id}`" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                            →
                        </Link>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                                <span>تعديل فاتورة مبيعات رقم:</span>
                                <span class="text-amber-400 font-mono">{{ invoice.invoice_number }}</span>
                            </h1>
                            <p class="text-xs text-slate-400 font-bold mt-0.5">
                                تعديل الأصناف، الأسعار، العميل، والمدفوعات مع إعادة تسوية المخزون فورياً
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitUpdate" class="space-y-6">
                <!-- Top Details Grid -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">العميل *</label>
                        <SearchableSelect
                            v-model="form.customer_id"
                            :options="customerOptions"
                            placeholder="اختر العميل..."
                        />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">تاريخ الفاتورة *</label>
                        <DatePicker v-model="form.invoice_date" placeholder="تاريخ الفاتورة..." />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">نوع التحصيل / الدفع *</label>
                        <select
                            v-model="form.payment_type"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        >
                            <option value="cash">نقداً (كاش بالكامل) 💵</option>
                            <option value="credit">آجل (على الحساب بالكامل) ⏳</option>
                            <option value="partial">دفع جزئي (مقدم + آجل) ⚖️</option>
                        </select>
                    </div>
                </div>

                <!-- Add Items Section -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-b border-slate-800 pb-3">
                        <h2 class="text-sm font-black text-white flex items-center gap-2">
                            <span>📦</span>
                            <span>بنود وأصناف الفاتورة</span>
                        </h2>

                        <div class="w-full sm:w-96 flex items-center gap-2">
                            <SearchableSelect
                                v-model="selectedItemId"
                                :options="itemCatalogOptions"
                                placeholder="🔍 اختر صنفاً لإضافته..."
                            />
                            <button
                                @click="addItemToInvoice"
                                type="button"
                                class="h-10 px-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black shrink-0 transition cursor-pointer"
                            >
                                + إضافة
                            </button>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                    <th class="pb-3">الصنف</th>
                                    <th class="pb-3 w-28">الكمية</th>
                                    <th class="pb-3 w-28">السعر (ج.م)</th>
                                    <th class="pb-3 w-28">الخصم (ج.م)</th>
                                    <th class="pb-3 w-28 font-mono">الإجمالي</th>
                                    <th class="pb-3 text-center w-12">حذف</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 font-sans">
                                <tr v-for="(item, idx) in form.items" :key="idx" class="hover:bg-slate-800/30 transition">
                                    <td class="py-3 font-tajawal">
                                        <div class="font-bold text-white">{{ item.name }}</div>
                                        <div class="text-[10px] text-slate-500 font-mono">{{ item.code || '—' }} ({{ item.unit }})</div>
                                    </td>

                                    <td class="py-3">
                                        <input
                                            v-model.number="item.quantity"
                                            @input="calculateLineTotal(item)"
                                            type="number"
                                            step="0.001"
                                            min="0.001"
                                            class="w-full px-2.5 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-mono font-bold text-white text-center focus:border-amber-500 focus:outline-none"
                                        >
                                    </td>

                                    <td class="py-3">
                                        <input
                                            v-model.number="item.unit_price"
                                            @input="calculateLineTotal(item)"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="w-full px-2.5 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-mono font-bold text-white text-center focus:border-amber-500 focus:outline-none"
                                        >
                                    </td>

                                    <td class="py-3">
                                        <input
                                            v-model.number="item.discount_amount"
                                            @input="calculateLineTotal(item)"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="w-full px-2.5 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-mono text-rose-400 text-center focus:border-amber-500 focus:outline-none"
                                        >
                                    </td>

                                    <td class="py-3 font-mono font-black text-amber-400">
                                        {{ formatMoney(item.total_price) }} ج.م
                                    </td>

                                    <td class="py-3 text-center">
                                        <button
                                            @click="removeItem(idx)"
                                            type="button"
                                            class="p-1 rounded-lg text-rose-400 hover:bg-rose-500/20 transition cursor-pointer"
                                        >
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="form.items.length === 0" class="py-10 text-center text-slate-500 text-xs font-bold">
                            ⚠️ لا توجد أصناف مضافة للفاتورة بعد
                        </div>
                    </div>
                </div>

                <!-- Financial Summary & Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left: Notes & Discounts -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                        <h2 class="text-xs font-black text-white border-b border-slate-800 pb-2 flex items-center gap-2">
                            <span>🏷️</span>
                            <span>الخصم والمصاريف الإضافية</span>
                        </h2>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-300">نوع الخصم</label>
                                <select
                                    v-model="form.discount_type"
                                    class="w-full px-3 py-2 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                                >
                                    <option value="fixed">مبلغ ثابت (ج.م)</option>
                                    <option value="percentage">نسبة مئوية (%)</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-300">قيمة الخصم</label>
                                <input
                                    v-model.number="form.discount_value"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 rounded-2xl bg-slate-950 border border-slate-800 text-xs font-mono font-bold text-rose-400 focus:border-amber-500 focus:outline-none"
                                >
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-300">ملاحظات الفاتورة</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="أي ملاحظات خاصة بالفاتورة..."
                                class="w-full px-3 py-2 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Right: Calculations & Save -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                        <h2 class="text-xs font-black text-white border-b border-slate-800 pb-2 flex items-center gap-2">
                            <span>💰</span>
                            <span>الحسابات النهائية والاعتماد</span>
                        </h2>

                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between text-slate-400">
                                <span>إجمالي البنود (قبل الخصم):</span>
                                <span class="font-mono text-white">{{ formatMoney(subtotal) }} ج.م</span>
                            </div>

                            <div class="flex justify-between text-slate-400">
                                <span>قيمة الخصم:</span>
                                <span class="font-mono text-rose-400">- {{ formatMoney(discountTotal) }} ج.م</span>
                            </div>

                            <div class="flex justify-between pt-2 border-t border-slate-800 text-sm font-black text-white">
                                <span>صافي الفاتورة النهائي:</span>
                                <span class="font-mono text-amber-400 text-base">{{ formatMoney(netTotal) }} ج.م</span>
                            </div>

                            <div v-if="form.payment_type !== 'credit'" class="pt-2">
                                <label class="text-[11px] font-bold text-slate-300 block mb-1">المبلغ المسدد والمدفوع (ج.م)</label>
                                <input
                                    v-model.number="form.paid_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 rounded-2xl bg-slate-950 border border-slate-800 text-xs font-mono font-bold text-emerald-400 focus:border-amber-500 focus:outline-none"
                                >
                            </div>

                            <div class="flex justify-between text-slate-400 pt-1">
                                <span>المتبقي آجل على العميل:</span>
                                <span class="font-mono text-rose-400 font-bold">{{ formatMoney(remainingAmount) }} ج.م</span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                            <Link
                                :href="`/invoices/${invoice.id}`"
                                class="px-5 py-2.5 rounded-2xl border border-slate-700 hover:bg-slate-800 text-slate-300 text-xs font-bold transition"
                            >
                                إلغاء
                            </Link>

                            <button
                                type="submit"
                                :disabled="form.processing || form.items.length === 0"
                                class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/25 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                            >
                                {{ form.processing ? 'جاري التعديل...' : 'حفظ وتحديث الفاتورة 💾' }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>