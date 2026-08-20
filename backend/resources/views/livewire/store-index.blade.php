<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>🏬 إدارة الفروع وعربات التوزيع والمخازن</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">إدارة محلات التجزئة، عربيات البيع والجملة، والمخزن الرئيسي وتعيين الموظفين</p>
        </div>
        <div>
            <button 
                type="button" 
                wire:click="openCreateModal" 
                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-lg shadow-emerald-600/20 transition-all cursor-pointer flex items-center gap-1.5"
            >
                <span>➕ إضافة فرع / عربية توزيع</span>
            </button>
        </div>
    </div>

    @if($successMessage)
    <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold flex items-center justify-between">
        <span>✅ {{ $successMessage }}</span>
        <button wire:click="$set('successMessage', '')" class="text-emerald-500 hover:text-emerald-700 font-black cursor-pointer">✕</button>
    </div>
    @endif

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-sm">
        <div class="w-full sm:w-72 relative">
            <input 
                type="text" 
                wire:model.live.debounce.200ms="searchQuery" 
                placeholder="ابحث بالاسم أو الكود..." 
                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500"
            >
        </div>

        <div class="flex flex-wrap items-center gap-1.5 w-full sm:w-auto text-xs">
            <span class="text-slate-500 dark:text-slate-400 text-[11px] hidden sm:inline">الحالة:</span>
            <button wire:click="$set('statusFilter', 'active')" class="px-2.5 py-1.5 rounded-xl font-bold transition-colors cursor-pointer {{ $statusFilter === 'active' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">النشطة</button>
            <button wire:click="$set('statusFilter', 'trashed')" class="px-2.5 py-1.5 rounded-xl font-bold transition-colors cursor-pointer flex items-center gap-1 {{ $statusFilter === 'trashed' ? 'bg-rose-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-rose-600 dark:text-rose-400' }}">
                <span>سلة المحذوفات</span>
                @if($trashedCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $statusFilter === 'trashed' ? 'bg-white text-rose-600' : 'bg-rose-500/20 text-rose-600' }} font-mono font-bold">{{ $trashedCount }}</span>
                @endif
            </button>
            <button wire:click="$set('statusFilter', 'all')" class="px-2.5 py-1.5 rounded-xl font-bold transition-colors cursor-pointer {{ $statusFilter === 'all' ? 'bg-slate-700 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">الكل</button>

            <span class="text-slate-300 dark:text-slate-700 mx-1">|</span>

            <button wire:click="$set('typeFilter', 'all')" class="px-2.5 py-1.5 rounded-xl font-bold transition-colors cursor-pointer {{ $typeFilter === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">الكل</button>
            <button wire:click="$set('typeFilter', 'retail_shop')" class="px-2.5 py-1.5 rounded-xl font-bold transition-colors cursor-pointer {{ $typeFilter === 'retail_shop' ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">🏬 محلات</button>
            <button wire:click="$set('typeFilter', 'wholesale_van')" class="px-2.5 py-1.5 rounded-xl font-bold transition-colors cursor-pointer {{ $typeFilter === 'wholesale_van' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">🚚 عربيات</button>
            <button wire:click="$set('typeFilter', 'main_warehouse')" class="px-2.5 py-1.5 rounded-xl font-bold transition-colors cursor-pointer {{ $typeFilter === 'main_warehouse' ? 'bg-teal-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">🏢 مخازن</button>
        </div>
    </div>

    <!-- Stores Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($stores as $st)
        @php
            $isCurrent = (int)session('current_store_id') === (int)$st->id;
        @endphp
        <div class="bg-white dark:bg-slate-900 rounded-2xl border {{ $isCurrent ? 'border-emerald-500 shadow-md shadow-emerald-500/10' : 'border-slate-200 dark:border-slate-800' }} p-4 space-y-3 flex flex-col justify-between">
            <div class="space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">
                            @if($st->type === 'wholesale_van') 🚚 @elseif($st->type === 'main_warehouse') 🏢 @else 🏬 @endif
                        </span>
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white text-sm flex items-center gap-1.5">
                                <span>{{ $st->name }}</span>
                                @if($st->is_main)
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-teal-500/10 text-teal-700 dark:text-teal-400 border border-teal-500/20">رئيسي</span>
                                @endif
                            </h3>
                            <span class="text-[10px] font-mono text-slate-400">كود: {{ $st->code }}</span>
                        </div>
                    </div>

                    @if($isCurrent)
                    <span class="px-2.5 py-1 rounded-xl bg-emerald-500 text-white text-[10px] font-black shadow-sm">نشط حالياً</span>
                    @else
                    <button 
                        type="button" 
                        wire:click="switchToStore({{ $st->id }})" 
                        class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-emerald-600 hover:text-white text-slate-700 dark:text-slate-300 text-[10px] font-bold transition-colors cursor-pointer"
                    >
                        ⚡ تبديل إليه
                    </button>
                    @endif
                </div>

                <div class="grid grid-cols-3 gap-2 py-2 border-y border-slate-100 dark:border-slate-800/80 text-center text-[11px]">
                    <div class="bg-slate-50 dark:bg-slate-950 p-1.5 rounded-xl">
                        <div class="text-slate-400 text-[9px]">أصناف بالمخزن</div>
                        <div class="font-mono font-bold text-slate-900 dark:text-white">{{ $st->stocks_count }}</div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-950 p-1.5 rounded-xl">
                        <div class="text-slate-400 text-[9px]">الموظفين</div>
                        <div class="font-mono font-bold text-slate-900 dark:text-white">{{ $st->users_count }}</div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-950 p-1.5 rounded-xl">
                        <div class="text-slate-400 text-[9px]">الفواتير</div>
                        <div class="font-mono font-bold text-slate-900 dark:text-white">{{ $st->invoices_count }}</div>
                    </div>
                </div>

                @if($st->phone || $st->address)
                <div class="text-[11px] text-slate-500 space-y-0.5">
                    @if($st->phone) <div>📞 {{ $st->phone }}</div> @endif
                    @if($st->address) <div class="line-clamp-1">📍 {{ $st->address }}</div> @endif
                </div>
                @endif
            </div>

            <!-- Card Actions -->
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                <button 
                    type="button" 
                    wire:click="openUserAssignmentModal({{ $st->id }})" 
                    class="px-2.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-indigo-600 hover:text-white text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors cursor-pointer flex items-center gap-1"
                >
                    <span>👥 تعيين الموظفين</span>
                </button>

                <div class="flex items-center gap-1">
                    @if($st->trashed())
                    <button 
                        type="button" 
                        wire:click="restoreStore({{ $st->id }})" 
                        class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-600 text-emerald-700 dark:text-emerald-400 hover:text-white text-xs font-bold border border-emerald-500/30 transition-colors cursor-pointer"
                        title="استعادة الفرع"
                    >
                        ♻️ استعادة
                    </button>
                    @else
                    <a 
                        href="{{ route('store-stocks') }}?store_id={{ $st->id }}" 
                        class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-amber-600 hover:text-white text-slate-600 dark:text-slate-400 transition-colors cursor-pointer"
                        title="جرد وأسعار الفرع"
                    >
                        📦
                    </a>
                    <button 
                        type="button" 
                        wire:click="openEditModal({{ $st->id }})" 
                        class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-600 hover:text-white text-slate-600 dark:text-slate-400 transition-colors cursor-pointer"
                        title="تعديل بيانات الفرع"
                    >
                        ✏️
                    </button>
                    @if(!$st->is_main)
                    <button 
                        type="button" 
                        wire:click="deleteStore({{ $st->id }})" 
                        wire:confirm="هل أنت متأكد من نقل هذا الفرع لسلة المحذوفات؟"
                        class="p-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-600 text-rose-600 dark:text-rose-400 hover:text-white transition-colors cursor-pointer"
                        title="أرشفة الفرع"
                    >
                        🗑️
                    </button>
                    @endif
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 p-8 text-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-400">
            لا توجد فروع أو عربات توزيع مسجلة
        </div>
        @endforelse
    </div>

    <!-- Create / Edit Store Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 max-w-lg w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-black text-slate-900 dark:text-white text-base">
                    {{ $isEditing ? '✏️ تعديل بيانات الفرع / عربية التوزيع' : '➕ إضافة فرع / عربية توزيع جديدة' }}
                </h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <form wire:submit.prevent="saveStore" class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">نوع الفرع / الوحدة:</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button 
                            type="button" 
                            wire:click="$set('type', 'retail_shop')" 
                            class="py-2 text-xs font-bold rounded-xl border transition-all cursor-pointer {{ $type === 'retail_shop' ? 'bg-amber-500/20 border-amber-500 text-amber-700 dark:text-amber-400' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600' }}"
                        >
                            🏬 محل تجزئة
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('type', 'wholesale_van')" 
                            class="py-2 text-xs font-bold rounded-xl border transition-all cursor-pointer {{ $type === 'wholesale_van' ? 'bg-indigo-500/20 border-indigo-500 text-indigo-700 dark:text-indigo-400' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600' }}"
                        >
                            🚚 عربية توزيع
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('type', 'main_warehouse')" 
                            class="py-2 text-xs font-bold rounded-xl border transition-all cursor-pointer {{ $type === 'main_warehouse' ? 'bg-teal-500/20 border-teal-500 text-teal-700 dark:text-teal-400' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600' }}"
                        >
                            🏢 مخزن رئيسي
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم الفرع / العربية:</label>
                        <input type="text" wire:model="name" placeholder="مثلاً: فرع المعادي / عربية 1" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                        @error('name') <span class="text-[10px] text-rose-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الكود التعريفي (فريد):</label>
                        <input type="text" wire:model="code" placeholder="مثلاً: VAN-01 / MAADI" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                        @error('code') <span class="text-[10px] text-rose-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">رقم الهاتف:</label>
                        <input type="text" wire:model="phone" placeholder="010..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div class="flex items-center gap-4 pt-5">
                        <label class="flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="rounded text-emerald-600 focus:ring-0">
                            <span>نشط ويعمل</span>
                        </label>
                        <label class="flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" wire:model="is_main" class="rounded text-teal-600 focus:ring-0">
                            <span>فرع رئيسي</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">العنوان / مسار خط التوزيع:</label>
                    <textarea wire:model="address" rows="2" placeholder="العنوان أو خط سير عربية التوزيع..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-md">حفظ البيانات</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- User Assignment Modal -->
    @if($showUserModal && $targetStore)
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-black text-slate-900 dark:text-white text-sm">
                    👥 تعيين الموظفين والمناديب لـ [{{ $targetStore->name }}]
                </h3>
                <button wire:click="$set('showUserModal', false)" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($allUsers as $u)
                <label class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-900">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" value="{{ $u->id }}" wire:model="selectedUsers" class="rounded text-emerald-600 focus:ring-0">
                        <div>
                            <div class="font-bold text-xs text-slate-900 dark:text-white">{{ $u->name }}</div>
                            <div class="text-[10px] text-slate-400">{{ $u->phone }}</div>
                        </div>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-800 font-bold text-slate-600 dark:text-slate-400">
                        {{ $u->getRoleNames()->first() ?? 'مستخدم' }}
                    </span>
                </label>
                @endforeach
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" wire:click="$set('showUserModal', false)" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold">إلغاء</button>
                <button type="button" wire:click="saveUserAssignment" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-md">حفظ التعيينات</button>
            </div>
        </div>
    </div>
    @endif
</div>
