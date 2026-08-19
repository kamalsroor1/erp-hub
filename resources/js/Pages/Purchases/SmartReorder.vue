<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    items: { type: Array, default: () => [] },
});

const { formatMoney } = useMoney();
</script>

<template>
    <Head title="اقتراح إعادة الطلب الذكي للنواقص" />

    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link href="/purchases" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                            →
                        </Link>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            🧠 نظام التنبؤ وإعادة الطلب الذكي للنواقص
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        تحليل الأصناف التي وصلت للحد الحرج أو نفدت، واقتراح كميات التوريد المثالية
                    </p>
                </div>

                <Link
                    href="/purchases/create"
                    class="h-11 px-5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-600/30 transition transform active:scale-95 cursor-pointer"
                >
                    <span class="text-base font-black">+</span>
                    <span>إنشاء فاتورة شراء فورية</span>
                </Link>
            </div>

            <!-- Reorder Matrix Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h2 class="text-sm font-black text-white flex items-center gap-2">
                        <span>🚨</span>
                        <span>أصناف وصلت للحد الأدنى للسلامة ({{ items.length }})</span>
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">الصنف / الخامة</th>
                                <th class="pb-3">التصنيف</th>
                                <th class="pb-3 font-mono">الرصيد الفعلي الحالي</th>
                                <th class="pb-3 font-mono">حد الأمان</th>
                                <th class="pb-3 font-mono text-amber-400">الكمية المقترحة للطلب</th>
                                <th class="pb-3 font-mono">التكلفة التقديرية</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="it in items" :key="it.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3.5">
                                    <div class="font-black text-white font-tajawal">{{ it.name }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono">{{ it.code }}</div>
                                </td>

                                <td class="py-3.5 font-tajawal text-slate-300">
                                    {{ it.category || 'عام' }}
                                </td>

                                <td class="py-3.5 font-mono font-black">
                                    <span
                                        class="px-2.5 py-1 rounded-xl text-xs"
                                        :class="it.current_stock <= 0 ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30'"
                                    >
                                        {{ it.current_stock }} {{ it.unit }}
                                    </span>
                                </td>

                                <td class="py-3.5 font-mono text-slate-400 font-bold">
                                    {{ it.min_stock_level }} {{ it.unit }}
                                </td>

                                <td class="py-3.5 font-mono font-black text-emerald-400 text-sm">
                                    {{ it.suggested_reorder_qty }} {{ it.unit }}
                                </td>

                                <td class="py-3.5 font-mono font-bold text-white">
                                    {{ formatMoney(it.suggested_reorder_qty * it.cost_price) }} ج.م
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="items.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">🎉</span>
                        <p class="text-xs font-bold text-emerald-400 font-tajawal">مستويات المخزون ممتازة! لا توجد أصناف في النطاق الحرج حالياً.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
