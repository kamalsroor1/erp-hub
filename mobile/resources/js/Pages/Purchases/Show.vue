<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    purchase: { type: Object, default: null },
});

const p = computed(() => props.purchase || {});

const cancelPurchase = () => {
    haptic.warning();
    if (confirm(`هل أنت متأكد من إلغاء فاتورة المشتريات #${p.value.purchase_number} وعكس أثرها في المخزن؟`)) {
        router.post(`/purchases/${p.value.id}/cancel`, {}, {
            onSuccess: () => {
                router.visit('/purchases');
            }
        });
    }
};
</script>

<template>
    <MobileLayout>
        <div class="space-y-4 pb-24 select-none">
            <!-- Header Toolbar -->
            <div class="flex items-center justify-between">
                <Link
                    href="/purchases"
                    class="h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5 shadow-xs touch-active"
                >
                    <span>⬅️</span>
                    <span>قائمة المشتريات</span>
                </Link>

                <span
                    class="px-2.5 py-1 rounded-xl text-xs font-black"
                    :class="p.status === 'cancelled' ? 'bg-rose-500/10 text-rose-500 border border-rose-500/20' : (p.remaining_amount == 0 ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-500 border border-amber-500/20')"
                >
                    {{ p.status === 'cancelled' ? 'فاتورة ملغاة 🚫' : (p.remaining_amount == 0 ? 'مسددة بالكامل ✓' : 'متبقي آجل ⏳') }}
                </span>
            </div>

            <!-- Main Invoice Card -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <!-- Meta Info -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <div class="text-base font-black font-mono text-amber-600 dark:text-amber-400">
                            #{{ p.purchase_number }}
                        </div>
                        <div class="text-xs font-extrabold text-slate-900 dark:text-white mt-0.5">
                            المورد: {{ p.supplier?.name || 'غير محدد' }}
                        </div>
                    </div>
                    <div class="text-end text-[11px] text-slate-400 font-mono">
                        <div>{{ p.purchase_date }}</div>
                        <div v-if="p.supplier_invoice_ref" class="text-slate-500 font-bold">إذن: {{ p.supplier_invoice_ref }}</div>
                    </div>
                </div>

                <!-- Items Breakdown Table -->
                <div class="space-y-2">
                    <div class="text-xs font-extrabold text-slate-500">الأصناف والخامات الموردة:</div>

                    <div class="space-y-2">
                        <div
                            v-for="line in p.items"
                            :key="line.id"
                            class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs"
                        >
                            <div>
                                <div class="font-extrabold text-slate-900 dark:text-white">
                                    {{ line.item?.name || 'صنف بن' }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                    {{ Number(line.quantity).toFixed(2) }} كجم × {{ Number(line.cost_price).toFixed(2) }} ج.م
                                </div>
                            </div>

                            <div class="font-black font-mono text-slate-900 dark:text-white">
                                {{ Number(line.total_price).toFixed(2) }} ج.م
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary Box -->
                <div class="p-4 bg-slate-50 dark:bg-slate-800/80 rounded-2xl space-y-2 text-xs border border-slate-100 dark:border-slate-700">
                    <div class="flex justify-between text-slate-500 dark:text-slate-400">
                        <span>إجمالي الأصناف:</span>
                        <span class="font-mono">{{ Number(p.subtotal).toFixed(2) }} ج.م</span>
                    </div>

                    <div v-if="p.discount_amount > 0" class="flex justify-between text-rose-500">
                        <span>الخصم الممنوح:</span>
                        <span class="font-mono">-{{ Number(p.discount_amount).toFixed(2) }} ج.م</span>
                    </div>

                    <div class="flex justify-between text-sm font-black text-slate-900 dark:text-white pt-2 border-t border-slate-200 dark:border-slate-700">
                        <span>صافي الفاتورة:</span>
                        <span class="text-amber-600 dark:text-amber-400 font-mono">{{ Number(p.net_total).toFixed(2) }} ج.م</span>
                    </div>

                    <div class="flex justify-between text-emerald-600 font-bold">
                        <span>المدفوع نقداً:</span>
                        <span class="font-mono">{{ Number(p.paid_amount).toFixed(2) }} ج.م</span>
                    </div>

                    <div v-if="p.remaining_amount > 0" class="flex justify-between text-amber-600 dark:text-amber-400 font-bold">
                        <span>المتبقي آجل:</span>
                        <span class="font-mono">{{ Number(p.remaining_amount).toFixed(2) }} ج.م</span>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="p.notes" class="text-xs text-slate-500 p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                    ملاحظات: {{ p.notes }}
                </div>
            </div>

            <!-- Cancel Button if not cancelled -->
            <button
                v-if="p.status !== 'cancelled'"
                @click="cancelPurchase"
                type="button"
                class="w-full h-12 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 font-black text-xs rounded-2xl flex items-center justify-center gap-2 border border-rose-500/20 transition touch-active"
            >
                <span>🚫</span>
                <span>إلغاء فاتورة المشتريات وعكس رصيد المخزن</span>
            </button>
        </div>
    </MobileLayout>
</template>
