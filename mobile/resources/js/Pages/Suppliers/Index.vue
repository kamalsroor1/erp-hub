<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import SkeletonCard from '@/Components/SkeletonCard.vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    suppliers: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref('');
const statusFilter = ref('all'); // all, with_balance, active
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDisburseModal = ref(false);
const showActionSheet = ref(false);
const activeSupplier = ref(null);
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

const filteredSuppliers = computed(() => {
    return props.suppliers.filter(s => {
        const matchesSearch = !search.value || 
            (s.name && s.name.toLowerCase().includes(search.value.toLowerCase())) ||
            (s.phone && s.phone.includes(search.value)) ||
            (s.code && s.code.toLowerCase().includes(search.value.toLowerCase()));

        if (!matchesSearch) return false;

        const bal = parseFloat(s.current_balance || 0);
        if (statusFilter.value === 'with_balance') {
            return bal > 0;
        }
        if (statusFilter.value === 'active') {
            return s.is_active;
        }

        return true;
    });
});

// Forms
const form = useForm({
    name: '',
    phone: '',
    address: '',
    opening_balance: 0,
    notes: '',
});

const editForm = useForm({
    name: '',
    phone: '',
    address: '',
    notes: '',
});

const disburseForm = useForm({
    supplier_id: null,
    amount: '',
    payment_type: 'cash',
    notes: '',
});

const openActions = (s) => {
    haptic.light();
    activeSupplier.value = s;
    showActionSheet.value = true;
};

const openCreate = () => {
    haptic.light();
    form.reset();
    showCreateModal.value = true;
};

const submitSupplier = () => {
    form.post('/suppliers', {
        onSuccess: () => {
            haptic.success();
            showCreateModal.value = false;
            form.reset();
        }
    });
};

const openEditFromSheet = () => {
    if (!activeSupplier.value) return;
    const s = activeSupplier.value;
    editForm.name = s.name;
    editForm.phone = s.phone || '';
    editForm.address = s.address || '';
    editForm.notes = s.notes || '';
    showActionSheet.value = false;
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!activeSupplier.value) return;
    editForm.put(`/suppliers/${activeSupplier.value.id}`, {
        onSuccess: () => {
            haptic.success();
            showEditModal.value = false;
        }
    });
};

const openDisburseFromSheet = () => {
    if (!activeSupplier.value) return;
    const s = activeSupplier.value;
    disburseForm.supplier_id = s.id;
    disburseForm.amount = Math.max(0, parseFloat(s.current_balance || 0)).toString();
    disburseForm.notes = `سداد دفعة نقدية للمورد ${s.name}`;
    showActionSheet.value = false;
    showDisburseModal.value = true;
};

const submitDisburse = () => {
    disburseForm.post('/payments/supplier-voucher', {
        onSuccess: () => {
            haptic.success();
            showDisburseModal.value = false;
            disburseForm.reset();
        }
    });
};

const deleteSupplierFromSheet = () => {
    if (!activeSupplier.value) return;
    const s = activeSupplier.value;
    showActionSheet.value = false;
    haptic.heavy();
    if (confirm(`هل أنت متأكد من حذف/تعطيل حساب المورد (${s.name})؟`)) {
        router.delete(`/suppliers/${s.id}`, {
            onSuccess: () => {
                haptic.success();
            }
        });
    }
};

const getWhatsAppUrl = (s) => {
    if (!s || !s.phone) return '#';
    const cleanPhone = s.phone.replace(/[^0-9]/g, '');
    const phoneWithCode = cleanPhone.startsWith('01') ? '2' + cleanPhone : cleanPhone;
    const msg = `مرحباً ${s.name} ☕\nتحياتنا من سرور كوفي بخصوص الحساب ومستحقات التوريد.`;
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
                        <span>🏭</span>
                        <span>دليل الموردين وخامات البن</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        فلوس علينا للموردين: <span class="font-bold text-amber-500 font-mono">{{ Number(summary.total_payable || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span> ج.م
                    </p>
                </div>

                <button @click="openCreate" type="button" class="h-10 px-3.5 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-extrabold text-xs rounded-xl shadow-md flex items-center gap-1.5 transition touch-active">
                    <span>➕</span>
                    <span>مورد جديد</span>
                </button>
            </div>

            <!-- Search & Filter Controls -->
            <div class="space-y-2">
                <div class="relative">
                    <input
                        v-model="search"
                        @input="handleSearch"
                        type="text"
                        placeholder="بحث باسم المورد، الهاتف، أو الكود..."
                        class="w-full h-11 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl px-4 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none shadow-xs"
                    >
                    <button v-if="search" @click="search = ''; handleSearch();" class="absolute left-3 top-3 text-slate-400 text-xs">✕</button>
                </div>

                <!-- Filter Chips -->
                <div class="flex gap-2 overflow-x-auto pb-1 text-xs">
                    <button @click="setFilter('all')" type="button" class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap touch-active" :class="statusFilter === 'all' ? 'bg-amber-600 text-white' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'">
                        الكل ({{ suppliers.length }})
                    </button>
                    <button @click="setFilter('with_balance')" type="button" class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap touch-active" :class="statusFilter === 'with_balance' ? 'bg-amber-600 text-white' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'">
                        مستحقات واجبة السداد ⚠️
                    </button>
                    <button @click="setFilter('active')" type="button" class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap touch-active" :class="statusFilter === 'active' ? 'bg-amber-600 text-white' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'">
                        النشطين فقط
                    </button>
                </div>
            </div>

            <!-- Skeleton Shimmer During Filtering -->
            <div v-if="isFiltering">
                <SkeletonCard :count="3" :lines="2" />
            </div>

            <!-- Suppliers Cards List -->
            <div v-else class="space-y-3">
                <div
                    v-for="supplier in filteredSuppliers"
                    :key="supplier.id"
                    class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs hover:border-amber-500/50 transition space-y-3"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-bold text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md">
                                    {{ supplier.code || ('#SUPP-' + supplier.id) }}
                                </span>
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white truncate">
                                    {{ supplier.name }}
                                </h3>
                            </div>

                            <div v-if="supplier.phone" class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 font-mono">
                                <span>📞</span>
                                <a :href="'tel:' + supplier.phone" class="hover:underline text-amber-600 dark:text-amber-400 font-bold">
                                    {{ supplier.phone }}
                                </a>
                            </div>

                            <div v-if="supplier.address" class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1 truncate">
                                <span>📍</span>
                                <span>{{ supplier.address }}</span>
                            </div>
                        </div>

                        <!-- Balance Badge -->
                        <div class="text-end shrink-0">
                            <div class="text-[10px] text-slate-400 font-bold">مستحق له</div>
                            <div class="text-base font-black font-mono leading-tight" :class="parseFloat(supplier.current_balance) > 0 ? 'text-amber-500' : 'text-slate-500'">
                                {{ Number(supplier.current_balance || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                            </div>
                            <div class="text-[9px] text-slate-400 font-semibold">ج.م</div>
                        </div>
                    </div>

                    <!-- Clean 2-Button Action Bar (Main Action + Native ⋯ Menu) -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
                        <!-- Primary: Statement -->
                        <Link
                            :href="'/suppliers/' + supplier.id + '/statement'"
                            class="flex-1 h-9 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 text-amber-700 dark:text-amber-300 font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 transition touch-active"
                        >
                            <span>📑</span>
                            <span>كشف الحساب والتوريدات</span>
                        </Link>

                        <!-- Quick Disburse if has balance -->
                        <button
                            v-if="parseFloat(supplier.current_balance) > 0"
                            @click="activeSupplier = supplier; openDisburseFromSheet()"
                            type="button"
                            class="h-9 px-3 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl flex items-center gap-1 shadow-xs transition touch-active shrink-0"
                        >
                            <span>💵</span>
                            <span>سداد</span>
                        </button>

                        <!-- Native Action Sheet Trigger Button (⋯) -->
                        <button
                            @click="openActions(supplier)"
                            type="button"
                            class="w-9 h-9 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl flex items-center justify-center text-sm font-black transition touch-active shrink-0 border border-slate-200 dark:border-slate-700"
                            title="خيارات إضافية"
                        >
                            ⋯
                        </button>
                    </div>
                </div>

                <div v-if="filteredSuppliers.length === 0" class="text-center py-10 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <div class="text-3xl mb-2">🏭</div>
                    <div class="text-sm font-bold text-slate-700 dark:text-slate-300">لا يوجد موردين يطابقون البحث</div>
                    <p class="text-xs text-slate-400 mt-1">اضغط على زر "مورد جديد" لإضافة مورد</p>
                </div>
            </div>

            <!-- Native Bottom Action Sheet for Supplier -->
            <div
                v-if="showActionSheet"
                @click="showActionSheet = false"
                class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-end justify-center select-none animate-in fade-in duration-150"
            >
                <div
                    @click.stop
                    class="w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 shadow-2xl p-5 pb-8 space-y-4 animate-in slide-in-from-bottom duration-200"
                >
                    <!-- Drag Handle -->
                    <div class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto -mt-2 mb-2"></div>

                    <!-- Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <div class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span>🏭</span>
                                <span>{{ activeSupplier?.name }}</span>
                            </div>
                            <div class="text-xs text-slate-400 font-mono mt-0.5">{{ activeSupplier?.phone || 'بدون هاتف' }}</div>
                        </div>

                        <div class="text-end">
                            <div class="text-[10px] text-slate-400 font-bold">المستحق له</div>
                            <div class="text-sm font-black font-mono text-amber-500">
                                {{ Number(activeSupplier?.current_balance || 0).toLocaleString('en-US') }} ج.م
                            </div>
                        </div>
                    </div>

                    <!-- Action List -->
                    <div class="space-y-1.5">
                        <!-- 1. Statement -->
                        <Link
                            :href="'/suppliers/' + activeSupplier?.id + '/statement'"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center text-sm font-bold">📑</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">كشف حساب وتوريدات المورد</div>
                                    <div class="text-[10px] text-slate-400">سجل فواتير الشراء ودفعات السداد</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </Link>

                        <!-- 2. Disburse Payment -->
                        <button
                            @click="openDisburseFromSheet"
                            type="button"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-sm font-bold">💵</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">سند صرف / سداد دفعة للمورد</div>
                                    <div class="text-[10px] text-slate-400">دفع نقدية من الخزينة لتسوية الحساب</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </button>

                        <!-- 3. Edit -->
                        <button
                            @click="openEditFromSheet"
                            type="button"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-sm font-bold">✏️</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">تعديل بيانات المورد</div>
                                    <div class="text-[10px] text-slate-400">تحديث الاسم، الهاتف، والعنوان</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </button>

                        <!-- 4. WhatsApp -->
                        <a
                            v-if="activeSupplier?.phone"
                            :href="getWhatsAppUrl(activeSupplier)"
                            target="_blank"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-sm font-bold">💬</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">مراسلة عبر WhatsApp</div>
                                    <div class="text-[10px] text-slate-400">إرسال رسالة مباشرة للمورد</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </a>

                        <!-- 5. Phone Call -->
                        <a
                            v-if="activeSupplier?.phone"
                            :href="'tel:' + activeSupplier?.phone"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-slate-500/10 text-slate-500 flex items-center justify-center text-sm font-bold">📞</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">اتصال هاتفي مباشر</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ activeSupplier?.phone }}</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </a>

                        <!-- 6. Delete Supplier -->
                        <button
                            @click="deleteSupplierFromSheet"
                            type="button"
                            class="w-full p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 flex items-center justify-between text-start transition touch-active text-rose-600 dark:text-rose-400"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-rose-500/15 text-rose-600 flex items-center justify-center text-sm font-bold">🗑️</div>
                                <div>
                                    <div class="text-xs font-black">حذف أو تعطيل المورد</div>
                                    <div class="text-[10px] text-rose-400">إزالة المورد من القائمة</div>
                                </div>
                            </div>
                            <span class="font-bold">‹</span>
                        </button>
                    </div>

                    <!-- Close Sheet -->
                    <button
                        @click="showActionSheet = false"
                        type="button"
                        class="w-full py-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-2xl transition touch-active"
                    >
                        إغلاق القائمة
                    </button>
                </div>
            </div>

            <!-- Create Supplier Modal -->
            <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4 animate-in fade-in duration-150">
                <div class="bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl w-full max-w-md p-5 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-3 animate-in slide-in-from-bottom duration-200">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-base font-black text-slate-900 dark:text-white">إضافة مورد جديد 🏭</h3>
                        <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                    </div>

                    <form @submit.prevent="submitSupplier" class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم المورد / الشركة *:</label>
                            <input v-model="form.name" type="text" placeholder="مثال: شركة النيل لتجارة البن" required class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">رقم الهاتف:</label>
                                <input v-model="form.phone" type="text" dir="ltr" placeholder="010xxxxxxxx" class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-mono font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الرصيد الافتتاحي:</label>
                                <input v-model="form.opening_balance" type="number" step="0.001" dir="ltr" placeholder="0.00" class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-mono font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">العنوان:</label>
                            <input v-model="form.address" type="text" placeholder="مثال: ميناء الإسكندرية" class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" :disabled="form.processing" class="flex-1 h-12 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-extrabold text-xs rounded-xl shadow-md flex items-center justify-center gap-1.5 transition touch-active">
                                <span>{{ form.processing ? 'جاري الحفظ...' : 'حفظ المورد' }}</span>
                            </button>
                            <button @click="showCreateModal = false" type="button" class="h-12 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition touch-active">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Supplier Modal -->
            <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4 animate-in fade-in duration-150">
                <div class="bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl w-full max-w-md p-5 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-3 animate-in slide-in-from-bottom duration-200">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-base font-black text-slate-900 dark:text-white">تعديل بيانات المورد ✏️</h3>
                        <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                    </div>

                    <form @submit.prevent="submitEdit" class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم المورد *:</label>
                            <input v-model="editForm.name" type="text" required class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">رقم الهاتف:</label>
                            <input v-model="editForm.phone" type="text" dir="ltr" class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-mono font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">العنوان:</label>
                            <input v-model="editForm.address" type="text" class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" :disabled="editForm.processing" class="flex-1 h-12 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-extrabold text-xs rounded-xl shadow-md flex items-center justify-center gap-1.5 transition touch-active">
                                <span>{{ editForm.processing ? 'جاري التعديل...' : 'حفظ التعديلات' }}</span>
                            </button>
                            <button @click="showEditModal = false" type="button" class="h-12 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition touch-active">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Disburse Modal -->
            <div v-if="showDisburseModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4 animate-in fade-in duration-150">
                <div class="bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl w-full max-w-md p-5 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-3 animate-in slide-in-from-bottom duration-200">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">سداد دفعة نقدية للمورد 💵</h3>
                            <p class="text-[11px] text-slate-400 font-bold">{{ activeSupplier?.name }}</p>
                        </div>
                        <button @click="showDisburseModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                    </div>

                    <form @submit.prevent="submitDisburse" class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">المبلغ المدفوع (ج.م) *:</label>
                            <input v-model="disburseForm.amount" type="number" step="0.001" required dir="ltr" class="w-full h-12 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-4 text-base font-mono font-black text-amber-600 dark:text-amber-400 focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ملاحظات الصرف:</label>
                            <input v-model="disburseForm.notes" type="text" class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" :disabled="disburseForm.processing" class="flex-1 h-12 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-extrabold text-xs rounded-xl shadow-md flex items-center justify-center gap-1.5 transition touch-active">
                                <span>{{ disburseForm.processing ? 'جاري الحفظ...' : 'تأكيد وصرف المبلغ ✓' }}</span>
                            </button>
                            <button @click="showDisburseModal = false" type="button" class="h-12 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition touch-active">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
