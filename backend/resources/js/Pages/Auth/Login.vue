<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Phone,
    Lock,
    Eye,
    EyeOff,
    LogIn,
    Key,
    Crown
} from 'lucide-vue-next';

const props = defineProps({
    tenant: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    phone: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};

const fillAccount = (phone, password) => {
    form.phone = phone;
    form.password = password;
};
</script>

<template>
    <Head :title="`${$t('auth.login_title')} | ${tenant?.name || 'سرور كوفي ERP'}`" />

    <div class="min-h-screen bg-slate-950 flex items-center justify-center p-4 sm:p-6 selection:bg-amber-500 selection:text-white relative overflow-hidden font-sans" dir="rtl">
        <!-- Glowing Ambient Lighting Background Blobs -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md bg-slate-900/90 backdrop-blur-2xl border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 relative z-10">
            <!-- Header / Brand Logo -->
            <div class="text-center space-y-3">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-white dark:bg-slate-800 p-2 shadow-2xl shadow-theme-primary border border-slate-200 dark:border-slate-700">
                    <img src="/logo-light.png" alt="Logo Light" class="w-full h-full object-contain filter drop-shadow-sm dark:hidden">
                    <img src="/logo-dark.png" alt="Logo Dark" class="w-full h-full object-contain filter drop-shadow-sm hidden dark:block">
                </div>
                <div>
                    <h1 class="text-2xl font-black text-white font-tajawal tracking-tight">
                        {{ tenant?.name || $t('auth.app_name') }}
                    </h1>
                    <p class="text-xs text-slate-400 font-bold mt-1">
                        {{ $t('auth.subtitle') }}
                    </p>
                </div>
            </div>

            <!-- Validation Errors Global Alert -->
            <div v-if="form.errors.phone" class="p-3.5 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-xs text-rose-400 font-bold flex items-center gap-2">
                <AlertTriangle class="w-4 h-4 shrink-0" />
                <span>{{ form.errors.phone }}</span>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Phone / Username Field -->
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-300 mb-1.5 font-tajawal">
                        {{ $t('auth.phone') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            v-model="form.phone"
                            type="text"
                            id="phone"
                            required
                            autofocus
                            dir="ltr"
                            :placeholder="$t('auth.phone_placeholder')"
                            class="w-full pr-10 pl-4 py-3 bg-slate-950/80 border border-slate-700 rounded-2xl text-white text-sm font-mono focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all placeholder:text-slate-500"
                            :class="{ 'border-rose-500 focus:ring-rose-500': form.errors.phone }"
                        >
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 pointer-events-none">
                            <Phone class="w-4 h-4" />
                        </span>
                    </div>
                </div>

                <!-- Password Field with Toggle -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-300 mb-1.5 font-tajawal">
                        {{ $t('auth.password_label') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            required
                            dir="ltr"
                            :placeholder="$t('auth.password_placeholder')"
                            class="w-full pr-10 pl-11 py-3 bg-slate-950/80 border border-slate-700 rounded-2xl text-white text-sm font-mono focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all placeholder:text-slate-500"
                            :class="{ 'border-rose-500 focus:ring-rose-500': form.errors.password }"
                        >
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 pointer-events-none">
                            <Lock class="w-4 h-4" />
                        </span>
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 hover:text-amber-400 transition-colors cursor-pointer"
                        >
                            <Eye v-if="!showPassword" class="w-4 h-4" />
                            <EyeOff v-else class="w-4 h-4" />
                        </button>
                    </div>
                    <span v-if="form.errors.password" class="text-xs text-rose-500 mt-1.5 block font-bold">
                        {{ form.errors.password }}
                    </span>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-amber-500 focus:ring-amber-500 cursor-pointer"
                        >
                        <span class="text-xs text-slate-300 font-semibold font-tajawal">
                            {{ $t('auth.remember_me') }}
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3.5 px-6 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-black rounded-2xl shadow-lg shadow-amber-600/30 hover:shadow-amber-500/40 transition-all font-tajawal flex items-center justify-center gap-2 cursor-pointer text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <template v-if="!form.processing">
                            <LogIn class="w-4 h-4" />
                            <span>{{ $t('auth.login_button') }}</span>
                        </template>
                        <span v-else class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ $t('auth.logging_in') }}</span>
                        </span>
                    </button>
                </div>
            </form>

            <!-- Quick Credentials Helper Box -->
            <div class="p-4 bg-slate-950/60 border border-slate-800 rounded-2xl space-y-2 text-xs">
                <div class="flex items-center justify-between text-slate-400 font-bold font-tajawal">
                    <span class="flex items-center gap-1.5">
                        <Key class="w-3.5 h-3.5 text-amber-400" />
                        <span>{{ $t('auth.quick_accounts') }}</span>
                    </span>
                    <span class="text-[10px] text-amber-400">{{ $t('auth.click_to_fill') }}</span>
                </div>
                <div class="grid grid-cols-1 gap-2 pt-1">
                    <button
                        type="button"
                        @click="fillAccount('01012316954', 'password')"
                        class="w-full flex items-center justify-between p-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700/60 text-right transition-colors cursor-pointer group shadow-xs"
                    >
                        <div>
                            <p class="font-bold text-white group-hover:text-amber-400 transition-colors flex items-center gap-1.5">
                                <Crown class="w-3.5 h-3.5 text-purple-400" />
                                <span>{{ $t('auth.super_admin_1') }}</span>
                            </p>
                            <p class="text-[11px] text-slate-400 font-mono" dir="ltr">01012316954</p>
                        </div>
                        <span class="text-[10px] px-2 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/30 rounded-lg font-mono">
                            password
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="fillAccount('01558088841', '123456789')"
                        class="w-full flex items-center justify-between p-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700/60 text-right transition-colors cursor-pointer group shadow-xs"
                    >
                        <div>
                            <p class="font-bold text-white group-hover:text-amber-400 transition-colors flex items-center gap-1.5">
                                <Crown class="w-3.5 h-3.5 text-purple-400" />
                                <span>{{ $t('auth.super_admin_2') }}</span>
                            </p>
                            <p class="text-[11px] text-slate-400 font-mono" dir="ltr">01558088841</p>
                        </div>
                        <span class="text-[10px] px-2 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/30 rounded-lg font-mono">
                            123456789
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
