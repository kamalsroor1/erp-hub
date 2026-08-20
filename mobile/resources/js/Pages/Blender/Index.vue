<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    coffees: { type: Array, default: () => [] },
    spices: { type: Object, default: () => ({ cardamom: null, mastic: null }) },
    customers: { type: Array, default: () => [] },
    roast_types: { type: Array, default: () => ['فاتح', 'وسط', 'غامق', 'محروق'] },
    grind_levels: { type: Array, default: () => ['تركي ناعم (كنكة)', 'إسبريسو ناعم', 'فلتر و V60 وسط', 'فرينش بريس خشن', 'حبوب بدون طحن'] },
    presets: { type: Array, default: () => [] },
});

// Blend Form State
const blendName = ref('توليفة مخصوصة سرور');
const selectedCustomerId = ref(props.customers[0]?.id || 1);
const targetWeightGrams = ref(250);
const roastType = ref('وسط');
const grindLevel = ref('تركي ناعم (كنكة)');
const cardamomGrams = ref(0);
const masticGrams = ref(0);

// Selected Coffee Components: [{ id, name, percentage, selling_price, cost_price }]
const components = ref([]);

// Default popular starter components if available
onMounted(() => {
    if (props.coffees.length >= 2) {
        components.value = [
            {
                id: props.coffees[0].id,
                name: props.coffees[0].name,
                percentage: 60,
                selling_price: Number(props.coffees[0].selling_price || 0),
                cost_price: Number(props.coffees[0].cost_price || 0),
            },
            {
                id: props.coffees[1].id,
                name: props.coffees[1].name,
                percentage: 40,
                selling_price: Number(props.coffees[1].selling_price || 0),
                cost_price: Number(props.coffees[1].cost_price || 0),
            },
        ];
    } else if (props.coffees.length === 1) {
        components.value = [
            {
                id: props.coffees[0].id,
                name: props.coffees[0].name,
                percentage: 100,
                selling_price: Number(props.coffees[0].selling_price || 0),
                cost_price: Number(props.coffees[0].cost_price || 0),
            }
        ];
    }
});

// Total Percentage Check
const totalPercentage = computed(() => {
    return components.value.reduce((acc, c) => acc + (Number(c.percentage) || 0), 0);
});

// Add Bean to Blend
const addCoffeeBean = (coffee) => {
    haptic.light();
    if (components.value.some(c => c.id === coffee.id)) return;

    // Remaining percentage to 100%
    const currentTotal = totalPercentage.value;
    const remaining = Math.max(0, 100 - currentTotal);

    components.value.push({
        id: coffee.id,
        name: coffee.name,
        percentage: remaining > 0 ? remaining : 10,
        selling_price: Number(coffee.selling_price || 0),
        cost_price: Number(coffee.cost_price || 0),
    });
};

// Remove Bean from Blend
const removeComponent = (index) => {
    haptic.medium();
    components.value.splice(index, 1);
};

// Preset Target Weight Picker
const setPresetWeight = (grams) => {
    haptic.light();
    targetWeightGrams.value = Number(grams);
};

// Calculated Components with Real Grams & Costs
const calculatedComponents = computed(() => {
    const totalG = Number(targetWeightGrams.value) || 250;
    return components.value.map(c => {
        const pct = Number(c.percentage) || 0;
        const grams = (totalG * pct) / 100;
        const kg = grams / 1000;
        const linePrice = kg * c.selling_price;
        const lineCost = kg * c.cost_price;
        return {
            ...c,
            grams: Number(grams.toFixed(1)),
            kg: Number(kg.toFixed(4)),
            linePrice: Number(linePrice.toFixed(2)),
            lineCost: Number(lineCost.toFixed(2)),
        };
    });
});

// Total Blend Selling Price
const totalSellingPrice = computed(() => {
    let sum = calculatedComponents.value.reduce((acc, c) => acc + c.linePrice, 0);

    // Add Cardamom price if any (approx or from item)
    if (props.spices?.cardamom && cardamomGrams.value > 0) {
        const kg = cardamomGrams.value / 1000;
        sum += kg * Number(props.spices.cardamom.selling_price || 800);
    }

    // Add Mastic price if any
    if (props.spices?.mastic && masticGrams.value > 0) {
        const kg = masticGrams.value / 1000;
        sum += kg * Number(props.spices.mastic.selling_price || 2500);
    }

    return Number(sum.toFixed(2));
});

// Checkout Blend Directly
const isSubmitting = ref(false);

const checkoutNow = () => {
    if (components.value.length === 0) {
        alert('يجب اختيار نوع بن واحد على الأقل في التوليفة');
        return;
    }

    if (totalPercentage.value !== 100) {
        if (!confirm(`مجموع نسب البن حالياً (${totalPercentage.value}%) ولا يساوي 100%. هل تريد المتابعة على أية حال؟`)) {
            return;
        }
    }

    haptic.success();
    isSubmitting.value = true;

    router.post('/blender/checkout', {
        customer_id: selectedCustomerId.value,
        blend_name: blendName.value,
        target_weight_grams: targetWeightGrams.value,
        roast_type: roastType.value,
        grind_level: grindLevel.value,
        cardamom_grams: cardamomGrams.value,
        mastic_grams: masticGrams.value,
        payment_type: 'cash',
        paid_amount: totalSellingPrice.value,
        components: calculatedComponents.value.map(c => ({
            item_id: c.id,
            grams: c.grams,
        })),
    }, {
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};
</script>

<template>
    <MobileLayout>
        <div class="space-y-4 pb-28 select-none">
            <!-- Header Banner -->
            <div class="bg-gradient-to-l from-amber-600 to-amber-700 rounded-3xl p-4 text-white shadow-lg shadow-amber-600/20 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">☕</span>
                        <h2 class="text-base font-black">معمل وخلاط توليفات البن</h2>
                    </div>
                    <p class="text-[11px] text-amber-100 font-bold mt-0.5">
                        خلط نسب البن والتحبيشة وحساب السعر والخصم المخزني تلقائياً
                    </p>
                </div>
                <Link href="/pos" class="w-9 h-9 rounded-2xl bg-white/20 hover:bg-white/30 backdrop-blur-md flex items-center justify-center text-xs font-bold transition">
                    ⚡
                </Link>
            </div>

            <!-- 1. Preset Target Weight Chips -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2.5">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-extrabold text-slate-700 dark:text-slate-200">الوزن المطلوب للتوليفة:</label>
                    <span class="text-xs font-mono font-black text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2.5 py-0.5 rounded-xl border border-amber-500/20">
                        {{ targetWeightGrams }} جرام ({{ (targetWeightGrams / 1000).toFixed(3) }} كجم)
                    </span>
                </div>

                <div class="grid grid-cols-4 gap-2">
                    <button
                        v-for="p in [
                            { label: 'ثمن ك (125جم)', grams: 125 },
                            { label: 'ربع ك (250جم)', grams: 250 },
                            { label: 'نصف ك (500جم)', grams: 500 },
                            { label: 'كيلو (1000جم)', grams: 1000 }
                        ]"
                        :key="p.grams"
                        @click="setPresetWeight(p.grams)"
                        type="button"
                        class="py-2.5 rounded-2xl font-black text-[11px] transition touch-active text-center border"
                        :class="targetWeightGrams === p.grams ? 'bg-amber-500 border-amber-500 text-white shadow-md shadow-amber-500/25' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'"
                    >
                        {{ p.label }}
                    </button>
                </div>
            </div>

            <!-- 2. Components Balancer Table -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-xs font-extrabold text-slate-900 dark:text-white">
                        <span>⚖️</span>
                        <span>مكونات خلطة البن</span>
                    </div>
                    <div class="text-[11px] font-mono font-bold" :class="totalPercentage === 100 ? 'text-emerald-500' : 'text-amber-500'">
                        المجموع: {{ totalPercentage }}% {{ totalPercentage === 100 ? '✓' : '(غير مكتمل)' }}
                    </div>
                </div>

                <!-- Percentage Progress Bar -->
                <div class="w-full h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden flex">
                    <div
                        v-for="(c, idx) in calculatedComponents"
                        :key="c.id"
                        class="h-full transition-all duration-300"
                        :class="['bg-amber-500', 'bg-emerald-500', 'bg-sky-500', 'bg-rose-500', 'bg-purple-500'][idx % 5]"
                        :style="{ width: c.percentage + '%' }"
                    ></div>
                </div>

                <!-- Components Rows -->
                <div class="space-y-2.5">
                    <div
                        v-for="(c, idx) in calculatedComponents"
                        :key="c.id"
                        class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/80 dark:border-slate-800 flex items-center justify-between gap-3"
                    >
                        <div class="flex-1 min-w-0">
                            <div class="font-extrabold text-xs text-slate-900 dark:text-white truncate">
                                {{ c.name }}
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                {{ c.grams }} جم • {{ c.linePrice }} ج.م
                            </div>
                        </div>

                        <!-- Percentage Input Control -->
                        <div class="flex items-center gap-1">
                            <input
                                v-model.number="components[idx].percentage"
                                type="number"
                                min="0"
                                max="100"
                                class="w-14 h-8 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-center font-mono font-black text-xs text-amber-600 dark:text-amber-400"
                            />
                            <span class="text-xs font-bold text-slate-400">%</span>
                        </div>

                        <!-- Delete Component -->
                        <button
                            @click="removeComponent(idx)"
                            type="button"
                            class="w-7 h-7 rounded-xl bg-rose-500/10 text-rose-500 hover:bg-rose-500/20 flex items-center justify-center text-xs font-bold transition"
                        >
                            ✕
                        </button>
                    </div>

                    <div v-if="components.length === 0" class="text-center py-6 text-xs text-slate-400 font-bold">
                        اختر أصناف البن من الأسفل لإضافتها إلى الخلطة ☕
                    </div>
                </div>

                <!-- Quick Add Bean Chips -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-500">أضف بن للخلطة:</label>
                    <div class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto">
                        <button
                            v-for="cof in coffees"
                            :key="cof.id"
                            @click="addCoffeeBean(cof)"
                            type="button"
                            class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-amber-500/20 text-slate-700 dark:text-slate-300 font-bold text-[11px] border border-slate-200 dark:border-slate-700 flex items-center gap-1 transition touch-active"
                        >
                            <span>+</span>
                            <span>{{ cof.name }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. Spices & Aroma (التحبيشة بالميزان) -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <div class="flex items-center gap-1.5 text-xs font-extrabold text-slate-900 dark:text-white">
                    <span>🌿</span>
                    <span>التحبيشة والإضافات العطرية الدقيقة</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Cardamom -->
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-1.5">
                        <label class="block text-[11px] font-black text-emerald-600 dark:text-emerald-400">حبهان هندي أخضر:</label>
                        <div class="flex items-center gap-1.5">
                            <input
                                v-model.number="cardamomGrams"
                                type="number"
                                step="0.5"
                                min="0"
                                class="w-full h-8 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-center font-mono font-black text-xs text-emerald-600 dark:text-emerald-400"
                            />
                            <span class="text-[10px] text-slate-400 font-bold">جرام</span>
                        </div>
                    </div>

                    <!-- Mastic -->
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-1.5">
                        <label class="block text-[11px] font-black text-teal-600 dark:text-teal-400">مستكة يوناني حرة:</label>
                        <div class="flex items-center gap-1.5">
                            <input
                                v-model.number="masticGrams"
                                type="number"
                                step="0.5"
                                min="0"
                                class="w-full h-8 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-center font-mono font-black text-xs text-teal-600 dark:text-teal-400"
                            />
                            <span class="text-[10px] text-slate-400 font-bold">جرام</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Roasting & Grinding Specifications -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 mb-1">درجة التحميص:</label>
                        <select
                            v-model="roastType"
                            class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-2.5 text-xs font-bold text-slate-900 dark:text-white"
                        >
                            <option v-for="r in roast_types" :key="r" :value="r">{{ r }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 mb-1">درجة الطحن:</label>
                        <select
                            v-model="grindLevel"
                            class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-2.5 text-xs font-bold text-slate-900 dark:text-white"
                        >
                            <option v-for="g in grind_levels" :key="g" :value="g">{{ g }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-slate-500 mb-1">اسم التوليفة المطبوع على الفاتورة:</label>
                    <input
                        v-model="blendName"
                        type="text"
                        class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white"
                    />
                </div>
            </div>

            <!-- 5. Fixed Checkout Footer Bar -->
            <div class="fixed bottom-15 left-0 right-0 z-30 max-w-md mx-auto p-3">
                <div class="bg-slate-950/95 backdrop-blur-md rounded-3xl p-3.5 border border-slate-800 shadow-2xl flex items-center justify-between gap-3 text-white">
                    <div>
                        <div class="text-[10px] text-slate-400 font-bold">سعر بيع التوليفة الكلي</div>
                        <div class="text-lg font-black font-mono text-emerald-400">
                            {{ totalSellingPrice.toLocaleString('en-US', { minimumFractionDigits: 2 }) }} <span class="text-xs font-normal">ج.م</span>
                        </div>
                    </div>

                    <button
                        @click="checkoutNow"
                        :disabled="isSubmitting"
                        type="button"
                        class="h-12 px-5 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 active:scale-98 text-white font-black text-xs rounded-2xl shadow-lg shadow-emerald-500/30 flex items-center justify-center gap-1.5 transition touch-active"
                    >
                        <span>⚡</span>
                        <span>{{ isSubmitting ? 'جاري الفاتورة...' : 'بيع الفاتورة فوراً' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
