<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    item: { type: Object, required: true },
    movements: { type: Object, required: true },
    stores: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const { formatMoney } = useMoney();

const dateFrom = ref(props.filters.from || '');
const dateTo = ref(props.filters.to || '');
const storeId = ref(props.filters.store_id || 'all');
const movementType = ref(props.filters.type || 'all');

const storeOptions = [
    { id: 'all', name: 'كافة الفروع والمخازن' },
    ...props.stores
];

const movementTypeOptions = [
    { id: 'all', name: 'كافة أنواع الحركات' },
    { id: 'sale', name: 'مبيعات وفواتير POS 🛒' },
    { id: 'purchase', name: 'مشتريات وتوريدات 🚛' },
    { id: 'transfer_in', name: 'تحويل وارد من مخزن 📥' },
    { id: 'transfer_out', name: 'تحويل منصرف إلى مخزن 📤' },
    { id: 'return_in', name: 'مرتجع مبيعات من عميل ↩️' },
    { id: 'return_out', name: 'مرتجع مشتريات إلى مورد ↪️' },
    { id: 'adjustment', name: 'تسوية وجرد مخزني ⚖️' },
];

const applyFilters = () => {
    router.get(`/items/${props.item.id}/movements`, {
        from: dateFrom.value || undefined,
        to: dateTo.value || undefined,
        store_id: storeId.value !== 'all' ? storeId.value : undefined,
        type: movementType.value !== 'all' ? movementType.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};
</script>

<template>
    <Head :title="`كشف حركة المخزون: ${item.name}`" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link href="/items" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                            →
                        </Link>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                                <span>كشف وتتبع حركة الصنف:</span>
                                <span class="text-amber-400">{{ item.name }}</span>
                                <span v-if="item.code" class="text-xs text-slate-400 font-mono font-normal">({{ item.code }})</span>
                            </h1>
                            <p class="text-xs text-slate-400 font-bold mt-0.5">
                                التصنيف: {{ item.category || 'عام' }} | الوحدة: {{ item.unit }} | التكلفة: {{ formatMoney(item.cost_price) }} ج.م | البيع: {{ formatMoney(item.selling_price) }} ج.م
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl flex items-center gap-3">
                    <span class="text-xs text-slate-400 font-bold">الرصيد الفعلي الحالي:</span>
                    <span class="font-mono font-black text-amber-400 text-lg">{{ item.current_stock }} {{ item.unit }}</span>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-300">نوع الحركة</label>
                    <SearchableSelect
                        v-model="movementType"
                        :options="movementTypeOptions"
                        placeholder="اختر النوع..."
                    />
                </div>

                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-300">الفرع / المخزن</label>
                    <SearchableSelect
                        v-model="storeId"
                        :options="storeOptions"
                        placeholder="اختر المخزن..."
                    />
                </div>

                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-300">من تاريخ</label>
                    <DatePicker v-model="dateFrom" placeholder="من..." />
                </div>

                <div class="space-y-1 flex items-end gap-2">
                    <div class="flex-1">
                        <label class="text-[11px] font-bold text-slate-300">إلى تاريخ</label>
                        <DatePicker v-model="dateTo" placeholder="إلى..." />
                    </div>
                    <button
                        @click="applyFilters"
                        type="button"
                        class="h-10 px-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition cursor-pointer"
                    >
                        تطبيق
                    </button>
                </div>
            </div>

            <!-- Movements Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">التاريخ والوقت</th>
                                <th class="pb-3">نوع الحركة</th>
                                <th class="pb-3">رقم السند / الفاتورة</th>
                                <th class="pb-3 font-mono">الكمية</th>
                                <th class="pb-3 font-mono">الرصيد قبل</th>
                                <th class="pb-3 font-mono">الرصيد بعد</th>
                                <th class="pb-3">الفرع والمستخدم</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="m in movements.data" :key="m.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3.5 font-mono text-slate-400 text-[11px]">
                                    {{ m.created_at }}
                                </td>

                                <td class="py-3.5 font-tajawal font-bold">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px]"
                                        :class="m.quantity > 0 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400'"
                                    >
                                        {{ m.movement_type }}
                                    </span>
                                </td>

                                <td class="py-3.5 font-mono text-amber-400 font-bold">
                                    {{ m.document_number || '—' }}
                                </td>

                                <td class="py-3.5 font-mono font-black text-sm" :class="m.quantity > 0 ? 'text-emerald-400' : 'text-rose-400'">
                                    {{ m.quantity > 0 ? '+' : '' }}{{ m.quantity }} {{ item.unit }}
                                </td>

                                <td class="py-3.5 font-mono text-slate-400">
                                    {{ m.stock_before }}
                                </td>

                                <td class="py-3.5 font-mono font-bold text-white">
                                    {{ m.stock_after }}
                                </td>

                                <td class="py-3.5 font-tajawal text-slate-300">
                                    <div>{{ m.store_name || 'المخزن الرئيسي' }}</div>
                                    <div class="text-[10px] text-slate-500">{{ m.user_name }}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="!movements.data || movements.data.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">📦</span>
                        <p class="text-xs font-bold text-slate-400 font-tajawal">لا توجد حركات مسجلة لهذا الصنف في الفترة المحددة</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="movements.links && movements.links.length > 3" class="pt-4 border-t border-slate-800/80 flex items-center justify-between font-sans">
                    <span class="text-xs text-slate-400 font-tajawal">
                        عرض {{ movements.from || 0 }} إلى {{ movements.to || 0 }} من إجمالي {{ movements.total || 0 }} حركة
                    </span>

                    <div class="flex items-center gap-1">
                        <template v-for="(link, lIdx) in movements.links" :key="lIdx">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
                                :class="link.active ? 'bg-amber-500 text-slate-950 font-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="px-3 py-1.5 rounded-xl text-xs text-slate-600 font-bold"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>