<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import CustomerPickerSheet from '@/Components/CustomerPickerSheet.vue';
import SupplierPickerSheet from '@/Components/SupplierPickerSheet.vue';

const props = defineProps({
    payments: Array,
    summary: Object,
    customers: Array,
    suppliers: Array,
    filters: Object,
    activeStore: Object,
});

const selectedType = ref(props.filters?.type || 'all');
const showVoucherSheet = ref(false);
const voucherMode = ref('customer'); // 'customer' or 'supplier'

const showCustomerSheet = ref(false);
const showSupplierSheet = ref(false);

const selectedCustomer = ref(null);
const selectedSupplier = ref(null);
const clientError = ref('');

const form = useForm({
    customer_id: '',
    supplier_id: '',
    amount: '',
    payment_method: 'cash',
    notes: '',
});

const openModal = (mode) => {
    voucherMode.value = mode;
    clientError.value = '';
    form.reset();
    form.clearErrors();

    if (mode === 'customer') {
        selectedCustomer.value = props.customers?.[0] || null;
        form.customer_id = selectedCustomer.value?.id || '';
    } else {
        selectedSupplier.value = props.suppliers?.[0] || null;
        form.supplier_id = selectedSupplier.value?.id || '';
    }
    showVoucherSheet.value = true;
};

const onSelectCustomer = (c) => {
    selectedCustomer.value = c;
    form.customer_id = c.id;
    clientError.value = '';
};

const onSelectSupplier = (s) => {
    selectedSupplier.value = s;
    form.supplier_id = s.id;
    clientError.value = '';
};

// Quick shortcut amounts based on active balance
const quickShortcuts = computed(() => {
    const bal = voucherMode.value === 'customer'
        ? Math.max(0, parseFloat(selectedCustomer.value?.current_balance || 0))
        : Math.max(0, parseFloat(selectedSupplier.value?.current_balance || 0));

    const list = [];
    if (bal > 0) {
        list.push({ label: 'تصفية الحساب كله (' + bal.toLocaleString('en-US') + ')', val: bal });
        if (bal >= 200) {
            list.push({ label: 'نص المبلغ (' + (bal / 2).toLocaleString('en-US') + ')', val: bal / 2 });
        }
    }
    [100, 500, 1000, 2000, 5000].forEach(d => {
        if (!list.some(x => x.val === d)) {
            list.push({ label: d + ' ج.م', val: d });
        }
    });
    return list.slice(0, 4);
});

const setAmount = (val) => {
    form.amount = val.toString();
    clientError.value = '';
};

const submitVoucher = () => {
    clientError.value = '';

    if (voucherMode.value === 'customer' && !form.customer_id) {
        clientError.value = '⚠️ يرجى تحديد العميل أولاً';
        return;
    }
    if (voucherMode.value === 'supplier' && !form.supplier_id) {
        clientError.value = '⚠️ يرجى تحديد المورد أولاً';
        return;
    }
    if (!form.amount || parseFloat(form.amount) <= 0) {
        clientError.value = '⚠️ يرجى إدخال مبلغ صحيح أكبر من الصفر';
        return;
    }

    const routeUrl = voucherMode.value === 'customer' 
        ? '/payments/customer-receipt' 
        : '/payments/supplier-voucher';

    form.post(routeUrl, {
        onSuccess: () => {
            showVoucherSheet.value = false;
            form.reset();
            clientError.value = '';
        },
        onError: (errs) => {
            clientError.value = Object.values(errs)[0] || 'فشل حفظ السند';
        }
    });
};

const filterByType = (type) => {
    selectedType.value = type;
    router.get('/payments', { type }, { preserveState: true, replace: true });
};
</script>

<template>
    <MobileLayout>
        <div class="space-y-3.5 pb-10">
            <!-- Header -->
            <div class="flex items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>💰</span>
                        <span>حركة الفلوس (قبض ودفع)</span>
                    </h2>
                    <p class="text-xs text-slate-400 font-bold">تسجيل الفلوس اللي داخلة وخارجة من المحل</p>
                </div>

                <div class="flex items-center gap-1.5">
                    <button
                        @click="openModal('customer')"
                        type="button"
                        class="px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-xs touch-active flex items-center gap-1"
                    >
                        <span>+</span>
                        <span>قبض من زبون</span>
                    </button>
                    <button
                        @click="openModal('supplier')"
                        type="button"
                        class="px-2.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black shadow-xs touch-active flex items-center gap-1"
                    >
                        <span>-</span>
                        <span>دفع لمورد</span>
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 gap-2 text-center text-xs">
                <div class="bg-white dark:bg-slate-900 p-3 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
                    <div class="text-[10px] text-slate-500 font-bold mb-0.5">فلوس جمعناها من الزباين 📥</div>
                    <div class="text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono">
                        {{ Number(summary?.total_collections || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 p-3 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
                    <div class="text-[10px] text-slate-500 font-bold mb-0.5">فلوس دفعناها للموردين 📤</div>
                    <div class="text-sm font-black text-rose-600 dark:text-rose-400 font-mono">
                        {{ Number(summary?.total_disbursements || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="flex items-center gap-1.5 text-xs">
                <button
                    @click="filterByType('all')"
                    type="button"
                    class="px-3 py-1.5 rounded-xl font-bold transition touch-active"
                    :class="selectedType === 'all' ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'"
                >
                    الكل
                </button>
                <button
                    @click="filterByType('customer')"
                    type="button"
                    class="px-3 py-1.5 rounded-xl font-bold transition touch-active"
                    :class="selectedType === 'customer' ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'"
                >
                    فلوس داخلة (قبض) 📥
                </button>
                <button
                    @click="filterByType('supplier')"
                    type="button"
                    class="px-3 py-1.5 rounded-xl font-bold transition touch-active"
                    :class="selectedType === 'supplier' ? 'bg-rose-600 text-white' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'"
                >
                    فلوس خارجة (دفع) 📤
                </button>
            </div>

            <!-- Payments List -->
            <div class="space-y-3">
                <div
                    v-for="p in payments"
                    :key="p.id"
                    class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3 hover:border-emerald-500/50 transition"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-base">{{ p.customer ? '📥' : '📤' }}</span>
                            <span class="text-xs font-black font-mono" :class="p.customer ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                {{ p.customer ? 'سند قبض' : 'سند صرف' }} #{{ p.payment_number }}
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">{{ p.payment_date }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100 dark:border-slate-800">
                        <div>
                            <div class="font-extrabold text-slate-900 dark:text-white">
                                {{ p.customer?.name || p.supplier?.name || 'طرف خارجي' }}
                            </div>
                            <div class="text-[10px] text-slate-400">{{ p.notes || (p.customer ? 'تحصيل نقدية' : 'سداد مستحقات') }}</div>
                        </div>

                        <div class="text-base font-black font-mono" :class="p.customer ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                            {{ p.customer ? '+' : '-' }}{{ Number(p.amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م
                        </div>
                    </div>

                    <!-- Clean Action Row -->
                    <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
                        <Link
                            :href="p.customer ? '/customers/' + p.customer.id + '/statement' : (p.supplier ? '/suppliers/' + p.supplier.id + '/statement' : '#')"
                            class="flex-1 h-9 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-800 dark:text-slate-200 font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 transition touch-active"
                        >
                            <span>📑</span>
                            <span>كشف الحساب والعمليات</span>
                        </Link>

                        <a
                            v-if="p.customer?.phone || p.supplier?.phone"
                            :href="'https://wa.me/2' + (p.customer?.phone || p.supplier?.phone).replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent('☕ تأكيد ' + (p.customer ? 'استلام سند قبض' : 'سند صرف') + ' بقيمة: ' + Number(p.amount).toFixed(2) + ' ج.م لدى سرور كوفي.')"
                            target="_blank"
                            class="w-9 h-9 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl flex items-center justify-center text-xs shadow-xs transition touch-active shrink-0"
                            title="واتساب"
                        >
                            💬
                        </a>
                    </div>
                </div>

                <div v-if="!payments || payments.length === 0" class="text-center py-10 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <div class="text-3xl mb-1">💳</div>
                    <div class="text-xs font-bold text-slate-600 dark:text-slate-300">لا توجد سندات مالية مسجلة</div>
                </div>
            </div>

            <!-- Create Voucher Bottom Sheet -->
            <div
                v-if="showVoucherSheet"
                @click="showVoucherSheet = false"
                class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs flex items-end justify-center select-none"
            >
                <div
                    @click.stop
                    class="w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 shadow-2xl p-4 pb-8 space-y-4 max-h-[90vh] overflow-y-auto animate-slide-up"
                >
                    <!-- Drag Handle -->
                    <div class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto -mt-1 mb-1"></div>

                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">{{ voucherMode === 'customer' ? '📥' : '📤' }}</span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white">
                                    {{ voucherMode === 'customer' ? 'تسجيل سند قبض وتحصيل نقدية' : 'تسجيل سند صرف وسداد مورد' }}
                                </h3>
                                <p class="text-[10px] text-slate-400 font-bold">
                                    {{ voucherMode === 'customer' ? 'تحصيل من حساب العميل وتخفيض مديونيته' : 'سداد لمستحقات المورد وتخفيض حسابه' }}
                                </p>
                            </div>
                        </div>
                        <button @click="showVoucherSheet = false" type="button" class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-xs">✕</button>
                    </div>

                    <!-- Error Alert -->
                    <div v-if="clientError || form.errors?.amount || form.errors?.customer_id" class="p-3 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-500 text-xs font-bold animate-shake">
                        {{ clientError || form.errors?.amount || form.errors?.customer_id }}
                    </div>

                    <form @submit.prevent="submitVoucher" class="space-y-3.5">
                        <!-- Select Customer or Supplier with Touch Sheet Trigger -->
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 mb-1">
                                {{ voucherMode === 'customer' ? 'العميل المستلم منه:' : 'المورد المسدد له:' }} <span class="text-rose-500">*</span>
                            </label>

                            <!-- Customer Touch Pill -->
                            <button
                                v-if="voucherMode === 'customer'"
                                @click="showCustomerSheet = true"
                                type="button"
                                class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border transition flex items-center justify-between text-start touch-active"
                                :class="!selectedCustomer ? 'border-rose-500/50 ring-1 ring-rose-500/30' : 'border-slate-200 dark:border-slate-700'"
                            >
                                <div class="flex items-center gap-2.5 truncate">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-500 flex items-center justify-center font-black text-xs shrink-0">
                                        👤
                                    </div>
                                    <div class="truncate">
                                        <div class="text-xs font-black text-slate-900 dark:text-white truncate">
                                            {{ selectedCustomer?.name || 'اضغط لاختيار العميل...' }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ selectedCustomer?.phone || 'بدون هاتف' }}</div>
                                    </div>
                                </div>

                                <div class="text-end shrink-0">
                                    <div class="text-xs font-black font-mono text-rose-500">
                                        {{ Number(selectedCustomer?.current_balance || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م
                                    </div>
                                    <div class="text-[9px] text-slate-400 font-bold">المديونية الحالية ▾</div>
                                </div>
                            </button>

                            <!-- Supplier Touch Pill -->
                            <button
                                v-else
                                @click="showSupplierSheet = true"
                                type="button"
                                class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border transition flex items-center justify-between text-start touch-active"
                                :class="!selectedSupplier ? 'border-rose-500/50 ring-1 ring-rose-500/30' : 'border-slate-200 dark:border-slate-700'"
                            >
                                <div class="flex items-center gap-2.5 truncate">
                                    <div class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-500 flex items-center justify-center font-black text-xs shrink-0">
                                        🏭
                                    </div>
                                    <div class="truncate">
                                        <div class="text-xs font-black text-slate-900 dark:text-white truncate">
                                            {{ selectedSupplier?.name || 'اضغط لاختيار المورد...' }}
                                        </div>
                                        <div class="text-[10px] text-amber-500 font-bold truncate">{{ selectedSupplier?.company_name || selectedSupplier?.phone }}</div>
                                    </div>
                                </div>

                                <div class="text-end shrink-0">
                                    <div class="text-xs font-black font-mono text-amber-500">
                                        {{ Number(selectedSupplier?.current_balance || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م
                                    </div>
                                    <div class="text-[9px] text-slate-400 font-bold">المستحق له ▾</div>
                                </div>
                            </button>
                        </div>

                        <!-- Amount with Quick Preset Badges -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-extrabold text-slate-500">
                                    المبلغ المحصل / المسدد (ج.م): <span class="text-rose-500">*</span>
                                </label>
                                <span v-if="!form.amount" class="text-[10px] text-rose-500 font-bold">مطلوب</span>
                            </div>

                            <input
                                v-model="form.amount"
                                type="number"
                                step="0.001"
                                placeholder="0.00"
                                required
                                class="w-full h-12 bg-slate-50 dark:bg-slate-800 border rounded-2xl px-4 text-sm font-mono font-black text-slate-900 dark:text-white outline-none transition"
                                :class="clientError && (!form.amount || parseFloat(form.amount) <= 0) ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-emerald-500'"
                            >

                            <!-- Quick Preset Shortcuts -->
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                <button
                                    v-for="sc in quickShortcuts"
                                    :key="sc.label"
                                    @click="setAmount(sc.val)"
                                    type="button"
                                    class="px-2.5 py-1 rounded-xl text-[11px] font-mono font-black border transition touch-active"
                                    :class="form.amount == sc.val.toString() ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200'"
                                >
                                    {{ sc.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 mb-1">ملاحظات / البيان:</label>
                            <input
                                v-model="form.notes"
                                type="text"
                                placeholder="مثال: دفعة نقدية تحت الحساب أو تحويل بنكي..."
                                class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-3.5 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-emerald-500"
                            >
                        </div>

                        <!-- Submit Button (Always clickable with active feedback) -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-3.5 rounded-2xl font-black text-xs text-white shadow-lg transition touch-active flex items-center justify-center gap-2 cursor-pointer"
                            :class="voucherMode === 'customer' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/30' : 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/30'"
                        >
                            <span>{{ form.processing ? 'جاري الترحيل...' : 'حفظ السند والترحيل الفوري للحسابات ✓' }}</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Customer Picker Sheet (Layered at top with z-[70]) -->
            <CustomerPickerSheet
                :isOpen="showCustomerSheet"
                :customers="customers"
                :selectedId="selectedCustomer?.id"
                @close="showCustomerSheet = false"
                @select="onSelectCustomer"
                @created="(c) => { customers?.push(c); onSelectCustomer(c); }"
            />

            <!-- Supplier Picker Sheet (Layered at top with z-[70]) -->
            <SupplierPickerSheet
                :isOpen="showSupplierSheet"
                :suppliers="suppliers"
                :selectedId="selectedSupplier?.id"
                @close="showSupplierSheet = false"
                @select="onSelectSupplier"
                @created="(s) => { suppliers?.push(s); onSelectSupplier(s); }"
            />
        </div>
    </MobileLayout>
</template>
