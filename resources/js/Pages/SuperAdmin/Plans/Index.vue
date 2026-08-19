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
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-white">{{ $t('super.plans') }}</h1>
                    <p class="text-xs text-slate-400 font-bold mt-0.5">
                        {{ $t('super.platform_subtitle') }}
                    </p>
                </div>
            </div>

            <!-- Plans Selector Horizontal Tabs -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <button
                    v-for="p in plans"
                    :key="p.id"
                    @click="selectedPlan = p"
                    type="button"
                    class="p-4 rounded-3xl border text-right transition"
                    :class="selectedPlan?.id === p.id ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-900 border-slate-800 text-slate-300 hover:bg-slate-800'"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black">{{ p.name }}</span>
                        <span v-if="p.is_popular" class="px-2 py-0.5 rounded-full text-[9px] font-black bg-amber-500 text-slate-950">
                            {{ $t('super.is_popular') }}
                        </span>
                    </div>
                    <div class="text-lg font-black font-mono mt-2">
                        {{ formatMoney(p.price_monthly) }} <span class="text-[10px]">{{ $t('common.currency') }}/شهر</span>
                    </div>
                    <div class="text-[10px] opacity-75 mt-1">
                        {{ p.tenants_count || 0 }} {{ $t('super.tenants') }}
                    </div>
                </button>
            </div>

            <!-- Selected Plan Editor Card -->
            <div v-if="selectedPlan" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h2 class="text-base font-black text-white">{{ $t('super.update_plan') }}: {{ selectedPlan.name }}</h2>
                        <p class="text-xs text-slate-400 font-mono">Slug: {{ selectedPlan.slug }}</p>
                    </div>

                    <button
                        @click="savePlan"
                        type="button"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs shadow-lg shadow-indigo-600/30 transition flex items-center gap-1.5"
                    >
                        <span>💾</span>
                        <span>{{ $t('common.save') }}</span>
                    </button>
                </div>

                <!-- Limits Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">{{ $t('super.monthly_price') }}:</label>
                        <input
                            v-model.number="selectedPlan.price_monthly"
                            type="number"
                            class="w-full h-10 bg-slate-800 border border-slate-700 rounded-xl px-3 text-xs text-white font-mono font-bold"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">{{ $t('super.yearly_price') }}:</label>
                        <input
                            v-model.number="selectedPlan.price_yearly"
                            type="number"
                            class="w-full h-10 bg-slate-800 border border-slate-700 rounded-xl px-3 text-xs text-white font-mono font-bold"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">{{ $t('super.max_users') }}:</label>
                        <input
                            v-model.number="selectedPlan.max_users"
                            type="number"
                            class="w-full h-10 bg-slate-800 border border-slate-700 rounded-xl px-3 text-xs text-white font-mono"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">{{ $t('super.max_stores') }}:</label>
                        <input
                            v-model.number="selectedPlan.max_stores"
                            type="number"
                            class="w-full h-10 bg-slate-800 border border-slate-700 rounded-xl px-3 text-xs text-white font-mono"
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
                        <div v-for="(moduleFeatures, moduleName) in grouped_features" :key="moduleName" class="space-y-2">
                            <h4 class="text-xs font-black text-indigo-400 uppercase tracking-wider">
                                {{ moduleName }}
                            </h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                <div
                                    v-for="feat in moduleFeatures"
                                    :key="feat.key"
                                    @click="togglePlanFeature(feat.key)"
                                    class="p-3 rounded-2xl border flex items-center justify-between cursor-pointer transition"
                                    :class="selectedPlan.features?.[feat.key] ? 'bg-indigo-600/15 border-indigo-500/50 text-white' : 'bg-slate-800/30 border-slate-800 text-slate-400'"
                                >
                                    <div>
                                        <div class="font-bold text-xs">{{ feat.name }}</div>
                                        <div class="text-[10px] font-mono opacity-60">{{ feat.key }}</div>
                                    </div>
                                    <span class="text-xs font-black" :class="selectedPlan.features?.[feat.key] ? 'text-emerald-400' : 'text-slate-600'">
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
