<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

const props = defineProps({
    plans: Array,
    central_domain: String,
});

const form = useForm({
    name: '',
    slug: '',
    email: '',
    phone: '',
    plan_id: props.plans?.[0]?.id || '',
    password: '',
    custom_domain: '',
    trial_days: 14,
});

const submit = () => {
    form.post('/admin/super/tenants');
};
</script>

<template>
    <Head :title="$t('super.create_tenant')" />

    <SuperAdminLayout>
        <div class="max-w-3xl mx-auto space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-white">{{ $t('super.create_tenant') }}</h1>
                    <p class="text-xs text-slate-400 font-bold mt-0.5">
                        {{ $t('super.platform_subtitle') }}
                    </p>
                </div>

                <Link
                    href="/admin/super/tenants"
                    class="h-10 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 flex items-center gap-1.5 transition active:scale-95 shadow-xs border border-slate-700"
                >
                    <span>←</span>
                    <span>{{ $t('common.back') }}</span>
                </Link>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit" class="bg-slate-900 border border-slate-800 rounded-3xl p-4 sm:p-6 space-y-5 shadow-xs">
                <!-- Row 1: Company Name & Subdomain Slug -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            {{ $t('super.tenant_name') }} <span class="text-rose-400">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="مثال: متجر الرياض للقهوة"
                            class="w-full h-11 bg-slate-800 border border-slate-700 rounded-2xl px-4 text-xs sm:text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 font-bold shadow-inner"
                            required
                        />
                        <div v-if="form.errors.name" class="text-rose-400 text-[10px] mt-1">{{ form.errors.name }}</div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            {{ $t('super.subdomain') }} <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <input
                                v-model="form.slug"
                                type="text"
                                placeholder="riyadh-coffee"
                                class="w-full h-11 bg-slate-800 border border-slate-700 rounded-2xl pl-4 pr-3.5 text-xs sm:text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 font-mono font-bold shadow-inner"
                                required
                            />
                        </div>
                        <p class="text-[10px] text-indigo-400 font-mono mt-1">
                            Domain: {{ form.slug || 'slug' }}.{{ central_domain || 'localhost' }}
                        </p>
                        <div v-if="form.errors.slug" class="text-rose-400 text-[10px] mt-1">{{ form.errors.slug }}</div>
                    </div>
                </div>

                <!-- Row 2: Email & Phone -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            {{ $t('super.admin_email') }} <span class="text-rose-400">*</span>
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="admin@company.com"
                            class="w-full h-11 bg-slate-800 border border-slate-700 rounded-2xl px-4 text-xs sm:text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 font-mono shadow-inner"
                            required
                        />
                        <div v-if="form.errors.email" class="text-rose-400 text-[10px] mt-1">{{ form.errors.email }}</div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            {{ $t('super.admin_phone') }}
                        </label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            placeholder="01000000000"
                            class="w-full h-11 bg-slate-800 border border-slate-700 rounded-2xl px-4 text-xs sm:text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 font-mono shadow-inner"
                        />
                        <div v-if="form.errors.phone" class="text-rose-400 text-[10px] mt-1">{{ form.errors.phone }}</div>
                    </div>
                </div>

                <!-- Row 3: Admin Password & Plan Selection -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            {{ $t('super.admin_password') }} <span class="text-rose-400">*</span>
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="w-full h-11 bg-slate-800 border border-slate-700 rounded-2xl px-4 text-xs sm:text-sm text-white focus:outline-none focus:border-indigo-500 font-mono shadow-inner"
                            required
                        />
                        <div v-if="form.errors.password" class="text-rose-400 text-[10px] mt-1">{{ form.errors.password }}</div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            {{ $t('super.select_plan') }} <span class="text-rose-400">*</span>
                        </label>
                        <select
                            v-model="form.plan_id"
                            class="w-full h-11 bg-slate-800 border border-slate-700 rounded-2xl px-3.5 text-xs sm:text-sm text-white focus:outline-none focus:border-indigo-500 font-bold shadow-inner"
                            required
                        >
                            <option v-for="p in plans" :key="p.id" :value="p.id">
                                {{ p.name }} ({{ Number(p.price_monthly).toLocaleString() }} {{ $t('common.currency') }}/شهر)
                            </option>
                        </select>
                        <div v-if="form.errors.plan_id" class="text-rose-400 text-[10px] mt-1">{{ form.errors.plan_id }}</div>
                    </div>
                </div>

                <!-- Row 4: Custom Domain & Trial Days -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            {{ $t('super.custom_domain') }}
                        </label>
                        <input
                            v-model="form.custom_domain"
                            type="text"
                            placeholder="erp.mycompany.com"
                            class="w-full h-11 bg-slate-800 border border-slate-700 rounded-2xl px-4 text-xs sm:text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 font-mono shadow-inner"
                        />
                        <div v-if="form.errors.custom_domain" class="text-rose-400 text-[10px] mt-1">{{ form.errors.custom_domain }}</div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            {{ $t('super.trial_days') }}
                        </label>
                        <input
                            v-model.number="form.trial_days"
                            type="number"
                            min="0"
                            class="w-full h-11 bg-slate-800 border border-slate-700 rounded-2xl px-4 text-xs sm:text-sm text-white font-mono shadow-inner"
                        />
                        <div v-if="form.errors.trial_days" class="text-rose-400 text-[10px] mt-1">{{ form.errors.trial_days }}</div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-end gap-3">
                    <Link
                        href="/admin/super/tenants"
                        class="w-full sm:w-auto h-11 px-5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs flex items-center justify-center transition active:scale-95 border border-slate-700 shadow-xs"
                    >
                        {{ $t('common.cancel') }}
                    </Link>

                    <button
                        :disabled="form.processing"
                        type="submit"
                        class="w-full sm:w-auto h-11 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-black text-xs flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/25 transition active:scale-95 cursor-pointer"
                    >
                        <span>⚡</span>
                        <span>{{ form.processing ? 'جاري التجهيز...' : $t('super.save_provision') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </SuperAdminLayout>
</template>
