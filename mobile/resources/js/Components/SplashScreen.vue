<script setup>
import { ref, onMounted } from 'vue';

const showSplash = ref(false);
const progress = ref(10);
const statusText = ref('جاري تهيئة نظام سرور كوفي ERP...');

// Check immediately before mount to avoid any flash on subsequent navigations
if (typeof window !== 'undefined') {
    const isDone = window.__erpSplashDone || sessionStorage.getItem('erp_splash_done');
    if (!isDone) {
        showSplash.value = true;
    }
}

onMounted(() => {
    if (!showSplash.value) return;

    // Step 1: 700ms
    setTimeout(() => {
        progress.value = 35;
        statusText.value = 'فحص الاتصال والخدمات السحابية...';
    }, 700);

    // Step 2: 1500ms
    setTimeout(() => {
        progress.value = 65;
        statusText.value = 'مزامنة قواعد البيانات والفرع...';
    }, 1500);

    // Step 3: 2300ms
    setTimeout(() => {
        progress.value = 90;
        statusText.value = 'تحميل واجهة المستخدم والمبيعات...';
    }, 2300);

    // Step 4: 2800ms (Complete)
    setTimeout(() => {
        progress.value = 100;
        statusText.value = 'مرحباً بك في سرور كوفي ☕';
    }, 2800);

    // Step 5: 3200ms (Smooth Exit and record that it has been shown)
    setTimeout(() => {
        showSplash.value = false;
        window.__erpSplashDone = true;
        sessionStorage.setItem('erp_splash_done', 'true');
    }, 3200);
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-500 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-105 pointer-events-none"
    >
        <div
            v-if="showSplash"
            class="fixed inset-0 z-[99999] bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 flex flex-col items-center justify-between p-8 select-none"
        >
            <!-- Background Ambient Glow -->
            <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-64 h-64 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none animate-pulse"></div>
            <div class="absolute bottom-1/3 left-1/2 -translate-x-1/2 w-48 h-48 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="w-full"></div>

            <!-- Center Logo & Branding -->
            <div class="flex flex-col items-center text-center relative z-10 space-y-4">
                <!-- Coffee Logo Icon with Aura -->
                <div class="relative">
                    <div class="absolute -inset-2 bg-gradient-to-r from-emerald-500 to-amber-500 rounded-3xl blur-md opacity-40 animate-pulse"></div>
                    <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-emerald-500 via-emerald-600 to-slate-900 flex items-center justify-center text-5xl shadow-2xl border border-white/20 relative z-10 animate-bounce" style="animation-duration: 2s;">
                        ☕
                    </div>
                </div>

                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight flex items-center justify-center gap-2">
                        <span>سرور كوفي ERP</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold border border-emerald-500/30">Mobile</span>
                    </h1>
                    <p class="text-xs text-slate-400 font-semibold mt-1">
                        لتوريدات خامات مطاحن البن والمبيعات
                    </p>
                </div>
            </div>

            <!-- Bottom Loading Bar & Status -->
            <div class="w-full max-w-xs relative z-10 space-y-2.5">
                <div class="flex items-center justify-between text-[11px] font-bold text-slate-400">
                    <span class="flex items-center gap-1.5 text-emerald-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>{{ statusText }}</span>
                    </span>
                    <span class="font-mono text-amber-400">{{ progress }}%</span>
                </div>

                <!-- Progress Track -->
                <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden p-0.5 border border-slate-700/50 shadow-inner">
                    <div
                        class="h-full bg-gradient-to-r from-emerald-500 via-amber-400 to-emerald-400 rounded-full transition-all duration-300 ease-out shadow-sm"
                        :style="{ width: progress + '%' }"
                    ></div>
                </div>

                <div class="text-center text-[10px] text-slate-500 font-mono">
                    v1.0.0 • NativePHP Edition
                </div>
            </div>
        </div>
    </Transition>
</template>
