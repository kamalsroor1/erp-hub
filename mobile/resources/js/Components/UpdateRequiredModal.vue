<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { haptic } from '@/Utils/haptics';

const page = usePage();

const appUpdate = computed(() => page.props.appUpdate || {});
const hasUpdate = computed(() => Boolean(appUpdate.value.has_update));
const isForced = computed(() => Boolean(appUpdate.value.force_update));

const dismissKey = computed(() => 'sroor_update_dismissed_v' + (appUpdate.value.latest_version || '1.1.0'));
const bannerHideKey = computed(() => 'sroor_banner_hidden_v' + (appUpdate.value.latest_version || '1.1.0'));

const isDismissed = ref(false);
const isBannerHidden = ref(false);

onMounted(() => {
    try {
        if (typeof window !== 'undefined') {
            if (sessionStorage.getItem(dismissKey.value) === 'true') {
                isDismissed.value = true;
            }
            if (sessionStorage.getItem(bannerHideKey.value) === 'true') {
                isBannerHidden.value = true;
            }

            window.addEventListener('open-app-update-modal', () => {
                isDismissed.value = false;
                isBannerHidden.value = false;
                sessionStorage.removeItem(dismissKey.value);
                sessionStorage.removeItem(bannerHideKey.value);
            });
        }
    } catch (e) {
        // ignore
    }
});

// Modal is visible if an update exists AND (it is forced OR user hasn't dismissed it in this session)
const shouldShowModal = computed(() => {
    if (!hasUpdate.value) return false;
    if (isForced.value) return true; // Forced: Cannot dismiss
    return !isDismissed.value;
});

// Download States: 'idle' | 'downloading' | 'completed' | 'error'
const downloadState = ref('idle');
const progressPercent = ref(0);
const downloadedMb = ref(0);
const totalMb = ref(0);
const errorMessage = ref('');
const downloadedBlobUrl = ref(null);

const dismissOptionalUpdate = () => {
    haptic.light();
    isDismissed.value = true;
    try {
        if (typeof window !== 'undefined') {
            sessionStorage.setItem(dismissKey.value, 'true');
        }
    } catch (e) {
        // ignore
    }
};

const openUpdateModal = () => {
    haptic.light();
    isDismissed.value = false;
    try {
        if (typeof window !== 'undefined') {
            sessionStorage.removeItem(dismissKey.value);
        }
    } catch (e) {
        // ignore
    }
};

const hideBanner = () => {
    haptic.light();
    isBannerHidden.value = true;
    try {
        if (typeof window !== 'undefined') {
            sessionStorage.setItem(bannerHideKey.value, 'true');
        }
    } catch (e) {
        // ignore
    }
};

const startInAppDownload = () => {
    haptic.medium();
    const url = appUpdate.value.download_url;
    if (!url) return;

    downloadState.value = 'downloading';
    progressPercent.value = 0;
    downloadedMb.value = 0;
    errorMessage.value = '';

    const xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.responseType = 'blob';

    xhr.onprogress = (event) => {
        if (event.lengthComputable) {
            const percent = Math.round((event.loaded / event.total) * 100);
            progressPercent.value = percent;
            downloadedMb.value = (event.loaded / (1024 * 1024)).toFixed(1);
            totalMb.value = (event.total / (1024 * 1024)).toFixed(1);
        } else {
            const loaded = (event.loaded / (1024 * 1024)).toFixed(1);
            downloadedMb.value = loaded;
            const approxTotal = appUpdate.value.file_size_mb || 285;
            totalMb.value = approxTotal;
            progressPercent.value = Math.min(99, Math.round((event.loaded / (approxTotal * 1024 * 1024)) * 100));
        }
    };

    xhr.onload = () => {
        if (xhr.status >= 200 && xhr.status < 300) {
            haptic.success();
            progressPercent.value = 100;
            downloadState.value = 'completed';

            const blob = xhr.response;
            const blobUrl = window.URL.createObjectURL(blob);
            downloadedBlobUrl.value = blobUrl;

            // Trigger prompt to install/save
            triggerApkInstall();
        } else {
            haptic.heavy();
            downloadState.value = 'error';
            errorMessage.value = 'فشل التحميل من السيرفر (كود: ' + xhr.status + ')';
        }
    };

    xhr.onerror = () => {
        haptic.heavy();
        downloadState.value = 'error';
        errorMessage.value = 'تعذر الاتصال بالسيرفر، تأكد من اتصال الإنترنت وحاول مجدداً.';
    };

    xhr.send();
};

const triggerApkInstall = () => {
    haptic.success();
    if (downloadedBlobUrl.value) {
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = downloadedBlobUrl.value;
        a.download = `sroor-coffee-erp-v${appUpdate.value.latest_version || '1.1.0'}.apk`;
        document.body.appendChild(a);
        a.click();
        setTimeout(() => {
            document.body.removeChild(a);
        }, 100);
    } else {
        window.location.href = appUpdate.value.download_url;
    }
};
</script>

<template>
    <div>
        <!-- 1. Optional In-Flow Top Alert Banner (Pushes content down, NO overlapping!) -->
        <div
            v-if="hasUpdate && !isForced && isDismissed && !isBannerHidden"
            class="mb-3.5 bg-gradient-to-r from-amber-500 to-amber-600 text-white p-3 rounded-2xl shadow-md shadow-amber-500/20 flex items-center justify-between gap-2 text-xs font-bold animate-in slide-in-from-top duration-300 border border-amber-400/30"
        >
            <div @click="openUpdateModal" class="flex items-center gap-2 cursor-pointer flex-1 min-w-0">
                <span class="text-base shrink-0">🚀</span>
                <span class="truncate">يتوفر تحديث جديد (v{{ appUpdate.latest_version }})</span>
            </div>

            <div class="flex items-center gap-1.5 shrink-0">
                <button
                    @click="openUpdateModal"
                    type="button"
                    class="px-2.5 py-1 rounded-xl bg-white text-amber-600 font-black text-[10px] shadow-xs active:scale-95 transition"
                >
                    تحديث الآن
                </button>
                <button
                    @click="hideBanner"
                    type="button"
                    class="w-6 h-6 rounded-lg bg-black/15 hover:bg-black/25 text-white flex items-center justify-center text-xs font-bold transition"
                    title="إخفاء التنبيه"
                >
                    ✕
                </button>
            </div>
        </div>

        <!-- 2. Main Update Modal (Fullscreen Overlay) -->
        <div
            v-if="shouldShowModal"
            class="fixed inset-0 z-[999] bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-4 select-none animate-in fade-in duration-300"
        >
            <div
                class="bg-white dark:bg-slate-900 w-full max-w-sm rounded-3xl border-2 border-amber-500/40 shadow-2xl p-6 text-center space-y-4 animate-in zoom-in-95 duration-200 relative"
            >
                <!-- Optional Close Button (Only if NOT forced) -->
                <button
                    v-if="!isForced"
                    @click="dismissOptionalUpdate"
                    type="button"
                    class="absolute top-4 left-4 w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-white flex items-center justify-center text-xs font-bold transition"
                    title="إغلاق والتذكير لاحقاً"
                >
                    ✕
                </button>

                <!-- Animated Icon State -->
                <div class="relative w-20 h-20 mx-auto">
                    <div v-if="downloadState === 'downloading'" class="absolute inset-0 bg-emerald-500/20 rounded-3xl animate-ping opacity-75"></div>
                    <div v-else class="absolute inset-0 bg-amber-500/20 rounded-3xl animate-ping opacity-75"></div>

                    <div
                        class="relative w-20 h-20 rounded-3xl flex items-center justify-center text-4xl shadow-lg transition duration-300"
                        :class="downloadState === 'completed' ? 'bg-gradient-to-tr from-emerald-500 to-emerald-400 shadow-emerald-500/30' : 'bg-gradient-to-tr from-amber-500 to-amber-400 shadow-amber-500/30'"
                    >
                        <span v-if="downloadState === 'completed'">✅</span>
                        <span v-else-if="downloadState === 'downloading'" class="animate-bounce">📥</span>
                        <span v-else>🚀</span>
                    </div>
                </div>

                <!-- Title & Subtitle -->
                <div class="space-y-1">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">
                        <span v-if="downloadState === 'downloading'">جاري تنزيل التحديث...</span>
                        <span v-else-if="downloadState === 'completed'">تم تنزيل التحديث بنجاح! 🎉</span>
                        <span v-else-if="isForced">تحديث جديد إلزامي متاح! 🔒</span>
                        <span v-else>يتوفر إصدار جديد من التطبيق ✨</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold leading-relaxed">
                        {{ downloadState === 'downloading' ? 'يرجى الانتظار لحين اكتمال التنزيل والتثبيت المباشر.' : (downloadState === 'completed' ? 'اضغط على زر التثبيت لبدء التحديث الفوري.' : (appUpdate.message || 'يرجى تحديث التطبيق للمتابعة والتمتع بأحدث الميزات والأمان.')) }}
                    </p>
                </div>

                <!-- Version Comparison Pills -->
                <div class="grid grid-cols-2 gap-2 p-3 bg-slate-50 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs">
                    <div>
                        <div class="text-[10px] text-slate-400 font-bold">الإصدار الحالي</div>
                        <div class="text-xs font-mono font-bold text-slate-600 dark:text-slate-400">
                            v{{ appUpdate.current_app_version || '1.0.0' }}
                        </div>
                    </div>
                    <div class="border-s border-slate-200 dark:border-slate-700 ps-2">
                        <div class="text-[10px] text-emerald-500 font-bold">الإصدار الجديد</div>
                        <div class="text-xs font-mono font-black text-emerald-600 dark:text-emerald-400">
                            v{{ appUpdate.latest_version || '1.1.0' }} ⚡
                        </div>
                    </div>
                </div>

                <!-- IN-APP LIVE PROGRESS BAR -->
                <div v-if="downloadState === 'downloading'" class="space-y-2 p-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-start animate-in fade-in">
                    <div class="flex items-center justify-between text-xs font-bold font-mono">
                        <span class="text-emerald-600 dark:text-emerald-400">جاري التحميل...</span>
                        <span class="text-emerald-700 dark:text-emerald-300 font-black text-sm">{{ progressPercent }}%</span>
                    </div>

                    <!-- Progress Track -->
                    <div class="w-full h-3.5 bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden p-0.5 border border-emerald-500/30">
                        <div
                            class="h-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-amber-400 rounded-full transition-all duration-200 ease-out shadow-xs"
                            :style="{ width: progressPercent + '%' }"
                        ></div>
                    </div>

                    <div class="flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400 font-mono">
                        <span>{{ downloadedMb }} MB</span>
                        <span>من {{ totalMb || appUpdate.file_size_mb || 285 }} MB</span>
                    </div>
                </div>

                <!-- Error Banner -->
                <div v-if="downloadState === 'error'" class="p-3 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-500 text-xs font-bold space-y-1">
                    <div>⚠️ فشل تنزيل التحديث:</div>
                    <p class="text-[11px] font-normal">{{ errorMessage }}</p>
                </div>

                <!-- Release Notes / Features (Shown before download starts) -->
                <div v-if="downloadState === 'idle' && appUpdate.release_notes?.length" class="text-start space-y-1.5 p-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs">
                    <div class="font-black text-amber-600 dark:text-amber-400 text-[11px] flex items-center gap-1">
                        <span>✨</span>
                        <span>أبرز ما في التحديث:</span>
                    </div>
                    <ul class="space-y-1 text-[11px] text-slate-700 dark:text-slate-300 font-medium">
                        <li v-for="(note, idx) in appUpdate.release_notes" :key="idx" class="flex items-start gap-1.5">
                            <span class="text-amber-500 font-bold">•</span>
                            <span>{{ note }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2">
                    <!-- 1. Idle: Start in-app download -->
                    <button
                        v-if="downloadState === 'idle'"
                        @click="startInAppDownload"
                        type="button"
                        class="w-full h-13 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 active:scale-98 text-white font-black text-sm rounded-2xl shadow-xl shadow-emerald-600/30 flex items-center justify-center gap-2 transition touch-active"
                    >
                        <span>📥</span>
                        <span>تنزيل وتثبيت التحديث داخل التطبيق</span>
                    </button>

                    <!-- 2. Optional Dismiss Button (Only if NOT forced) -->
                    <button
                        v-if="downloadState === 'idle' && !isForced"
                        @click="dismissOptionalUpdate"
                        type="button"
                        class="w-full h-10 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition touch-active"
                    >
                        تذكيري لاحقاً والمتابعة للعمل
                    </button>

                    <!-- 3. Downloading: Disabled Button -->
                    <div
                        v-else-if="downloadState === 'downloading'"
                        class="w-full h-13 bg-slate-100 dark:bg-slate-800 text-slate-500 font-black text-xs rounded-2xl flex items-center justify-center gap-2"
                    >
                        <div class="w-4 h-4 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                        <span>جاري استلام حزمة التحديث ({{ progressPercent }}%)...</span>
                    </div>

                    <!-- 4. Completed: Install APK Button -->
                    <button
                        v-else-if="downloadState === 'completed'"
                        @click="triggerApkInstall"
                        type="button"
                        class="w-full h-13 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 active:scale-98 text-white font-black text-sm rounded-2xl shadow-xl shadow-emerald-500/40 flex items-center justify-center gap-2 transition touch-active animate-pulse"
                    >
                        <span>⚡</span>
                        <span>تثبيت التحديث الآن (Install APK)</span>
                    </button>

                    <!-- 5. Error: Retry Button -->
                    <button
                        v-else-if="downloadState === 'error'"
                        @click="startInAppDownload"
                        type="button"
                        class="w-full h-13 bg-rose-600 hover:bg-rose-700 active:scale-98 text-white font-black text-xs rounded-2xl shadow-lg shadow-rose-600/30 flex items-center justify-center gap-2 transition touch-active"
                    >
                        <span>🔄</span>
                        <span>إعادة محاولة التحميل</span>
                    </button>
                </div>

                <p class="text-[10px] text-slate-400 font-semibold">
                    * يتم التحميل المباشر من السيرفر دون الحاجة لمتجر Google Play.
                </p>
            </div>
        </div>
    </div>
</template>
