<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useMoney } from '@/Composables/useMoney';
import { usePOSCart } from '@/Composables/usePOSCart';
import { useKeyboardShortcuts } from '@/Composables/useKeyboardShortcuts';
import { posService } from '@/Services/posService';

// Atomic POS Components (SOLID - SRP)
import POSItemCard from '@/Components/POS/POSItemCard.vue';
import POSCartItem from '@/Components/POS/POSCartItem.vue';
import POSWeightPickerModal from '@/Components/POS/POSWeightPickerModal.vue';
import POSCustomerPickerModal from '@/Components/POS/POSCustomerPickerModal.vue';
import POSQuickCustomerModal from '@/Components/POS/POSQuickCustomerModal.vue';
import POSSuccessModal from '@/Components/POS/POSSuccessModal.vue';

const props = defineProps({
    items: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
    default_customer: { type: Object, default: () => ({}) },
    active_store: { type: Object, default: null },
    active_shift: { type: Object, default: null },
});

const page = usePage();
const tenant = computed(() => page.props.tenant);

// Composables
const { formatMoney } = useMoney();

// Search & Category filter
const searchQuery = ref('');
const selectedCategory = ref('all');
const searchInputRef = ref(null);

// Customer state
const selectedCustomer = ref(props.default_customer || props.customers[0] || null);
const showCustomerModal = ref(false);
const showNewCustomerModal = ref(false);

// Cart Composable
const {
    cart,
    discountType,
    discountValue,
    paymentType,
    paymentMethod,
    paidAmount,
    shippingCost,
    additionalExpenses,
    invoiceNotes,
    subtotal,
    discountAmount,
    expensesTotal,
    netTotal,
    remainingAmount,
    changeDue,
    autoSetCashPaid,
    addItem,
    removeItem,
    clearCart,
} = usePOSCart(selectedCustomer);

// UI States & Modals
const showExpensesSection = ref(false);
const isSubmitting = ref(false);
const errorMessage = ref('');

const showWeightModal = ref(false);
const activeWeightItem = ref(null);

const showSuccessModal = ref(false);
const completedInvoice = ref(null);

// Filtered Items
const filteredItems = computed(() => {
    return props.items.filter(item => {
        const matchesCategory = selectedCategory.value === 'all' || item.category === selectedCategory.value;
        const query = searchQuery.value.trim().toLowerCase();
        const matchesSearch = !query ||
            item.name.toLowerCase().includes(query) ||
            item.code?.toLowerCase().includes(query);
        return matchesCategory && matchesSearch;
    });
});

// Fetch Customer Last Price via Service Layer (DIP)
const fetchCustomerLastPrice = async (itemId) => {
    return await posService.getCustomerLastSoldPrice(
        selectedCustomer.value?.id,
        itemId,
        props.active_store?.id
    );
};

// Item Click Handler
const handleItemClick = async (item) => {
    const isWeightBased = item.unit === 'كجم' || item.unit === 'جم' || item.unit?.includes('كيلو');
    if (isWeightBased) {
        activeWeightItem.value = item;
        showWeightModal.value = true;
    } else {
        const lastPrice = await fetchCustomerLastPrice(item.id);
        addItem(item, 1, lastPrice);
    }
};

// Weight Confirmation from Modal
const handleWeightConfirm = async ({ item, quantity }) => {
    const lastPrice = await fetchCustomerLastPrice(item.id);
    addItem(item, quantity, lastPrice);
    showWeightModal.value = false;
    activeWeightItem.value = null;
};

// Apply Last Price to Cart Line
const applyLastSoldPrice = (line) => {
    if (line.last_sold_price?.unit_price) {
        line.unit_price = Number(line.last_sold_price.unit_price);
        autoSetCashPaid();
    }
};

// Customer Selection & Pricing Update
const selectCustomer = (c) => {
    selectedCustomer.value = c;
    showCustomerModal.value = false;

    cart.value.forEach(async (line) => {
        const itemObj = props.items.find(i => i.id === line.item_id);
        if (itemObj) {
            line.unit_price = c.price_tier === 'wholesale' ? itemObj.price_wholesale : itemObj.price_retail;
            line.last_sold_price = await fetchCustomerLastPrice(line.item_id);
        }
    });

    autoSetCashPaid();
};

const handleCustomerCreated = (created) => {
    props.customers.unshift(created);
    selectCustomer(created);
    showNewCustomerModal.value = false;
};

// Expenses Rows
const addExpenseRow = () => {
    additionalExpenses.value.push({ title: 'شحن وتوصيل', amount: 0 });
};
const removeExpenseRow = (idx) => {
    additionalExpenses.value.splice(idx, 1);
};

// Clear Cart with Confirmation
const handleClearCart = () => {
    if (cart.value.length === 0) return;
    if (confirm('هل أنت متأكد من تفريغ السلة بالكامل؟')) {
        clearCart();
    }
};

// POS Checkout via Service Layer (DIP)
const submitCheckout = async () => {
    if (cart.value.length === 0) {
        errorMessage.value = 'يرجى إضافة أصناف إلى السلة أولاً!';
        return;
    }

    if (!props.active_store?.id) {
        errorMessage.value = 'يرجى تحديد فرع نشط أولاً!';
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = '';

    const payload = {
        customer_id: selectedCustomer.value?.id || 1,
        store_id: props.active_store.id,
        invoice_date: new Date().toISOString().split('T')[0],
        payment_type: paymentType.value,
        payment_method: paymentMethod.value,
        discount_type: discountType.value,
        discount_value: Number(discountValue.value || 0),
        paid_amount: Number(paidAmount.value || 0),
        notes: invoiceNotes.value || null,
        items: cart.value.map(line => ({
            item_id: line.item_id,
            quantity: Number(line.quantity),
            unit_price: Number(line.unit_price),
        })),
        additional_expenses: additionalExpenses.value.map(exp => ({
            title: exp.title,
            amount: Number(exp.amount),
        })),
    };

    try {
        const response = await posService.processCheckout(payload);
        if (response.status === 'success') {
            completedInvoice.value = response.invoice;
            showSuccessModal.value = true;
            clearCart();
        }
    } catch (err) {
        errorMessage.value = err.response?.data?.message || 'حدث خطأ أثناء حفظ الفاتورة، يرجى مراجعة المخزون والبيانات.';
    } finally {
        isSubmitting.value = false;
    }
};

// Keyboard Shortcuts (SRP Composable)
useKeyboardShortcuts({
    'F2': (e) => {
        e.preventDefault();
        searchInputRef.value?.focus();
    },
    'Enter': (e) => {
        if (searchQuery.value && filteredItems.value.length === 1) {
            e.preventDefault();
            handleItemClick(filteredItems.value[0]);
            searchQuery.value = '';
        }
    }
});
</script>

<template>
    <Head :title="$t('pos.title')" />

    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col antialiased selection:bg-emerald-500 selection:text-white select-none">
        <!-- Top Cashier Header Bar -->
        <header class="h-14 bg-slate-900 border-b border-slate-800 px-4 flex items-center justify-between z-30 shrink-0">
            <div class="flex items-center gap-3">
                <Link href="/" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-slate-950 font-black text-sm flex items-center justify-center shadow-md">
                        ⚡
                    </div>
                    <span class="font-black text-sm text-white hidden sm:inline">
                        {{ $t('pos.title') }} • {{ tenant?.name || 'مخزني ERP' }}
                    </span>
                </Link>

                <span class="px-2.5 py-0.5 rounded-lg bg-slate-800 border border-slate-700 text-xs font-bold text-slate-300 flex items-center gap-1.5">
                    <span>🏬</span>
                    <span>{{ active_store?.name || $t('common.main_store_default') }}</span>
                </span>

                <span
                    class="px-2.5 py-0.5 rounded-lg text-xs font-black flex items-center gap-1"
                    :class="active_shift ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/15 text-rose-400 border border-rose-500/30'"
                >
                    <span class="w-2 h-2 rounded-full animate-pulse" :class="active_shift ? 'bg-emerald-400' : 'bg-rose-400'"></span>
                    <span>{{ active_shift ? $t('dashboard.shift_number', { number: active_shift.shift_number }) : $t('dashboard.closed_shift') }}</span>
                </span>
            </div>

            <div class="flex items-center gap-2">
                <Link
                    href="/invoices"
                    class="h-8 px-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold flex items-center gap-1 transition"
                >
                    <span>🧾</span>
                    <span>{{ $t('nav.invoices_log') }}</span>
                </Link>

                <Link
                    href="/"
                    class="h-8 px-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold flex items-center gap-1 transition"
                >
                    <span>📊</span>
                    <span>{{ $t('nav.dashboard') }}</span>
                </Link>
            </div>
        </header>

        <!-- POS Main Body Split Screen -->
        <div class="flex-1 flex flex-col lg:flex-row overflow-hidden">
            <!-- Left & Center: Items Catalog & Categories (65% width) -->
            <div class="flex-1 flex flex-col p-4 space-y-3 overflow-hidden border-l border-slate-800/80">
                <!-- Search & Category Filters Bar -->
                <div class="space-y-2.5 shrink-0">
                    <div class="relative flex items-center">
                        <input
                            ref="searchInputRef"
                            v-model="searchQuery"
                            type="text"
                            :placeholder="$t('pos.search_placeholder')"
                            class="w-full h-11 bg-slate-900 border border-slate-800 rounded-2xl pl-10 pr-4 text-xs font-bold text-white placeholder:text-slate-500 focus:outline-none focus:border-emerald-500 transition shadow-inner"
                        />
                        <button
                            v-if="searchQuery"
                            @click="searchQuery = ''"
                            type="button"
                            class="absolute left-3 w-6 h-6 rounded-lg bg-slate-800 text-slate-400 text-xs font-bold flex items-center justify-center"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Category Chips Scrollbar -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar text-xs">
                        <button
                            @click="selectedCategory = 'all'"
                            type="button"
                            class="px-3.5 py-1.5 rounded-xl font-bold transition shrink-0"
                            :class="selectedCategory === 'all' ? 'bg-emerald-500 text-slate-950 font-black shadow-md shadow-emerald-500/20' : 'bg-slate-900 text-slate-400 hover:bg-slate-800 hover:text-white border border-slate-800'"
                        >
                            {{ $t('common.all') }} ({{ items.length }})
                        </button>

                        <button
                            v-for="cat in categories"
                            :key="cat"
                            @click="selectedCategory = cat"
                            type="button"
                            class="px-3.5 py-1.5 rounded-xl font-bold transition shrink-0"
                            :class="selectedCategory === cat ? 'bg-emerald-500 text-slate-950 font-black shadow-md shadow-emerald-500/20' : 'bg-slate-900 text-slate-400 hover:bg-slate-800 hover:text-white border border-slate-800'"
                        >
                            {{ cat }}
                        </button>
                    </div>
                </div>

                <!-- Items Grid (SRP: Rendering with POSItemCard Component) -->
                <div class="flex-1 overflow-y-auto pr-1">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-2.5">
                        <POSItemCard
                            v-for="item in filteredItems"
                            :key="item.id"
                            :item="item"
                            :customer-price-tier="selectedCustomer?.price_tier || 'retail'"
                            @select="handleItemClick"
                        />
                    </div>

                    <div v-if="filteredItems.length === 0" class="py-16 text-center text-slate-500 text-xs font-bold">
                        {{ $t('common.no_data') }}
                    </div>
                </div>
            </div>

            <!-- Right Panel: Smart Cart & Checkout Engine (35% width) -->
            <div class="w-full lg:w-[420px] bg-slate-900/95 flex flex-col h-full border-t lg:border-t-0 shrink-0">
                <!-- Customer Selector Bar -->
                <div class="p-3 border-b border-slate-800 flex items-center justify-between gap-2 bg-slate-900">
                    <div
                        @click="showCustomerModal = true"
                        class="flex-1 flex items-center gap-2 p-2 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 cursor-pointer transition"
                    >
                        <span class="text-base">👤</span>
                        <div class="flex-1 truncate">
                            <div class="text-xs font-black text-white truncate">{{ selectedCustomer?.name || $t('pos.cash_customer') }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">
                                {{ selectedCustomer?.phone || $t('pos.cash_customer') }} • {{ $t('common.remaining') }}: {{ formatMoney(selectedCustomer?.current_balance) }} {{ $t('common.currency') }}
                            </div>
                        </div>
                        <span class="text-slate-400 text-xs">▼</span>
                    </div>

                    <button
                        @click="showNewCustomerModal = true"
                        type="button"
                        class="h-9 px-3 rounded-xl bg-indigo-600/20 hover:bg-indigo-600/30 border border-indigo-500/30 text-indigo-300 font-bold text-xs flex items-center gap-1 transition"
                        :title="$t('pos.add_new_customer')"
                    >
                        <span>➕</span>
                    </button>
                </div>

                <!-- Cart Line Items List (SRP: Rendering with POSCartItem Component) -->
                <div class="flex-1 overflow-y-auto p-3 space-y-2">
                    <POSCartItem
                        v-for="(line, idx) in cart"
                        :key="line.item_id"
                        :line="line"
                        :index="idx"
                        @remove="removeItem"
                        @apply-last-price="applyLastSoldPrice"
                        @change="autoSetCashPaid"
                    />

                    <div v-if="cart.length === 0" class="py-16 text-center text-slate-500 text-xs font-bold space-y-1">
                        <div class="text-2xl">🛒</div>
                        <div>{{ $t('pos.empty_cart') }}</div>
                    </div>
                </div>

                <!-- Financials & Payment Section (Fixed Bottom) -->
                <div class="p-3.5 bg-slate-900 border-t border-slate-800 space-y-3 shrink-0">
                    <!-- Subtotal & Discount -->
                    <div class="space-y-1.5 text-xs text-slate-400">
                        <div class="flex items-center justify-between">
                            <span>{{ $t('common.subtotal') }}:</span>
                            <span class="font-mono font-bold text-slate-200">{{ formatMoney(subtotal) }} {{ $t('common.currency') }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <span>{{ $t('common.discount') }}:</span>
                            <div class="flex items-center gap-1.5">
                                <button
                                    @click="discountType = discountType === 'fixed' ? 'percentage' : 'fixed'"
                                    type="button"
                                    class="px-1.5 py-0.5 rounded bg-slate-800 border border-slate-700 text-[10px] font-black text-slate-300"
                                >
                                    {{ discountType === 'fixed' ? $t('common.currency') : '%' }}
                                </button>
                                <input
                                    v-model.number="discountValue"
                                    type="number"
                                    min="0"
                                    class="w-16 h-6 bg-slate-800 border border-slate-700 rounded text-center text-xs font-mono font-bold text-white"
                                />
                                <span class="font-mono font-bold text-rose-400">-{{ formatMoney(discountAmount) }}</span>
                            </div>
                        </div>

                        <!-- Optional Additional Expenses Toggle -->
                        <div class="flex items-center justify-between">
                            <button
                                @click="showExpensesSection = !showExpensesSection"
                                type="button"
                                class="text-[11px] text-indigo-400 hover:underline font-bold flex items-center gap-1"
                            >
                                <span>🚚 {{ $t('pos.shipping_cost') }}:</span>
                                <span>{{ showExpensesSection ? '▲' : '▼' }}</span>
                            </button>
                            <span v-if="expensesTotal > 0" class="font-mono font-bold text-white">+{{ formatMoney(expensesTotal) }} {{ $t('common.currency') }}</span>
                        </div>

                        <!-- Expenses List Dropdown -->
                        <div v-if="showExpensesSection" class="p-2.5 rounded-xl bg-slate-800/60 space-y-2 border border-slate-700/60">
                            <div v-for="(exp, eIdx) in additionalExpenses" :key="eIdx" class="flex items-center gap-2">
                                <input
                                    v-model="exp.title"
                                    type="text"
                                    placeholder="البيان (مثال: شحن)"
                                    class="flex-1 h-7 bg-slate-900 border border-slate-700 rounded-lg px-2 text-[10px] text-white"
                                />
                                <input
                                    v-model.number="exp.amount"
                                    type="number"
                                    placeholder="المبلغ"
                                    class="w-20 h-7 bg-slate-900 border border-slate-700 rounded-lg px-2 text-[10px] font-mono text-white text-center"
                                />
                                <button @click="removeExpenseRow(eIdx)" type="button" class="text-rose-400 text-xs">✕</button>
                            </div>
                            <button
                                @click="addExpenseRow"
                                type="button"
                                class="text-[10px] font-bold text-indigo-400 hover:text-indigo-300"
                            >
                                + إضافة بند مصروف آخر
                            </button>
                        </div>

                        <div class="flex items-center justify-between pt-1 border-t border-slate-800 text-sm">
                            <span class="font-black text-white">{{ $t('common.net') }}:</span>
                            <span class="font-mono font-black text-lg text-emerald-400">{{ formatMoney(netTotal) }} {{ $t('common.currency') }}</span>
                        </div>
                    </div>

                    <!-- Payment Type Selection Chips -->
                    <div class="grid grid-cols-3 gap-1.5 text-xs">
                        <button
                            @click="paymentType = 'cash'; autoSetCashPaid()"
                            type="button"
                            class="py-1.5 rounded-xl font-black transition text-center"
                            :class="paymentType === 'cash' ? 'bg-emerald-500 text-slate-950' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'"
                        >
                            {{ $t('pos.payment_cash') }}
                        </button>
                        <button
                            @click="paymentType = 'credit'; autoSetCashPaid()"
                            type="button"
                            class="py-1.5 rounded-xl font-black transition text-center"
                            :class="paymentType === 'credit' ? 'bg-rose-500 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'"
                        >
                            {{ $t('pos.payment_credit') }}
                        </button>
                        <button
                            @click="paymentType = 'partial'; autoSetCashPaid()"
                            type="button"
                            class="py-1.5 rounded-xl font-black transition text-center"
                            :class="paymentType === 'partial' ? 'bg-amber-500 text-slate-950' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'"
                        >
                            {{ $t('pos.payment_partial') }}
                        </button>
                    </div>

                    <!-- Paid Amount & Change Indicator -->
                    <div v-if="paymentType !== 'credit'" class="flex items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-1.5 flex-1">
                            <span class="text-slate-400">{{ $t('common.paid') }}:</span>
                            <input
                                v-model.number="paidAmount"
                                type="number"
                                min="0"
                                class="w-full h-8 bg-slate-800 border border-slate-700 rounded-xl px-2 text-center text-xs font-mono font-black text-white focus:outline-none focus:border-emerald-500"
                            />
                        </div>

                        <div v-if="changeDue > 0" class="px-2 py-1 rounded-xl bg-amber-500/15 text-amber-300 font-mono font-black text-xs">
                            {{ $t('pos.change_due') }}: {{ formatMoney(changeDue) }} {{ $t('common.currency') }}
                        </div>
                    </div>

                    <!-- Error Alert -->
                    <div v-if="errorMessage" class="p-2.5 rounded-xl bg-rose-500/20 border border-rose-500/30 text-rose-300 text-xs font-bold">
                        ⚠️ {{ errorMessage }}
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2">
                        <button
                            @click="handleClearCart"
                            type="button"
                            class="w-11 h-11 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-rose-400 font-black text-sm flex items-center justify-center transition shrink-0"
                            :title="$t('pos.clear_cart')"
                        >
                            🗑️
                        </button>

                        <button
                            :disabled="isSubmitting || cart.length === 0"
                            @click="submitCheckout"
                            type="button"
                            class="flex-1 h-11 rounded-2xl bg-emerald-500 hover:bg-emerald-400 disabled:opacity-50 text-slate-950 font-black text-sm flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/25 transition transform active:scale-95"
                        >
                            <span>⚡</span>
                            <span>{{ isSubmitting ? 'جاري الحفظ...' : `${$t('pos.confirm_invoice')} (Enter)` }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Atomic Modals (SRP) -->
        <POSWeightPickerModal
            :show="showWeightModal"
            :item="activeWeightItem"
            :customer-price-tier="selectedCustomer?.price_tier || 'retail'"
            @close="showWeightModal = false"
            @confirm="handleWeightConfirm"
        />

        <POSCustomerPickerModal
            :show="showCustomerModal"
            :customers="customers"
            :selected-customer-id="selectedCustomer?.id"
            @close="showCustomerModal = false"
            @select="selectCustomer"
        />

        <POSQuickCustomerModal
            :show="showNewCustomerModal"
            @close="showNewCustomerModal = false"
            @created="handleCustomerCreated"
        />

        <POSSuccessModal
            :show="showSuccessModal"
            :invoice="completedInvoice"
            @close="showSuccessModal = false"
        />
    </div>
</template>
