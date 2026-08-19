<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    items: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
});

const { formatMoney } = useMoney();

const blendName = ref('توليفة بن مخصوصة (خلطة سرور المميزة)');
const targetWeightGrams = ref(250);
const selectedCustomerId = ref(props.customers[0]?.id || null);
const selectedItemIdToAdd = ref(null);
const roastType = ref('وسط');
const grindLevel = ref('تركي ناعم');
const cardamomGrams = ref(0);
const notes = ref('');

const presetWeights = [
    { label: 'ثمن كيلو (125 جم)', value: 125 },
    { label: 'ربع كيلو (250 جم)', value: 250 },
    { label: 'نصف كيلو (500 جم)', value: 500 },
    { label: 'كيلو كامل (1000 جم)', value: 1000 },
];

const customerOptions = computed(() => {
    return props.customers.map(c => ({
        id: c.id,
        name: `${c.name} ${c.phone ? '(' + c.phone + ')' : ''}`,
    }));
});

const availableItemOptions = computed(() => {
    return props.items.map(item => ({
        id: item.id,
        name: `${item.name} - سعر الكيلو: ${item.selling_price} ج.م (المتوفر: ${item.current_stock} كجم)`,
    }));
});

// Components array: [{ item_id, name, percentage, grams, cost_price, selling_price }]
const components = ref([]);

// Initialize with first 2 items if available
if (props.items.length >= 2) {
    components.value = [
        {
            item_id: props.items[0].id,
            name: props.items[0].name,
            percentage: 60,
            cost_price: Number(props.items[0].cost_price),
            selling_price: Number(props.items[0].selling_price),
        },
        {
            item_id: props.items[1].id,
            name: props.items[1].name,
            percentage: 40,
            cost_price: Number(props.items[1].cost_price),
            selling_price: Number(props.items[1].selling_price),
        }
    ];
} else if (props.items.length === 1) {
    components.value = [
        {
            item_id: props.items[0].id,
            name: props.items[0].name,
            percentage: 100,
            cost_price: Number(props.items[0].cost_price),
            selling_price: Number(props.items[0].selling_price),
        }
    ];
}

const addComponent = () => {
    if (!selectedItemIdToAdd.value) return;
    const item = props.items.find(i => i.id === selectedItemIdToAdd.value);
    if (!item) return;

    if (components.value.some(c => c.item_id === item.id)) {
        alert('هذا الصنف مضاف بالفعل في التوليفة');
        return;
    }

    components.value.push({
        item_id: item.id,
        name: item.name,
        percentage: 0,
        cost_price: Number(item.cost_price),
        selling_price: Number(item.selling_price),
    });

    selectedItemIdToAdd.value = null;
};

const removeComponent = (index) => {
    components.value.splice(index, 1);
};

// Calculations
const calculatedComponents = computed(() => {
    const target = Number(targetWeightGrams.value) || 0;
    return components.value.map(c => {
        const pct = Number(c.percentage) || 0;
        const grams = (target * pct) / 100;
        const kg = grams / 1000;
        const cost = kg * c.cost_price;
        const price = kg * c.selling_price;

        return {
            ...c,
            grams: Number(grams.toFixed(1)),
            kg: Number(kg.toFixed(3)),
            cost: Number(cost.toFixed(2)),
            price: Number(price.toFixed(2)),
        };
    });
});

const totalPercentage = computed(() => {
    return components.value.reduce((sum, c) => sum + (Number(c.percentage) || 0), 0);
});

const totalCalculatedCost = computed(() => {
    let cost = calculatedComponents.value.reduce((sum, c) => sum + c.cost, 0);
    // Add Cardamom cost if any (assuming approx 1.2 EGP per gram)
    if (cardamomGrams.value > 0) {
        cost += (Number(cardamomGrams.value) * 1.5);
    }
    return Number(cost.toFixed(2));
});

const totalCalculatedPrice = computed(() => {
    let price = calculatedComponents.value.reduce((sum, c) => sum + c.price, 0);
    if (cardamomGrams.value > 0) {
        price += (Number(cardamomGrams.value) * 2.5);
    }
    return Number(price.toFixed(2));
});

const profitMargin = computed(() => {
    if (totalCalculatedPrice.value <= 0) return 0;
    const profit = totalCalculatedPrice.value - totalCalculatedCost.value;
    return Number(((profit / totalCalculatedPrice.value) * 100).toFixed(1));
});

// Submit Invoice Form
const form = useForm({
    blend_name: '',
    customer_id: null,
    components: [],
    notes: '',
});

const submitBlendInvoice = () => {
    if (components.value.length === 0) {
        alert('يرجى إضافة مكونات التوليفة أولاً');
        return;
    }
    if (totalPercentage.value !== 100) {
        if (!confirm(`مجموع النسب الحالية هو ${totalPercentage.value}% (وليس 100%). هل ترغب في المتابعة بهذا الوزن التقديري؟`)) {
            return;
        }
    }

    form.blend_name = `${blendName.value} (${roastType.value} - ${grindLevel.value} - ${targetWeightGrams.value}جم)`;
    form.customer_id = selectedCustomerId.value;
    form.components = calculatedComponents.value.map(c => ({
        item_id: c.item_id,
        grams: c.grams,
        unit_price: c.selling_price,
    }));
    form.notes = `درجة التحميص: ${roastType.value} | الطحن: ${grindLevel.value}` + (cardamomGrams.value > 0 ? ` | حبهان: ${cardamomGrams.value}جم` : '') + (notes.value ? ` | ${notes.value}` : '');

    form.post('/coffee-blender/invoice', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="حاسبة وتوليفات البن والمطحنة" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">☕</span>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            أداة وحاسبة توليفات البن الاحترافية
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        توليف حبوب البن بالنسب المئوية والجرامات، حساب التكلفة، وإصدار الفاتورة للعميل مباشرة
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 Cols: Blending Workspace -->
                <div class="lg:col-span-2 space-y-5">
                    <!-- Blend Configuration -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                        <h2 class="text-sm font-black text-white border-b border-slate-800 pb-2.5 flex items-center gap-2">
                            <span>⚙️</span>
                            <span>إعدادات التوليفة والوزن المستهدف</span>
                        </h2>

                        <div class="space-y-3">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">اسم التوليفة / كود الخلطة</label>
                                <input
                                    v-model="blendName"
                                    type="text"
                                    class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                                >
                            </div>

                            <!-- Target Weight Buttons -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">الوزن الإجمالي المطلوب (بالجرام)</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <button
                                        v-for="w in presetWeights"
                                        :key="w.value"
                                        @click="targetWeightGrams = w.value"
                                        type="button"
                                        class="py-2.5 px-3 rounded-2xl border text-xs font-bold transition cursor-pointer"
                                        :class="targetWeightGrams === w.value ? 'bg-amber-500 text-slate-950 font-black border-amber-400 shadow-md shadow-amber-500/20' : 'bg-slate-950 border-slate-800 text-slate-400 hover:text-white'"
                                    >
                                        {{ w.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- Custom Weight Input -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-400">وزن مخصص (جرام)</label>
                                    <input
                                        v-model.number="targetWeightGrams"
                                        type="number"
                                        step="1"
                                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-amber-400 font-mono font-black focus:border-amber-500 focus:outline-none"
                                    >
                                </div>

                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-400">درجة التحميص</label>
                                    <select
                                        v-model="roastType"
                                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                                    >
                                        <option value="فاتح">تحميص فاتح 🟡</option>
                                        <option value="وسط">تحميص وسط (الأكثر طلباً) 🟤</option>
                                        <option value="غامق">تحميص غامق ⚫</option>
                                        <option value="محروق">تحميص دبل روست / محروق 🔥</option>
                                    </select>
                                </div>

                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-400">درجة الطحن</label>
                                    <select
                                        v-model="grindLevel"
                                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                                    >
                                        <option value="تركي ناعم">تركي ناعم (وش قهوة) ☕</option>
                                        <option value="إسبريسو">إسبريسو متوسط النعومة ⚡</option>
                                        <option value="فرينش بريس">فرينش بريس / فلتر خشن 🫖</option>
                                        <option value="حبوب بدون طحن">حبوب سليمة بدون طحن 🫘</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Blend Components Builder -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-2.5">
                            <h2 class="text-sm font-black text-white flex items-center gap-2">
                                <span>🫘</span>
                                <span>مكونات وأصناف البن في التوليفة</span>
                            </h2>
                            <span
                                class="px-2.5 py-1 rounded-xl text-xs font-mono font-black"
                                :class="totalPercentage === 100 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30'"
                            >
                                إجمالي النسبة: {{ totalPercentage }}%
                            </span>
                        </div>

                        <!-- Add Item to Blend Selector -->
                        <div class="flex items-center gap-2">
                            <div class="flex-1">
                                <SearchableSelect
                                    v-model="selectedItemIdToAdd"
                                    :options="availableItemOptions"
                                    placeholder="اختر صنف بن لإضافته للخلطة..."
                                />
                            </div>
                            <button
                                @click="addComponent"
                                type="button"
                                class="h-10 px-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition cursor-pointer"
                            >
                                + إضافة
                            </button>
                        </div>

                        <!-- Components Table / Cards -->
                        <div class="space-y-3 pt-2">
                            <div
                                v-for="(comp, cIdx) in calculatedComponents"
                                :key="comp.item_id"
                                class="p-3.5 bg-slate-950/80 border border-slate-800 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-3"
                            >
                                <div class="w-full sm:w-1/3">
                                    <div class="font-black text-white text-xs">{{ comp.name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                        سعر الكيلو: {{ comp.selling_price }} ج.م
                                    </div>
                                </div>

                                <!-- Percentage Slider / Input -->
                                <div class="w-full sm:w-1/3 flex items-center gap-2">
                                    <input
                                        v-model.number="components[cIdx].percentage"
                                        type="range"
                                        min="0"
                                        max="100"
                                        step="5"
                                        class="w-full accent-amber-500"
                                    >
                                    <div class="w-14 flex items-center">
                                        <input
                                            v-model.number="components[cIdx].percentage"
                                            type="number"
                                            min="0"
                                            max="100"
                                            class="w-12 px-1.5 py-1 text-center bg-slate-900 border border-slate-700 rounded-lg text-xs font-mono font-black text-amber-400 focus:outline-none"
                                        >
                                        <span class="text-xs text-slate-400 mr-1">%</span>
                                    </div>
                                </div>

                                <!-- Calculated Grams & Line Total -->
                                <div class="w-full sm:w-1/3 flex items-center justify-between sm:justify-end gap-3 font-mono">
                                    <div class="text-left">
                                        <div class="text-xs font-black text-emerald-400">{{ comp.grams }} جم</div>
                                        <div class="text-[11px] text-slate-400">{{ comp.price }} ج.م</div>
                                    </div>

                                    <button
                                        @click="removeComponent(cIdx)"
                                        type="button"
                                        class="w-8 h-8 rounded-xl bg-rose-500/15 hover:bg-rose-500/30 text-rose-400 flex items-center justify-center transition cursor-pointer"
                                    >
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Extra Spices (Cardamom) -->
                        <div class="pt-3 border-t border-slate-800 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">إضافة حبهان / هيل أخضر (بالجرام)</label>
                                <input
                                    v-model.number="cardamomGrams"
                                    type="number"
                                    min="0"
                                    step="1"
                                    placeholder="0"
                                    class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-emerald-400 font-mono font-bold focus:border-amber-500 focus:outline-none"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">ملاحظات التحويجة للعميل</label>
                                <input
                                    v-model="notes"
                                    type="text"
                                    placeholder="مثال: تحويجة دوبل حبهان ومستكة..."
                                    class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Col: Financial Summary & Direct Cashier Action -->
                <div class="space-y-5">
                    <div class="bg-slate-900 border border-amber-500/30 rounded-3xl p-5 shadow-xl space-y-5 bg-gradient-to-br from-slate-900 to-amber-950/20 sticky top-20">
                        <div class="border-b border-slate-800 pb-3">
                            <h2 class="text-base font-black text-white">ملخص تكلفة وسعر التوليفة</h2>
                            <p class="text-xs text-amber-400 font-mono mt-0.5">{{ targetWeightGrams }} جرام ({{ (targetWeightGrams / 1000).toFixed(3) }} كجم)</p>
                        </div>

                        <div class="space-y-3 font-mono">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 font-tajawal">إجمالي تكلفة الخامات:</span>
                                <span class="text-rose-400 font-bold">{{ formatMoney(totalCalculatedCost) }} ج.م</span>
                            </div>

                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 font-tajawal">سعر البيع المقترح:</span>
                                <span class="text-emerald-400 font-black text-base">{{ formatMoney(totalCalculatedPrice) }} ج.م</span>
                            </div>

                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 font-tajawal">هامش الربح في هذه التوليفة:</span>
                                <span class="text-amber-400 font-bold">{{ profitMargin }}% ({{ formatMoney(totalCalculatedPrice - totalCalculatedCost) }} ج.م)</span>
                            </div>
                        </div>

                        <!-- Customer Selection for POS Order -->
                        <div class="space-y-2 pt-3 border-t border-slate-800">
                            <label class="text-xs font-black text-slate-200">العميل المستلم للطلب *</label>
                            <SearchableSelect
                                v-model="selectedCustomerId"
                                :options="customerOptions"
                                placeholder="اختر العميل..."
                            />
                        </div>

                        <!-- Action Button -->
                        <button
                            @click="submitBlendInvoice"
                            type="button"
                            :disabled="form.processing || components.length === 0"
                            class="w-full h-12 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/25 flex items-center justify-center gap-2 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            <span>🧾</span>
                            <span>{{ form.processing ? 'جاري إنشاء الفاتورة...' : 'إصدار الفاتورة والخصم من المخزن (F2)' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>