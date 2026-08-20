<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import SkeletonCard from '@/Components/SkeletonCard.vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    items: Array,
    categories: Array,
    filters: Object,
    activeStore: Object,
});

const search = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || 'all');
const onlyLowStock = ref(false);
const isFiltering = ref(false);
const showActionSheet = ref(false);
const activeItem = ref(null);

const lowStockCount = computed(() => {
    return (props.items || []).filter(i => i.is_low_stock || Number(i.current_stock || 0) <= Number(i.min_stock_level || 0)).length;
});

const handleSearch = () => {
    isFiltering.value = true;
    setTimeout(() => {
        isFiltering.value = false;
    }, 120);
};

const selectCategory = (cat) => {
    haptic.light();
    selectedCategory.value = cat;
    handleSearch();
};

const toggleLowStock = () => {
    haptic.medium();
    onlyLowStock.value = !onlyLowStock.value;
};

const openActions = (item) => {
    haptic.light();
    activeItem.value = item;
    showActionSheet.value = true;
};

const filteredItems = computed(() => {
    return (props.items || []).filter(item => {
        const isLow = item.is_low_stock || Number(item.current_stock || 0) <= Number(item.min_stock_level || 0);
        if (onlyLowStock.value && !isLow) {
            return false;
        }
        const matchesCategory = selectedCategory.value === 'all' || item.category === selectedCategory.value;
        const matchesSearch = !search.value || 
            item.name.toLowerCase().includes(search.value.toLowerCase()) || 
            item.code.toLowerCase().includes(search.value.toLowerCase());
        return matchesCategory && matchesSearch;
    });
});
</script>

<template>
    <MobileLayout>
        <div class="space-y-3.5 pb-12 select-none">
            <!-- Top Header with Stats -->
            <div class="flex items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>📦</span>
                        <span>الأصناف والمخزون</span>
                    </h2>
                    <p class="text-xs text-slate-400 font-bold">
                        الفرع: <span class="text-emerald-500 font-extrabold">{{ activeStore?.name || 'الفرع الرئيسي' }}</span>
                    </p>
                </div>

                <Link
                    href="/pos"
                    class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 flex items-center gap-1.5 touch-active"
                >
                    <span>⚡</span>
                    <span>كاشير POS</span>
                </Link>
            </div>

            <!-- Search Bar -->
            <div class="relative">
                <input
                    v-model="search"
                    @input="handleSearch"
                    type="text"
                    placeholder="بحث باسم الصنف، الكود، أو التصنيف..."
                    class="w-full h-11 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl ps-10 pe-9 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-emerald-500 shadow-xs"
                >
                <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none text-slate-400">
                    🔍
                </div>
                <button
                    v-if="search"
                    @click="search = ''; handleSearch();"
                    type="button"
                    class="absolute inset-y-0 end-0 flex items-center pe-3 text-slate-400 text-xs font-bold"
                >
                    ✕
                </button>
            </div>

            <!-- Category & Low Stock Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar">
                <!-- Low Stock Radar Chip -->
                <button
                    @click="toggleLowStock"
                    type="button"
                    class="px-3 py-1.5 rounded-xl font-black text-xs transition shrink-0 touch-active flex items-center gap-1 border"
                    :class="onlyLowStock ? 'bg-rose-600 text-white border-rose-700 shadow-md shadow-rose-600/30' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20'"
                >
                    <span>⚠️</span>
                    <span>رادار النواقص ({{ lowStockCount }})</span>
                </button>

                <button
                    @click="selectCategory('all')"
                    type="button"
                    class="px-3 py-1.5 rounded-xl font-bold text-xs transition shrink-0 touch-active"
                    :class="selectedCategory === 'all' && !onlyLowStock ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'"
                >
                    الكل ({{ items?.length || 0 }})
                </button>
                <button
                    v-for="cat in categories"
                    :key="cat"
                    @click="selectCategory(cat)"
                    type="button"
                    class="px-3 py-1.5 rounded-xl font-bold text-xs transition shrink-0 touch-active"
                    :class="selectedCategory === cat ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'"
                >
                    {{ cat }}
                </button>
            </div>

            <!-- Skeleton Shimmer Loading State -->
            <div v-if="isFiltering">
                <SkeletonCard :count="3" :lines="2" />
            </div>

            <!-- Items Clean Cards List -->
            <div v-else class="space-y-3">
                <div
                    v-for="item in filteredItems"
                    :key="item.id"
                    class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3 hover:border-emerald-500/50 transition"
                >
                    <!-- Header Row -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-500/15 text-emerald-500 flex items-center justify-center text-lg font-black shrink-0">
                                📦
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-xs font-black text-slate-900 dark:text-white leading-tight truncate">
                                    {{ item.name }}
                                </h3>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[10px] text-slate-400 font-mono font-bold">#{{ item.code }}</span>
                                    <span v-if="item.category" class="text-[9px] px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                        {{ item.category }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Price Tag -->
                        <div class="text-end shrink-0">
                            <div class="text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                {{ Number(item.selling_price || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                            </div>
                            <span class="text-[9px] text-slate-400 font-bold">ج.م / {{ item.unit || 'كجم' }}</span>
                        </div>
                    </div>

                    <!-- Stock Status Bar -->
                    <div class="flex items-center justify-between text-xs py-1.5 px-3 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full" :class="parseFloat(item.current_balance || 0) > 10 ? 'bg-emerald-500' : (parseFloat(item.current_balance || 0) > 0 ? 'bg-amber-500' : 'bg-rose-500')"></span>
                            <span class="text-[10px] text-slate-400 font-bold">الرصيد المتاح بالمخزن:</span>
                        </div>
                        <div class="font-black font-mono text-xs" :class="parseFloat(item.current_balance || 0) > 0 ? 'text-slate-900 dark:text-white' : 'text-rose-500'">
                            {{ Number(item.current_balance || 0).toFixed(2) }} {{ item.unit || 'كجم' }}
                        </div>
                    </div>

                    <!-- Clean 2-Button Action Bar (Main POS Action + Native ⋯ Menu) -->
                    <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
                        <!-- Primary: Sell in POS -->
                        <Link
                            href="/pos"
                            class="flex-1 h-9 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 transition touch-active"
                        >
                            <span>⚡</span>
                            <span>بيع الصنف في الكاشير</span>
                        </Link>

                        <!-- Native Action Sheet Trigger Button (⋯) -->
                        <button
                            @click="openActions(item)"
                            type="button"
                            class="w-9 h-9 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl flex items-center justify-center text-sm font-black transition touch-active shrink-0 border border-slate-200 dark:border-slate-700"
                            title="خيارات الصنف"
                        >
                            ⋯
                        </button>
                    </div>
                </div>

                <div v-if="filteredItems.length === 0" class="text-center py-10 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <div class="text-3xl mb-1">☕</div>
                    <div class="text-xs font-bold text-slate-600 dark:text-slate-300">لا توجد أصناف مطابقة للبحث</div>
                </div>
            </div>

            <!-- Native Bottom Action Sheet for Coffee Item -->
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
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-amber-500/15 text-amber-500 flex items-center justify-center text-lg font-black shrink-0">
                                ☕
                            </div>
                            <div>
                                <div class="text-sm font-black text-slate-900 dark:text-white">{{ activeItem?.name }}</div>
                                <div class="text-xs text-slate-400 font-mono">الكود: #{{ activeItem?.code }} • {{ activeItem?.category || 'صنف عام' }}</div>
                            </div>
                        </div>

                        <div class="text-end">
                            <div class="text-[10px] text-slate-400 font-bold">سعر البيع</div>
                            <div class="text-sm font-black font-mono text-emerald-600 dark:text-emerald-400">
                                {{ Number(activeItem?.selling_price || 0).toLocaleString('en-US') }} ج.م
                            </div>
                        </div>
                    </div>

                    <!-- Action List -->
                    <div class="space-y-1.5">
                        <!-- 1. Sell in POS -->
                        <Link
                            href="/pos"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-sm font-bold">⚡</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">فتح الكاشير وبيع الصنف (POS)</div>
                                    <div class="text-[10px] text-slate-400">إضافة فورية لسلة البيع مع تحديد الوزن</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </Link>

                        <!-- 2. Stock Info -->
                        <div class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-between text-start">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center text-sm font-bold">📦</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">الرصيد الفعلي بالمخزن</div>
                                    <div class="text-[10px] text-slate-400">الكمية المتاحة حالياً للبيع الفوري</div>
                                </div>
                            </div>
                            <div class="text-xs font-black font-mono text-slate-900 dark:text-white">
                                {{ Number(activeItem?.current_balance || 0).toFixed(2) }} {{ activeItem?.unit || 'كجم' }}
                            </div>
                        </div>

                        <!-- 3. Cost & Margin Info -->
                        <div class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-between text-start">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-sm font-bold">🏷️</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">سعر التكلفة للوحدة</div>
                                    <div class="text-[10px] text-slate-400">متوسط تكلفة الشراء</div>
                                </div>
                            </div>
                            <div class="text-xs font-black font-mono text-slate-600 dark:text-slate-300">
                                {{ Number(activeItem?.cost_price || 0).toFixed(2) }} ج.م
                            </div>
                        </div>
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
        </div>
    </MobileLayout>
</template>
