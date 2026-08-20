<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

const props = defineProps({
    tenant: Object,
    features: Array,
    grouped_features: Object,
    plans: Array,
});

const isFeatureEnabled = (featureKey) => {
    if (props.tenant.enabled_features?.includes(featureKey)) {
        return true;
    }
    return props.tenant.plan?.features?.[featureKey] === true;
};

const isManualOverride = (featureKey) => {
    return props.tenant.enabled_features?.includes(featureKey);
};

const toggleFeature = (featureKey) => {
    router.post(`/admin/super/tenants/${props.tenant.id}/override-feature`, {
        feature_key: featureKey,
    }, {
        preserveScroll: true,
    });
};

const selectedStatus = ref(props.tenant.status);
const extendDays = ref(30);

const updateStatus = () => {
    router.post(`/admin/super/tenants/${props.tenant.id}/toggle-status`, {
        status: selectedStatus.value,
        extend_days: extendDays.value,
    }, {
        preserveScroll: true,
    });
};

const impersonate = () => {
    router.post(`/admin/super/tenants/${props.tenant.id}/impersonate`);
};
</script>

<template>
    <Head :title="`${$t('super.tenant_details')}: ${tenant.name}`" />

    <SuperAdminLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header Banner -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 sm:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xs">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="text-2xl">🏪</span>
                        <h1 class="text-xl sm:text-2xl font-black text-white">{{ tenant.name }}</h1>
                        <span
                            class="px-2.5 py-0.5 rounded-full text-[10px] font-black"
                            :class="tenant.status === 'active' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : (tenant.status === 'trial' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'bg-rose-500/15 text-rose-400 border border-rose-500/30')"
                        >
                            {{ tenant.status }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 font-mono">
                        ID: <span class="text-indigo-400 font-bold">{{ tenant.id }}</span> • Domain: <span class="text-white font-bold">{{ tenant.domain }}</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                    <button
                        @click="impersonate"
                        type="button"
                        class="flex-1 sm:flex-none h-11 px-4 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-slate-950 font-black text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-600/30 transition transform active:scale-95 cursor-pointer"
                        title="تسجيل الدخول الفوري كمسؤول المتجر"
                    >
                        <span>⚡</span>
                        <span>تسجيل الدخول كمسؤول المتجر</span>
                    </button>

                    <a
                        :href="`http://${tenant.domain}`"
                        target="_blank"
                        class="h-11 px-4 rounded-2xl bg-indigo-600/15 hover:bg-indigo-600/30 border border-indigo-500/30 text-indigo-300 font-black text-xs flex items-center justify-center gap-1.5 transition active:scale-95 shadow-xs"
                    >
                        <span>🌐</span>
                        <span>{{ $t('common.view') }}</span>
                    </a>

                    <Link
                        href="/admin/super/tenants"
                        class="h-11 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold flex items-center justify-center gap-1 transition active:scale-95 border border-slate-700 shadow-xs"
                    >
                        <span>←</span>
                        <span>{{ $t('common.back') }}</span>
                    </Link>
                </div>
            </div>

            <!-- Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Status & Subscription Management (1 Col) -->
                <div class="space-y-6">
                    <!-- Status Card -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 sm:p-5 space-y-4 shadow-xs">
                        <h2 class="font-black text-sm text-white border-b border-slate-800 pb-3 flex items-center gap-2">
                            <span>⚙️</span>
                            <span>{{ $t('super.account_actions') }}</span>
                        </h2>

                        <div class="space-y-3 text-xs">
                            <div class="space-y-1.5">
                                <label class="block text-slate-400 font-bold">{{ $t('common.status') }}:</label>
                                <select
                                    v-model="selectedStatus"
                                    class="w-full h-11 bg-slate-800 border border-slate-700 rounded-2xl px-3.5 text-xs sm:text-sm text-white focus:outline-none focus:border-indigo-500 shadow-inner"
                                >
                                    <option value="active">{{ $t('super.status_active') }}</option>
                                    <option value="trial">{{ $t('super.status_trial') }}</option>
                                    <option value="suspended">{{ $t('super.status_suspended') }}</option>
                                    <option value="expired">{{ $t('super.status_expired') }}</option>
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-slate-400 font-bold">{{ $t('super.extend_days') }}:</label>
                                <input
                                    v-model.number="extendDays"
                                    type="number"
                                    min="0"
                                    class="w-full h-11 bg-slate-800 border border-slate-700 rounded-2xl px-3.5 text-xs sm:text-sm text-white font-mono shadow-inner focus:outline-none focus:border-indigo-500"
                                />
                            </div>

                            <button
                                @click="updateStatus"
                                type="button"
                                class="w-full h-11 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs transition active:scale-95 cursor-pointer shadow-indigo-600/30 shadow-lg mt-2 flex items-center justify-center"
                            >
                                {{ $t('common.save') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Feature Overrides Matrix (2 Cols) -->
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-4 sm:p-5 space-y-4 shadow-xs">
                    <h2 class="font-black text-sm text-white border-b border-slate-800 pb-3 flex items-center gap-2">
                        <span>🛡️</span>
                        <span>{{ $t('super.feature_overrides') }}</span>
                    </h2>

                    <div class="space-y-6">
                        <div v-for="(moduleFeatures, moduleName) in grouped_features" :key="moduleName" class="space-y-2.5">
                            <h3 class="text-xs font-black text-indigo-400 uppercase tracking-wider">
                                {{ moduleName }}
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div
                                    v-for="feat in moduleFeatures"
                                    :key="feat.key"
                                    class="p-3.5 rounded-2xl border flex items-center justify-between transition shadow-xs"
                                    :class="isFeatureEnabled(feat.key) ? 'bg-slate-800/80 border-indigo-500/40' : 'bg-slate-900/40 border-slate-800'"
                                >
                                    <div>
                                        <div class="font-bold text-xs text-white">{{ feat.name }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ feat.key }}</div>
                                    </div>

                                    <button
                                        @click="toggleFeature(feat.key)"
                                        type="button"
                                        class="h-9 min-w-[52px] px-3 rounded-xl text-xs font-black transition active:scale-95 cursor-pointer flex items-center justify-center"
                                        :class="isFeatureEnabled(feat.key) ? 'bg-emerald-500 text-slate-950 shadow-emerald-500/20 shadow-md' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'"
                                    >
                                        {{ isFeatureEnabled(feat.key) ? 'ON' : 'OFF' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>
