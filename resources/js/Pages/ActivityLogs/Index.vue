<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import FilterDrawer from '@/Components/FilterDrawer.vue';

const props = defineProps({
    logs: { type: Object, required: true },
    modules: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const moduleName = ref(props.filters.module || 'all');
const action = ref(props.filters.action || 'all');
const dateFrom = ref(props.filters.from || '');
const dateTo = ref(props.filters.to || '');
const isDrawerOpen = ref(false);

const moduleOptions = computed(() => {
    return [
        { id: 'all', name: 'كافة الأقسام والوحدات' },
        ...Object.entries(props.modules).map(([k, v]) => ({ id: k, name: v }))
    ];
});

const actionOptions = [
    { id: 'all', name: 'كافة العمليات' },
    { id: 'create', name: 'إضافة جديدة 🟢' },
    { id: 'update', name: 'تعديل 🟡' },
    { id: 'delete', name: 'حذف أو أرشفة 🔴' },
    { id: 'login', name: 'تسجيل دخول 🔐' },
    { id: 'cancel', name: 'إلغاء فاتورة ❌' },
];

const activeFiltersCount = computed(() => {
    let count = 0;
    if (search.value) count++;
    if (moduleName.value !== 'all') count++;
    if (action.value !== 'all') count++;
    if (dateFrom.value || dateTo.value) count++;
    return count;
});

const applyFilters = () => {
    router.get('/activity-logs', {
        search: search.value || undefined,
        module: moduleName.value !== 'all' ? moduleName.value : undefined,
        action: action.value !== 'all' ? action.value : undefined,
        from: dateFrom.value || undefined,
        to: dateTo.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
            isDrawerOpen.value = false;
        }
    });
};

let searchTimer = null;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        applyFilters();
    }, 400);
});

const resetFilters = () => {
    search.value = '';
    moduleName.value = 'all';
    action.value = 'all';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};
</script>

<template>
    <Head title="سجل التدقيق وتتبع النشاطات" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🛡️</span>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            سجل التدقيق الأمني ومراقبة كافة نشاطات المستخدمين
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        تتبع زمني لكافة حركات البيع، التعديل، الحذف، المصروفات، وتسجيل الدخول مع IP
                    </p>
                </div>
            </div>

            <!-- Quick Filter Bar -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm space-y-3">
                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                    <div class="w-full md:w-96 relative">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="... بحث في نص النشاط أو اسم المستخدم أو IP"
                            class="w-full pr-10 pl-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-2xl text-xs text-white placeholder:text-slate-500 focus:ring-2 focus:ring-amber-500 focus:outline-none transition"
                        >
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                            🔍
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            @click="isDrawerOpen = true"
                            type="button"
                            class="h-10 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-bold flex items-center gap-2 transition cursor-pointer"
                        >
                            <span>⚙️</span>
                            <span>تصفية السجل</span>
                            <span v-if="activeFiltersCount > 0" class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-mono font-black text-[11px] flex items-center justify-center">
                                {{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Activity Logs Timeline Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">القسم / الوحدة</th>
                                <th class="pb-3">تفاصيل الإجراء</th>
                                <th class="pb-3">المستخدم المسؤول</th>
                                <th class="pb-3 font-mono">عنوان IP</th>
                                <th class="pb-3">التوقيت والتاريخ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="l in logs.data" :key="l.id" class="hover:bg-slate-800/30 transition">
                                <!-- Module Badge -->
                                <td class="py-3.5 font-tajawal">
                                    <span class="px-2.5 py-1 rounded-xl bg-slate-800 text-amber-400 font-bold text-[11px] inline-flex items-center gap-1.5">
                                        <span>{{ l.module_badge?.icon || '📋' }}</span>
                                        <span>{{ l.module_badge?.label || l.module }}</span>
                                    </span>
                                </td>

                                <!-- Description -->
                                <td class="py-3.5 font-tajawal">
                                    <div class="font-bold text-white text-xs">{{ l.description }}</div>
                                    <div v-if="l.store_name" class="text-[10px] text-slate-500 font-bold mt-0.5">الفرع: {{ l.store_name }}</div>
                                </td>

                                <!-- User -->
                                <td class="py-3.5 font-tajawal font-black text-amber-300">
                                    {{ l.user_name }}
                                </td>

                                <!-- IP Address -->
                                <td class="py-3.5 font-mono text-slate-400 text-[11px]">
                                    {{ l.ip_address || '127.0.0.1' }}
                                </td>

                                <!-- Time -->
                                <td class="py-3.5 font-mono">
                                    <div class="text-slate-300 text-[11px]">{{ l.created_at }}</div>
                                    <div class="text-[10px] text-slate-500 font-tajawal">{{ l.time_ago }}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="!logs.data || logs.data.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">🛡️</span>
                        <p class="text-xs font-bold text-slate-400 font-tajawal">لا توجد سجلات نشاط مطابقة للبحث</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="logs.links && logs.links.length > 3" class="pt-4 border-t border-slate-800/80 flex items-center justify-between font-sans">
                    <span class="text-xs text-slate-400 font-tajawal">
                        عرض {{ logs.from || 0 }} إلى {{ logs.to || 0 }} من إجمالي {{ logs.total || 0 }} نشاط مسجل
                    </span>

                    <div class="flex items-center gap-1">
                        <template v-for="(link, lIdx) in logs.links" :key="lIdx">
                            <a
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

        <!-- Filter Slide-Over Drawer -->
        <FilterDrawer
            :show="isDrawerOpen"
            :active-count="activeFiltersCount"
            @close="isDrawerOpen = false"
            @apply="applyFilters"
            @reset="resetFilters"
        >
            <div class="space-y-5">
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">🔍 البحث</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="... اكتب للبحث"
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950/80 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none transition"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">📋 القسم / الوحدة</label>
                    <SearchableSelect
                        v-model="moduleName"
                        :options="moduleOptions"
                        placeholder="اختر القسم..."
                    />
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">⚡ نوع العملية</label>
                    <SearchableSelect
                        v-model="action"
                        :options="actionOptions"
                        placeholder="اختر العملية..."
                    />
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-slate-300">من تاريخ</label>
                        <DatePicker v-model="dateFrom" placeholder="من..." />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-slate-300">إلى تاريخ</label>
                        <DatePicker v-model="dateTo" placeholder="إلى..." />
                    </div>
                </div>
            </div>
        </FilterDrawer>
    </AppLayout>
</template>