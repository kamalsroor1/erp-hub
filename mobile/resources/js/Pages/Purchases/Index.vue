<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    purchases: { type: Array, default: () => [] },
    total_purchases: { type: [Number, String], default: 0 },
    total_remaining: { type: [Number, String], default: 0 },
    total_count: { type: Number, default: 0 },
    suppliers: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const selectedSupplier = ref(props.filters.supplier_id || 'all');

const applySupplier = (supId) => {
    haptic.light();
    selectedSupplier.value = supId;
    router.get('/purchases', {
        search: search.value,
        supplier_id: supId,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const applySearch = () => {
    router.get('/purchases', {
        search: search.value,
        supplier_id: selectedSupplier.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Create Purchase Modal State
const showCreateModal = ref(false);

const form = useForm({
    supplier_id: props.suppliers[0]?.id || '',
    purchase_date: new Date().toISOString().split('T')[0],
    supplier_invoice_ref: '',
    discount_amount: '0.00',
    paid_amount: '0.00',
    notes: '',
    items: [
        {
            item_id: props.items[0]?.id || '',
            quantity: 10,
            cost_price: props.items[0]?.cost_price || 0,
        }
    ],
});

const openCreateModal = () => {
    haptic.medium();
    form.reset();
    form.supplier_id = props.suppliers[0]?.id || '';
    form.purchase_date = new Date().toISOString().split('T')[0];
    form.items = [
        {
            item_id: props.items[0]?.id || '',
            quantity: 10,
            cost_price: Number(props.items[0]?.cost_price || 0),
        }
    ];
    showCreateModal.value = true;
};

const addItemLine = () => {
    haptic.light();
    form.items.push({
        item_id: props.items[0]?.id || '',
        quantity: 10,
        cost_price: Number(props.items[0]?.cost_price || 0),
    });
};

const removeItemLine = (idx) => {
    haptic.medium();
    if (form.items.length > 1) {
        form.items.splice(idx, 1);
    }
};

const onItemSelectChange = (idx) => {
    const selectedItem = props.items.find(i => i.id === form.items[idx].item_id);
    if (selectedItem) {
        form.items[idx].cost_price = Number(selectedItem.cost_price || 0);
    }
};

// Subtotal & Calculations
const formSubtotal = computed(() => {
    return form.items.reduce((acc, it) => {
        const qty = Number(it.quantity) || 0;
        const cost = Number(it.cost_price) || 0;
        return acc + (qty * cost);
    }, 0);
});

const formNetTotal = computed(() => {
    const disc = Number(form.discount_amount) || 0;
    return Math.max(0, formSubtotal.value - disc);
});

const formRemaining = computed(() => {
    const paid = Number(form.paid_amount) || 0;
    return Math.max(0, formNetTotal.value - paid);
});

const setPayAll = () => {
    haptic.light();
    form.paid_amount = Number(formNetTotal.value).toFixed(2);
};

const submitPurchase = () => {
    haptic.success();
    form.post('/purchases', {
        onSuccess: () => {
            showCreateModal.value = false;
        }
    });
};

// Action Sheet for Purchase (⋯)
const activeActionPurchase = ref(null);

const openActionSheet = (p) => {
    haptic.light();
    activeActionPurchase.value = p;
};

const cancelPurchase = (p) => {
    haptic.warning();
    if (confirm(`هل أنت متأكد من إلغاء فاتورة المشتريات #${p.purchase_number} وعكس أثرها في المخزن؟`)) {
        activeActionPurchase.value = null;
        router.post(`/purchases/${p.id}/cancel`);
    }
};
</script>

<template>
    <MobileLayout>
        <div class="space-y-4 pb-24 select-none">
            <!-- Header Banner & Create Button -->
            <div class="bg-gradient-to-l from-amber-600 to-amber-700 rounded-3xl p-4 text-white shadow-lg shadow-amber-600/20 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">📦</span>
                        <h2 class="text-base font-black">فواتير المشتريات وتوريد البن</h2>
                    </div>
                    <p class="text-[11px] text-amber-100 font-bold mt-0.5">
                        استلام شحنات خامات البن من الموردين وزيادة رصيد المخزن
                    </p>
                </div>
                <button
                    @click="openCreateModal"
                    type="button"
                    class="h-10 px-3.5 bg-white text-amber-700 hover:bg-amber-50 font-black text-xs rounded-2xl shadow-md flex items-center gap-1.5 transition touch-active shrink-0"
                >
                    <span>➕</span>
                    <span>فاتورة شراء</span>
                </button>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-1">
                    <div class="text-[10px] text-slate-400 font-bold">إجمالي المشتريات</div>
                    <div class="text-lg font-black font-mono text-emerald-600 dark:text-emerald-400">
                        {{ Number(total_purchases).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} <span class="text-[10px] font-sans">ج.م</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-1">
                    <div class="text-[10px] text-slate-400 font-bold">المتبقي (آجل للموردين)</div>
                    <div class="text-lg font-black font-mono text-amber-600 dark:text-amber-400">
                        {{ Number(total_remaining).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} <span class="text-[10px] font-sans">ج.م</span>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative">
                <input
                    v-model="search"
                    @keyup.enter="applySearch"
                    type="text"
                    placeholder="بحث برقم الفاتورة، المورد، أو رقم إذن التوريد..."
                    class="w-full h-11 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl pe-10 ps-4 text-xs font-bold text-slate-900 dark:text-white shadow-xs focus:border-amber-500"
                />
                <button @click="applySearch" type="button" class="absolute left-3 top-2.5 text-slate-400 text-sm">
                    🔍
                </button>
            </div>

            <!-- Purchases List -->
            <div class="space-y-3">
                <div
                    v-for="p in purchases"
                    :key="p.id"
                    class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3 hover:border-amber-500/50 transition"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black font-mono text-amber-600 dark:text-amber-400">
                                #{{ p.purchase_number }}
                            </span>
                            <span
                                class="px-2 py-0.5 rounded-lg text-[10px] font-black"
                                :class="p.status === 'cancelled' ? 'bg-rose-500/10 text-rose-500 border border-rose-500/20' : (p.remaining_amount == 0 ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500')"
                            >
                                {{ p.status === 'cancelled' ? 'ملغاة 🚫' : (p.remaining_amount == 0 ? 'مسددة بالكامل ✓' : 'متبقي آجل ⏳') }}
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">{{ p.purchase_date }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100 dark:border-slate-800">
                        <div>
                            <div class="font-extrabold text-slate-900 dark:text-white">
                                {{ p.supplier?.name || 'مورد غير محدد' }}
                            </div>
                            <div v-if="p.supplier_invoice_ref" class="text-[10px] text-slate-400 font-mono mt-0.5">
                                إذن توريد: {{ p.supplier_invoice_ref }}
                            </div>
                        </div>

                        <div class="text-end">
                            <div class="text-base font-black font-mono text-slate-900 dark:text-white">
                                {{ Number(p.net_total).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م
                            </div>
                            <div v-if="p.remaining_amount > 0" class="text-[10px] text-amber-600 dark:text-amber-400 font-bold font-mono">
                                باقي: {{ Number(p.remaining_amount).toFixed(2) }} ج.م
                            </div>
                        </div>
                    </div>

                    <!-- Clean Action Row -->
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
                        <Link
                            :href="'/purchases/' + p.id"
                            class="flex-1 h-9 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-800 dark:text-slate-200 font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 transition touch-active"
                        >
                            <span>👁️</span>
                            <span>تفاصيل وأصناف الفاتورة</span>
                        </Link>

                        <button
                            @click="openActionSheet(p)"
                            type="button"
                            class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 font-black text-xs flex items-center justify-center transition touch-active shrink-0"
                            title="خيارات"
                        >
                            ⋯
                        </button>
                    </div>
                </div>

                <div v-if="!purchases || purchases.length === 0" class="text-center py-10 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <div class="text-3xl mb-1">📦</div>
                    <div class="text-xs font-bold text-slate-600 dark:text-slate-300">لا توجد فواتير مشتريات مسجلة</div>
                </div>
            </div>

            <!-- CREATE INBOUND PURCHASE BOTTOM SHEET -->
            <div
                v-if="showCreateModal"
                @click="showCreateModal = false"
                class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-end justify-center select-none"
            >
                <div
                    @click.stop
                    class="w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 shadow-2xl p-5 pb-8 space-y-4 max-h-[90vh] overflow-y-auto animate-slide-up"
                >
                    <div class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto -mt-1 mb-1"></div>

                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">📦</span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white">
                                    تسجيل فاتورة شراء وتوريد خامات
                                </h3>
                                <p class="text-[10px] text-slate-400 font-bold">
                                    إضافة البضاعة فوراً للمخزن وتحديث حساب المورد
                                </p>
                            </div>
                        </div>
                        <button @click="showCreateModal = false" type="button" class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-xs">✕</button>
                    </div>

                    <form @submit.prevent="submitPurchase" class="space-y-4">
                        <!-- Supplier Picker -->
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 mb-1">
                                المورد المورد للشحنة: <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.supplier_id"
                                required
                                class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white"
                            >
                                <option v-for="s in suppliers" :key="s.id" :value="s.id">
                                    {{ s.name }} ({{ s.phone || 'بدون هاتف' }})
                                </option>
                            </select>
                        </div>

                        <!-- Date & Supplier Ref -->
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-500 mb-1">تاريخ الفاتورة:</label>
                                <input
                                    v-model="form.purchase_date"
                                    type="date"
                                    required
                                    class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-mono font-bold text-slate-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-500 mb-1">رقم إذن التوريد / المورد:</label>
                                <input
                                    v-model="form.supplier_invoice_ref"
                                    type="text"
                                    placeholder="مثال: INV-9902"
                                    class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white"
                                />
                            </div>
                        </div>

                        <!-- Items Table Header -->
                        <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-black text-slate-900 dark:text-white">الأصناف والخامات الموردة:</label>
                                <button
                                    @click="addItemLine"
                                    type="button"
                                    class="px-2.5 py-1 bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-xl text-[11px] font-black border border-amber-500/20"
                                >
                                    ➕ إضافة صنف آخر
                                </button>
                            </div>

                            <!-- Item Rows -->
                            <div class="space-y-2.5">
                                <div
                                    v-for="(line, idx) in form.items"
                                    :key="idx"
                                    class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-2"
                                >
                                    <div class="flex items-center justify-between gap-2">
                                        <select
                                            v-model="line.item_id"
                                            @change="onItemSelectChange(idx)"
                                            class="flex-1 h-9 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-2.5 text-xs font-bold text-slate-900 dark:text-white"
                                        >
                                            <option v-for="it in items" :key="it.id" :value="it.id">
                                                {{ it.name }} (كود: {{ it.code }})
                                            </option>
                                        </select>
                                        <button
                                            v-if="form.items.length > 1"
                                            @click="removeItemLine(idx)"
                                            type="button"
                                            class="w-7 h-7 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center text-xs font-bold"
                                        >
                                            ✕
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] text-slate-400 font-bold mb-0.5">الوزن / الكمية (كجم):</label>
                                            <input
                                                v-model="line.quantity"
                                                type="number"
                                                step="0.05"
                                                min="0.001"
                                                required
                                                class="w-full h-9 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-2 text-center font-mono font-black text-xs text-slate-900 dark:text-white"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-[10px] text-slate-400 font-bold mb-0.5">سعر الكيلو تكلفة (ج.م):</label>
                                            <input
                                                v-model="line.cost_price"
                                                type="number"
                                                step="0.5"
                                                min="0"
                                                required
                                                class="w-full h-9 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-2 text-center font-mono font-black text-xs text-slate-900 dark:text-white"
                                            />
                                        </div>
                                    </div>

                                    <div class="flex justify-between text-[11px] font-mono text-slate-500 dark:text-slate-400 pt-1 border-t border-slate-200/60 dark:border-slate-700">
                                        <span>إجمالي الصنف:</span>
                                        <span class="font-bold text-slate-900 dark:text-white">
                                            {{ (line.quantity * line.cost_price).toFixed(2) }} ج.م
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Summary Breakdown -->
                        <div class="p-3.5 bg-slate-100 dark:bg-slate-800 rounded-2xl space-y-2 text-xs">
                            <div class="flex justify-between text-slate-500 dark:text-slate-400">
                                <span>إجمالي الأصناف:</span>
                                <span class="font-mono font-bold">{{ formSubtotal.toFixed(2) }} ج.م</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">خصم المورد:</span>
                                <input
                                    v-model="form.discount_amount"
                                    type="number"
                                    step="0.5"
                                    min="0"
                                    class="w-20 h-7 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 text-center font-mono font-bold text-xs"
                                />
                            </div>

                            <div class="flex justify-between text-sm font-black text-slate-900 dark:text-white pt-1 border-t border-slate-200 dark:border-slate-700">
                                <span>صافي الفاتورة:</span>
                                <span class="text-amber-600 dark:text-amber-400 font-mono">{{ formNetTotal.toFixed(2) }} ج.م</span>
                            </div>

                            <div class="flex items-center justify-between pt-1 border-t border-slate-200 dark:border-slate-700">
                                <span class="text-slate-500 font-bold">المدفوع نقداً للمورد:</span>
                                <div class="flex items-center gap-1.5">
                                    <input
                                        v-model="form.paid_amount"
                                        type="number"
                                        step="0.5"
                                        min="0"
                                        class="w-24 h-8 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-2 text-center font-mono font-bold text-xs text-emerald-600"
                                    />
                                    <button
                                        @click="setPayAll"
                                        type="button"
                                        class="px-2 py-1 bg-emerald-500 text-white rounded-lg font-bold text-[10px]"
                                    >
                                        سداد كامل
                                    </button>
                                </div>
                            </div>

                            <div class="flex justify-between text-[11px] font-bold text-amber-600 dark:text-amber-400">
                                <span>المتبقي آجل على الحساب:</span>
                                <span class="font-mono">{{ formRemaining.toFixed(2) }} ج.م</span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            :disabled="form.processing"
                            type="submit"
                            class="w-full h-13 bg-amber-600 hover:bg-amber-700 text-white font-black text-sm rounded-2xl shadow-xl shadow-amber-600/30 flex items-center justify-center gap-2 transition touch-active"
                        >
                            <span>📦</span>
                            <span>{{ form.processing ? 'جاري توريد الشحنة...' : 'تأكيد التوريد وإضافة البضاعة للمخزن' }}</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- ACTION SHEET FOR PURCHASE (⋯) -->
            <div
                v-if="activeActionPurchase"
                @click="activeActionPurchase = null"
                class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-end justify-center select-none"
            >
                <div
                    @click.stop
                    class="w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 shadow-2xl p-4 pb-8 space-y-3 animate-slide-up"
                >
                    <div class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto -mt-1 mb-1"></div>

                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">
                                فاتورة مشتريات #{{ activeActionPurchase.purchase_number }}
                            </h3>
                            <div class="text-[10px] text-slate-400 font-mono">
                                {{ activeActionPurchase.supplier?.name }} • {{ Number(activeActionPurchase.net_total).toFixed(2) }} ج.م
                            </div>
                        </div>
                        <button @click="activeActionPurchase = null" type="button" class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-xs">✕</button>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-1.5">
                        <Link
                            :href="'/purchases/' + activeActionPurchase.id"
                            class="w-full h-11 bg-slate-50 dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-xs rounded-2xl flex items-center justify-between px-3.5 transition"
                        >
                            <span class="flex items-center gap-2">
                                <span>👁️</span>
                                <span>عرض بنود وأصناف الفاتورة</span>
                            </span>
                            <span class="text-slate-400 text-xs">‹</span>
                        </Link>

                        <a
                            v-if="activeActionPurchase.supplier?.phone"
                            :href="'https://wa.me/2' + activeActionPurchase.supplier.phone.replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent('☕ تأكيد استلام فاتورة توريد رقم ' + activeActionPurchase.purchase_number + ' بقيمة ' + Number(activeActionPurchase.net_total).toFixed(2) + ' ج.م لدى سرور كوفي.')"
                            target="_blank"
                            class="w-full h-11 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 font-bold text-xs rounded-2xl flex items-center justify-between px-3.5 transition"
                        >
                            <span class="flex items-center gap-2">
                                <span>💬</span>
                                <span>مراسلة المورد عبر WhatsApp</span>
                            </span>
                            <span class="text-xs">💬</span>
                        </a>

                        <button
                            v-if="activeActionPurchase.status !== 'cancelled'"
                            @click="cancelPurchase(activeActionPurchase)"
                            type="button"
                            class="w-full h-11 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 font-bold text-xs rounded-2xl flex items-center justify-between px-3.5 transition"
                        >
                            <span class="flex items-center gap-2">
                                <span>🚫</span>
                                <span>إلغاء فاتورة الشراء وعكس المخزن</span>
                            </span>
                            <span class="text-xs">⚠️</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
