<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import SkeletonCard from '@/Components/SkeletonCard.vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    expenses: { type: Array, default: () => [] },
    total_amount: { type: [Number, String], default: 0 },
    total_count: { type: Number, default: 0 },
    quick_categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || 'all');
const isLoading = ref(false);

// Filter Categories
const applyCategory = (cat) => {
    haptic.light();
    selectedCategory.value = cat;
    router.get('/expenses', {
        search: search.value,
        category: cat,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const applySearch = () => {
    router.get('/expenses', {
        search: search.value,
        category: selectedCategory.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Create / Edit Modal State
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    category: 'شنط وأكياس',
    title: 'شراء شنط وأكياس',
    amount: '',
    expense_date: new Date().toISOString().split('T')[0],
    payment_method: 'cash',
    notes: '',
});

const openCreateModal = () => {
    haptic.medium();
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.category = 'شنط وأكياس';
    form.title = 'شراء شنط وأكياس';
    form.expense_date = new Date().toISOString().split('T')[0];
    showModal.value = true;
};

const selectQuickCat = (cat) => {
    haptic.light();
    form.category = cat;
    form.title = 'شراء ' + cat;
};

const setQuickAmount = (val) => {
    haptic.light();
    form.amount = String(val);
};

const openEditModal = (exp) => {
    haptic.medium();
    activeActionExpense.value = null;
    isEditing.value = true;
    editingId.value = exp.id;
    form.category = exp.category || 'شنط وأكياس';
    form.title = exp.title;
    form.amount = String(exp.amount);
    form.expense_date = exp.expense_date ? exp.expense_date.split('T')[0] : new Date().toISOString().split('T')[0];
    form.payment_method = exp.payment_method || 'cash';
    form.notes = exp.notes || '';
    showModal.value = true;
};

const submitExpense = () => {
    haptic.success();
    if (isEditing.value && editingId.value) {
        form.put(`/expenses/${editingId.value}`, {
            onSuccess: () => {
                showModal.value = false;
            }
        });
    } else {
        form.post('/expenses', {
            onSuccess: () => {
                showModal.value = false;
            }
        });
    }
};

// Action Sheet for Expense (⋯)
const activeActionExpense = ref(null);

const openActionSheet = (exp) => {
    haptic.light();
    activeActionExpense.value = exp;
};

const deleteExpense = (exp) => {
    haptic.warning();
    if (confirm(`هل أنت متأكد من حذف مصروف "${exp.title}" بقيمة ${exp.amount} ج.م؟`)) {
        activeActionExpense.value = null;
        router.delete(`/expenses/${exp.id}`);
    }
};
</script>

<template>
    <MobileLayout>
        <div class="space-y-4 pb-24 select-none">
            <!-- Header Banner & Create Button -->
            <div class="bg-gradient-to-l from-rose-600 to-rose-700 rounded-3xl p-4 text-white shadow-lg shadow-rose-600/20 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">💸</span>
                        <h2 class="text-base font-black">المصروفات وتكلفة التشغيل</h2>
                    </div>
                    <p class="text-[11px] text-rose-100 font-bold mt-0.5">
                        تسجيل مشتريات الشنط والأكواب والإيجار والنثريات اليومية
                    </p>
                </div>
                <button
                    @click="openCreateModal"
                    type="button"
                    class="h-10 px-3.5 bg-white text-rose-600 hover:bg-rose-50 font-black text-xs rounded-2xl shadow-md flex items-center gap-1.5 transition touch-active shrink-0"
                >
                    <span>➕</span>
                    <span>تسجيل مصروف</span>
                </button>
            </div>

            <!-- Stats KPI Overview Card -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-1">
                    <div class="text-[10px] text-slate-400 font-bold">إجمالي المصروفات</div>
                    <div class="text-lg font-black font-mono text-rose-600 dark:text-rose-400">
                        {{ Number(total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} <span class="text-[10px] font-sans">ج.م</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-1">
                    <div class="text-[10px] text-slate-400 font-bold">عدد السندات المسجلة</div>
                    <div class="text-lg font-black font-mono text-slate-900 dark:text-white">
                        {{ total_count }} <span class="text-[10px] font-sans text-slate-400">سند</span>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative">
                <input
                    v-model="search"
                    @keyup.enter="applySearch"
                    type="text"
                    placeholder="بحث برقم السند، البيان، أو الملاحظات..."
                    class="w-full h-11 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl pe-10 ps-4 text-xs font-bold text-slate-900 dark:text-white shadow-xs focus:border-rose-500"
                />
                <button @click="applySearch" type="button" class="absolute left-3 top-2.5 text-slate-400 text-sm">
                    🔍
                </button>
            </div>

            <!-- Category Filter Chips (Horizontal Scrolling) -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1">
                <button
                    @click="applyCategory('all')"
                    type="button"
                    class="px-3 py-1.5 rounded-xl font-bold text-[11px] shrink-0 transition touch-active"
                    :class="selectedCategory === 'all' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'"
                >
                    الكل ({{ total_count }})
                </button>

                <button
                    v-for="cat in quick_categories"
                    :key="cat"
                    @click="applyCategory(cat)"
                    type="button"
                    class="px-3 py-1.5 rounded-xl font-bold text-[11px] shrink-0 transition touch-active"
                    :class="selectedCategory === cat ? 'bg-rose-600 text-white shadow-sm' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'"
                >
                    {{ cat }}
                </button>
            </div>

            <!-- Expenses List -->
            <div class="space-y-3">
                <div
                    v-for="exp in expenses"
                    :key="exp.id"
                    class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2.5 hover:border-rose-500/50 transition"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 font-bold text-[10px]">
                                {{ exp.category }}
                            </span>
                            <span class="text-[10px] font-mono text-slate-400">
                                #{{ exp.expense_number }}
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">{{ exp.expense_date }}</span>
                    </div>

                    <div class="flex items-center justify-between pt-1 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex-1 min-w-0">
                            <div class="font-black text-xs text-slate-900 dark:text-white truncate">
                                {{ exp.title }}
                            </div>
                            <div v-if="exp.notes" class="text-[10px] text-slate-400 truncate mt-0.5">
                                {{ exp.notes }}
                            </div>
                        </div>

                        <div class="text-base font-black font-mono text-rose-600 dark:text-rose-400 ps-2">
                            -{{ Number(exp.amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م
                        </div>
                    </div>

                    <!-- Clean Action Bar -->
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
                        <button
                            @click="openEditModal(exp)"
                            type="button"
                            class="flex-1 h-8 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-[11px] rounded-xl flex items-center justify-center gap-1 transition touch-active"
                        >
                            <span>✏️</span>
                            <span>تعديل</span>
                        </button>

                        <button
                            @click="openActionSheet(exp)"
                            type="button"
                            class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 font-black text-xs flex items-center justify-center transition touch-active shrink-0"
                            title="خيارات"
                        >
                            ⋯
                        </button>
                    </div>
                </div>

                <div v-if="!expenses || expenses.length === 0" class="text-center py-10 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <div class="text-3xl mb-1">💸</div>
                    <div class="text-xs font-bold text-slate-600 dark:text-slate-300">لا توجد مصروفات مسجلة في هذه الفترة</div>
                </div>
            </div>

            <!-- CREATE / EDIT EXPENSE BOTTOM SHEET -->
            <div
                v-if="showModal"
                @click="showModal = false"
                class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-end justify-center select-none"
            >
                <div
                    @click.stop
                    class="w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 shadow-2xl p-5 pb-8 space-y-4 max-h-[90vh] overflow-y-auto animate-slide-up"
                >
                    <!-- Drag Handle -->
                    <div class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto -mt-1 mb-1"></div>

                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">💸</span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white">
                                    {{ isEditing ? 'تعديل بيانات المصروف' : 'تسجيل مصروف ونثريات جديدة' }}
                                </h3>
                                <p class="text-[10px] text-slate-400 font-bold">
                                    خصم المصروف من درج الكاشير / الخزينة
                                </p>
                            </div>
                        </div>
                        <button @click="showModal = false" type="button" class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-xs">✕</button>
                    </div>

                    <form @submit.prevent="submitExpense" class="space-y-3.5">
                        <!-- Quick Category Chips -->
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 mb-1">
                                تصنيف المصروف: <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex flex-wrap gap-1.5">
                                <button
                                    v-for="cat in quick_categories"
                                    :key="cat"
                                    @click="selectQuickCat(cat)"
                                    type="button"
                                    class="px-2.5 py-1 rounded-xl text-[11px] font-bold border transition"
                                    :class="form.category === cat ? 'bg-rose-600 border-rose-600 text-white' : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'"
                                >
                                    {{ cat }}
                                </button>
                            </div>
                        </div>

                        <!-- Title Input -->
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 mb-1">
                                بيان المصروف (الاسم): <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.title"
                                type="text"
                                required
                                placeholder="مثال: شراء أكياس تعبئة بن مقاس ربع"
                                class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white"
                            />
                        </div>

                        <!-- Amount Input -->
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 mb-1">
                                المبلغ المدفوع (ج.م): <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.amount"
                                type="number"
                                step="0.5"
                                min="0.01"
                                required
                                placeholder="0.00"
                                class="w-full h-12 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 font-mono font-black text-lg text-rose-600 dark:text-rose-400 text-center"
                            />
                            <!-- Quick Amount Chips -->
                            <div class="grid grid-cols-5 gap-1.5 mt-2">
                                <button
                                    v-for="amt in [20, 50, 100, 200, 500]"
                                    :key="amt"
                                    @click="setQuickAmount(amt)"
                                    type="button"
                                    class="py-1 bg-slate-100 dark:bg-slate-800 hover:bg-rose-500/20 text-slate-700 dark:text-slate-300 rounded-xl text-[10px] font-bold border border-slate-200 dark:border-slate-700"
                                >
                                    {{ amt }} ج
                                </button>
                            </div>
                        </div>

                        <!-- Date & Payment Method -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-500 mb-1">التاريخ:</label>
                                <input
                                    v-model="form.expense_date"
                                    type="date"
                                    required
                                    class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-mono font-bold text-slate-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-500 mb-1">طريقة الدفع:</label>
                                <select
                                    v-model="form.payment_method"
                                    class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-2.5 text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    <option value="cash">نقدي (من الدرج) 💵</option>
                                    <option value="bank">تحويل بنكي / فودافون كاش 📱</option>
                                </select>
                            </div>
                        </div>

                        <!-- Notes Input -->
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 mb-1">ملاحظات إضافية:</label>
                            <input
                                v-model="form.notes"
                                type="text"
                                placeholder="اختياري..."
                                class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white"
                            />
                        </div>

                        <!-- Submit Button -->
                        <button
                            :disabled="form.processing"
                            type="submit"
                            class="w-full h-13 bg-rose-600 hover:bg-rose-700 text-white font-black text-sm rounded-2xl shadow-xl shadow-rose-600/30 flex items-center justify-center gap-2 transition touch-active"
                        >
                            <span>💾</span>
                            <span>{{ form.processing ? 'جاري الحفظ...' : (isEditing ? 'حفظ تعديلات المصروف' : 'تسجيل المصروف والخصم من الدرج') }}</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- ACTION SHEET FOR EXPENSE (⋯) -->
            <div
                v-if="activeActionExpense"
                @click="activeActionExpense = null"
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
                                {{ activeActionExpense.title }}
                            </h3>
                            <div class="text-[10px] text-slate-400 font-mono">
                                #{{ activeActionExpense.expense_number }} • {{ Number(activeActionExpense.amount).toFixed(2) }} ج.م
                            </div>
                        </div>
                        <button @click="activeActionExpense = null" type="button" class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-xs">✕</button>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-1.5">
                        <button
                            @click="openEditModal(activeActionExpense)"
                            type="button"
                            class="w-full h-11 bg-slate-50 dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-xs rounded-2xl flex items-center justify-between px-3.5 transition"
                        >
                            <span class="flex items-center gap-2">
                                <span>✏️</span>
                                <span>تعديل بيانات المصروف</span>
                            </span>
                            <span class="text-slate-400 text-xs">‹</span>
                        </button>

                        <button
                            @click="deleteExpense(activeActionExpense)"
                            type="button"
                            class="w-full h-11 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 font-bold text-xs rounded-2xl flex items-center justify-between px-3.5 transition"
                        >
                            <span class="flex items-center gap-2">
                                <span>🗑️</span>
                                <span>حذف هذا المصروف</span>
                            </span>
                            <span class="text-xs">⚠️</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
