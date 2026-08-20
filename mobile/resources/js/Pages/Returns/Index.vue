<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    returns: { type: Array, default: () => [] },
    total_returns: { type: [Number, String], default: 0 },
    total_count: { type: Number, default: 0 },
    customers: { type: Array, default: () => [] },
    suppliers: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const currentTypeTab = ref(props.filters.type || 'all');
const search = ref(props.filters.search || '');

const setTypeFilter = (t) => {
    haptic.light();
    currentTypeTab.value = t;
    router.get('/returns', {
        type: t,
        search: search.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Modal State
const showSalesReturnModal = ref(false);
const showPurchaseReturnModal = ref(false);

// 1. Sales Return Form
const salesForm = useForm({
    customer_id: props.customers[0]?.id || '',
    return_date: new Date().toISOString().split('T')[0],
    reason: 'مرتجع مبيعات من العميل',
    items: [
        {
            item_id: props.items[0]?.id || '',
            quantity: 1,
            unit_price: Number(props.items[0]?.price_retail || 0),
        }
    ],
});

const openSalesReturnModal = () => {
    haptic.medium();
    salesForm.reset();
    salesForm.customer_id = props.customers[0]?.id || '';
    salesForm.return_date = new Date().toISOString().split('T')[0];
    salesForm.items = [
        {
            item_id: props.items[0]?.id || '',
            quantity: 1,
            unit_price: Number(props.items[0]?.price_retail || 0),
        }
    ];
    showSalesReturnModal.value = true;
};

const addSalesItem = () => {
    haptic.light();
    salesForm.items.push({
        item_id: props.items[0]?.id || '',
        quantity: 1,
        unit_price: Number(props.items[0]?.price_retail || 0),
    });
};

const removeSalesItem = (idx) => {
    if (salesForm.items.length > 1) {
        salesForm.items.splice(idx, 1);
    }
};

const onSalesItemChange = (idx) => {
    const it = props.items.find(i => i.id === salesForm.items[idx].item_id);
    if (it) {
        salesForm.items[idx].unit_price = Number(it.price_retail || 0);
    }
};

const salesReturnTotal = computed(() => {
    return salesForm.items.reduce((acc, it) => acc + (Number(it.quantity) * Number(it.unit_price)), 0);
});

const submitSalesReturn = () => {
    haptic.success();
    salesForm.post('/returns/sales', {
        onSuccess: () => {
            showSalesReturnModal.value = false;
        }
    });
};

// 2. Purchase Return Form
const purchaseForm = useForm({
    supplier_id: props.suppliers[0]?.id || '',
    return_date: new Date().toISOString().split('T')[0],
    reason: 'مرتجع خامات تالفة للمورد',
    items: [
        {
            item_id: props.items[0]?.id || '',
            quantity: 10,
            unit_price: Number(props.items[0]?.cost_price || 0),
        }
    ],
});

const openPurchaseReturnModal = () => {
    haptic.medium();
    purchaseForm.reset();
    purchaseForm.supplier_id = props.suppliers[0]?.id || '';
    purchaseForm.return_date = new Date().toISOString().split('T')[0];
    purchaseForm.items = [
        {
            item_id: props.items[0]?.id || '',
            quantity: 10,
            unit_price: Number(props.items[0]?.cost_price || 0),
        }
    ];
    showPurchaseReturnModal.value = true;
};

const addPurchaseItem = () => {
    haptic.light();
    purchaseForm.items.push({
        item_id: props.items[0]?.id || '',
        quantity: 10,
        unit_price: Number(props.items[0]?.cost_price || 0),
    });
};

const removePurchaseItem = (idx) => {
    if (purchaseForm.items.length > 1) {
        purchaseForm.items.splice(idx, 1);
    }
};

const onPurchaseItemChange = (idx) => {
    const it = props.items.find(i => i.id === purchaseForm.items[idx].item_id);
    if (it) {
        purchaseForm.items[idx].unit_price = Number(it.cost_price || 0);
    }
};

const purchaseReturnTotal = computed(() => {
    return purchaseForm.items.reduce((acc, it) => acc + (Number(it.quantity) * Number(it.unit_price)), 0);
});

const submitPurchaseReturn = () => {
    haptic.success();
    purchaseForm.post('/returns/purchases', {
        onSuccess: () => {
            showPurchaseReturnModal.value = false;
        }
    });
};

// Cancel Return
const cancelReturnDoc = (r) => {
    haptic.warning();
    if (confirm(`هل أنت متأكد من إلغاء مستند المرتجع #${r.return_number} وعكس أثره المخزني والمالي؟`)) {
        router.post(`/returns/${r.id}/cancel`);
    }
};
</script>

<template>
    <MobileLayout>
        <div class="space-y-4 pb-24 select-none">
            <!-- Header Banner & Create Buttons -->
            <div class="bg-gradient-to-l from-rose-600 via-rose-700 to-slate-900 rounded-3xl p-4 text-white shadow-xl shadow-rose-900/30 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">🔄</span>
                            <h2 class="text-base font-black">مرتجهات المبيعات والمشتريات</h2>
                        </div>
                        <p class="text-[11px] text-rose-100 font-bold mt-0.5">
                            استرجاع بضاعة العملاء أو رد خامات للموردين مع تعديل المخزن والحساب
                        </p>
                    </div>
                </div>

                <!-- Quick Action Buttons -->
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <button
                        @click="openSalesReturnModal"
                        type="button"
                        class="h-10 px-3 bg-white text-rose-700 hover:bg-rose-50 font-black text-xs rounded-2xl shadow-md flex items-center justify-center gap-1.5 transition touch-active"
                    >
                        <span>🛍️</span>
                        <span>مرتجع مبيعات عميل</span>
                    </button>

                    <button
                        @click="openPurchaseReturnModal"
                        type="button"
                        class="h-10 px-3 bg-white/20 hover:bg-white/30 text-white font-black text-xs rounded-2xl flex items-center justify-center gap-1.5 transition touch-active border border-white/20"
                    >
                        <span>📦</span>
                        <span>مرتجع مشتريات مورد</span>
                    </button>
                </div>
            </div>

            <!-- Filter Tabs (All / Sales Returns / Purchase Returns) -->
            <div class="grid grid-cols-3 gap-1.5 bg-slate-200/70 dark:bg-slate-900 p-1 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs">
                <button
                    @click="setTypeFilter('all')"
                    type="button"
                    class="py-2 rounded-xl font-black transition touch-active text-center"
                    :class="currentTypeTab === 'all' ? 'bg-white dark:bg-slate-800 text-rose-600 dark:text-rose-400 shadow-sm' : 'text-slate-600 dark:text-slate-400'"
                >
                    الكل ({{ total_count }})
                </button>
                <button
                    @click="setTypeFilter('sales_return')"
                    type="button"
                    class="py-2 rounded-xl font-black transition touch-active text-center"
                    :class="currentTypeTab === 'sales_return' ? 'bg-white dark:bg-slate-800 text-rose-600 dark:text-rose-400 shadow-sm' : 'text-slate-600 dark:text-slate-400'"
                >
                    🛍️ مبيعات
                </button>
                <button
                    @click="setTypeFilter('purchase_return')"
                    type="button"
                    class="py-2 rounded-xl font-black transition touch-active text-center"
                    :class="currentTypeTab === 'purchase_return' ? 'bg-white dark:bg-slate-800 text-rose-600 dark:text-rose-400 shadow-sm' : 'text-slate-600 dark:text-slate-400'"
                >
                    📦 مشتريات
                </button>
            </div>

            <!-- Returns List -->
            <div class="space-y-3">
                <div
                    v-for="r in returns"
                    :key="r.id"
                    class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2.5 hover:border-rose-500/50 transition"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span
                                class="px-2 py-0.5 rounded-lg text-[10px] font-black"
                                :class="r.return_type === 'sales_return' ? 'bg-rose-500/10 text-rose-600 border border-rose-500/20' : 'bg-amber-500/10 text-amber-600 border border-amber-500/20'"
                            >
                                {{ r.return_type === 'sales_return' ? 'مرتجع مبيعات 🛍️' : 'مرتجع مشتريات 📦' }}
                            </span>
                            <span class="text-xs font-mono font-bold text-slate-400">#{{ r.return_number }}</span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">{{ r.return_date }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100 dark:border-slate-800">
                        <div>
                            <div class="font-extrabold text-slate-900 dark:text-white">
                                {{ r.return_type === 'sales_return' ? (r.customer?.name || 'عميل نقدي') : (r.supplier?.name || 'مورد') }}
                            </div>
                            <div v-if="r.reason" class="text-[10px] text-slate-400 truncate mt-0.5">
                                السبب: {{ r.reason }}
                            </div>
                        </div>

                        <div class="text-end">
                            <div class="text-base font-black font-mono text-rose-600 dark:text-rose-400">
                                {{ Number(r.total_amount).toFixed(2) }} ج.م
                            </div>
                            <div class="text-[10px] text-slate-400">
                                {{ r.items?.length || 0 }} أصناف
                            </div>
                        </div>
                    </div>

                    <!-- Items Preview -->
                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 text-[11px] space-y-1">
                        <div v-for="it in r.items" :key="it.id" class="flex justify-between text-slate-600 dark:text-slate-300 font-mono">
                            <span>• {{ it.item?.name || 'صنف' }} ({{ Number(it.quantity).toFixed(2) }} كجم)</span>
                            <span class="font-bold">{{ Number(it.total_price).toFixed(2) }} ج</span>
                        </div>
                    </div>

                    <!-- Action Row -->
                    <div class="pt-1 flex justify-end">
                        <button
                            @click="cancelReturnDoc(r)"
                            type="button"
                            class="px-3 py-1.5 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 text-[11px] font-bold border border-rose-500/20 transition touch-active"
                        >
                            إلغاء المرتجع وعكس الأثر 🚫
                        </button>
                    </div>
                </div>

                <div v-if="!returns || returns.length === 0" class="text-center py-10 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <div class="text-3xl mb-1">🔄</div>
                    <div class="text-xs font-bold text-slate-600 dark:text-slate-300">لا توجد مستندات مرتجع مسجلة</div>
                </div>
            </div>

            <!-- SALES RETURN MODAL SHEET -->
            <div v-if="showSalesReturnModal" @click="showSalesReturnModal = false" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-end justify-center select-none">
                <div @click.stop class="w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 shadow-2xl p-5 pb-8 space-y-4 max-h-[90vh] overflow-y-auto animate-slide-up">
                    <div class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto -mt-1 mb-1"></div>

                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🛍️</span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white">تسجيل مرتجع مبيعات من عميل</h3>
                                <p class="text-[10px] text-slate-400 font-bold">إعادة البضاعة للمخزن وتخفيض مديونية العميل</p>
                            </div>
                        </div>
                        <button @click="showSalesReturnModal = false" type="button" class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-xs">✕</button>
                    </div>

                    <form @submit.prevent="submitSalesReturn" class="space-y-3.5">
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 mb-1">العميل المسترجع منه:</label>
                            <select v-model="salesForm.customer_id" required class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white">
                                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} ({{ c.phone || 'بدون هاتف' }})</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 mb-1">سبب المرتجع:</label>
                            <input v-model="salesForm.reason" type="text" placeholder="مثال: رغبة العميل في استبدال الصنف أو تلف في التغليف" class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white" />
                        </div>

                        <!-- Items Table -->
                        <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-black text-slate-900 dark:text-white">الأصناف المسترجعة:</label>
                                <button @click="addSalesItem" type="button" class="px-2.5 py-1 bg-rose-500/10 text-rose-600 rounded-xl text-[11px] font-black border border-rose-500/20">➕ إضافة صنف</button>
                            </div>

                            <div v-for="(line, idx) in salesForm.items" :key="idx" class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <select v-model="line.item_id" @change="onSalesItemChange(idx)" class="flex-1 h-9 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-2 text-xs font-bold text-slate-900 dark:text-white">
                                        <option v-for="it in items" :key="it.id" :value="it.id">{{ it.name }} (كود: {{ it.code }})</option>
                                    </select>
                                    <button v-if="salesForm.items.length > 1" @click="removeSalesItem(idx)" type="button" class="w-7 h-7 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center text-xs font-bold">✕</button>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-slate-400 font-bold mb-0.5">الوزن / الكمية (كجم):</label>
                                        <input v-model="line.quantity" type="number" step="0.05" min="0.001" required class="w-full h-9 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-2 text-center font-mono font-black text-xs text-slate-900 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-slate-400 font-bold mb-0.5">سعر الاسترجاع (ج.م):</label>
                                        <input v-model="line.unit_price" type="number" step="0.5" min="0" required class="w-full h-9 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-2 text-center font-mono font-black text-xs text-slate-900 dark:text-white" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-between text-xs font-black text-slate-900 dark:text-white">
                            <span>إجمالي قيمة المرتجع:</span>
                            <span class="text-rose-600 font-mono text-base">{{ salesReturnTotal.toFixed(2) }} ج.م</span>
                        </div>

                        <button :disabled="salesForm.processing" type="submit" class="w-full h-13 bg-rose-600 hover:bg-rose-700 text-white font-black text-sm rounded-2xl shadow-xl shadow-rose-600/30 flex items-center justify-center gap-2 transition touch-active">
                            <span>🛍️</span>
                            <span>{{ salesForm.processing ? 'جاري الحفظ...' : 'تأكيد مرتجع المبيعات وإعادة البضاعة للمخزن' }}</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- PURCHASE RETURN MODAL SHEET -->
            <div v-if="showPurchaseReturnModal" @click="showPurchaseReturnModal = false" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-end justify-center select-none">
                <div @click.stop class="w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 shadow-2xl p-5 pb-8 space-y-4 max-h-[90vh] overflow-y-auto animate-slide-up">
                    <div class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto -mt-1 mb-1"></div>

                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">📦</span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white">تسجيل مرتجع مشتريات لمورد</h3>
                                <p class="text-[10px] text-slate-400 font-bold">إنقاص البضاعة من المخزن وتخفيض حساب المورد</p>
                            </div>
                        </div>
                        <button @click="showPurchaseReturnModal = false" type="button" class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-xs">✕</button>
                    </div>

                    <form @submit.prevent="submitPurchaseReturn" class="space-y-3.5">
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 mb-1">المورد المسترجع إليه:</label>
                            <select v-model="purchaseForm.supplier_id" required class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white">
                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }} ({{ s.phone || 'بدون هاتف' }})</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 mb-1">سبب المرتجع:</label>
                            <input v-model="purchaseForm.reason" type="text" placeholder="مثال: خامات غير مطابقة للمواصفات أو تالفة" class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white" />
                        </div>

                        <!-- Items Table -->
                        <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-black text-slate-900 dark:text-white">الأصناف والخامات المردودة:</label>
                                <button @click="addPurchaseItem" type="button" class="px-2.5 py-1 bg-amber-500/10 text-amber-600 rounded-xl text-[11px] font-black border border-amber-500/20">➕ إضافة صنف</button>
                            </div>

                            <div v-for="(line, idx) in purchaseForm.items" :key="idx" class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <select v-model="line.item_id" @change="onPurchaseItemChange(idx)" class="flex-1 h-9 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-2 text-xs font-bold text-slate-900 dark:text-white">
                                        <option v-for="it in items" :key="it.id" :value="it.id">{{ it.name }} (كود: {{ it.code }})</option>
                                    </select>
                                    <button v-if="purchaseForm.items.length > 1" @click="removePurchaseItem(idx)" type="button" class="w-7 h-7 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center text-xs font-bold">✕</button>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-slate-400 font-bold mb-0.5">الوزن / الكمية (كجم):</label>
                                        <input v-model="line.quantity" type="number" step="0.05" min="0.001" required class="w-full h-9 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-2 text-center font-mono font-black text-xs text-slate-900 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-slate-400 font-bold mb-0.5">سعر التكلفة المسترد (ج.م):</label>
                                        <input v-model="line.unit_price" type="number" step="0.5" min="0" required class="w-full h-9 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-2 text-center font-mono font-black text-xs text-slate-900 dark:text-white" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-between text-xs font-black text-slate-900 dark:text-white">
                            <span>إجمالي قيمة المرتجع للمورد:</span>
                            <span class="text-amber-600 font-mono text-base">{{ purchaseReturnTotal.toFixed(2) }} ج.م</span>
                        </div>

                        <button :disabled="purchaseForm.processing" type="submit" class="w-full h-13 bg-amber-600 hover:bg-amber-700 text-white font-black text-sm rounded-2xl shadow-xl shadow-amber-600/30 flex items-center justify-center gap-2 transition touch-active">
                            <span>📦</span>
                            <span>{{ purchaseForm.processing ? 'جاري الحفظ...' : 'تأكيد مرتجع المشتريات وإنقاص رصيد المخزن' }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
