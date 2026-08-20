<script setup>
import { ref, computed } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import SkeletonCard from '@/Components/SkeletonCard.vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    invoices: Array,
    summary: Object,
    pagination: Object,
    filters: Object,
    activeStore: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || 'all');
const isFiltering = ref(false);

const showActionSheet = ref(false);
const showCancelModal = ref(false);
const showCollectModal = ref(false);
const activeInvoice = ref(null);

const cancelForm = useForm({
    reason: '',
});

const collectForm = useForm({
    customer_id: null,
    amount: '',
    payment_type: 'cash',
    notes: '',
});

// Instant Real-time Client Filtering
const filteredInvoices = computed(() => {
    let list = props.invoices || [];

    if (status.value !== 'all') {
        list = list.filter(inv => inv.payment_status === status.value);
    }

    if (!search.value.trim()) {
        return list;
    }

    const q = search.value.toLowerCase().trim();
    return list.filter(inv => 
        (inv.invoice_number && inv.invoice_number.toLowerCase().includes(q)) ||
        (inv.customer?.name && inv.customer.name.toLowerCase().includes(q)) ||
        (inv.customer?.phone && inv.customer.phone.includes(q))
    );
});

const handleSearchInput = () => {
    isFiltering.value = true;
    setTimeout(() => {
        isFiltering.value = false;
    }, 150);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    return dateStr.substring(0, 10);
};

const openActions = (inv) => {
    haptic.light();
    activeInvoice.value = inv;
    showActionSheet.value = true;
};

const openCancelFromSheet = () => {
    if (!activeInvoice.value) return;
    showActionSheet.value = false;
    haptic.heavy();
    cancelForm.reason = 'طلب العميل إلغاء الفاتورة وإرجاع البضاعة';
    showCancelModal.value = true;
};

const submitCancel = () => {
    if (!activeInvoice.value) return;
    cancelForm.post(`/invoices/${activeInvoice.value.id}/cancel`, {
        onSuccess: () => {
            haptic.success();
            showCancelModal.value = false;
        }
    });
};

const openCollectFromSheet = () => {
    if (!activeInvoice.value) return;
    const inv = activeInvoice.value;
    collectForm.customer_id = inv.customer_id;
    collectForm.amount = Math.max(0, parseFloat(inv.remaining_amount || 0)).toString();
    collectForm.notes = `سداد فاتورة رقم ${inv.invoice_number}`;
    showActionSheet.value = false;
    showCollectModal.value = true;
};

const submitCollect = () => {
    collectForm.post('/payments/customer-receipt', {
        onSuccess: () => {
            haptic.success();
            showCollectModal.value = false;
            collectForm.reset();
        }
    });
};

const getInvoiceWhatsApp = (inv) => {
    if (!inv) return '#';
    const phone = inv.customer?.phone ? inv.customer.phone.replace(/[^0-9]/g, '') : '';
    const phoneWithCode = phone.startsWith('01') ? '2' + phone : phone;
    const text = `☕ *فاتورة مبيعات - سرور كوفي ERP*\n--------------------------------\n📄 رقم الفاتورة: ${inv.invoice_number}\n📅 التاريخ: ${inv.invoice_date}\n👤 العميل: ${inv.customer?.name || 'عميل نقدي'}\n💵 الإجمالي: ${Number(inv.net_total).toFixed(2)} ج.م\n✅ المدفوع: ${Number(inv.paid_amount).toFixed(2)} ج.م\n⚠️ المتبقي: ${Number(inv.remaining_amount).toFixed(2)} ج.م\nشكراً لتعاملكم معنا!`;
    return `https://wa.me/${phoneWithCode}?text=${encodeURIComponent(text)}`;
};
</script>

<template>
    <MobileLayout>
        <div class="space-y-3.5 pb-12">
            <!-- Header -->
            <div class="flex items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>🧾</span>
                        <span>فواتير المبيعات</span>
                    </h2>
                    <p class="text-xs text-slate-400 font-bold">{{ activeStore?.name || 'الفرع الرئيسي' }}</p>
                </div>

                <Link
                    href="/pos"
                    class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-md shadow-emerald-600/20 flex items-center gap-1.5 touch-active"
                >
                    <span>➕</span>
                    <span>فاتورة جديدة</span>
                </Link>
            </div>

            <!-- Summary KPI -->
            <div class="grid grid-cols-2 gap-2 text-center text-xs">
                <div class="bg-white dark:bg-slate-900 p-3 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
                    <div class="text-[10px] text-slate-500 font-bold mb-0.5">إجمالي المبيعات</div>
                    <div class="text-sm font-black text-slate-900 dark:text-white font-mono">
                        {{ Number(summary?.total_sales || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 p-3 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
                    <div class="text-[10px] text-slate-500 font-bold mb-0.5">المحصل نقداً</div>
                    <div class="text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono">
                        {{ Number(summary?.total_paid || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                    </div>
                </div>
            </div>

            <!-- Instant Search Bar -->
            <div class="relative">
                <input
                    v-model="search"
                    @input="handleSearchInput"
                    type="text"
                    placeholder="بحث فوري برقم الفاتورة أو اسم العميل..."
                    class="w-full h-11 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl ps-10 pe-9 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-emerald-500"
                >
                <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 text-slate-400 text-sm">
                    🔍
                </div>
                <button
                    v-if="search"
                    @click="search = ''; haptic.light();"
                    type="button"
                    class="absolute inset-y-0 end-0 flex items-center pe-3 text-slate-400 text-xs font-bold"
                >
                    ✕
                </button>
            </div>

            <!-- Skeleton Loading State During Filtering -->
            <div v-if="isFiltering">
                <SkeletonCard :count="3" :lines="2" />
            </div>

            <!-- Invoices Clean List -->
            <div v-else class="space-y-3">
                <div
                    v-for="inv in filteredInvoices"
                    :key="inv.id"
                    class="block bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3 hover:border-emerald-500/50 transition"
                >
                    <!-- Top header row -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                #{{ inv.invoice_number }}
                            </span>
                            <span class="text-[10px] px-2.5 py-0.5 rounded-full font-bold" :class="inv.status === 'cancelled' ? 'bg-rose-500/15 text-rose-500 font-black' : (inv.payment_status === 'paid' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600' : (inv.payment_status === 'partially_paid' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-600'))">
                                {{ inv.status === 'cancelled' ? 'ملغاة ❌' : (inv.payment_status === 'paid' ? 'مدفوعة ✓' : (inv.payment_status === 'partially_paid' ? 'مسدد جزئي ⏳' : 'آجلة ⚠️')) }}
                            </span>
                        </div>

                        <span class="text-[10px] text-slate-400 font-mono">{{ formatDate(inv.invoice_date) }}</span>
                    </div>

                    <!-- Client and Amount -->
                    <div class="flex items-center justify-between text-xs">
                        <div>
                            <div class="font-extrabold text-slate-900 dark:text-white">{{ inv.customer?.name || 'عميل نقدي' }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ inv.customer?.phone || 'بدون هاتف' }}</div>
                        </div>

                        <div class="text-end">
                            <div class="font-black font-mono text-base text-slate-900 dark:text-white">
                                {{ Number(inv.net_total || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }} ج.م
                            </div>
                            <div v-if="parseFloat(inv.remaining_amount) > 0 && inv.status !== 'cancelled'" class="text-[10px] text-rose-500 font-mono font-bold">
                                باقي: {{ Number(inv.remaining_amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                            </div>
                        </div>
                    </div>

                    <!-- Clean 2-Button Action Bar (Main Action + Native ⋯ Menu) -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
                        <!-- Primary: View Details -->
                        <Link
                            :href="`/invoices/${inv.id}`"
                            class="flex-1 h-9 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-800 dark:text-slate-200 font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 transition touch-active"
                        >
                            <span>👁️</span>
                            <span>تفاصيل وبنود الفاتورة</span>
                        </Link>

                        <!-- Quick Print -->
                        <a
                            :href="`/invoices/${inv.id}/print/thermal`"
                            target="_blank"
                            class="h-9 px-3 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 text-emerald-600 dark:text-emerald-400 font-bold text-xs rounded-xl flex items-center justify-center gap-1 transition touch-active shrink-0"
                            title="طباعة حراري"
                        >
                            <span>🖨️</span>
                            <span>إيصال</span>
                        </a>

                        <!-- Native Action Sheet Trigger Button (⋯) -->
                        <button
                            @click="openActions(inv)"
                            type="button"
                            class="w-9 h-9 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl flex items-center justify-center text-sm font-black transition touch-active shrink-0 border border-slate-200 dark:border-slate-700"
                            title="خيارات الفاتورة"
                        >
                            ⋯
                        </button>
                    </div>
                </div>

                <div v-if="filteredInvoices.length === 0" class="text-center py-10 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <div class="text-3xl mb-1">🔍</div>
                    <div class="text-xs font-bold text-slate-600 dark:text-slate-300">لا توجد فواتير مطابقة للبحث</div>
                    <p class="text-[10px] text-slate-400 mt-1">تأكد من رقم الفاتورة أو اسم العميل</p>
                </div>
            </div>

            <!-- Native Bottom Action Sheet for Invoice -->
            <div
                v-if="showActionSheet"
                @click="showActionSheet = false"
                class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-end justify-center select-none animate-in fade-in duration-150"
            >
                <div
                    @click.stop
                    class="w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 shadow-2xl p-5 pb-8 space-y-4 animate-in slide-in-from-bottom duration-200"
                >
                    <!-- Drag Indicator -->
                    <div class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto -mt-2 mb-2"></div>

                    <!-- Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <div class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span>🧾</span>
                                <span>فاتورة مبيعات #{{ activeInvoice?.invoice_number }}</span>
                            </div>
                            <div class="text-xs text-slate-400 font-bold mt-0.5">العميل: {{ activeInvoice?.customer?.name || 'عميل نقدي' }}</div>
                        </div>

                        <div class="text-end">
                            <div class="text-[10px] text-slate-400 font-bold">الإجمالي</div>
                            <div class="text-sm font-black font-mono text-emerald-600 dark:text-emerald-400">
                                {{ Number(activeInvoice?.net_total || 0).toLocaleString('en-US') }} ج.م
                            </div>
                        </div>
                    </div>

                    <!-- Action List -->
                    <div class="space-y-1.5">
                        <!-- 1. View -->
                        <Link
                            :href="`/invoices/${activeInvoice?.id}`"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center text-sm font-bold">👁️</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">عرض تفاصيل الفاتورة</div>
                                    <div class="text-[10px] text-slate-400">استعراض بنود وأوزان وخصومات الفاتورة</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </Link>

                        <!-- 2. Thermal Receipt -->
                        <a
                            :href="`/invoices/${activeInvoice?.id}/print/thermal`"
                            target="_blank"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-sm font-bold">🖨️</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">طباعة إيصال كاشير حراري (80mm)</div>
                                    <div class="text-[10px] text-slate-400">متوافق مع طابعات البلوتوث والشبكة</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </a>

                        <!-- 3. A4 Print -->
                        <a
                            :href="`/invoices/${activeInvoice?.id}/print/a4`"
                            target="_blank"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center text-sm font-bold">📄</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">طباعة فاتورة رسمية (A4 / PDF)</div>
                                    <div class="text-[10px] text-slate-400">تصدير الفاتورة بتصميم ضريبي كامل</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </a>

                        <!-- 4. Quick Collect -->
                        <button
                            v-if="parseFloat(activeInvoice?.remaining_amount || 0) > 0 && activeInvoice?.status !== 'cancelled'"
                            @click="openCollectFromSheet"
                            type="button"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-sm font-bold">💵</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">تحصيل باقي الفاتورة الآجلة</div>
                                    <div class="text-[10px] text-rose-500 font-bold font-mono">متبقي: {{ Number(activeInvoice?.remaining_amount).toLocaleString('en-US') }} ج.م</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </button>

                        <!-- 5. WhatsApp -->
                        <a
                            v-if="activeInvoice?.customer?.phone"
                            :href="getInvoiceWhatsApp(activeInvoice)"
                            target="_blank"
                            class="w-full p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 flex items-center justify-between text-start transition touch-active"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-sm font-bold">💬</div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">مشاركة الفاتورة عبر WhatsApp</div>
                                    <div class="text-[10px] text-slate-400">إرسال تفاصيل الفاتورة لرقم العميل</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold">‹</span>
                        </a>

                        <!-- 6. Cancel / Void Invoice -->
                        <button
                            v-if="activeInvoice?.status !== 'cancelled'"
                            @click="openCancelFromSheet"
                            type="button"
                            class="w-full p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 flex items-center justify-between text-start transition touch-active text-rose-600 dark:text-rose-400"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-rose-500/15 text-rose-600 flex items-center justify-center text-sm font-bold">🚫</div>
                                <div>
                                    <div class="text-xs font-black">إلغاء واسترجاع الفاتورة</div>
                                    <div class="text-[10px] text-rose-400">عكس المخزن وتصفير الأثر المالي بأمان</div>
                                </div>
                            </div>
                            <span class="font-bold">‹</span>
                        </button>
                    </div>

                    <!-- Close Sheet -->
                    <button
                        @click="showActionSheet = false"
                        type="button"
                        class="w-full py-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-2xl transition touch-active"
                    >
                        إغلاق القائمة
                    </button>
                </div>
            </div>

            <!-- Cancel Invoice Modal -->
            <div v-if="showCancelModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4 animate-in fade-in duration-150">
                <div class="bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl w-full max-w-md p-5 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-3 animate-in slide-in-from-bottom duration-200">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div>
                            <h3 class="text-base font-black text-rose-600 flex items-center gap-1.5">
                                <span>⚠️</span>
                                <span>إلغاء فاتورة مبيعات</span>
                            </h3>
                            <p class="text-[11px] text-slate-400 font-bold">رقم الفاتورة: #{{ activeInvoice?.invoice_number }}</p>
                        </div>
                        <button @click="showCancelModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                    </div>

                    <div class="p-3 bg-rose-50 dark:bg-rose-950/40 rounded-2xl border border-rose-200 dark:border-rose-900/50 text-xs text-rose-600 space-y-1">
                        <div class="font-bold">⚠️ تنبيه هام:</div>
                        <p class="text-[11px]">سيتم إلغاء الفاتورة وعكس أثرها المالي وإرجاع كافة الأصناف المباعة إلى رصيد المخزن فوراً وفق الأصول المحاسبية.</p>
                    </div>

                    <form @submit.prevent="submitCancel" class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">سبب الإلغاء *:</label>
                            <input v-model="cancelForm.reason" type="text" required placeholder="اكتب سبب إلغاء الفاتورة..." class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 outline-none">
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" :disabled="cancelForm.processing" class="flex-1 h-12 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-extrabold text-xs rounded-xl shadow-md flex items-center justify-center gap-1.5 transition touch-active">
                                <span>{{ cancelForm.processing ? 'جاري الإلغاء...' : 'تأكيد إلغاء الفاتورة' }}</span>
                            </button>
                            <button @click="showCancelModal = false" type="button" class="h-12 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition touch-active">
                                تراجع
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Collect Modal -->
            <div v-if="showCollectModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4 animate-in fade-in duration-150">
                <div class="bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl w-full max-w-md p-5 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-3 animate-in slide-in-from-bottom duration-200">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">تحصيل باقي الفاتورة 💵</h3>
                            <p class="text-[11px] text-slate-400 font-bold">فاتورة رقم: #{{ activeInvoice?.invoice_number }} ({{ activeInvoice?.customer?.name }})</p>
                        </div>
                        <button @click="showCollectModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                    </div>

                    <form @submit.prevent="submitCollect" class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">المبلغ المحصل (ج.م) *:</label>
                            <input v-model="collectForm.amount" type="number" step="0.001" required dir="ltr" class="w-full h-12 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-4 text-base font-mono font-black text-emerald-600 dark:text-emerald-400 focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">طريقة القبض:</label>
                            <select v-model="collectForm.payment_type" class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 text-xs font-bold text-slate-900 dark:text-white outline-none">
                                <option value="cash">💵 نقداً (كاش)</option>
                                <option value="bank_transfer">🏦 تحويل بنكي / فودافون كاش</option>
                            </select>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" :disabled="collectForm.processing" class="flex-1 h-12 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-xs rounded-xl shadow-md flex items-center justify-center gap-1.5 transition touch-active">
                                <span>{{ collectForm.processing ? 'جاري الحفظ...' : 'حفظ وقبض الفلوس ✓' }}</span>
                            </button>
                            <button @click="showCollectModal = false" type="button" class="h-12 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition touch-active">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
