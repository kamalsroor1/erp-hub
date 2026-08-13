@props([
    'placeholder' => 'اختر التاريخ...',
    'enableTime' => false,
    'dateFormat' => 'Y-m-d',
    'altFormat' => 'Y-m-d',
    'mode' => 'single',
    'minDate' => null,
    'maxDate' => null,
])

<div
    x-data="{
        value: @entangle($attributes->wire('model')),
        instance: null,
        init() {
            this.instance = flatpickr(this.$refs.input, {
                locale: 'ar',
                mode: '{{ $mode }}',
                dateFormat: '{{ $dateFormat }}',
                altInput: true,
                altFormat: '{{ $altFormat }}',
                enableTime: {{ $enableTime ? 'true' : 'false' }},
                @if($minDate) minDate: '{{ $minDate }}', @endif
                @if($maxDate) maxDate: '{{ $maxDate }}', @endif
                defaultDate: this.value,
                onChange: (selectedDates, dateStr) => {
                    this.value = dateStr;
                }
            });

            this.$watch('value', (val) => {
                if (this.instance && val !== this.instance.input.value) {
                    this.instance.setDate(val, false);
                }
            });
        }
    }"
    class="relative inline-block w-full"
>
    <input
        x-ref="input"
        type="text"
        placeholder="{{ $placeholder }}"
        {{ $attributes->whereDoesntStartWith('wire:model')->merge([
            'class' => 'w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-xs font-mono font-bold focus:ring-2 focus:ring-amber-500 focus:outline-none cursor-pointer placeholder-slate-400 dark:placeholder-slate-500'
        ]) }}
    />
</div>
