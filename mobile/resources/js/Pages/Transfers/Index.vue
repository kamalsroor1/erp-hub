<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    transfers: { type: Array, default: () => [] },
    total_count: { type: Number, default: 0 },
    items: { type: Array, default: () => [] },
    stores: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const showCreateModal = ref(false);

const form = useForm({
    from_store_id: props.stores[0]?.id || '',
    to_store_id: props.stores[1]?.id || props.stores[0]?.id || '',
    transfer_date: new Date().toISOString().split('T')[0],
    notes: '',
    items: [
        {
            item_id: props.items[0]?.id || '',
            quantity: 5,
        }
    ],
});

const openCreateModal = () => {
    haptic.medium();
    form.reset();
    form.from_store_id = props.stores[0]?.id || '';
    form.to_store_id = props.stores[1]?.id || props.stores[0]?.id || '';
    form.transfer_date = new Date().toISOString().split('T')[0];
    form.items = [
        {
            item_id: props.items[0]?.id || '',
            quantity: 5,
        }
    ];
    showCreateModal.value = true;
};

const addItemLine = () => {
    haptic.light();
    form.items.push({
        item_id: props.items[0]?.id || '',
        quantity: 5,
    });
};

const removeItemLine = (idx) => {
    if (form.items.length > 1) {
        form.items.splice(idx, 1);
    }
};

const submitTransfer = () => {
    if (form.from_store_id === form.to_store_id) {
        alert('يرجى اختيار فرعين مختلفين للتحويل بينهما.');
        return;
    }
    haptic.success();
    form.post('/transfers', {
        onSuccess: () => {
            showCreateModal.value = false;
        }
    });
};

const cancelTransfer = (t) => {
    haptic.warning();
    if (confirm(`هل أنت متأكد من إلغاء إذن التحويل #${t.transfer_number} وعكس أثر البضاعة بين الفرعين؟`)) {
        router.post(`/transfers/${t.id}/cancel`);
    }
};
</script>

<template>
    <MobileLayout>
        <div class="space-y-4 pb-24 select-none">
            <!-- Header Banner & Create Button -->
            <div class="bg-gradient-to-l from-teal-600 via-teal-700 to-slate-900 rounded-3xl p-4 text-white shadow-xl shadow-teal-900/30 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🚚</span>
                        <h2 class="text-base font-black">التحويل المخزني بين الفروع</h2>
                    </div>
                    <p class="text-[11px] text-teal-100 font-bold mt-0.5">
                        نقل خامات وشحنات البن من المخزن الرئيسي للفروع والعكس
                    </p>
                </div>

                <button
                    @click="openCreateModal"
                    type="button"
                    class="h-10 px-3.5 bg-white text-teal-700 hover:bg-teal-50 font-black text-xs rounded-2xl shadow-md flex items-center gap-1.5 transition touch-active shrink-0"
                >
                    <span>➕</span>
                    <span>إذن تحويل</span>
                </button>
            </div>

            <!-- Transfers List -->
            <div class="space-y-3">
                <div
                    v-for="t in transfers"
                    :key="t.id"
                    class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3 hover:border-teal-500/50 transition"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black font-mono text-teal-600 dark:text-teal-400">
                                #{{ t.transfer_number }}
                            </span>
                            <span
                                class="px-2 py-0.5 rounded-lg text-[10px] font-black"
                                :class="t.status === 'cancelled' ? 'bg-rose-500/10 text-rose-500' : 'bg-teal-500/10 text-teal-600 dark:text-teal-400'"
                            >
                                {{ t.status === 'cancelled' ? 'ملغي 🚫' : 'تم النقل والاعتماد ✓' }}
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">{{ t.transfer_date }}</span>
                    </div>

                    <!-- Branches Flow Card -->
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/70 rounded-2xl border border-slate-100 dark:border-slate-700 flex items-center justify-between text-xs font-bold">
                        <div class="text-center flex-1">
                            <span class="text-[9px] text-slate-400 block mb-0.5">من فرع / مخزن</span>
                            <span class="text-rose-600 dark:text-rose-400 font-black">{{ t.from_store?.name }}</span>
                        </div>

                        <div class="px-2 text-slate-400 text-lg">➔</div>

                        <div class="text-center flex-1">
                            <span class="text-[9px] text-slate-400 block mb-0.5">إلى فرع / مخزن</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-black">{{ t.to_store?.name }}</span>
                        </div>
                    </div>

                    <!-- Items Transferred -->
                    <div class="space-y-1 text-xs">
                        <div v-for="it in t.items" :key="it.id" class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/40">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ it.item?.name }}</span>
                            <span class="font-mono font-black text-teal-600 dark:text-teal-400">{{ Number(it.quantity).toFixed(2) }} كجم</span>
                        </div>
                    </div>

                    <div v-if="t.notes" class="text-[10px] text-slate-400 truncate">
                        ملاحظات: {{ t.notes }}
                    </div>

                    <!-- Cancel Action -->
                    <div v-if="t.status !== 'cancelled'" class="pt-1 flex justify-end">
                        <button
                            @click="cancelTransfer(t)"
                            type="button"
                            class="px-3 py-1.5 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 text-[11px] font-bold border border-rose-500/20 transition touch-active"
                        >
                            إلغاء التحويل وعكس المخزون 🚫
                        </button>
                    </div>
                </div>

                <div v-if="!transfers || transfers.length === 0" class="text-center py-10 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <div class="text-3xl mb-1">🚚</div>
                    <div class="text-xs font-bold text-slate-600 dark:text-slate-300">لا توجد أذونات تحويل مخزني مسجلة</div>
                </div>
            </div>

            <!-- CREATE TRANSFER MODAL SHEET -->
            <div v-if="showCreateModal" @click="showCreateModal = false" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-end justify-center select-none">
                <div @click.stop class="w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 shadow-2xl p-5 pb-8 space-y-4 max-h-[90vh] overflow-y-auto animate-slide-up">
                    <div class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto -mt-1 mb-1"></div>

                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🚚</span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white">إنشاء إذن تحويل مخزني</h3>
                                <p class="text-[10px] text-slate-400 font-bold">خصم الكمية من الفرع المصدر وإضافتها للفرع المستلم</p>
                            </div>
                        </div>
                        <button @click="showCreateModal = false" type="button" class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-xs">✕</button>
                    </div>

                    <form @submit.prevent="submitTransfer" class="space-y-3.5">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-500 mb-1">من مخزن / فرع:</label>
                                <select v-model="form.from_store_id" required class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-2.5 text-xs font-bold text-slate-900 dark:text-white">
                                    <option v-for="s in stores" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-500 mb-1">إلى مخزن / فرع:</label>
                                <select v-model="form.to_store_id" required class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-2.5 text-xs font-bold text-slate-900 dark:text-white">
                                    <option v-for="s in stores" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 mb-1">تاريخ التحويل:</label>
                            <input v-model="form.transfer_date" type="date" required class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-mono font-bold text-slate-900 dark:text-white" />
                        </div>

                        <!-- Items Table -->
                        <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-black text-slate-900 dark:text-white">الأصناف المطلوب تحويلها:</label>
                                <button @click="addItemLine" type="button" class="px-2.5 py-1 bg-teal-500/10 text-teal-600 rounded-xl text-[11px] font-black border border-teal-500/20">➕ إضافة صنف</button>
                            </div>

                            <div v-for="(line, idx) in form.items" :key="idx" class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <select v-model="line.item_id" class="flex-1 h-9 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-2 text-xs font-bold text-slate-900 dark:text-white">
                                        <option v-for="it in items" :key="it.id" :value="it.id">{{ it.name }} (كود: {{ it.code }})</option>
                                    </select>
                                    <button v-if="form.items.length > 1" @click="removeItemLine(idx)" type="button" class="w-7 h-7 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center text-xs font-bold">✕</button>
                                </div>

                                <div>
                                    <label class="block text-[10px] text-slate-400 font-bold mb-0.5">الوزن / الكمية المحولة (كجم):</label>
                                    <input v-model="line.quantity" type="number" step="0.1" min="0.001" required class="w-full h-9 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-2 text-center font-mono font-black text-xs text-slate-900 dark:text-white" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 mb-1">ملاحظات التحويل:</label>
                            <input v-model="form.notes" type="text" placeholder="اسم السائق أو رقم سيارة الشحن..." class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white" />
                        </div>

                        <button :disabled="form.processing" type="submit" class="w-full h-13 bg-teal-600 hover:bg-teal-700 text-white font-black text-sm rounded-2xl shadow-xl shadow-teal-600/30 flex items-center justify-center gap-2 transition touch-active">
                            <span>🚚</span>
                            <span>{{ form.processing ? 'جاري التحويل...' : 'اعتماد التحويل المخزني ونقل الأرصدة' }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
