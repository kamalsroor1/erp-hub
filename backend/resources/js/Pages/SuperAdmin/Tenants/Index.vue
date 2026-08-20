<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

const props = defineProps({
    tenants: Object,
    plans: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const planId = ref(props.filters.plan_id || 'all');

const applyFilter = () => {
    router.get('/admin/super/tenants', {
        search: search.value,
        status: status.value,
        plan_id: planId.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const impersonate = (tenantId) => {
    router.post(`/admin/super/tenants/${tenantId}/impersonate`);
};
</script>

<template>
    <Head :title="$t('super.tenants')" />

    <SuperAdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-black text-white">{{ $t('super.tenants') }}</h1>
                    <p class="text-xs text-slate-400 font-bold mt-0.5">
                        {{ $t('super.total_tenants') }}: {{ tenants?.total || 0 }}
                    </p>
                </div>

                <Link
                    href="/admin/super/tenants/create"
                    class="h-10 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center gap-2 shadow-lg shadow-indigo-600/25 transition"
                >
                    <span>➕</span>
                    <span>{{ $t('super.create_tenant') }}</span>
                </Link>
            </div>

            <!-- Filters Bar -->
            <div class="p-4 rounded-3xl bg-slate-900 border border-slate-800 flex flex-col md:flex-row items-center gap-3">
                <div class="flex-1 w-full relative">
                    <input
                        v-model="search"
                        @keyup.enter="applyFilter"
                        type="text"
                        :placeholder="$t('super.search_tenants')"
                        class="w-full h-10 bg-slate-800 border border-slate-700 rounded-xl px-3.5 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500"
                    />
                </div>

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select
                        v-model="status"
                        @change="applyFilter"
                        class="h-10 bg-slate-800 border border-slate-700 rounded-xl px-3 text-xs text-slate-300 focus:outline-none focus:border-indigo-500"
                    >
                        <option value="all">{{ $t('super.all_statuses') }}</option>
                        <option value="active">{{ $t('super.status_active') }}</option>
                        <option value="trial">{{ $t('super.status_trial') }}</option>
                        <option value="suspended">{{ $t('super.status_suspended') }}</option>
                    </select>

                    <select
                        v-model="planId"
                        @change="applyFilter"
                        class="h-10 bg-slate-800 border border-slate-700 rounded-xl px-3 text-xs text-slate-300 focus:outline-none focus:border-indigo-500"
                    >
                        <option value="all">{{ $t('super.all_plans') }}</option>
                        <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>

                    <button
                        @click="applyFilter"
                        type="button"
                        class="h-10 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-bold text-slate-300"
                    >
                        {{ $t('common.filter') }}
                    </button>
                </div>
            </div>

            <!-- Tenants Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-800/80">
                                <th class="pb-3 font-bold">{{ $t('super.tenant_name') }}</th>
                                <th class="pb-3 font-bold">{{ $t('super.subdomain') }}</th>
                                <th class="pb-3 font-bold">{{ $t('super.admin_email') }}</th>
                                <th class="pb-3 font-bold">{{ $t('super.plans') }}</th>
                                <th class="pb-3 font-bold">{{ $t('common.status') }}</th>
                                <th class="pb-3 font-bold text-left">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            <tr v-for="t in tenants.data" :key="t.id" class="hover:bg-slate-800/40 transition">
                                <td class="py-3 font-black text-white">
                                    <Link :href="`/admin/super/tenants/${t.id}`" class="hover:text-indigo-400">
                                        {{ t.name }}
                                    </Link>
                                </td>
                                <td class="py-3 font-mono text-indigo-400 font-bold">{{ t.domain }}</td>
                                <td class="py-3 text-slate-300 font-mono">{{ t.email }}</td>
                                <td class="py-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-indigo-600/15 text-indigo-400 border border-indigo-500/30">
                                        {{ t.plan?.name || '-' }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-black"
                                        :class="t.status === 'active' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-amber-500/15 text-amber-400'"
                                    >
                                        {{ t.status }}
                                    </span>
                                </td>
                                <td class="py-3 text-left">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="impersonate(t.id)"
                                            type="button"
                                            class="px-2.5 py-1 rounded-lg bg-amber-500/15 hover:bg-amber-500/25 text-amber-400 border border-amber-500/30 font-bold transition flex items-center gap-1 cursor-pointer"
                                            title="تسجيل الدخول المباشر كمسؤول المتجر"
                                        >
                                            <span>⚡</span>
                                            <span>دخول للمتجر</span>
                                        </button>
                                        <Link
                                            :href="`/admin/super/tenants/${t.id}`"
                                            class="px-3 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold transition inline-block"
                                        >
                                            {{ $t('common.view') }}
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="tenants.data.length === 0" class="py-12 text-center text-slate-500 font-bold">
                        {{ $t('common.no_data') }}
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>
