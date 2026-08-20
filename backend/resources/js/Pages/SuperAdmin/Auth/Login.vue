<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    platform_name: { type: String, default: 'مخزني ERP' },
    platform_version: { type: String, default: 'Enterprise Hub' },
});

const form = useForm({
    phone: '',
    password: '',
    remember: true,
});

const showPassword = ref(false);

const submit = () => {
    form.post('/admin/login', {
        onFinish: () => form.reset('password'),
    });
};

const fillDemoCredentials = () => {
    form.phone = '01012316954';
    form.password = 'password';
};
</script>

<template>
    <Head title="تسجيل دخول الإدارة المركزية | Super Admin" />

    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center items-center p-4 relative overflow-hidden font-sans select-none" dir="rtl">
        <!-- Ambient Glowing Background Circles -->
        <div class="absolute top-1/4 -right-20 w-96 h-96 bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-1/4 -left-20 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md space-y-6 relative z-10">
            <!-- Platform Header -->
            <div class="text-center space-y-3">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-tr from-purple-900 via-slate-900 to-slate-800 border border-purple-500/30 shadow-2xl shadow-purple-600/20 text-3xl ring-4 ring-purple-500/10">
                    👑
                </div>
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-purple-500/15 border border-purple-500/30 text-purple-400 text-xs font-black tracking-wider uppercase">
                        Super Admin Hub
                    </span>
                    <h1 class="text-2xl font-black text-white mt-2 font-tajawal">لوحة التحكم المركزية</h1>
                    <p class="text-xs text-slate-400 font-bold mt-1">منصة {{ platform_name }} لإدارة المستأجرين والاشتراكات</p>
                </div>
            </div>

            <!-- Login Card -->
            <div class="bg-slate-900/90 backdrop-blur-xl border border-purple-500/20 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-5 font-tajawal">
                <!-- Flash / General Errors -->
                <div v-if="form.errors.phone" class="p-3.5 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-400 text-xs font-bold flex items-center gap-2">
                    <span>⚠️</span>
                    <span>{{ form.errors.phone }}</span>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Phone / Email -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300 block">رقم الهاتف أو البريد الإلكتروني</label>
                        <div class="relative">
                            <input
                                v-model="form.phone"
                                type="text"
                                required
                                autofocus
                                placeholder="01012316954"
                                class="w-full h-11 px-4 rounded-xl bg-slate-950/80 border border-slate-800 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 text-white text-sm font-sans transition outline-none"
                                :class="form.errors.phone ? 'border-rose-500/50' : ''"
                            />
                            <span class="absolute end-3.5 top-3 text-slate-500 text-sm">👤</span>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300 block">كلمة المرور المركزية</label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="••••••••"
                                class="w-full h-11 px-4 rounded-xl bg-slate-950/80 border border-slate-800 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 text-white text-sm font-sans transition outline-none"
                                :class="form.errors.password ? 'border-rose-500/50' : ''"
                            />
                            <button
                                @click="showPassword = !showPassword"
                                type="button"
                                class="absolute end-3 top-2.5 p-1 text-slate-500 hover:text-slate-300 transition"
                            >
                                <span class="text-xs">{{ showPassword ? '🙈' : '👁️' }}</span>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-[11px] text-rose-400 font-bold mt-1">{{ form.errors.password }}</p>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-400 font-bold">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="w-4 h-4 rounded-md bg-slate-950 border-slate-800 text-purple-600 focus:ring-purple-500/30"
                            />
                            <span>تذكر جلسة الدخول</span>
                        </label>

                        <button
                            @click="fillDemoCredentials"
                            type="button"
                            class="text-[11px] text-purple-400 hover:text-purple-300 font-bold transition"
                        >
                            ⚡ تعبئة حساب السوبر أدمن
                        </button>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-12 rounded-2xl bg-gradient-to-r from-purple-600 via-purple-500 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-black text-sm flex items-center justify-center gap-2 shadow-lg shadow-purple-600/30 transition transform active:scale-98 disabled:opacity-50 cursor-pointer"
                    >
                        <span v-if="form.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span v-else>👑 تسجيل الدخول للوحة المركزية</span>
                    </button>
                </form>
            </div>

            <!-- Footer Notes -->
            <div class="text-center space-y-2 text-xs text-slate-500 font-bold">
                <p>⚠️ هذه الصفحة مخصصة لمديري النظام السحابي فقط</p>
                <div class="flex items-center justify-center gap-4 text-[11px]">
                    <a href="/login" class="text-amber-500 hover:text-amber-400 transition font-bold">
                        ← الانتقال لتسجيل دخول المتجر
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
