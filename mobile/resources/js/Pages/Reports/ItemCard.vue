<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';

const props = defineProps({
    item: { type: Object, default: null },
    movements: { type: Array, default: () => [] },
});

const itemData = computed(() => props.item || {});

const movementTypeBadge = (type) => {
    switch (type) {
        case 'sale_out':
            return { label: 'مبيعات (صرف)', class: 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20' };
        case 'purchase_in':
            return { label: 'شراء (توريد)', class: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' };
        case 'transfer_out':
            return { label: 'تحويل صادر', class: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20' };
        case 'transfer_in':
            return { label: 'تحويل وارد', class: 'bg-teal-500/10 text-teal-600 dark:text-teal-400 border-teal-500/20' };
        case 'return_in':
            return { label: 'مرتجع بيع', class: 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20' };
        default:
            return { label: type || 'حركة مخزن', class: 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200' };
    }
};
</script>

<template>
    <MobileLayout>
        <div class="space-y-4 pb-24 select-none">
            <!-- Header Toolbar -->
            <div class="flex items-center justify-between">
                <Link
                    href="/reports"
                    class="h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5 shadow-xs touch-active"
                >
                    <span>⬅️</span>
                    <span>تقارير الأرباح</span>
                </Link>

                <span class="px-2.5 py-1 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 font-mono font-black text-xs">
                    رصيد المخزن: {{ Number(itemData.current_stock || 0).toFixed(2) }} {{ itemData.unit || 'وحدة' }}
                </span>
            </div>

            <!-- Item Header Profile Card -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400">كود: {{ itemData.code }}</span>
                        <h2 class="text-base font-black text-slate-900 dark:text-white mt-0.5">
                            {{ itemData.name }}
                        </h2>
                    </div>
                    <span class="px-2.5 py-1 rounded-xl bg-amber-500/10 text-amber-600 font-bold text-xs">
                        {{ itemData.category || 'عام' }}
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-xs text-center">
                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                        <span class="text-[10px] text-slate-400 block font-bold">سعر التكلفة:</span>
                        <span class="font-mono font-black text-slate-900 dark:text-white">{{ Number(itemData.cost_price || 0).toFixed(2) }} ج</span>
                    </div>

                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                        <span class="text-[10px] text-slate-400 block font-bold">سعر البيع قطاعي:</span>
                        <span class="font-mono font-black text-emerald-600">{{ Number(itemData.price_retail || 0).toFixed(2) }} ج</span>
                    </div>

                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                        <span class="text-[10px] text-slate-400 block font-bold">سعر البيع جملة:</span>
                        <span class="font-mono font-black text-amber-600">{{ Number(itemData.price_wholesale || 0).toFixed(2) }} ج</span>
                    </div>
                </div>
            </div>

            <!-- Movement Log History (كارت حركة الصنف) -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between px-1">
                    <span class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>📜</span>
                        <span>سجل حركات المخزن للصنف (آخر 50 حركة)</span>
                    </span>
                    <span class="text-[10px] text-slate-400 font-bold font-mono">{{ movements.length }} حركة</span>
                </div>

                <div class="space-y-2">
                    <div
                        v-for="m in movements"
                        :key="m.id"
                        class="bg-white dark:bg-slate-900 rounded-2xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span
                                    class="px-2 py-0.5 rounded-lg border text-[10px] font-black"
                                    :class="movementTypeBadge(m.movement_type).class"
                                >
                                    {{ movementTypeBadge(m.movement_type).label }}
                                </span>
                                <span class="text-[10px] font-mono text-slate-400">
                                    {{ m.document_number ? '#' + m.document_number : '' }}
                                </span>
                            </div>

                            <span class="text-[10px] text-slate-400 font-mono">
                                {{ m.created_at ? m.created_at.split('T')[0] : '' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100 dark:border-slate-800">
                            <div>
                                <span class="text-[10px] text-slate-400 block">الكمية / التغيير:</span>
                                <span
                                    class="font-black font-mono text-sm"
                                    :class="m.movement_type === 'purchase_in' || m.movement_type === 'return_in' ? 'text-emerald-600' : 'text-rose-600'"
                                >
                                    {{ m.movement_type === 'purchase_in' || m.movement_type === 'return_in' ? '+' : '-' }}{{ Number(m.quantity).toFixed(2) }} كجم
                                </span>
                            </div>

                            <div class="text-end">
                                <span class="text-[10px] text-slate-400 block">الرصيد بعد الحركة:</span>
                                <span class="font-black font-mono text-slate-900 dark:text-white">
                                    {{ Number(m.stock_after || 0).toFixed(2) }} كجم
                                </span>
                            </div>
                        </div>

                        <div v-if="m.notes" class="text-[10px] text-slate-400 pt-1 border-t border-slate-50 dark:border-slate-800/60 truncate">
                            {{ m.notes }}
                        </div>
                    </div>

                    <div v-if="!movements || movements.length === 0" class="text-center py-8 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                        <div class="text-2xl mb-1">📦</div>
                        <div class="text-xs font-bold text-slate-500">لا توجد حركات مخزنية مسجلة لهذا الصنف</div>
                    </div>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
