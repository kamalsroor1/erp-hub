<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DatePicker from '@/Components/DatePicker.vue';

const props = defineProps({
    transfers: { type: Object, required: true },
    stores: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const fromStoreId = ref(props.filters.from_store_id || 'all');
const toStoreId = ref(props.filters.to_store_id || 'all');

const applyFilters = () => {
    router.get('/stock-transfers', {
        search: search.value || undefined,
        from_store_id: fromStoreId.value !== 'all' ? fromStoreId.value : undefined,
        to_store_id: toStoreId.value !== 'all' ? toStoreId.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

let searchTimer = null;
watch([search, fromStoreId, toStoreId], () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        applyFilters();
    }, 400);
});

// Transfer Details Modal
const showDetailsModal = ref(false);
const selectedTransfer = ref(null);

const openDetailsModal = (t) => {
    selectedTransfer.value = t;
    showDetailsModal.value = true;
};
</script>

<template>
    <Head title="سجل التحويلات المخزنية بين الفروع" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🚚</span>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            أذونات وسجل التحويلات المخزنية بين الفروع والعربيات
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        تتبع شحنات وتحويلات البضاعة بين المخزن الرئيسي ومنافذ البيع وعربيات التوزيع
                    </p>
                </div>

                <Link
                    href="/stock-transfers/create"
                    class="h-11 px-5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-600/30 transition transform active:scale-95 cursor-pointer"
                >
                    <span class="text-base font-black">+</span>
                    <span>إذن تحويل بضاعة جديد</span>
                </Link>
            </div>

            <!-- Transfers Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">رقم الإذن</th>
                                <th class="pb-3">من مخزن / فرع</th>
                                <th class="pb-3">إلى مخزن / عربية</th>
                                <th class="pb-3">التاريخ</th>
                                <th class="pb-3">الأصناف</th>
                                <th class="pb-3 text-center">الحالة</th>
                                <th class="pb-3 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="t in transfers.data" :key="t.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3.5 font-mono font-bold text-amber-400">
                                    {{ t.transfer_number }}
                                </td>

                                <td class="py-3.5 font-bold text-rose-300 font-tajawal">
                                    {{ t.from_store_name }}
                                </td>

                                <td class="py-3.5 font-bold text-emerald-300 font-tajawal">
                                    {{ t.to_store_name }}
                                </td>

                                <td class="py-3.5 font-mono text-slate-400 text-[11px]">
                                    {{ t.transfer_date }}
                                </td>

                                <td class="py-3.5 text-slate-300 font-tajawal">
                                    {{ t.items_count }} صنف
                                </td>

                                <td class="py-3.5 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                        تم التحويل بنجاح 🟢
                                    </span>
                                </td>

                                <td class="py-3.5 text-center">
                                    <button
                                        @click="openDetailsModal(t)"
                                        type="button"
                                        class="px-2.5 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition cursor-pointer"
                                    >
                                        عرض الأصناف
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="!transfers.data || transfers.data.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">🚚</span>
                        <p class="text-xs font-bold text-slate-400 font-tajawal">لا توجد أذونات تحويل مسجلة</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transfer Details Modal -->
        <div
            v-if="showDetailsModal && selectedTransfer"
            @click="showDetailsModal = false"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal"
            dir="rtl"
        >
            <div @click.stop class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="font-black text-base text-white">إذن التحويل: {{ selectedTransfer.transfer_number }}</h3>
                        <p class="text-xs text-amber-400 font-bold mt-0.5">{{ selectedTransfer.from_store_name }} ← {{ selectedTransfer.to_store_name }}</p>
                    </div>
                    <button @click="showDetailsModal = false" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 text-xs hover:text-white">✕</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-2">الصنف</th>
                                <th class="pb-2 font-mono">الكمية المحولة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="it in selectedTransfer.items" :key="it.id">
                                <td class="py-2.5 font-bold text-white font-tajawal">{{ it.item_name }}</td>
                                <td class="py-2.5 font-mono font-bold text-amber-400">{{ it.quantity }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>