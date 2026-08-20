<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    has_active: { type: Boolean, default: false },
    active_shift: { type: Object, default: null },
    metrics: { type: Object, default: null },
});

// 1. Open Shift Form
const openForm = useForm({
    opening_cash_balance: '500.00',
    notes: '',
});

const submitOpenShift = () => {
    haptic.success();
    openForm.post('/shifts/open');
};

const setQuickOpeningCash = (amount) => {
    haptic.light();
    openForm.opening_cash_balance = Number(amount).toFixed(2);
};

// 2. Close Shift Modal & Form
const showCloseModal = ref(false);
const closeForm = useForm({
    shift_id: props.active_shift?.id || null,
    actual_cash_balance: props.metrics?.expected_cash_balance || '0.00',
    notes: '',
});

const openCloseShiftSheet = () => {
    haptic.medium();
    closeForm.shift_id = props.active_shift?.id;
    closeForm.actual_cash_balance = props.metrics?.expected_cash_balance || '0.00';
    showCloseModal.value = true;
};

// Live Discrepancy (Actual - Expected)
const discrepancy = computed(() => {
    const expected = Number(props.metrics?.expected_cash_balance || 0);
    const actual = Number(closeForm.actual_cash_balance || 0);
    return Number((actual - expected).toFixed(2));
});

const submitCloseShift = () => {
    haptic.warning();
    closeForm.post('/shifts/close', {
        onSuccess: () => {
            showCloseModal.value = false;
        }
    });
};
</script>

<template>
    <MobileLayout>
        <div class="space-y-4 pb-24 select-none">
            <!-- Header Banner -->
            <div class="bg-gradient-to-l from-emerald-600 to-emerald-700 rounded-3xl p-4 text-white shadow-lg shadow-emerald-600/20 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🔐</span>
                        <h2 class="text-base font-black">إدارة ورديات الكاشير والدرج</h2>
                    </div>
                    <p class="text-[11px] text-emerald-100 font-bold mt-0.5">
                        متابعة عهدة الدرج، النقدية، وتقفيل الوردية اليومي (Z-Report)
                    </p>
                </div>
                <Link href="/pos" class="w-9 h-9 rounded-2xl bg-white/20 hover:bg-white/30 backdrop-blur-md flex items-center justify-center text-xs font-bold transition">
                    ⚡
                </Link>
            </div>

            <!-- STATE 1: NO ACTIVE SHIFT (Prompt to Open Shift) -->
            <div v-if="!has_active" class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 text-center">
                <div class="w-16 h-16 rounded-3xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-3xl mx-auto shadow-inner">
                    🔒
                </div>

                <div class="space-y-1">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">
                        لا توجد وردية عمل مفتوحة حالياً
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-bold leading-relaxed">
                        لبدء تسجيل المبيعات وحركات الكاشير، يرجى فتح وردية جديدة وتحديد رصيد الفكة الافتتاحي في الدرج.
                    </p>
                </div>

                <form @submit.prevent="submitOpenShift" class="space-y-4 text-start pt-2 border-t border-slate-100 dark:border-slate-800">
                    <!-- Opening Cash Input -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 dark:text-slate-200 mb-1.5">
                            رصيد الفكة الافتتاحي بالدرج (ج.م): <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="openForm.opening_cash_balance"
                            type="number"
                            step="0.5"
                            min="0"
                            required
                            class="w-full h-12 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 font-mono font-black text-base text-slate-900 dark:text-white text-center"
                        />
                        <!-- Quick Cash Chips -->
                        <div class="grid grid-cols-4 gap-1.5 mt-2">
                            <button
                                v-for="amt in [100, 200, 500, 1000]"
                                :key="amt"
                                @click="setQuickOpeningCash(amt)"
                                type="button"
                                class="py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-emerald-500/20 text-slate-700 dark:text-slate-300 rounded-xl text-[11px] font-bold border border-slate-200 dark:border-slate-700 transition"
                            >
                                {{ amt }} ج.م
                            </button>
                        </div>
                    </div>

                    <!-- Notes Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            ملاحظات الوردية (اختياري):
                        </label>
                        <input
                            v-model="openForm.notes"
                            type="text"
                            placeholder="مثال: وردية صباحية - كاشير محمد"
                            class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white"
                        />
                    </div>

                    <!-- Open Shift Submit Button -->
                    <button
                        :disabled="openForm.processing"
                        type="submit"
                        class="w-full h-13 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white font-black text-sm rounded-2xl shadow-xl shadow-emerald-600/30 flex items-center justify-center gap-2 transition touch-active"
                    >
                        <span>🔓</span>
                        <span>{{ openForm.processing ? 'جاري فتح الوردية...' : 'فتح الوردية وبدء العمل' }}</span>
                    </button>
                </form>
            </div>

            <!-- STATE 2: ACTIVE SHIFT DASHBOARD -->
            <div v-else class="space-y-4">
                <!-- Shift Header Status Card -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-emerald-500/40 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white">
                                    {{ active_shift.shift_number }}
                                </h3>
                                <span class="text-[10px] text-slate-400 font-mono">
                                    فُتحت: {{ active_shift.opened_at }}
                                </span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-bold text-[10px]">
                            🟢 وردية نشطة
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-2xl">
                            <div class="text-[10px] text-slate-400 font-bold">الكاشير المسئول</div>
                            <div class="font-extrabold text-slate-800 dark:text-slate-200 mt-0.5 truncate">
                                {{ active_shift.cashier_name }}
                            </div>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-2xl">
                            <div class="text-[10px] text-slate-400 font-bold">الفرع</div>
                            <div class="font-extrabold text-slate-800 dark:text-slate-200 mt-0.5 truncate">
                                {{ active_shift.store_name }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Shift Financial Metrics Grid -->
                <div class="grid grid-cols-2 gap-2.5">
                    <!-- 1. Cash Sales -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-1">
                        <div class="flex items-center justify-between text-slate-400 text-xs">
                            <span class="font-bold text-[11px]">مبيعات كاش</span>
                            <span>💵</span>
                        </div>
                        <div class="text-base font-black font-mono text-emerald-600 dark:text-emerald-400">
                            {{ Number(metrics?.total_cash_sales || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م
                        </div>
                    </div>

                    <!-- 2. Credit Sales -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-1">
                        <div class="flex items-center justify-between text-slate-400 text-xs">
                            <span class="font-bold text-[11px]">مبيعات آجلة (شكك)</span>
                            <span>📑</span>
                        </div>
                        <div class="text-base font-black font-mono text-amber-600 dark:text-amber-400">
                            {{ Number(metrics?.total_credit_sales || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م
                        </div>
                    </div>

                    <!-- 3. Payments Collected -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-1">
                        <div class="flex items-center justify-between text-slate-400 text-xs">
                            <span class="font-bold text-[11px]">تحصيلات وسندات</span>
                            <span>📥</span>
                        </div>
                        <div class="text-base font-black font-mono text-teal-600 dark:text-teal-400">
                            {{ Number(metrics?.total_payments_collected || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م
                        </div>
                    </div>

                    <!-- 4. Expenses & Outflows -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-1">
                        <div class="flex items-center justify-between text-slate-400 text-xs">
                            <span class="font-bold text-[11px]">مصروفات ونثريات</span>
                            <span>💸</span>
                        </div>
                        <div class="text-base font-black font-mono text-rose-600 dark:text-rose-400">
                            -{{ Number(metrics?.total_expenses || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م
                        </div>
                    </div>
                </div>

                <!-- Expected Cash in Drawer Card -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-950 text-white rounded-3xl p-4 border border-slate-800 shadow-xl space-y-2">
                    <div class="flex items-center justify-between text-xs text-slate-400 font-bold">
                        <span>رصيد النقدية المفترض وجوده بالدرج:</span>
                        <span>فكة + مبيعات - مصاريف</span>
                    </div>

                    <div class="text-2xl font-black font-mono text-emerald-400 flex items-center justify-between">
                        <span>{{ Number(metrics?.expected_cash_balance || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span>
                        <span class="text-sm font-sans text-slate-400">جنيه مصري</span>
                    </div>

                    <div class="text-[10px] text-slate-400 flex items-center justify-between pt-2 border-t border-slate-800">
                        <span>الرصيد الافتتاحي: {{ Number(metrics?.opening_cash_balance || 0).toFixed(2) }} ج.م</span>
                        <span>المرتجعات: {{ Number(metrics?.total_refunds || 0).toFixed(2) }} ج.م</span>
                    </div>
                </div>

                <!-- Close Shift Button -->
                <button
                    @click="openCloseShiftSheet"
                    type="button"
                    class="w-full h-13 bg-rose-600 hover:bg-rose-700 active:scale-98 text-white font-black text-sm rounded-2xl shadow-xl shadow-rose-600/30 flex items-center justify-center gap-2 transition touch-active"
                >
                    <span>🔒</span>
                    <span>تقفيل الوردية الحالية وإغلاق الدرج (Z-Report)</span>
                </button>
            </div>

            <!-- CLOSE SHIFT BOTTOM SHEET / MODAL -->
            <div
                v-if="showCloseModal"
                @click="showCloseModal = false"
                class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-end justify-center select-none"
            >
                <div
                    @click.stop
                    class="w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 shadow-2xl p-5 pb-8 space-y-4 max-h-[90vh] overflow-y-auto animate-slide-up"
                >
                    <!-- Drag Handle -->
                    <div class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto -mt-1 mb-1"></div>

                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🔒</span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white">
                                    تقفيل الوردية اليومية (Z-Report)
                                </h3>
                                <p class="text-[10px] text-slate-400 font-bold">
                                    جرد النقدية الفعلية ومطابقتها مع الحسابات
                                </p>
                            </div>
                        </div>
                        <button @click="showCloseModal = false" type="button" class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-xs">✕</button>
                    </div>

                    <!-- Expected Balance Info -->
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-500">النقدية المحسوبة بالسستم:</span>
                        <span class="font-black font-mono text-emerald-600 dark:text-emerald-400 text-sm">
                            {{ Number(metrics?.expected_cash_balance || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م
                        </span>
                    </div>

                    <form @submit.prevent="submitCloseShift" class="space-y-4">
                        <!-- Actual Cash Input -->
                        <div>
                            <label class="block text-xs font-black text-slate-700 dark:text-slate-200 mb-1.5">
                                النقدية الفعلية بعد عدّ الدرج (ج.م): <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="closeForm.actual_cash_balance"
                                type="number"
                                step="0.5"
                                min="0"
                                required
                                class="w-full h-12 bg-white dark:bg-slate-900 border-2 border-emerald-500/50 rounded-2xl px-3 font-mono font-black text-lg text-slate-900 dark:text-white text-center focus:border-emerald-500"
                            />
                        </div>

                        <!-- Discrepancy Indicator Banner -->
                        <div
                            class="p-3 rounded-2xl border text-xs font-black flex items-center justify-between"
                            :class="discrepancy === 0 ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-600 dark:text-emerald-400' : (discrepancy > 0 ? 'bg-sky-500/10 border-sky-500/30 text-sky-600 dark:text-sky-400' : 'bg-rose-500/10 border-rose-500/30 text-rose-600 dark:text-rose-400')"
                        >
                            <span>حالة النقدية:</span>
                            <span v-if="discrepancy === 0">✓ مطابقة تماماً (بدون عجز أو زيادة)</span>
                            <span v-else-if="discrepancy > 0">⚡ زيادة بالدرج: +{{ discrepancy.toFixed(2) }} ج.م</span>
                            <span v-else>⚠️ عجز بالدرج: {{ discrepancy.toFixed(2) }} ج.م</span>
                        </div>

                        <!-- Notes Input -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 mb-1">
                                سبب العجز/الزيادة أو ملاحظات الإغلاق:
                            </label>
                            <input
                                v-model="closeForm.notes"
                                type="text"
                                placeholder="اختياري..."
                                class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white"
                            />
                        </div>

                        <!-- Submit Button -->
                        <button
                            :disabled="closeForm.processing"
                            type="submit"
                            class="w-full h-13 bg-rose-600 hover:bg-rose-700 text-white font-black text-sm rounded-2xl shadow-xl shadow-rose-600/30 flex items-center justify-center gap-2 transition touch-active"
                        >
                            <span>🔒</span>
                            <span>{{ closeForm.processing ? 'جاري تقفيل الوردية...' : 'تأكيد التقفيل وطباعة تقرير Z-Report' }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
