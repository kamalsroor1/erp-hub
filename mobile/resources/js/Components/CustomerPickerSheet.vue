<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    isOpen: Boolean,
    customers: Array,
    selectedId: [Number, String],
});

const emit = defineEmits(['close', 'select', 'created']);

const mode = ref('list'); // 'list' | 'create'
const search = ref('');
const isSaving = ref(false);
const errorMessage = ref('');

// New Customer Form State
const newCust = ref({
    name: '',
    phone: '',
    address: '',
    opening_balance: 0,
    notes: '',
});

const resetForm = () => {
    newCust.value = {
        name: '',
        phone: '',
        address: '',
        opening_balance: 0,
        notes: '',
    };
    errorMessage.value = '';
    mode.value = 'list';
};

const filteredCustomers = computed(() => {
    if (!search.value) return props.customers || [];
    const q = search.value.toLowerCase().trim();
    return (props.customers || []).filter(c => 
        (c.name && c.name.toLowerCase().includes(q)) ||
        (c.phone && c.phone.includes(q))
    );
});

const selectCustomer = (customer) => {
    emit('select', customer);
    emit('close');
};

const submitNewCustomer = async () => {
    if (!newCust.value.name.trim()) {
        errorMessage.value = 'يرجى كتابة اسم العميل';
        return;
    }

    isSaving.value = true;
    errorMessage.value = '';

    try {
        const response = await fetch('/customers', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify(newCust.value),
        });

        const data = await response.json();

        if (data && (data.success || data.customer)) {
            const created = data.customer || {
                id: Date.now(),
                name: newCust.value.name,
                phone: newCust.value.phone,
                current_balance: newCust.value.opening_balance || '0.000',
            };
            emit('created', created);
            emit('select', created);
            resetForm();
            emit('close');
        } else {
            errorMessage.value = data?.message || 'فشل إضافة العميل';
        }
    } catch (e) {
        errorMessage.value = 'حدث خطأ أثناء حفظ العميل';
    } finally {
        isSaving.value = false;
    }
};
</script>

<template>
    <!-- Backdrop -->
    <Transition
        enter-active-class="transition duration-250 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isOpen"
            @click="$emit('close')"
            class="fixed inset-0 z-[70] bg-slate-950/75 backdrop-blur-xs flex items-end justify-center select-none"
        >
            <!-- Bottom Sheet -->
            <div
                @click.stop
                class="w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 shadow-2xl p-4 pb-8 space-y-3.5 max-h-[88vh] flex flex-col animate-slide-up"
            >
                <!-- Drag Handle Pill -->
                <div class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto -mt-1 mb-1"></div>

                <!-- Sheet Header -->
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-500 flex items-center justify-center text-base font-black">
                            {{ mode === 'create' ? '➕' : '👤' }}
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">
                                {{ mode === 'create' ? 'إضافة عميل جديد وتحديده' : 'تحديد عميل الفاتورة' }}
                            </h3>
                            <p class="text-[10px] text-slate-400 font-bold">
                                {{ mode === 'create' ? 'سجل بيانات العميل للاختيار الفوري' : 'اختر العميل لتسجيل الفاتورة أو المديونية' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button
                            v-if="mode === 'list'"
                            @click="mode = 'create'"
                            type="button"
                            class="px-2.5 py-1 rounded-xl bg-emerald-600 text-white font-black text-[11px] flex items-center gap-1 touch-active shadow-xs"
                        >
                            <span>➕</span>
                            <span>عميل جديد</span>
                        </button>

                        <button
                            v-else
                            @click="mode = 'list'"
                            type="button"
                            class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold text-[11px] touch-active"
                        >
                            رجوع للقائمة
                        </button>

                        <button
                            @click="$emit('close')"
                            type="button"
                            class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 flex items-center justify-center text-xs font-bold"
                        >
                            ✕
                        </button>
                    </div>
                </div>

                <!-- MODE 1: CUSTOMER SELECTION LIST -->
                <div v-if="mode === 'list'" class="space-y-3 flex-1 flex flex-col min-h-0">
                    <!-- Instant Search Input -->
                    <div class="relative">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="ابحث باسم العميل أو رقم الهاتف..."
                            class="w-full h-11 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl ps-10 pe-4 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-emerald-500"
                        >
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 text-slate-400 text-sm">
                            🔍
                        </div>
                        <button
                            v-if="search"
                            @click="search = ''"
                            type="button"
                            class="absolute inset-y-0 end-0 flex items-center pe-3 text-slate-400 text-xs font-bold"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Quick Cash Customer Button -->
                    <button
                        @click="selectCustomer(customers?.find(c => c.phone === '01000000000') || customers?.[0])"
                        type="button"
                        class="w-full p-2.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 flex items-center justify-between transition touch-active"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-lg">💵</span>
                            <div class="text-start">
                                <div class="text-xs font-black">عميل نقدي عام (تجزئة المطحنة)</div>
                                <div class="text-[10px] text-emerald-600/70 dark:text-emerald-400/70 font-semibold">مبيعات الكاش المباشرة بدون تسجيل حساب</div>
                            </div>
                        </div>
                        <span class="text-xs font-black px-2 py-0.5 rounded-lg bg-emerald-500 text-white shadow-xs">
                            سريع ⚡
                        </span>
                    </button>

                    <!-- Customers Scrollable List -->
                    <div class="flex-1 overflow-y-auto space-y-2 max-h-64 pe-0.5">
                        <button
                            v-for="c in filteredCustomers"
                            :key="c.id"
                            @click="selectCustomer(c)"
                            type="button"
                            class="w-full text-start p-3 rounded-2xl border transition flex items-center justify-between gap-2.5 touch-active"
                            :class="c.id === selectedId ? 'bg-emerald-500/10 border-emerald-500 dark:border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'bg-white dark:bg-slate-800/60 border-slate-200 dark:border-slate-700/60 text-slate-800 dark:text-slate-200 hover:border-slate-300'"
                        >
                            <div class="flex items-center gap-2.5 truncate">
                                <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center text-xs font-black shrink-0 font-mono">
                                    {{ c.name.charAt(0) }}
                                </div>
                                <div class="truncate">
                                    <div class="text-xs font-extrabold text-slate-900 dark:text-white truncate">{{ c.name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono font-bold">{{ c.phone || 'بدون هاتف' }}</div>
                                </div>
                            </div>

                            <!-- Balance Indicator -->
                            <div class="text-end shrink-0">
                                <div
                                    class="text-xs font-black font-mono"
                                    :class="parseFloat(c.current_balance) > 0 ? 'text-rose-500' : (parseFloat(c.current_balance) < 0 ? 'text-emerald-500' : 'text-slate-400')"
                                >
                                    {{ Number(c.current_balance || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                                </div>
                                <div class="text-[9px] text-slate-400 font-semibold">
                                    {{ parseFloat(c.current_balance) > 0 ? 'مديونية' : (parseFloat(c.current_balance) < 0 ? 'له رصيد' : 'خالص') }}
                                </div>
                            </div>
                        </button>

                        <div v-if="filteredCustomers.length === 0" class="text-center py-6 text-xs text-slate-400">
                            لا يوجد عميل بهذا الاسم - اضغط على "عميل جديد +" لإضافته فوراً
                        </div>
                    </div>
                </div>

                <!-- MODE 2: INLINE QUICK CUSTOMER CREATION FORM -->
                <div v-else class="space-y-3 flex-1 flex flex-col justify-between">
                    <div class="space-y-2.5">
                        <div v-if="errorMessage" class="p-2.5 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-500 text-xs font-bold">
                            ⚠️ {{ errorMessage }}
                        </div>

                        <!-- Name -->
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 dark:text-slate-300 mb-1">
                                اسم العميل / المحل <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="newCust.name"
                                type="text"
                                placeholder="مثال: كافيه الأهرام أو الأستاذ محمد"
                                class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-3.5 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-emerald-500"
                            >
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 dark:text-slate-300 mb-1">
                                رقم الهاتف
                            </label>
                            <input
                                v-model="newCust.phone"
                                type="tel"
                                placeholder="01xxxxxxxxx"
                                class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-3.5 text-xs font-mono font-bold text-slate-900 dark:text-white outline-none focus:border-emerald-500"
                            >
                        </div>

                        <!-- Address -->
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 dark:text-slate-300 mb-1">
                                العنوان / المنطقة
                            </label>
                            <input
                                v-model="newCust.address"
                                type="text"
                                placeholder="مثال: الزقازيق - القومية"
                                class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-3.5 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-emerald-500"
                            >
                        </div>

                        <!-- Opening Balance -->
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 dark:text-slate-300 mb-1">
                                رصيد مديونية سابق (إن وجد)
                            </label>
                            <input
                                v-model="newCust.opening_balance"
                                type="number"
                                step="0.001"
                                placeholder="0.00"
                                class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-3.5 text-xs font-mono font-bold text-slate-900 dark:text-white outline-none focus:border-emerald-500"
                            >
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-2 flex items-center gap-2">
                        <button
                            @click="submitNewCustomer"
                            type="button"
                            :disabled="isSaving"
                            class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-2xl shadow-lg shadow-emerald-600/30 transition touch-active flex items-center justify-center gap-1.5"
                        >
                            <span>{{ isSaving ? 'جاري الحفظ...' : 'حفظ واختيار العميل ✓' }}</span>
                        </button>

                        <button
                            @click="resetForm"
                            type="button"
                            class="py-3 px-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-2xl touch-active"
                        >
                            إلغاء
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
