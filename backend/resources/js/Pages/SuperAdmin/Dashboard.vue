<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    metrics: Object,
    plan_stats: Array,
    recent_tenants: Array,
});

const { formatMoney } = useMoney();
</script>

<template>
    <Head :title="$t('super.dashboard')" />

    <SuperAdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-white">{{ $t('super.platform_title') }}</h1>
                    <p class="text-xs text-slate-400 font-bold mt-0.5">
                        {{ $t('super.platform_subtitle') }}
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

            <!-- Platform KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Tenants -->
                <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between text-xs text-slate-400 font-bold">
                        <span>{{ $t('super.total_tenants') }}</span>
                        <span>🏪</span>
                    </div>
                    <div class="text-2xl font-black font-mono text-white">
                        {{ metrics?.total_tenants || 0 }}
                    </div>
                    <div class="text-[11px] text-emerald-400 font-bold">
                        {{ metrics?.active_tenants || 0 }} {{ $t('super.status_active') }}
                    </div>
                </div>

                <!-- Trial Tenants -->
                <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between text-xs text-slate-400 font-bold">
                        <span>{{ $t('super.trial_tenants') }}</span>
                        <span>⏳</span>
                    </div>
                    <div class="text-2xl font-black font-mono text-amber-400">
                        {{ metrics?.trial_tenants || 0 }}
                    </div>
                    <div class="text-[11px] text-slate-400 font-bold">
                        {{ $t('super.status_trial') }}
                    </div>
                </div>

                <!-- MRR -->
                <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between text-xs text-slate-400 font-bold">
                        <span>{{ $t('super.mrr') }}</span>
                        <span>💳</span>
                    </div>
                    <div class="text-2xl font-black font-mono text-emerald-400">
                        {{ formatMoney(metrics?.mrr) }} <span class="text-xs font-bold">{{ $t('common.currency') }}</span>
                    </div>
                    <div class="text-[11px] text-slate-400 font-bold">
                        {{ $t('super.subscriptions') }}
                    </div>
                </div>

                <!-- Suspended -->
                <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between text-xs text-slate-400 font-bold">
                        <span>{{ $t('super.suspended_tenants') }}</span>
                        <span>🚫</span>
                    </div>
                    <div class="text-2xl font-black font-mono text-rose-400">
                        {{ metrics?.suspended_tenants || 0 }}
                    </div>
                    <div class="text-[11px] text-slate-400 font-bold">
                        {{ $t('super.status_suspended') }}
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Tenants (2 Cols) -->
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-black text-white flex items-center gap-2">
                            <span>🏢</span>
                            <span>{{ $t('super.recent_tenants') }}</span>
                        </h2>
                        <Link href="/admin/super/tenants" class="text-xs font-bold text-indigo-400 hover:underline">
                            {{ $t('common.all') }} 👈
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                    <th class="pb-3">{{ $t('super.tenant_name') }}</th>
                                    <th class="pb-3">{{ $t('super.subdomain') }}</th>
                                    <th class="pb-3">{{ $t('super.plans') }}</th>
                                    <th class="pb-3">{{ $t('common.status') }}</th>
                                    <th class="pb-3 text-left">{{ $t('common.created_at') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                <tr v-for="t in recent_tenants" :key="t.id" class="hover:bg-slate-800/40 transition">
                                    <td class="py-3 font-bold text-white">
                                        <Link :href="`/admin/super/tenants/${t.id}`" class="hover:text-indigo-400">
                                            {{ t.name }}
                                        </Link>
                                    </td>
                                    <td class="py-3 font-mono text-slate-300">{{ t.domain }}</td>
                                    <td class="py-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-800 text-slate-300">
                                            {{ t.plan?.name || '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-black"
                                            :class="t.status === 'active' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-amber-500/15 text-amber-400'"
                                        >
                                            {{ t.status === 'active' ? $t('super.status_active') : (t.status === 'trial' ? $t('super.status_trial') : $t('super.status_suspended')) }}
                                        </span>
                                    </td>
                                    <td class="py-3 font-mono text-slate-400 text-left">{{ t.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Plan Distribution (1 Col) -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <h2 class="text-sm font-black text-white flex items-center gap-2">
                        <span>📊</span>
                        <span>{{ $t('super.plan_distribution') }}</span>
                    </h2>

                    <div class="space-y-3">
                        <div
                            v-for="p in plan_stats"
                            :key="p.plan_id"
                            class="p-3.5 rounded-2xl bg-slate-800/40 border border-slate-800 space-y-1.5"
                        >
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-white">{{ p.plan_name }}</span>
                                <span class="font-mono text-indigo-400">{{ p.tenants_count }} {{ $t('super.tenants') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                                <span>{{ formatMoney(p.price_monthly) }} {{ $t('common.currency') }} / {{ $t('super.per_month') }}</span>
                                <span class="text-emerald-400">{{ formatMoney(p.mrr_contribution) }} {{ $t('common.currency') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>
