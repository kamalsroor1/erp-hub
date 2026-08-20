<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    logs: { type: Array, default: () => [] },
    total_count: { type: Number, default: 0 },
    filters: { type: Object, default: () => ({}) },
});

const currentModule = ref(props.filters.module || 'all');
const search = ref(props.filters.search || '');

const setModuleFilter = (mod) => {
    haptic.light();
    currentModule.value = mod;
    router.get('/audit-logs', {
        module: mod,
        search: search.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const filterBadges = [
    { key: 'all', label: 'الكل', icon: '🌐' },
    { key: 'sales', label: 'المبيعات', icon: '🛒' },
    { key: 'purchases', label: 'المشتريات', icon: '🚚' },
    { key: 'inventory', label: 'المخزون', icon: '📦' },
    { key: 'shifts', label: 'الورديات', icon: '💵' },
    { key: 'expenses', label: 'المصروفات', icon: '💸' },
];
</script>

<template>
    <MobileLayout>
        <div class="space-y-4 pb-24 select-none">
            <!-- Header Banner -->
            <div class="bg-gradient-to-l from-indigo-800 via-indigo-900 to-slate-950 rounded-3xl p-4 text-white shadow-xl shadow-indigo-950/30 flex items-center justify-between border border-indigo-700/40">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🕵️‍♂️</span>
                        <h2 class="text-base font-black">سجل الرقابة وتدقيق العمليات</h2>
                    </div>
                    <p class="text-[11px] text-indigo-200 font-bold mt-0.5">
                        تتبع حي لكافة عمليات البيع، التعديلات، إغلاق الورديات، والتحويلات
                    </p>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-xl shrink-0">
                    🛡️
                </div>
            </div>

            <!-- Module Filters Scrollbar -->
            <div class="flex gap-2 overflow-x-auto pb-1 no-scrollbar text-xs">
                <button
                    v-for="b in filterBadges"
                    :key="b.key"
                    @click="setModuleFilter(b.key)"
                    type="button"
                    class="px-3.5 py-2 rounded-2xl font-bold whitespace-nowrap transition touch-active flex items-center gap-1.5"
                    :class="currentModule === b.key ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'"
                >
                    <span>{{ b.icon }}</span>
                    <span>{{ b.label }}</span>
                </button>
            </div>

            <!-- Logs Timeline -->
            <div class="space-y-3">
                <div
                    v-for="l in logs"
                    :key="l.id"
                    class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2 hover:border-indigo-500/50 transition"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-sm">{{ l.module_icon }}</span>
                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-black bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ l.module_label }}
                            </span>
                            <span class="text-[10px] font-mono font-bold text-slate-400">#{{ l.action }}</span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">{{ l.time_ago || l.created_at }}</span>
                    </div>

                    <!-- Description -->
                    <p class="text-xs font-bold text-slate-900 dark:text-white leading-relaxed">
                        {{ l.description }}
                    </p>

                    <!-- User & Store Footer -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800/80 text-[11px] text-slate-500 dark:text-slate-400">
                        <div class="flex items-center gap-1.5 font-bold">
                            <span>👤</span>
                            <span>{{ l.user_name }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span>🏬</span>
                            <span>{{ l.store_name }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="!logs || logs.length === 0" class="text-center py-10 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <div class="text-3xl mb-1">🕵️‍♂️</div>
                    <div class="text-xs font-bold text-slate-600 dark:text-slate-300">لا توجد سجلات تدقيق مسجلة</div>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
