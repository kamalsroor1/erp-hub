<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    period: { type: Object, default: () => ({ preset: 'this_month' }) },
    metrics: { type: Object, default: () => ({}) },
    top_items: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const currentPreset = ref(props.period.preset || 'this_month');
const activeTab = ref('profits'); // 'profits' | 'top_items' | 'item_cards'

const setPreset = (preset) => {
    haptic.light();
    currentPreset.value = preset;
    router.get('/reports', {
        preset: preset,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Item Card Selector State
const selectedItemId = ref(props.items[0]?.id || '');
const viewItemCard = (itemId) => {
    haptic.medium();
    router.visit(`/reports/items/${itemId || selectedItemId.value}/card`);
};
</script>

<template>
    <MobileLayout>
        <div class="space-y-4 pb-24 select-none">
            <!-- Header Banner -->
            <div class="bg-gradient-to-l from-emerald-700 via-emerald-800 to-slate-900 rounded-3xl p-4 text-white shadow-xl shadow-emerald-900/30 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">📈</span>
                        <h2 class="text-base font-black">تحليلات الأرباح والمبيعات</h2>
                    </div>
                    <p class="text-[11px] text-emerald-200 font-bold mt-0.5">
                        حساب صافي الربح الحقيقي وتكلفة البضاعة والأصناف الأكثر مبيعاً
                    </p>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-xl shrink-0">
                    📊
                </div>
            </div>

            <!-- Quick Period Filter Chips -->
            <div class="grid grid-cols-4 gap-1.5 bg-slate-200/70 dark:bg-slate-900 p-1 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs">
                <button
                    @click="setPreset('today')"
                    type="button"
                    class="py-2 rounded-xl font-black transition touch-active text-center"
                    :class="currentPreset === 'today' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-600 dark:text-slate-400'"
                >
                    اليوم
                </button>
                <button
                    @click="setPreset('this_week')"
                    type="button"
                    class="py-2 rounded-xl font-black transition touch-active text-center"
                    :class="currentPreset === 'this_week' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-600 dark:text-slate-400'"
                >
                    الأسبوع
                </button>
                <button
                    @click="setPreset('this_month')"
                    type="button"
                    class="py-2 rounded-xl font-black transition touch-active text-center"
                    :class="currentPreset === 'this_month' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-600 dark:text-slate-400'"
                >
                    هذا الشهر
                </button>
                <button
                    @click="setPreset('this_year')"
                    type="button"
                    class="py-2 rounded-xl font-black transition touch-active text-center"
                    :class="currentPreset === 'this_year' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-600 dark:text-slate-400'"
                >
                    العام
                </button>
            </div>

            <!-- Navigation Tabs (Profits Summary vs Top Items vs Stock Movement) -->
            <div class="flex items-center gap-1 border-b border-slate-200 dark:border-slate-800 pb-2 text-xs font-bold">
                <button
                    @click="activeTab = 'profits'"
                    type="button"
                    class="flex-1 py-2 rounded-xl transition text-center"
                    :class="activeTab === 'profits' ? 'bg-emerald-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                >
                    💎 صافي الربح التنفيذي
                </button>
                <button
                    @click="activeTab = 'top_items'"
                    type="button"
                    class="flex-1 py-2 rounded-xl transition text-center"
                    :class="activeTab === 'top_items' ? 'bg-emerald-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                >
                    ☕ الأكثر مبيعاً وربحية
                </button>
            </div>

            <!-- TAB 1: EXECUTIVE PROFITS & P&L SUMMARY -->
            <div v-if="activeTab === 'profits'" class="space-y-4 animate-in fade-in">
                <!-- HERO CARD: TRUE NET PROFIT -->
                <div class="bg-gradient-to-br from-emerald-600 via-teal-700 to-slate-900 rounded-3xl p-5 text-white shadow-xl shadow-emerald-600/20 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase text-emerald-200 tracking-wider">
                            صافي الربح الحقيقي (بعد خصم التكلفة والمصاريف)
                        </span>
                        <span class="px-2.5 py-1 rounded-xl bg-white/20 text-white font-mono font-black text-xs">
                            هامش: {{ metrics.profit_margin_pct }}%
                        </span>
                    </div>

                    <div class="text-3xl font-black font-mono tracking-tight">
                        {{ Number(metrics.net_profit || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                        <span class="text-sm font-sans font-normal text-emerald-100">ج.م</span>
                    </div>

                    <div class="text-[11px] text-emerald-100/90 font-medium pt-2 border-t border-white/10 flex items-center justify-between">
                        <span>المعادلة: مجمل ربح المبيعات - المصروفات والنثريات</span>
                        <span>{{ period.from_date }} إلى {{ period.to_date }}</span>
                    </div>
                </div>

                <!-- 4-TIER FINANCIAL BREAKDOWN GRID -->
                <div class="grid grid-cols-2 gap-3">
                    <!-- 1. Total Sales -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-1">
                        <div class="text-[10px] text-slate-400 font-bold flex items-center gap-1">
                            <span>💵</span>
                            <span>إجمالي المبيعات</span>
                        </div>
                        <div class="text-base font-black font-mono text-slate-900 dark:text-white">
                            {{ Number(metrics.total_sales || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} <span class="text-[10px] font-sans">ج.م</span>
                        </div>
                    </div>

                    <!-- 2. Cost of Goods Sold (COGS) -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-1">
                        <div class="text-[10px] text-slate-400 font-bold flex items-center gap-1">
                            <span>📦</span>
                            <span>تكلفة البضاعة المباعة (COGS)</span>
                        </div>
                        <div class="text-base font-black font-mono text-slate-700 dark:text-slate-300">
                            {{ Number(metrics.total_cogs || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} <span class="text-[10px] font-sans">ج.م</span>
                        </div>
                    </div>

                    <!-- 3. Gross Sales Profit -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-1">
                        <div class="text-[10px] text-emerald-500 font-bold flex items-center gap-1">
                            <span>🏷️</span>
                            <span>مجمل ربح المبيعات</span>
                        </div>
                        <div class="text-base font-black font-mono text-emerald-600 dark:text-emerald-400">
                            {{ Number(metrics.gross_profit || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} <span class="text-[10px] font-sans">ج.م</span>
                        </div>
                    </div>

                    <!-- 4. Total Expenses -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-1">
                        <div class="text-[10px] text-rose-500 font-bold flex items-center gap-1">
                            <span>💸</span>
                            <span>المصروفات وتكلفة التشغيل</span>
                        </div>
                        <div class="text-base font-black font-mono text-rose-600 dark:text-rose-400">
                            -{{ Number(metrics.total_expenses || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} <span class="text-[10px] font-sans">ج.م</span>
                        </div>
                    </div>
                </div>

                <!-- CASH FLOW & SALES STATS CARD -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3 text-xs">
                    <div class="font-extrabold text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>📊</span>
                        <span>مؤشرات أداء المبيعات والتحصيل</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1 border-t border-slate-100 dark:border-slate-800">
                        <div class="space-y-0.5">
                            <span class="text-[10px] text-slate-400 font-bold">عدد الفواتير المنفذة</span>
                            <div class="font-black font-mono text-slate-900 dark:text-white">{{ metrics.invoice_count }} فاتورة</div>
                        </div>

                        <div class="space-y-0.5">
                            <span class="text-[10px] text-slate-400 font-bold">متوسط قيمة الفاتورة</span>
                            <div class="font-black font-mono text-slate-900 dark:text-white">{{ Number(metrics.average_ticket || 0).toFixed(2) }} ج.م</div>
                        </div>

                        <div class="space-y-0.5">
                            <span class="text-[10px] text-emerald-500 font-bold">التحصيل النقدي الفعلي</span>
                            <div class="font-black font-mono text-emerald-600">{{ Number(metrics.total_paid || 0).toFixed(2) }} ج.م</div>
                        </div>

                        <div class="space-y-0.5">
                            <span class="text-[10px] text-amber-500 font-bold">المتبقي الآجل (مديونية)</span>
                            <div class="font-black font-mono text-amber-600">{{ Number(metrics.total_remaining || 0).toFixed(2) }} ج.م</div>
                        </div>
                    </div>
                </div>

                <!-- QUICK SHORTCUT: VIEW ITEM STOCK CARD -->
                <div class="bg-slate-100 dark:bg-slate-800/80 rounded-3xl p-4 border border-slate-200 dark:border-slate-700 space-y-3">
                    <div class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>📜</span>
                        <span>استعراض كارت حركة الصنف:</span>
                    </div>

                    <div class="space-y-2">
                        <div class="w-full min-w-0">
                            <select
                                v-model="selectedItemId"
                                class="w-full h-11 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white truncate shadow-xs"
                            >
                                <option value="" disabled>-- اختر الصنف لعرض حركته --</option>
                                <option v-for="it in items" :key="it.id" :value="it.id">
                                    {{ it.name }} (رصيد: {{ Number(it.current_stock || 0).toFixed(2) }} {{ it.unit || 'وحدة' }})
                                </option>
                            </select>
                        </div>

                        <button
                            @click="viewItemCard(selectedItemId)"
                            :disabled="!selectedItemId"
                            type="button"
                            class="w-full h-11 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-black text-xs rounded-2xl shadow-md transition touch-active flex items-center justify-center gap-2"
                        >
                            <span>🔍</span>
                            <span>استعراض كارت حركة هذا الصنف</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- TAB 2: TOP SELLING & MOST PROFITABLE COFFEE ITEMS -->
            <div v-if="activeTab === 'top_items'" class="space-y-3 animate-in fade-in">
                <div
                    v-for="(item, idx) in top_items"
                    :key="item.item_id"
                    class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2.5 hover:border-emerald-500/50 transition"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-6 h-6 rounded-xl flex items-center justify-center font-mono font-black text-xs"
                                :class="idx === 0 ? 'bg-amber-400 text-slate-950 shadow-sm' : (idx === 1 ? 'bg-slate-300 text-slate-900' : (idx === 2 ? 'bg-amber-700 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'))"
                            >
                                #{{ idx + 1 }}
                            </span>
                            <span class="font-black text-xs text-slate-900 dark:text-white">
                                {{ item.name }}
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">كود: {{ item.code }}</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 block font-bold">الكمية المباعة:</span>
                            <span class="font-mono font-black text-slate-900 dark:text-white">
                                {{ Number(item.total_qty).toFixed(2) }} كجم
                            </span>
                        </div>

                        <div>
                            <span class="text-[10px] text-slate-400 block font-bold">إجمالي المبيعات:</span>
                            <span class="font-mono font-black text-slate-900 dark:text-white">
                                {{ Number(item.total_sales).toFixed(2) }} ج.م
                            </span>
                        </div>

                        <div class="text-end">
                            <span class="text-[10px] text-emerald-500 block font-bold">صافي الربح:</span>
                            <span class="font-mono font-black text-emerald-600 dark:text-emerald-400">
                                +{{ Number(item.total_profit).toFixed(2) }} ج.م
                            </span>
                        </div>
                    </div>

                    <!-- Quick Movement Card Link -->
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                        <Link
                            :href="'/reports/items/' + item.item_id + '/card'"
                            class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1"
                        >
                            <span>عرض كارت حركة هذا الصنف</span>
                            <span>‹</span>
                        </Link>
                    </div>
                </div>

                <div v-if="!top_items || top_items.length === 0" class="text-center py-10 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <div class="text-3xl mb-1">☕</div>
                    <div class="text-xs font-bold text-slate-600 dark:text-slate-300">لا توجد مبيعات مسجلة في هذه الفترة</div>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
