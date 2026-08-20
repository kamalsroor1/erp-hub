<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import CustomerPickerSheet from '@/Components/CustomerPickerSheet.vue';
import WeightPickerModal from '@/Components/WeightPickerModal.vue';
import SwipeableCartItem from '@/Components/SwipeableCartItem.vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    items: Array,
    categories: Array,
    customers: Array,
    activeStore: Object,
});

const search = ref('');
const selectedCategory = ref('all');
const showCheckoutSheet = ref(false);
const showCustomerSheet = ref(false);
const showWeightModal = ref(false);
const activeWeightItem = ref(null);

// Default Customer (General Cash Customer if exists, or first customer)
const defaultCustomer = computed(() => {
    return props.customers?.find(c => c.phone === '01000000000') || props.customers?.[0] || { id: 1, name: 'عميل نقدي عام', phone: '' };
});

const selectedCustomer = ref(defaultCustomer.value);

const onSelectCustomer = (cust) => {
    selectedCustomer.value = cust;
    haptic.light();
};

// Cart State: Map of item_id => Cart Item
const cart = ref([]);
const paymentType = ref('cash');
const discountType = ref('fixed');
const discountValue = ref(0);
const tenderedCash = ref('');
const notes = ref('');

// Category emoji icons helper
const getCategoryIcon = (cat) => {
    if (!cat) return '📦';
    if (cat.includes('خام')) return '🌱';
    if (cat.includes('محمص') || cat.includes('بن')) return '☕';
    if (cat.includes('توليف') || cat.includes('خلط')) return '⚖️';
    if (cat.includes('تعبئ') || cat.includes('مستلزم') || cat.includes('توابل') || cat.includes('كرتون')) return '📦';
    return '🏷️';
};

const isPieceUnit = (unit) => {
    const u = (unit || '').toLowerCase();
    return u.includes('قطعة') || u.includes('قطعه') || u.includes('عبوة') || u.includes('كيس') || u.includes('علبة');
};

const filteredItems = computed(() => {
    return (props.items || []).filter(item => {
        const matchesCategory = selectedCategory.value === 'all' || item.category === selectedCategory.value;
        const matchesSearch = !search.value || 
            item.name.toLowerCase().includes(search.value.toLowerCase()) || 
            item.code.toLowerCase().includes(search.value.toLowerCase());
        return matchesCategory && matchesSearch;
    });
});

// Get quantity in cart for specific item
const getItemQtyInCart = (itemId) => {
    const found = cart.value.find(c => c.item_id === itemId);
    return found ? found.quantity : 0;
};

// Open Weight/Quantity Picker
const openWeightModal = (item) => {
    haptic.light();
    activeWeightItem.value = item;
    showWeightModal.value = true;
};

// Add or update item from Weight Picker Modal
const onConfirmWeight = ({ item, quantity, unit_price, unit }) => {
    haptic.medium();
    const existingIdx = cart.value.findIndex(c => c.item_id === item.id);
    if (existingIdx !== -1) {
        cart.value[existingIdx].quantity = quantity;
    } else {
        cart.value.push({
            item_id: item.id,
            name: item.name,
            code: item.code,
            unit: unit || item.unit || 'كجم',
            unit_price: unit_price,
            quantity: quantity,
        });
    }
};

// Quick incremental step from card stepper
const addOneStep = (item) => {
    haptic.medium();
    const existing = cart.value.find(c => c.item_id === item.id);
    const step = isPieceUnit(item.unit) ? 1 : 0.250;

    if (existing) {
        existing.quantity = Number((existing.quantity + step).toFixed(3));
    } else {
        cart.value.push({
            item_id: item.id,
            name: item.name,
            code: item.code,
            unit: item.unit || (isPieceUnit(item.unit) ? 'قطعة' : 'كجم'),
            unit_price: parseFloat(item.selling_price || 0),
            quantity: step,
        });
    }
};

const removeOneStep = (item) => {
    haptic.light();
    const idx = cart.value.findIndex(c => c.item_id === item.id);
    if (idx !== -1) {
        const step = isPieceUnit(item.unit) ? 1 : 0.250;
        cart.value[idx].quantity = Number((cart.value[idx].quantity - step).toFixed(3));
        if (cart.value[idx].quantity <= 0) {
            cart.value.splice(idx, 1);
        }
    }
};

const handleQtyUpdate = ({ index, delta }) => {
    if (cart.value[index]) {
        const newQty = Number((cart.value[index].quantity + delta).toFixed(3));
        if (newQty <= 0) {
            cart.value.splice(index, 1);
        } else {
            cart.value[index].quantity = newQty;
        }
    }
};

const removeFromCart = (index) => {
    haptic.heavy();
    cart.value.splice(index, 1);
};

// Calculations
const cartSubtotal = computed(() => {
    return cart.value.reduce((sum, line) => sum + (line.quantity * line.unit_price), 0);
});

const discountAmount = computed(() => {
    const val = parseFloat(discountValue.value || 0);
    if (discountType.value === 'percentage') {
        return (cartSubtotal.value * val) / 100;
    }
    return Math.min(val, cartSubtotal.value);
});

const cartTotal = computed(() => {
    return Math.max(0, cartSubtotal.value - discountAmount.value);
});

// Quick Cash Tender Options
const cashShortcuts = computed(() => {
    const tot = Math.ceil(cartTotal.value);
    const options = [tot];
    [50, 100, 200, 500, 1000].forEach(denom => {
        if (denom >= tot && !options.includes(denom)) {
            options.push(denom);
        }
    });
    return options.slice(0, 4);
});

const setTendered = (amount) => {
    tenderedCash.value = amount.toString();
};

const changeDue = computed(() => {
    const tendered = parseFloat(tenderedCash.value || 0);
    if (paymentType.value !== 'cash' || tendered <= 0) return 0;
    return Math.max(0, tendered - cartTotal.value);
});

const isSubmitting = ref(false);

const submitInvoice = () => {
    if (cart.value.length === 0 || isSubmitting.value) return;

    isSubmitting.value = true;
    const paid = paymentType.value === 'cash' 
        ? cartTotal.value 
        : (paymentType.value === 'credit' ? 0 : cartTotal.value);

    const payload = {
        customer_id: selectedCustomer.value?.id || 1,
        store_id: props.activeStore?.id || 1,
        payment_type: paymentType.value,
        paid_amount: paid,
        discount_type: discountType.value,
        discount_value: discountValue.value,
        notes: notes.value,
        items: cart.value.map(line => ({
            item_id: line.item_id,
            quantity: line.quantity,
            unit_price: line.unit_price,
        })),
    };

    router.post('/invoices', payload, {
        onFinish: () => {
            isSubmitting.value = false;
        },
        onSuccess: () => {
            haptic.success();
            cart.value = [];
            showCheckoutSheet.value = false;
        },
        onError: () => {
            haptic.error();
        }
    });
};
</script>

<template>
    <MobileLayout>
        <!-- POS Main Touch Interface -->
        <div class="space-y-3 pb-28">

            <!-- Customer Bar & Quick Picker -->
            <div class="flex items-center justify-between gap-2 p-2.5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
                <!-- Tap to change customer -->
                <button
                    @click="showCustomerSheet = true"
                    type="button"
                    class="flex items-center gap-2.5 text-start flex-1 min-w-0 touch-active"
                >
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-sm shrink-0">
                        👤
                    </div>
                    <div class="truncate">
                        <div class="text-[10px] text-slate-400 font-bold leading-tight">العميل المحدد ▾</div>
                        <div class="text-xs font-black text-slate-900 dark:text-white truncate">
                            {{ selectedCustomer?.name || 'عميل نقدي عام' }}
                        </div>
                    </div>
                </button>

                <!-- Cart Top Trigger Button -->
                <button
                    v-if="cart.length > 0"
                    @click="showCheckoutSheet = true; haptic.light();"
                    type="button"
                    class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-black text-xs flex items-center gap-1.5 shadow-sm touch-active shrink-0 animate-pulse"
                >
                    <span>🛒</span>
                    <span>السلة ({{ cart.length }})</span>
                </button>

                <!-- Customer Balance Pill or Quick Switch -->
                <button
                    v-else
                    @click="showCustomerSheet = true"
                    type="button"
                    class="px-2.5 py-1 rounded-xl text-[10px] font-mono font-black shrink-0 transition"
                    :class="parseFloat(selectedCustomer?.current_balance || 0) > 0 ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 border border-rose-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                >
                    {{ parseFloat(selectedCustomer?.current_balance || 0) > 0 ? 'دين: ' + Number(selectedCustomer.current_balance).toLocaleString('en-US') : 'تغيير' }}
                </button>
            </div>

            <!-- Search & Quick Clear -->
            <div class="relative">
                <input
                    v-model="search"
                    type="text"
                    placeholder="بحث سريع باسم الصنف أو الكود..."
                    class="w-full h-11 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl ps-10 pe-8 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-emerald-500 shadow-xs"
                >
                <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 text-slate-400 text-sm pointer-events-none">
                    🔍
                </div>
                <button
                    v-if="search"
                    @click="search = ''"
                    type="button"
                    class="absolute inset-y-0 end-0 flex items-center pe-3 text-slate-400 text-xs font-black"
                >
                    ✕
                </button>
            </div>

            <!-- Category Horizontal Carousel Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 -mx-4 px-4 scrollbar-none">
                <button
                    @click="selectedCategory = 'all'"
                    type="button"
                    class="px-3 py-1.5 rounded-xl font-black text-xs transition shrink-0 touch-active flex items-center gap-1.5 shadow-xs"
                    :class="selectedCategory === 'all' ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300'"
                >
                    <span>☕</span>
                    <span>الكل ({{ items?.length || 0 }})</span>
                </button>

                <button
                    v-for="cat in categories"
                    :key="cat"
                    @click="selectedCategory = cat"
                    type="button"
                    class="px-3 py-1.5 rounded-xl font-black text-xs transition shrink-0 touch-active flex items-center gap-1.5 shadow-xs"
                    :class="selectedCategory === cat ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300'"
                >
                    <span>{{ getCategoryIcon(cat) }}</span>
                    <span>{{ cat }}</span>
                </button>
            </div>

            <!-- Items Touch Cards Grid (2 Columns) -->
            <div class="grid grid-cols-2 gap-2.5">
                <div
                    v-for="item in filteredItems"
                    :key="item.id"
                    class="relative bg-white dark:bg-slate-900 rounded-3xl p-3 border transition shadow-xs flex flex-col justify-between"
                    :class="getItemQtyInCart(item.id) > 0 ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/20 dark:bg-emerald-950/20' : 'border-slate-200 dark:border-slate-800'"
                >
                    <!-- Stock in Branch Badge & Unit Pill -->
                    <div class="flex items-center justify-between gap-1 mb-1.5">
                        <span class="text-[9px] font-mono font-bold text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded-md">
                            #{{ item.code }}
                        </span>
                        <span
                            class="text-[9px] font-bold px-1.5 py-0.5 rounded-md font-mono"
                            :class="parseFloat(item.current_stock) <= 0 ? 'bg-rose-100 dark:bg-rose-950/40 text-rose-500' : 'bg-emerald-100/60 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400'"
                        >
                            {{ Number(item.current_stock || 0).toFixed(0) }} {{ item.unit || 'كجم' }}
                        </span>
                    </div>

                    <!-- Item Name (Click opens weight picker for precision) -->
                    <div
                        @click="openWeightModal(item)"
                        class="text-xs font-black text-slate-900 dark:text-white line-clamp-2 leading-snug mb-2 cursor-pointer"
                    >
                        {{ item.name }}
                    </div>

                    <!-- Price & Touch Controls -->
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-xs font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                {{ Number(item.selling_price || 0).toFixed(2) }} <span class="text-[9px] font-bold text-slate-400">ج.م</span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-bold">/ {{ item.unit || 'كجم' }}</span>
                        </div>

                        <!-- Stepper / Add & Weight Button -->
                        <div v-if="getItemQtyInCart(item.id) > 0" class="flex items-center justify-between bg-emerald-600 text-white rounded-xl p-0.5 shadow-sm">
                            <button
                                @click="removeOneStep(item)"
                                type="button"
                                class="w-7 h-7 rounded-lg bg-white/20 active:bg-white/30 flex items-center justify-center font-black text-xs touch-active"
                            >
                                -
                            </button>

                            <!-- Tap quantity number to open weight/pieces modal -->
                            <button
                                @click="openWeightModal(item)"
                                type="button"
                                class="font-mono font-black text-xs px-1 hover:underline"
                            >
                                {{ getItemQtyInCart(item.id) }} {{ item.unit || (isPieceUnit(item.unit) ? 'ق' : 'كجم') }}
                            </button>

                            <button
                                @click="addOneStep(item)"
                                type="button"
                                class="w-7 h-7 rounded-lg bg-white/20 active:bg-white/30 flex items-center justify-center font-black text-xs touch-active"
                            >
                                +
                            </button>
                        </div>

                        <button
                            v-else
                            @click="openWeightModal(item)"
                            type="button"
                            class="w-full py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-emerald-500 hover:text-white dark:hover:bg-emerald-600 text-slate-700 dark:text-slate-200 text-xs font-black transition touch-active flex items-center justify-center gap-1"
                        >
                            <span>{{ isPieceUnit(item.unit) ? '📦 إضافة قطعة' : '⚖️ تحديد الوزن' }}</span>
                        </button>
                    </div>
                </div>

                <div v-if="filteredItems.length === 0" class="col-span-2 text-center py-12 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <div class="text-3xl mb-1">☕</div>
                    <div class="text-xs font-bold text-slate-600 dark:text-slate-300">لا توجد أصناف مطابقة للبحث</div>
                </div>
            </div>

            <!-- Customer Picker Bottom Sheet Component (With Inline Create) -->
            <CustomerPickerSheet
                :isOpen="showCustomerSheet"
                :customers="customers"
                :selectedId="selectedCustomer?.id"
                @close="showCustomerSheet = false"
                @select="onSelectCustomer"
                @created="(newC) => { customers?.push(newC); selectedCustomer = newC; }"
            />

            <!-- Weight & Pieces Selector Bottom Sheet Modal -->
            <WeightPickerModal
                :isOpen="showWeightModal"
                :item="activeWeightItem"
                :initialQty="activeWeightItem ? getItemQtyInCart(activeWeightItem.id) : 0.250"
                @close="showWeightModal = false"
                @confirm="onConfirmWeight"
            />

            <!-- Floating Cart Bar (Above Bottom Nav in Thumb Zone) -->
            <div
                v-if="cart.length > 0"
                @click="showCheckoutSheet = true; haptic.light();"
                class="fixed bottom-18 left-3 right-3 max-w-md mx-auto z-40 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white p-3 rounded-2xl shadow-xl shadow-emerald-600/30 flex items-center justify-between gap-3 animate-slide-up border border-emerald-400/30 cursor-pointer touch-active"
            >
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center font-black text-xs font-mono shadow-xs">
                        🛒 {{ cart.length }}
                    </div>
                    <div>
                        <div class="text-[10px] text-emerald-100 font-bold flex items-center gap-1">
                            <span>مراجعة السلة</span>
                            <span class="text-amber-300">▾</span>
                        </div>
                        <div class="text-sm font-black font-mono leading-tight">
                            {{ cartTotal.toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م
                        </div>
                    </div>
                </div>

                <button
                    @click.stop="showCheckoutSheet = true; haptic.medium();"
                    type="button"
                    class="px-4 py-2.5 bg-white hover:bg-emerald-50 text-emerald-700 font-black text-xs rounded-xl shadow-md touch-active flex items-center gap-1.5"
                >
                    <span>⚡ إتمام الدفع</span>
                    <span>‹</span>
                </button>
            </div>

            <!-- Slide-Up Checkout Sheet (Full Touch POS Review & Payment) -->
            <div
                v-if="showCheckoutSheet"
                @click="showCheckoutSheet = false"
                class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs flex items-end justify-center select-none"
            >
                <div
                    @click.stop
                    class="w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 shadow-2xl p-4 pb-8 space-y-3.5 max-h-[90vh] overflow-y-auto animate-slide-up"
                >
                    <!-- Drag Handle -->
                    <div class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto -mt-1 mb-1"></div>

                    <!-- Header -->
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🧾</span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white">إتمام الفاتورة والدفع</h3>
                                <p class="text-[10px] text-slate-400 font-bold">العميل: {{ selectedCustomer?.name }}</p>
                            </div>
                        </div>

                        <button
                            @click="showCheckoutSheet = false"
                            type="button"
                            class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-xs"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Line Items List with Swipe-to-Delete -->
                    <div class="space-y-2 max-h-52 overflow-y-auto pe-0.5">
                        <SwipeableCartItem
                            v-for="(line, idx) in cart"
                            :key="line.item_id"
                            :item="line"
                            :index="idx"
                            @update-qty="handleQtyUpdate"
                            @remove="removeFromCart"
                        />
                    </div>

                    <!-- Payment Method Toggle -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">طريقة الدفع:</label>
                        <div class="grid grid-cols-3 gap-2 text-xs font-black">
                            <button
                                @click="paymentType = 'cash'"
                                type="button"
                                class="py-2.5 rounded-xl border transition flex items-center justify-center gap-1 touch-active"
                                :class="paymentType === 'cash' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'"
                            >
                                <span>💵</span>
                                <span>كاش</span>
                            </button>

                            <button
                                @click="paymentType = 'credit'"
                                type="button"
                                class="py-2.5 rounded-xl border transition flex items-center justify-center gap-1 touch-active"
                                :class="paymentType === 'credit' ? 'bg-amber-500 text-white border-amber-500 shadow-sm' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'"
                            >
                                <span>⏳</span>
                                <span>شكك / آجل</span>
                            </button>

                            <button
                                @click="paymentType = 'bank_transfer'"
                                type="button"
                                class="py-2.5 rounded-xl border transition flex items-center justify-center gap-1 touch-active"
                                :class="paymentType === 'bank_transfer' ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'"
                            >
                                <span>🏦</span>
                                <span>تحويل / كاش</span>
                            </button>
                        </div>
                    </div>

                    <!-- Quick Cash Tender Shortcuts (If Cash) -->
                    <div v-if="paymentType === 'cash'" class="space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-400">
                            <span>الزبون دفع كام (كاش):</span>
                            <span v-if="changeDue > 0" class="text-emerald-500 font-mono font-black">
                                الفكة / الباقي للزبون: {{ changeDue.toFixed(2) }} ج.م
                            </span>
                        </div>

                        <!-- Quick Denomination Buttons -->
                        <div class="grid grid-cols-4 gap-1.5">
                            <button
                                v-for="amt in cashShortcuts"
                                :key="amt"
                                @click="setTendered(amt)"
                                type="button"
                                class="py-1.5 rounded-xl text-xs font-mono font-black border transition touch-active"
                                :class="tenderedCash === amt.toString() ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200'"
                            >
                                {{ amt }} ج.م
                            </button>
                        </div>

                        <!-- Custom Tender Input -->
                        <input
                            v-model="tenderedCash"
                            type="number"
                            placeholder="أو اكتب المبلغ اللي استلمته..."
                            class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 text-xs font-mono font-bold text-slate-900 dark:text-white outline-none"
                        >
                    </div>

                    <!-- Totals Box -->
                    <div class="p-3.5 bg-slate-50 dark:bg-slate-800/80 rounded-2xl space-y-1.5 text-xs">
                        <div class="flex justify-between text-slate-500 font-bold">
                            <span>حساب الأصناف:</span>
                            <span class="font-mono">{{ cartSubtotal.toFixed(2) }} ج.م</span>
                        </div>
                        <div class="flex justify-between text-base font-black text-slate-900 dark:text-white pt-1.5 border-t border-slate-200 dark:border-slate-700">
                            <span>المطلوب يتدفع:</span>
                            <span class="font-mono text-emerald-600 dark:text-emerald-400">{{ cartTotal.toFixed(2) }} ج.م</span>
                        </div>
                    </div>

                    <!-- Confirm Button -->
                    <button
                        @click="submitInvoice"
                        type="button"
                        :disabled="isSubmitting"
                        class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm rounded-2xl shadow-lg shadow-emerald-600/30 transition touch-active flex items-center justify-center gap-2"
                    >
                        <span>{{ isSubmitting ? 'جاري الحفظ وخصم المخزن...' : 'حفظ الفاتورة وقبض الفلوس ✓' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
