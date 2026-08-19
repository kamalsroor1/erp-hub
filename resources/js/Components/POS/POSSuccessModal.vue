<script setup>
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    show: { type: Boolean, default: false },
    invoice: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const { formatMoney } = useMoney();
</script>

<template>
    <div
        v-if="show && invoice"
        @click="emit('close')"
        class="fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div @click.stop class="w-full max-w-sm bg-slate-900 border border-emerald-500/40 rounded-3xl p-6 shadow-2xl text-center space-y-4 animate-scale-up">
            <div class="w-14 h-14 rounded-full bg-emerald-500/20 text-emerald-400 text-3xl flex items-center justify-center mx-auto">
                ✓
            </div>

            <div>
                <h3 class="text-base font-black text-white">{{ $t('pos.invoice_saved_success') }}</h3>
                <p class="text-xs text-slate-400 font-mono mt-0.5">
                    {{ $t('pos.invoice_number') }}: <span class="text-emerald-400 font-bold">#{{ invoice.invoice_number }}</span>
                </p>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-800/60 border border-slate-800 text-xs space-y-1.5 font-mono">
                <div class="flex items-center justify-between text-slate-300">
                    <span>{{ $t('common.total') }}:</span>
                    <span class="font-black text-emerald-400">{{ formatMoney(invoice.net_total) }} {{ $t('common.currency') }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-400">
                    <span>{{ $t('common.paid') }}:</span>
                    <span class="font-bold text-white">{{ formatMoney(invoice.paid_amount) }} {{ $t('common.currency') }}</span>
                </div>
                <div v-if="invoice.remaining_amount > 0" class="flex items-center justify-between text-rose-400">
                    <span>{{ $t('pos.amount_remaining_on_acc') }}:</span>
                    <span class="font-bold">{{ formatMoney(invoice.remaining_amount) }} {{ $t('common.currency') }}</span>
                </div>
            </div>

            <!-- Print & Actions Buttons -->
            <div class="grid grid-cols-2 gap-2 text-xs">
                <a
                    :href="invoice.print_thermal_url"
                    target="_blank"
                    class="p-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black flex items-center justify-center gap-1.5 shadow-md transition"
                >
                    <span>{{ $t('pos.print_thermal') }}</span>
                </a>

                <a
                    :href="invoice.print_a4_url"
                    target="_blank"
                    class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold flex items-center justify-center gap-1.5 transition"
                >
                    <span>{{ $t('pos.print_a4') }}</span>
                </a>
            </div>

            <button
                @click="emit('close')"
                type="button"
                class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-black text-xs transition"
            >
                فاتورة جديدة (متابعة البيع)
            </button>
        </div>
    </div>
</template>
