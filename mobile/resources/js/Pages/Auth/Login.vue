<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';

const props = defineProps({
    defaultApiUrl: {
        type: String,
        default: 'http://127.0.0.1:8000/api/v1',
    },
    error: {
        type: String,
        default: '',
    },
});

const form = useForm({
    login: '01012316954',
    password: 'password',
    apiUrl: props.defaultApiUrl,
});

const showServerSettings = ref(false);
const isTesting = ref(false);
const testStatus = ref(null);

const testConnection = async () => {
    isTesting.value = true;
    testStatus.value = null;
    try {
        const res = await fetch(`${form.apiUrl.replace(/\/$/, '')}/customers`, {
            headers: { 'Accept': 'application/json' }
        });
        if (res.status === 200 || res.status === 401 || res.status === 403) {
            testStatus.value = { ok: true, msg: 'تم الاتصال بالسيرفر بنجاح ✅' };
        } else {
            testStatus.value = { ok: false, msg: `استجاب السيرفر برمز: ${res.status}` };
        }
    } catch (e) {
        testStatus.value = { ok: false, msg: `تعذر الوصول للسيرفر: ${e.message}` };
    } finally {
        isTesting.value = false;
    }
};

const submit = () => {
    form.post('/login');
};
</script>

<template>
    <MobileLayout>
        <div class="py-4">
            <!-- Header Logo & Branding -->
            <div class="text-center mb-6">
                <div class="w-18 h-18 mx-auto mb-3 rounded-2xl bg-gradient-to-br from-emerald-500 via-emerald-600 to-emerald-800 flex items-center justify-center text-white text-3xl font-extrabold shadow-lg shadow-emerald-500/20 border border-emerald-400/20">
                    ☕
                </div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">سرور كوفي ERP</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">لتوريدات خامات مطاحن البن والمبيعات</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 shadow-xl border border-slate-200 dark:border-slate-800 relative">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        تسجيل دخول الموبايل (Vue 3)
                    </h3>
                    
                    <button @click="showServerSettings = !showServerSettings" type="button" class="text-xs font-semibold text-slate-500 hover:text-emerald-500 flex items-center gap-1 transition">
                        <span>⚙️ إعدادات السيرفر</span>
                    </button>
                </div>

                <!-- Server IP / URL Settings (Collapsible) -->
                <div v-if="showServerSettings" class="mb-4 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs transition-all">
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">عنوان سيرفر الـ API (Host IP / Domain):</label>
                    <div class="flex gap-2">
                        <input v-model="form.apiUrl" type="text" dir="ltr" placeholder="http://192.168.1.32:8000/api/v1" class="flex-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-3 py-2 text-xs font-mono text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 outline-none">
                        <button @click="testConnection" :disabled="isTesting" type="button" class="bg-amber-600 hover:bg-amber-700 text-white font-bold px-3 py-2 rounded-xl text-xs flex items-center gap-1 transition">
                            <span v-if="!isTesting">فحص</span>
                            <span v-else>⏳</span>
                        </button>
                    </div>

                    <div v-if="testStatus" class="mt-2 text-[11px] font-semibold" :class="testStatus.ok ? 'text-emerald-500' : 'text-rose-500'">
                        {{ testStatus.msg }}
                    </div>
                </div>

                <!-- Error Alert -->
                <div v-if="error || form.errors.login || form.errors.password" class="mb-4 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-600 dark:text-rose-400 text-xs font-bold flex items-center gap-2">
                    <span>⚠️</span>
                    <span>{{ error || form.errors.login || form.errors.password }}</span>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Username / Phone -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            رقم الهاتف أو اسم المستخدم:
                        </label>
                        <input v-model="form.login" type="text" dir="ltr" placeholder="01012316954" class="w-full h-12 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-4 text-sm font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            كلمة المرور:
                        </label>
                        <input v-model="form.password" type="password" dir="ltr" placeholder="••••••••" class="w-full h-12 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-4 text-sm font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" :disabled="form.processing" class="w-full h-12 bg-gradient-to-r from-emerald-500 to-emerald-700 hover:from-emerald-600 hover:to-emerald-800 active:scale-[0.98] text-white font-extrabold text-sm rounded-2xl shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 transition duration-150">
                            <span v-if="!form.processing">تسجيل الدخول عبر السيرفر</span>
                            <span v-else class="flex items-center gap-1.5">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>جاري التحقق والاتصال...</span>
                            </span>
                        </button>
                    </div>
                </form>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-center text-[11px] text-slate-400">
                    الحساب الافتراضي: <span class="font-mono font-bold text-slate-600 dark:text-slate-300">01012316954</span> | الباسورد: <span class="font-mono font-bold text-slate-600 dark:text-slate-300">password</span>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
