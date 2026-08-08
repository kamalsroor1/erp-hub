<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
        <div>
            <h2 class="text-xl font-black text-white flex items-center gap-2">
                <span>📦 إدارة الأصناف والمخزون</span>
            </h2>
            <p class="text-xs text-slate-400">قائمة الأصناف، الرصيد المتاح، أسعار التكلفة والبيع، وتعديل بيانات المنتجات</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('items.export.csv') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 flex items-center gap-2">
                📊 تصدير إكسيل (CSV)
            </a>
            <button wire:click="openCreateModal" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition-all">
                <span>+ إضافة صنف جديد</span>
            </button>
        </div>
    </div>

    @if (session()->has('success'))
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
        <span>✅ {{ session('success') }}</span>
    </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-900 p-4 rounded-2xl border border-slate-800">
        <div class="w-full sm:w-80">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="بحث بكود، اسم، أو قسم الصنف..." 
                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"
            >
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="text-slate-400">تصفية:</span>
            <button wire:click="$set('filterStock', 'all')" class="px-3 py-1.5 rounded-lg font-bold border transition-colors {{ $filterStock === 'all' ? 'bg-slate-800 border-slate-600 text-white' : 'border-transparent text-slate-400' }}">الكل</button>
            <button wire:click="$set('filterStock', 'low')" class="px-3 py-1.5 rounded-lg font-bold border transition-colors {{ $filterStock === 'low' ? 'bg-rose-500/20 border-rose-500/40 text-rose-400' : 'border-transparent text-slate-400' }}">نواقص (Low)</button>
            <button wire:click="$set('filterStock', 'out')" class="px-3 py-1.5 rounded-lg font-bold border transition-colors {{ $filterStock === 'out' ? 'bg-rose-950 border-rose-800 text-rose-300' : 'border-transparent text-slate-400' }}">نفد تماماً (0)</button>
        </div>
    </div>

    <!-- Items Table with Edit Button -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="p-3.5">كود الصنف</th>
                        <th class="p-3.5">اسم الصنف</th>
                        <th class="p-3.5">القسم</th>
                        <th class="p-3.5">الوحدة</th>
                        <th class="p-3.5">الرصيد الحالي</th>
                        <th class="p-3.5">سعر التكلفة</th>
                        <th class="p-3.5">سعر البيع</th>
                        <th class="p-3.5">الحد الأدنى</th>
                        <th class="p-3.5 text-center">الحالة</th>
                        <th class="p-3.5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($items as $item)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="p-3.5 font-mono font-bold text-emerald-400">{{ $item->code }}</td>
                        <td class="p-3.5 font-bold text-slate-100">{{ $item->name }}</td>
                        <td class="p-3.5 text-slate-400">{{ $item->category ?? 'عام' }}</td>
                        <td class="p-3.5 text-slate-400">{{ $item->unit }}</td>
                        <td class="p-3.5 font-mono font-bold {{ $item->isLowStock() ? 'text-rose-400' : 'text-slate-100' }}">
                            {{ number_format($item->current_stock, 3) }}
                        </td>
                        <td class="p-3.5 font-mono text-slate-300">{{ number_format($item->cost_price, 2) }} ج.م</td>
                        <td class="p-3.5 font-mono font-bold text-emerald-400">{{ number_format($item->selling_price, 2) }} ج.م</td>
                        <td class="p-3.5 font-mono text-slate-400">{{ number_format($item->min_stock_level, 2) }}</td>
                        <td class="p-3.5 text-center">
                            @if($item->isLowStock())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">ناقص بالمخزن</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">متوفر</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-center">
                            <button 
                                wire:click="openEditModal({{ $item->id }})" 
                                title="تعديل بيانات الصنف"
                                class="px-2.5 py-1 bg-slate-800 hover:bg-emerald-600 text-slate-300 hover:text-white rounded-lg text-xs font-bold border border-slate-700 transition-colors inline-flex items-center gap-1"
                            >
                                <span>✏️ تعديل</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="p-12 text-center text-slate-500">لا توجد أصناف مطابقة للبحث</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-800">
            {{ $items->links() }}
        </div>
    </div>

    <!-- Create & Edit Item Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-base flex items-center gap-2">
                    <span>{{ $isEditMode ? '✏️ تعديل بيانات الصنف' : '📦 إضافة صنف جديد للمخزون' }}</span>
                </h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <form wire:submit.prevent="saveItem" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">كود الصنف (الباركود):</label>
                        <input type="text" wire:model="code" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-mono">
                        @error('code') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">القسم / التصنيف:</label>
                        <select wire:model="category" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                            <option value="بن وتوليفات">☕ بن وتوليفات</option>
                            <option value="شاي وأعشاب">🍵 شاي وأعشاب</option>
                            <option value="نسكافيه ومشروبات سريعة">🥤 نسكافيه ومشروبات</option>
                            <option value="تحبيشات وإضافات">🌿 تحبيشات (حبهان ومستكة)</option>
                            <option value="معلبات ومستلزمات">📦 معلبات وباكتات</option>
                            <option value="عام">عام</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-300 mb-1">اسم الصنف:</label>
                        <input type="text" wire:model="name" placeholder="مثال: بن برازيلي سانتوس" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                        @error('name') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">الوحدة:</label>
                        <select wire:model="unit" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                            <option value="كجم">كجم (كيلو)</option>
                            <option value="جرام">جرام</option>
                            <option value="قطعة">قطعة</option>
                            <option value="علبة">علبة</option>
                            <option value="برطمان">برطمان</option>
                            <option value="باكت">باكت</option>
                            <option value="كرتونة">كرتونة</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">سعر التكلفة (ج.م):</label>
                        <input type="number" step="0.01" min="0" wire:model="cost_price" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">سعر البيع (ج.م):</label>
                        <input type="number" step="0.01" min="0" wire:model="selling_price" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-emerald-400 font-mono font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    @if(!$isEditMode)
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">رصيد أول المدة:</label>
                        <input type="number" step="0.001" min="0" wire:model="current_stock" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-mono">
                    </div>
                    @endif
                    <div class="{{ $isEditMode ? 'col-span-2' : '' }}">
                        <label class="block text-xs font-bold text-slate-300 mb-1">الحد الأدنى للطلب (تنبيه النواقص):</label>
                        <input type="number" step="0.001" min="0" wire:model="min_stock_level" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">ملاحظات الصنف:</label>
                    <input type="text" wire:model="notes" placeholder="ملاحظات حول طريقة التحضير أو التخزين..." class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30">
                        {{ $isEditMode ? '💾 حفظ التعديلات' : '➕ إضافة الصنف' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
