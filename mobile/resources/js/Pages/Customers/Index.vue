<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import SkeletonCard from '@/Components/SkeletonCard.vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    customers: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref('');
const statusFilter = ref('all'); // all, with_debt, active
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showReceiptModal = ref(false);
const showActionSheet = ref(false);
const activeCustomer = ref(null);
const isFiltering = ref(false);

const handleSearch = () => {
    isFiltering.value = true;
    setTimeout(() => {
        isFiltering.value = false;
    }, 120);
};

const setFilter = (type) => {
    haptic.light();
    statusFilter.value = type;
    handleSearch();
};

const filteredCustomers = computed(() => {
    return props.customers.filter(c => {
        const matchesSearch = !search.value || 
            (c.name && c.name.toLowerCase().includes(search.value.toLowerCase())) ||
            (c.phone && c.phone.includes(search.value)) ||
            (c.code && c.code.toLowerCase().includes(search.value.toLowerCase()));

        if (!matchesSearch) return false;

        const bal = parseFloat(c.current_balance || 0);
        if (statusFilter.value === 'with_debt') {
            return bal > 0;
        }
        if (statusFilter.value === 'active') {
            return c.is_active;
        }

        return true;
    });
});

// Forms
const form = useForm({
    name: '',
    phone: '',
    address: '',
    credit_limit: 0,
    opening_balance: 0,
    notes: '',
});

const editForm = useForm({
    name: '',
    phone: '',
    address: '',
    credit_limit: 0,
    notes: '',
});

const receiptForm = useForm({
    customer_id: null,
    amount: '',
    payment_type: 'cash',
    notes: '',
});

const openActions = (c) => {
    haptic.light();
    activeCustomer.value = c;
    showActionSheet.value = true;
};

const openCreate = () => {
    haptic.light();
    form.reset();
    showCreateModal.value = true;
};

const submitCustomer = () => {
    form.post('/customers', {
        onSuccess: () => {
            haptic.success();
            showCreateModal.value = false;
            form.reset();
        }
    });
};

const openEditFromSheet = () => {
    if (!activeCustomer.value) return;
    const c = activeCustomer.value;
    editForm.name = c.name;
    editForm.phone = c.phone || '';
    editForm.address = c.address || '';
    editForm.credit_limit = c.credit_limit || 0;
    editForm.notes = c.notes || '';
    showActionSheet.value = false;
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!activeCustomer.value) return;
    editForm.put(`/customers/${activeCustomer.value.id}`, {
        onSuccess: () => {
            haptic.success();
            showEditModal.value = false;
        }
    });
};

const openReceiptFromSheet = () => {
    if (!activeCustomer.value) return;
    const c = activeCustomer.value;
    receiptForm.customer_id = c.id;
    receiptForm.amount = Math.max(0, parseFloat(c.current_balance || 0)).toString();
    receiptForm.notes = `تحصيل نقدية من ${c.name}`;
    showActionSheet.value = false;
    showReceiptModal.value = true;
};

const submitReceipt = () => {
    receiptForm.post('/payments/customer-receipt', {
        onSuccess: () => {
            haptic.success();
            showReceiptModal.value = false;
            receiptForm.reset();
        }
    });
};

const deleteCustomerFromSheet = () => {
    if (!activeCustomer.value) return;
    const c = activeCustomer.value;
    showActionSheet.value = false;
    haptic.heavy();
    if (confirm(`هل أنت متأكد من حذف/تعطيل حساب العميل (${c.name})؟`)) {
        router.delete(`/customers/${c.id}`, {
            onSuccess: () => {
                haptic.success();
            }
        });
    }
};

const getWhatsAppUrl = (c) => {
    if (!c || !c.phone) return '#';
    const cleanPhone = c.phone.replace(/[^0-9]/g, '');
    const phoneWithCode = cleanPhone.startsWith('01') ? '2' + cleanPhone : cleanPhone;
    const msg = `مرحباً ${c.name} ☕\nنود إحاطتكم بأن رصيد حسابكم الحالي هو: ${Number(c.current_balance || 0).toLocaleString('en-US')} ج.م لدى سرور كوفي.`;
    return `https://wa.me/${phoneWithCode}?text=${encodeURIComponent(msg)}`;
};
</script>

<template>
    <MobileLayout>
        <div class="space-y-4 pb-12">
            <!-- Top Action Bar -->
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>👥</span>
                        <span>دليل وحسابات الزباين</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        فلوسنا برة (الديون): <span class="font-bold text-rose-500 font-mono">{{ Number(summary.total_receivable || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span> ج.م
                    </p>
                </div>

                <button @click="openCreate" type="button" class="h-10 px-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-xs rounded-xl shadow-md flex items-center gap-1.5 transition touch-active">
                    <span>➕</span>
                    <span>عميل جديد</span>
                </button>
            </div>

            <!-- Search & Filter Controls -->
            <div class="space-y-2">
                <div class="relative">
                    <input
                        v-model="search"
                        @input="handleSearch"
                        type="text"
                        placeholder="بحث باسم العميل، الهاتف، أو الكود..."
                        class="w-full h-11 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl px-4 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none shadow-xs"
                    >
                    <button v-if="search" @click="search = ''; handleSearch();" class="absolute left-3 top-3 text-slate-400 text-xs">✕</button>
                </div>

                <!-- Filter Chips -->
                <div class="flex gap-2 overflow-x-auto pb-1 text-xs">
                    <button @click="setFilter('all')" type="button" class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap touch-active" :class="statusFilter === 'all' ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'">
                        الكل ({{ customers.length }})
                    </button>
                    <button @click="setFilter('with_debt')" type="button" class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap touch-active" :class="statusFilter === 'with_debt' ? 'bg-rose-600 text-white' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'">
                        عليهم فلوس (ديون) ⚠️
                    </button>
                    <button @click="setFilter('active')" type="button" class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap touch-active" :class="statusFilter === 'active' ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'">
                        النشطين فقط
                    </button>
                </div>
            </div>

            <!-- Skeleton Shimmer During Filtering -->
            <div v-if="isFiltering">
                <SkeletonCard :count="3" :lines="2" />
            </div>

            <!-- Clean & Spacious Customer Cards List -->
            <div v-else class="space-y-3">
                <div
                    v-for="customer in filteredCustomers"
                    :key="customer.id"
                    class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500/50 transition space-y-3"
                >
                    <!-- Header Info Row -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-bold text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md">
                                    {{ customer.code || ('#CUST-' + customer.id) }}
                                </span>
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white truncate">
                                    {{ customer.name }}
                                </h3>
                            </div>

                            <div v-if="customer.phone" class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 font-mono">
                                <span>📞</span>
                                <a :href="'tel:' + customer.phone" class="hover:underline text-emerald-600 dark:text-emerald-400 font-bold">
                                    {{ customer.phone }}
                                </a>
                            </div>

                            <div v-if="customer.address" class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1 truncate">
                                <span>📍</span>
                                <span>{{ customer.address }}</span>
                            </div>
                        </div>

                        <!-- Current Balance Badge -->
                        <div class="text-end shrink-0">
                            <div class="text-[10px] text-slate-400 font-bold">المطلوب منه</div>
                            <div class="text-base font-black font-mono leading-tight" :class="parseFloat(customer.current_balance) > 0 ? 'text-rose-500' : (parseFloat(customer.current_balance) < 0 ? 'text-emerald-500' : 'text-slate-500')">
                                {{ Number(customer.current_balance || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                            </div>
                            <div class="text-[9px] text-slate-400 font-semibold">ج.م</div>
                        </div>
                    </div>

                    <!-- Clean 2-Button Action Bar (Main Action + Native ⋯ Menu) -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
                        <!-- Primary Action: Statement Link -->
                        <Link
                            :href="'/customers/' + customer.id + '/statement'"
                            class="flex-1 h-9 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 transition touch-active"
                        >
                            <span>📑</span>
                            <span>كشف الحساب والعمليات</span>
                        </Link>

                        <!-- Secondary Quick Action: Quick Collection if has debt -->
                        <button
                            v-if="parseFloat(customer.current_balance) > 0"
                            @click="activeCustomer = customer; openReceiptFromSheet()"
                            type="button"
                            class="h-9 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl flex items-center gap-1 shadow-xs transition touch-active shrink-0"
                        >
                            <span>💵</span>
                            <span>تحصيل</span>
                        </button>

                        <!-- The Native Action Sheet Trigger Button (⋯) -->
                        <button
                            @click="openActions(customer)"
                            type="button"
                            class="w-9 h-9 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl flex items-center justify-center text-sm font-black transition touch-active shrink-0 border border-slate-200 dark:border-slate-700"
                            title="خيارات إضافية"
                        >
                            ⋯
                        </button>
                    </div>
                </div>

                <div v-if="filteredCustomers.length === 0" class="text-center py-10 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <div class="text-3xl mb-2">👥</div>
                    <div class="text-sm font-bold text-slate-700 dark:text-slate-300">لا يوجد عملاء يطابقون البحث</div>
                    <p class="text-xs text-slate-400 mt-1">اضغط على زر "عميل جديد" لإضافة عميل</p>
                </div>
            </div>

            <!-- Native Bottom Action Sheet for Customer (Infinite Scalability) -->
            <div
                v-if="showActionSheet"
                @click="showActionSheet = false"
                class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-end justify-center select-none animate-in fade-in duration-150"
            >
                <div
                    @click.stop
                    class="w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 shadow-2xl p-5 pb-8 space-y-4 animate-in slide-in-from-bottom duration-200"
                >
                    <!-- Drag Indicator Handle -->
                    <div class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto -mt-2 mb-2"></div>

                    <!-- Sheet Header with Customer Profile Info -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <div class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span>👤</span>
                                <span>{{ activeCustomer?.name }}</span>
                            </div>
                            <div class="text-xs text-slate-400 font-mono mt-0.5">{{ activeCustomer?.phone || 'بدون هاتف' }}</div>
                        </div>

                        <div class="text-end">
                            <div class="text-[10px] text-slate-400 font-bold">الرصيد الحالي</div>
                            <div class="text-sm font-black font-mono" :class="parseFloat(activeCustomer?.current_balance || 0) > 0 ? 'text-rose-500' : 'text-emerald-500'">
                                {{ Number(activeCustomer?.current_balance || 0).toLocaleString('en-US') }} ج.م
                            </div>
                        </div>
                    </div>

                    <!-- Action List Items (Spacious, Beautiful & Scalable) -->
                    <div class="space-y-1.5">
                        <!-- 1. Statement -->
                        <Link
                            :href="'/customers/' + activeCustomer?.id + '/statement'"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center text-sm font-bold">📑</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">كشف الحساب والعمليات</div>
                                    <div class="text-[10px] text-slate-400">عرض فواتير المبيعات وسجل المدفوعات</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </Link>

                        <!-- 2. Quick Receipt -->
                        <button
                            @click="openReceiptFromSheet"
                            type="button"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-sm font-bold">💵</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">سند تحصيل نقدية (قبض)</div>
                                    <div class="text-[10px] text-slate-400">استلام مبالغ وتسديد ديون العميل فوراً</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </button>

                        <!-- 3. Edit Profile -->
                        <button
                            @click="openEditFromSheet"
                            type="button"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-sm font-bold">✏️</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">تعديل بيانات العميل</div>
                                    <div class="text-[10px] text-slate-400">تحديث الاسم، الهاتف، العنوان، وحد الائتمان</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </button>

                        <!-- 4. WhatsApp Share -->
                        <a
                            v-if="activeCustomer?.phone"
                            :href="getWhatsAppUrl(activeCustomer)"
                            target="_blank"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-sm font-bold">💬</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">مراسلة عبر واتساب</div>
                                    <div class="text-[10px] text-slate-400">إرسال كشف رصيد الحساب للعميل</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </a>

                        <!-- 5. Phone Call -->
                        <a
                            v-if="activeCustomer?.phone"
                            :href="'tel:' + activeCustomer?.phone"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-slate-500/10 text-slate-500 flex items-center justify-center text-sm font-bold">📞</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">اتصال هاتفي مباشر</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ activeCustomer?.phone }}</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </a>

                        <!-- 6. Delete Customer -->
                        <button
                            @click="deleteCustomerFromSheet"
                            type="button"
                            class="w-full p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 flex items-center justify-between text-start transition touch-active text-rose-600 dark:text-rose-400"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-rose-500/15 text-rose-600 flex items-center justify-center text-sm font-bold">🗑️</div>
                                <div>
                                    <div class="text-xs font-black">حذف أو تعطيل العميل</div>
                                    <div class="text-[10px] text-rose-400">إزالة العميل من القائمة والبحث</div>
                                </div>
                            </div>
                            <span class="font-bold">‹</span>
                        </button>
                    </div>

                    <!-- Cancel / Close Sheet -->
                    <button
                        @click="showActionSheet = false"
                        type="button"
                        class="w-full py-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-2xl transition touch-active"
                    >
                        إغلاق القائمة
                    </button>
                </div>
            </div>

            <!-- Create Modal -->
            <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4 animate-in fade-in duration-150">
                <div class="bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl w-full max-w-md p-5 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-3 animate-in slide-in-from-bottom duration-200">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-base font-black text-slate-900 dark:text-white">إضافة عميل جديد 👤</h3>
                        <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                    </div>

                    <form @submit.prevent="submitCustomer" class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم العميل / المحل *:</label>
                            <input v-model="form.name" type="text" placeholder="مثال: مطحن وكافيه الأندلس" required class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            <span v-if="form.errors.name" class="text-rose-500 text-[11px] font-bold">{{ form.errors.name }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">رقم الهاتف:</label>
                                <input v-model="form.phone" type="text" dir="ltr" placeholder="010xxxxxxxx" class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-mono font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الرصيد الافتتاحي:</label>
                                <input v-model="form.opening_balance" type="number" step="0.001" dir="ltr" placeholder="0.00" class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-mono font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">العنوان / المنطقة:</label>
                            <input v-model="form.address" type="text" placeholder="مثال: العاشر من رمضان" class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" :disabled="form.processing" class="flex-1 h-12 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-xs rounded-xl shadow-md flex items-center justify-center gap-1.5 transition touch-active">
                                <span>{{ form.processing ? 'جاري الحفظ...' : 'حفظ العميل' }}</span>
                            </button>
                            <button @click="showCreateModal = false" type="button" class="h-12 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition touch-active">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Modal -->
            <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4 animate-in fade-in duration-150">
                <div class="bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl w-full max-w-md p-5 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-3 animate-in slide-in-from-bottom duration-200">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-base font-black text-slate-900 dark:text-white">تعديل بيانات العميل ✏️</h3>
                        <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                    </div>

                    <form @submit.prevent="submitEdit" class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم العميل *:</label>
                            <input v-model="editForm.name" type="text" required class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">رقم الهاتف:</label>
                                <input v-model="editForm.phone" type="text" dir="ltr" class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-mono font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">حد الائتمان:</label>
                                <input v-model="editForm.credit_limit" type="number" dir="ltr" class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-mono font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">العنوان:</label>
                            <input v-model="editForm.address" type="text" class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" :disabled="editForm.processing" class="flex-1 h-12 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-extrabold text-xs rounded-xl shadow-md flex items-center justify-center gap-1.5 transition touch-active">
                                <span>{{ editForm.processing ? 'جاري التعديل...' : 'حفظ التعديلات' }}</span>
                            </button>
                            <button @click="showEditModal = false" type="button" class="h-12 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition touch-active">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Receipt Modal -->
            <div v-if="showReceiptModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4 animate-in fade-in duration-150">
                <div class="bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl w-full max-w-md p-5 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-3 animate-in slide-in-from-bottom duration-200">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">تحصيل فلوس من الزبون 💵</h3>
                            <p class="text-[11px] text-slate-400 font-bold">{{ activeCustomer?.name }}</p>
                        </div>
                        <button @click="showReceiptModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                    </div>

                    <form @submit.prevent="submitReceipt" class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">المبلغ المحصل (ج.م) *:</label>
                            <input v-model="receiptForm.amount" type="number" step="0.001" required dir="ltr" class="w-full h-12 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-4 text-base font-mono font-black text-emerald-600 dark:text-emerald-400 focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ملاحظات التحصيل:</label>
                            <input v-model="receiptForm.notes" type="text" class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" :disabled="receiptForm.processing" class="flex-1 h-12 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-xs rounded-xl shadow-md flex items-center justify-center gap-1.5 transition touch-active">
                                <span>{{ receiptForm.processing ? 'جاري الحفظ...' : 'تأكيد وقبض الفلوس ✓' }}</span>
                            </button>
                            <button @click="showReceiptModal = false" type="button" class="h-12 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition touch-active">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
