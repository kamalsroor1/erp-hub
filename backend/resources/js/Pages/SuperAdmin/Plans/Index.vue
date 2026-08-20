<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    plans: Array,
    grouped_features: Object,
});

const selectedPlan = ref(props.plans?.[0] || null);

const { formatMoney } = useMoney();

const togglePlanFeature = (featureKey) => {
    if (!selectedPlan.value) return;
    const currentFeatures = { ...selectedPlan.value.features };
    currentFeatures[featureKey] = !currentFeatures[featureKey];
    selectedPlan.value.features = currentFeatures;
};

const savePlan = () => {
    if (!selectedPlan.value) return;
    router.put(`/admin/super/plans/${selectedPlan.value.id}`, {
        name: selectedPlan.value.name,
        price_monthly: selectedPlan.value.price_monthly,
        price_yearly: selectedPlan.value.price_yearly,
        max_users: selectedPlan.value.max_users,
        max_stores: selectedPlan.value.max_stores,
        max_items: selectedPlan.value.max_items,
        max_invoices_per_month: selectedPlan.value.max_invoices_per_month,
        is_active: selectedPlan.value.is_active,
        is_popular: selectedPlan.value.is_popular,
        features: selectedPlan.value.features,
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="$t('super.plans')" />

    <SuperAdminLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-white">{{ $t('super.plans') }}</h1>
                    <p class="text-xs text-slate-400 font-bold mt-0.5">
                        {{ $t('super.platform_subtitle') }}
                    </p>
                </div>
            </div>

            <!-- Plans Selector Horizontal Tabs -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
                <button
                    v-for="p in plans"
                    :key="p.id"
                    @click="selectedPlan = p"
                    type="button"
                    class="p-3.5 sm:p-4 rounded-3xl border text-right transition active:scale-95 cursor-pointer shadow-xs"
                    :class="selectedPlan?.id === p.id ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-900 border-slate-800 text-slate-300 hover:bg-slate-800'"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black">{{ p.name }}</span>
                        <span v-if="p.is_popular" class="px-2 py-0.5 rounded-full text-[9px] font-black bg-amber-500 text-slate-950">
                            {{ $t('super.is_popular') }}
                        </span>
                    </div>
                    <div class="text-base sm:text-lg font-black font-mono mt-2">
                        {{ formatMoney(p.price_monthly) }} <span class="text-[10px]">{{ $t('common.currency') }}/شهر</span>
                    </div>
                    <div class="text-[10px] opacity-75 mt-1 font-tajawal">
                        {{ p.tenants_count || 0 }} {{ $t('super.tenants') }}
                    </div>
                </button>
            </div>

            <!-- Selected Plan Editor Card -->
            <div v-if="selectedPlan" class="bg-slate-900 border border-slate-800 rounded-3xl p-4 sm:p-6 space-y-6 shadow-xs">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-black text-white">{{ $t('super.update_plan') }}: {{ selectedPlan.name }}</h2>
                        <p class="text-xs text-slate-400 font-mono">Slug: {{ selectedPlan.slug }}</p>
                    </div>

                    <button
                        @click="savePlan"
                        type="button"
                        class="w-full sm:w-auto h-11 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs shadow-lg shadow-indigo-600/30 transition active:scale-95 cursor-pointer flex items-center justify-center gap-1.5"
                    >
                        <span>💾</span>
                        <span>{{ $t('common.save') }}</span>
                    </button>
                </div>

                <!-- Limits Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 font-tajawal">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-400">{{ $t('super.monthly_price') }}:</label>
                        <input
                            v-model.number="selectedPlan.price_monthly"
                            type="number"
                            class="w-full h-11 bg-slate-800 border border-slate-700 rounded-2xl px-3.5 text-xs sm:text-sm text-white font-mono font-bold focus:outline-none focus:border-indigo-500 shadow-inner"
                        />
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-400">{{ $t('super.yearly_price') }}:</label>
                        <input
                            v-model.number="selectedPlan.price_yearly"
                            type="number"
                            class="w-full h-11 bg-slate-800 border border-slate-700 rounded-2xl px-3.5 text-xs sm:text-sm text-white font-mono font-bold focus:outline-none focus:border-indigo-500 shadow-inner"
                        />
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-400">{{ $t('super.max_users') }}:</label>
                        <input
                            v-model.number="selectedPlan.max_users"
                            type="number"
                            class="w-full h-11 bg-slate-800 border border-slate-700 rounded-2xl px-3.5 text-xs sm:text-sm text-white font-mono focus:outline-none focus:border-indigo-500 shadow-inner"
                        />
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-400">{{ $t('super.max_stores') }}:</label>
                        <input
                            v-model.number="selectedPlan.max_stores"
                            type="number"
                            class="w-full h-11 bg-slate-800 border border-slate-700 rounded-2xl px-3.5 text-xs sm:text-sm text-white font-mono focus:outline-none focus:border-indigo-500 shadow-inner"
                        />
                    </div>
                </div>

                <!-- Plan Features Matrix -->
                <div class="space-y-6 pt-4 border-t border-slate-800">
                    <h3 class="text-sm font-black text-white flex items-center gap-2">
                        <span>🛡️</span>
                        <span>{{ $t('super.feature_overrides') }}</span>
                    </h3>

                    <div class="space-y-6">
                        <div v-for="(moduleFeatures, moduleName) in grouped_features" :key="moduleName" class="space-y-2.5">
                            <h4 class="text-xs font-black text-indigo-400 uppercase tracking-wider">
                                {{ moduleName }}
                            </h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                                <div
                                    v-for="feat in moduleFeatures"
                                    :key="feat.key"
                                    @click="togglePlanFeature(feat.key)"
                                    class="min-h-[48px] p-3 rounded-2xl border flex items-center justify-between cursor-pointer transition active:scale-98 shadow-xs"
                                    :class="selectedPlan.features?.[feat.key] ? 'bg-indigo-600/15 border-indigo-500/50 text-white' : 'bg-slate-800/30 border-slate-800 text-slate-400'"
                                >
                                    <div>
                                        <div class="font-bold text-xs">{{ feat.name }}</div>
                                        <div class="text-[10px] font-mono opacity-60">{{ feat.key }}</div>
                                    </div>
                                    <span class="text-xs font-black px-2 py-0.5 rounded-lg" :class="selectedPlan.features?.[feat.key] ? 'text-emerald-400 bg-emerald-500/10' : 'text-slate-600 bg-slate-800/50'">
                                        {{ selectedPlan.features?.[feat.key] ? '✓' : '✕' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>
