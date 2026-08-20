<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
});

const form = useForm({
    name: props.user.name,
    phone: props.user.phone,
    email: props.user.email || '',
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
    theme_preference: props.user.theme_preference || 'dark',
});

const submitProfile = () => {
    form.put('/profile', {
        preserveScroll: true,
        onSuccess: () => {
            form.current_password = '';
            form.new_password = '';
            form.new_password_confirmation = '';
        }
    });
};
</script>

<template>
    <Head :title="$t('profile.title')" />

    <AppLayout>
        <div class="max-w-2xl mx-auto space-y-6 font-tajawal">
            <!-- Header -->
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">👤</span>
                    <h1 class="text-xl sm:text-2xl font-black text-white">
                        {{ $t('profile.title') }}
                    </h1>
                </div>
                <p class="text-xs text-slate-400 font-bold">
                    {{ $t('profile.subtitle') }}
                </p>
            </div>

            <form @submit.prevent="submitProfile" class="space-y-6">
                <!-- User Info -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                    <h2 class="text-sm font-black text-white border-b border-slate-800 pb-3 flex items-center gap-2">
                        <span>📋</span>
                        <span>{{ $t('profile.basic_info') }}</span>
                    </h2>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('profile.full_name') }}</label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('profile.phone_for_login') }}</label>
                            <input
                                v-model="form.phone"
                                type="text"
                                required
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('profile.email_optional') }}</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>
                    </div>
                </div>

                <!-- Theme Preference -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                    <h2 class="text-sm font-black text-white border-b border-slate-800 pb-3 flex items-center gap-2">
                        <span>🎨</span>
                        <span>{{ $t('profile.theme_pref') }}</span>
                    </h2>

                    <div class="grid grid-cols-2 gap-3">
                        <button
                            @click="form.theme_preference = 'dark'"
                            type="button"
                            class="py-3 px-4 rounded-2xl border text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer"
                            :class="form.theme_preference === 'dark' ? 'bg-amber-500 text-slate-950 font-black border-amber-400' : 'bg-slate-950 border-slate-800 text-slate-400'"
                        >
                            <span>🌙</span>
                            <span>{{ $t('profile.dark_mode') }}</span>
                        </button>

                        <button
                            @click="form.theme_preference = 'light'"
                            type="button"
                            class="py-3 px-4 rounded-2xl border text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer"
                            :class="form.theme_preference === 'light' ? 'bg-amber-500 text-slate-950 font-black border-amber-400' : 'bg-slate-950 border-slate-800 text-slate-400'"
                        >
                            <span>☀️</span>
                            <span>{{ $t('profile.light_mode') }}</span>
                        </button>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                    <h2 class="text-sm font-black text-white border-b border-slate-800 pb-3 flex items-center gap-2">
                        <span>🔒</span>
                        <span>{{ $t('profile.change_password') }}</span>
                    </h2>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('profile.current_password') }}</label>
                            <input
                                v-model="form.current_password"
                                type="password"
                                placeholder="••••••••"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">{{ $t('profile.new_password') }}</label>
                                <input
                                    v-model="form.new_password"
                                    type="password"
                                    placeholder="••••••••"
                                    class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">{{ $t('profile.confirm_new_password') }}</label>
                                <input
                                    v-model="form.new_password_confirmation"
                                    type="password"
                                    placeholder="••••••••"
                                    class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="h-12 px-8 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/25 flex items-center justify-center gap-2 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                    >
                        <span>💾</span>
                        <span>{{ form.processing ? $t('profile.saving_profile') : $t('profile.save_profile_btn') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>