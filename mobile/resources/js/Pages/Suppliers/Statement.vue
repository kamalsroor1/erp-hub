<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';

const props = defineProps({
    supplier: Object,
    summary: Object,
    ledger: Array,
    filter: Object,
});

const fromDate = ref(props.filter?.from_date || '');
const toDate = ref(props.filter?.to_date || '');

const setQuickDate = (type) => {
    const today = new Date();
    const formatDate = (d) => d.toISOString().split('T')[0];

    if (type === 'today') {
        fromDate.value = formatDate(today);
        toDate.value = formatDate(today);
    } else if (type === '7days') {
        const past = new Date();
        past.setDate(today.getDate() - 7);
        fromDate.value = formatDate(past);
        toDate.value = formatDate(today);
    } else if (type === 'month') {
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        fromDate.value = formatDate(firstDay);
        toDate.value = formatDate(today);
    } else if (type === 'all') {
        fromDate.value = '';
        toDate.value = '';
    }

    filterStatement();
};

const filterStatement = () => {
    router.get(`/suppliers/${props.supplier.id}/statement`, {
        from_date: fromDate.value,
        to_date: toDate.value,
    }, {
        preserveState: true,
    });
};
</script>

<template>
    <MobileLayout>
        <div class="space-y-4">
            <!-- Header with Back Button -->
            <div class="flex items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <Link href="/suppliers" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center justify-center font-bold text-sm touch-active shadow-xs">
                        ‹
                    </Link>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">كشف حساب مورد</h2>
                        <p class="text-xs text-amber-600 dark:text-amber-400 font-bold">{{ supplier?.name }} ({{ supplier?.code }})</p>
                    </div>
                </div>

                <div class="text-end">
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block">صافي المستحق</span>
                    <span class="text-sm font-black font-mono text-amber-600 dark:text-amber-400">
                        {{ Number(supplier?.current_balance || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م
                    </span>
                </div>
            </div>

            <!-- Native Android Material Date Range Filter Card -->
            <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-xs font-black text-slate-800 dark:text-slate-200">
                        <span>📅</span>
                        <span>فترة كشف الحساب</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">فلترة ذكية</span>
                </div>

                <!-- Quick Date Selection Chips -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                    <button @click="setQuickDate('today')" type="button" class="px-2.5 py-1 rounded-lg font-bold text-[11px] bg-slate-100 dark:bg-slate-800 hover:bg-amber-500 hover:text-white text-slate-700 dark:text-slate-300 transition shrink-0 touch-active">
                        اليوم
                    </button>
                    <button @click="setQuickDate('7days')" type="button" class="px-2.5 py-1 rounded-lg font-bold text-[11px] bg-slate-100 dark:bg-slate-800 hover:bg-amber-500 hover:text-white text-slate-700 dark:text-slate-300 transition shrink-0 touch-active">
                        آخر 7 أيام
                    </button>
                    <button @click="setQuickDate('month')" type="button" class="px-2.5 py-1 rounded-lg font-bold text-[11px] bg-slate-100 dark:bg-slate-800 hover:bg-amber-500 hover:text-white text-slate-700 dark:text-slate-300 transition shrink-0 touch-active">
                        هذا الشهر
                    </button>
                    <button @click="setQuickDate('all')" type="button" class="px-2.5 py-1 rounded-lg font-bold text-[11px] bg-slate-100 dark:bg-slate-800 hover:bg-slate-700 hover:text-white text-slate-700 dark:text-slate-300 transition shrink-0 touch-active">
                        الكل
                    </button>
                </div>

                <!-- Native Mobile Date Inputs -->
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">من تاريخ:</label>
                        <input
                            v-model="fromDate"
                            type="date"
                            class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-2.5 text-xs font-mono font-bold text-slate-900 dark:text-white outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                        >
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">إلى تاريخ:</label>
                        <input
                            v-model="toDate"
                            type="date"
                            class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-2.5 text-xs font-mono font-bold text-slate-900 dark:text-white outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                        >
                    </div>
                </div>

                <button @click="filterStatement" type="button" class="w-full h-9 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 touch-active shadow-xs">
                    <span>تطبيق الفلترة</span>
                </button>
            </div>

            <!-- Summary Stats Bar -->
            <div class="grid grid-cols-2 gap-2 text-center text-xs">
                <div class="bg-white dark:bg-slate-900 p-3 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-bold mb-0.5">إجمالي المشتريات</div>
                    <div class="text-sm font-black text-slate-900 dark:text-white font-mono">
                        {{ Number(summary?.total_purchases_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 p-3 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-bold mb-0.5">إجمالي المسدد</div>
                    <div class="text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono">
                        {{ Number(summary?.total_paid_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                    </div>
                </div>
            </div>

            <!-- Ledger Transactions List -->
            <div class="space-y-2.5">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider px-1">
                    سجل العمليات ({{ ledger?.length || 0 }})
                </h3>

                <div v-for="item in ledger" :key="item.id + item.type" class="bg-white dark:bg-slate-900 rounded-2xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold shadow-xs" :class="item.type === 'purchase' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600'">
                            {{ item.type === 'purchase' ? '📦' : '💵' }}
                        </div>
                        <div>
                            <div class="text-xs font-extrabold text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span>{{ item.type_label }}</span>
                                <span class="font-mono text-[10px] text-slate-400">({{ item.document_number }})</span>
                            </div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                                {{ item.date }}
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <template v-if="item.type === 'purchase'">
                            <div class="text-xs font-black text-amber-600 dark:text-amber-400 font-mono">
                                +{{ Number(item.credit || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                            </div>
                            <div v-if="parseFloat(item.debit) > 0" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-mono">
                                مسدد: {{ Number(item.debit).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                            </div>
                        </template>
                        <template v-else>
                            <div class="text-xs font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                -{{ Number(item.debit || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                            </div>
                        </template>
                        <div class="text-[9px] text-slate-400 font-semibold">ج.م</div>
                    </div>
                </div>

                <div v-if="!ledger || ledger.length === 0" class="text-center py-8 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs">
                    <div class="text-2xl mb-1">📄</div>
                    <div class="text-xs font-bold text-slate-600 dark:text-slate-300">لا توجد حركات مسجلة لهذا المورد في هذه الفترة</div>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
