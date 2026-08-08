<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
        <div>
            <h2 class="text-xl font-black text-white flex items-center gap-2">
                <span>☕ خلاط وتوليفات البن المخصوصة (Custom Blend Master)</span>
            </h2>
            <p class="text-xs text-slate-400">توليف نسب البن (برازيلي / كولومبي / حبشي / يمني) وإضافات الحبهان والمستكة وحساب السعر والخصم المخزني تلقائيًا</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 font-bold text-xs rounded-xl">
                ⚖️ وزن التوليفة المطلوب: {{ number_format($total_weight_grams, 1) }} جرام
            </span>
        </div>
    </div>

    @if($errorMessage)
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs">
        {{ $errorMessage }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Recipe Designer & Coffee Selection -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Preset Target Weights -->
            <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800 space-y-3">
                <label class="block text-xs font-bold text-slate-300">الوزن الصافي المطلوب للتوليفة:</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <button type="button" wire:click="setPresetTargetWeight('125.000')" class="py-2 rounded-xl border text-xs font-bold transition-all {{ $target_weight_grams == '125.000' ? 'bg-amber-600 border-amber-500 text-white shadow-lg' : 'bg-slate-950 border-slate-800 text-slate-400' }}">
                        ثمن كيلو (125 جم)
                    </button>
                    <button type="button" wire:click="setPresetTargetWeight('250.000')" class="py-2 rounded-xl border text-xs font-bold transition-all {{ $target_weight_grams == '250.000' ? 'bg-amber-600 border-amber-500 text-white shadow-lg' : 'bg-slate-950 border-slate-800 text-slate-400' }}">
                        ربع كيلو (250 جم)
                    </button>
                    <button type="button" wire:click="setPresetTargetWeight('500.000')" class="py-2 rounded-xl border text-xs font-bold transition-all {{ $target_weight_grams == '500.000' ? 'bg-amber-600 border-amber-500 text-white shadow-lg' : 'bg-slate-950 border-slate-800 text-slate-400' }}">
                        نصف كيلو (500 جم)
                    </button>
                    <button type="button" wire:click="setPresetTargetWeight('1000.000')" class="py-2 rounded-xl border text-xs font-bold transition-all {{ $target_weight_grams == '1000.000' ? 'bg-amber-600 border-amber-500 text-white shadow-lg' : 'bg-slate-950 border-slate-800 text-slate-400' }}">
                        كيلو كامل (1000 جم)
                    </button>
                </div>
            </div>

            <!-- Blend Component Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-3 bg-slate-950/60 border-b border-slate-800 flex items-center justify-between text-xs font-bold text-slate-300">
                    <span>مكونات خلطة البن ونسب التوليف</span>
                    <span class="text-amber-400 font-mono">الإجمالي: {{ $total_weight_grams }} جم</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                            <tr>
                                <th class="p-3">نوع البن الخام</th>
                                <th class="p-3 w-28 text-center">النسبة %</th>
                                <th class="p-3 w-32 text-center">الوزن بالجرام</th>
                                <th class="p-3 w-28">سعر الكيلو</th>
                                <th class="p-3 text-center w-10">حذف</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($components as $idx => $comp)
                            <tr class="hover:bg-slate-800/20">
                                <td class="p-3 font-bold text-slate-200">{{ $comp['name'] }}</td>
                                <td class="p-3">
                                    <div class="flex items-center gap-1 justify-center">
                                        <input 
                                            type="number" 
                                            min="0" 
                                            max="100" 
                                            wire:model.live.debounce.250ms="components.{{ $idx }}.percentage" 
                                            class="w-16 bg-slate-950 border border-slate-700 rounded-lg px-2 py-1 text-center font-mono font-bold text-amber-400 text-xs"
                                        >
                                        <span class="text-slate-500 font-bold">%</span>
                                    </div>
                                </td>
                                <td class="p-3 text-center font-mono font-bold text-white">
                                    {{ number_format($comp['grams'], 1) }} جم
                                </td>
                                <td class="p-3 font-mono text-slate-400">
                                    {{ number_format($comp['selling_price'], 2) }} ج.م
                                </td>
                                <td class="p-3 text-center">
                                    <button wire:click="removeComponent({{ $idx }})" class="text-slate-500 hover:text-rose-400">🗑️</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">اختر أنواع البن من القائمة بالأسفل لإضافتها للتوليفة</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Add Raw Coffee Chips -->
            <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800 space-y-2">
                <label class="block text-xs font-bold text-slate-300">أضف أنواع بن أخرى للخلطة:</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($availableCoffees as $cof)
                    <button 
                        type="button" 
                        wire:click="addComponent({{ $cof->id }})" 
                        class="px-3 py-1.5 rounded-xl bg-slate-950 hover:bg-slate-800 border border-slate-800 hover:border-amber-500/50 text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition-all"
                    >
                        <span>+ {{ $cof->name }}</span>
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Spice Mix: Cardamom & Mastic -->
            <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800 space-y-3">
                <h3 class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                    <span>🌿 التحبيشة والإضافات العطرية بالميزان الدقيق:</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 space-y-1.5">
                        <label class="block text-[11px] font-bold text-emerald-400">حبهان (هيل) أخضر هندي:</label>
                        <div class="flex items-center gap-2">
                            <input 
                                type="number" 
                                step="0.5" 
                                min="0" 
                                wire:model.live.debounce.250ms="cardamom_grams" 
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs font-mono font-bold text-emerald-400 text-center"
                            >
                            <span class="text-xs text-slate-400">جرام</span>
                        </div>
                    </div>

                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 space-y-1.5">
                        <label class="block text-[11px] font-bold text-teal-400">مستكة يوناني فصوص حرة:</label>
                        <div class="flex items-center gap-2">
                            <input 
                                type="number" 
                                step="0.5" 
                                min="0" 
                                wire:model.live.debounce.250ms="mastic_grams" 
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs font-mono font-bold text-teal-400 text-center"
                            >
                            <span class="text-xs text-slate-400">جرام</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Col: Barista Roasting/Grind Specs & Instant Invoice Checkout -->
        <div class="space-y-4">
            <!-- Blend Settings -->
            <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800 space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">اسم التوليفة المخصوصة:</label>
                    <input type="text" wire:model="blend_name" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">العميل:</label>
                    <select wire:model.live="customer_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">درجة التحميص:</label>
                        <select wire:model="roast_type" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2 py-1.5 text-xs text-white">
                            <option value="فاتح">فاتح</option>
                            <option value="وسط">وسط</option>
                            <option value="غامق">غامق</option>
                            <option value="محروق">محروق (Dark Roast)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">درجة الطحن:</label>
                        <select wire:model="grind_level" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2 py-1.5 text-xs text-white">
                            <option value="تركي ناعم">تركي ناعم (كنكة)</option>
                            <option value="إسبريسو">إسبريسو ناعم</option>
                            <option value="فلتر و V60">فلتر V60 وسط</option>
                            <option value="فرينش بريس">فرينش بريس خشن</option>
                            <option value="حبوب بدون طحن">حبوب بدون طحن</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Financial Summary Card -->
            <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800 space-y-3">
                <h3 class="text-sm font-bold text-white border-b border-slate-800 pb-2">تكلفة وسعر بيع التوليفة</h3>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between text-slate-400">
                        <span>الوزن الكلي للتوليفة:</span>
                        <span class="font-mono font-bold text-amber-400">{{ number_format($total_weight_grams, 1) }} جم</span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>تكلفة الخامات المخزنية:</span>
                        <span class="font-mono text-slate-300">{{ number_format($blended_cost_price, 2) }} ج.م</span>
                    </div>
                    <div class="flex justify-between text-base font-black text-emerald-400 pt-2 border-t border-slate-800">
                        <span>سعر البيع المطلوب:</span>
                        <span class="font-mono">{{ number_format($blended_selling_price, 2) }} ج.م</span>
                    </div>
                </div>

                <button 
                    type="button" 
                    wire:click="createBlendInvoice" 
                    class="w-full mt-4 py-3 bg-gradient-to-r from-amber-600 to-orange-500 hover:from-amber-500 hover:to-orange-400 text-white font-bold rounded-xl shadow-lg shadow-amber-600/30 text-xs transition-all active:scale-95 flex items-center justify-center gap-2"
                >
                    <span>☕ اعتماد التوليفة وإصدار إيصال الكاشير</span>
                </button>
            </div>
        </div>
    </div>
</div>
