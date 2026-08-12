<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="bg-white dark:bg-slate-900/90 backdrop-blur-md border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white font-tajawal flex items-center gap-2">
                <span>👥</span>
                <span>إدارة المستخدمين والكاشير</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                إضافة كاشير جديد، تحديد الصلاحيات، وتعيين كلمات المرور
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-black shadow-lg shadow-emerald-600/30 flex items-center gap-1.5">
                <span>👥 إدارة المستخدمين</span>
            </span>
            <a href="{{ route('roles.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-all flex items-center gap-1.5">
                <span>🛡️ مصفوفة الصلاحيات</span>
            </a>
            <button
                wire:click="openCreateModal"
                class="px-4 py-2.5 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-black rounded-xl shadow-lg shadow-amber-600/30 transition-all flex items-center gap-1.5 text-xs cursor-pointer"
            >
                <span>➕</span>
                <span>إضافة مستخدم جديد</span>
            </button>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white dark:bg-slate-900/80 backdrop-blur-md border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row items-center gap-4">
        <div class="relative flex-1 w-full">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="ابحث بالاسم أو البريد الإلكتروني أو رقم الهاتف..."
                class="w-full pr-10 pl-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:outline-none"
            >
            <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400">🔍</span>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-slate-900/80 backdrop-blur-md border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/80 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4">المستخدم</th>
                        <th class="px-6 py-4">رقم الهاتف للدخول</th>
                        <th class="px-6 py-4">الصلاحية / الدور</th>
                        <th class="px-6 py-4">الحالة</th>
                        <th class="px-6 py-4">تاريخ الإنشاء</th>
                        <th class="px-6 py-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-500/20 border border-amber-500/40 text-amber-600 dark:text-amber-300 flex items-center justify-center font-bold text-sm">
                                    {{ mb_substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white">{{ $user->name }}</p>
                                    @if ($user->id === auth()->id())
                                        <span class="text-[10px] text-amber-600 dark:text-amber-400 font-normal">(حسابك الحالي)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-sm text-amber-600 dark:text-amber-300 font-bold" dir="ltr">
                                📱 {{ $user->phone ?? $user->email }}
                            </td>
                            <td class="px-6 py-4">
                                @php $roleName = $user->roles->first()?->name ?? 'cashier'; @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ \App\Enums\UserRole::getBadgeClass($roleName) }}">
                                    {{ \App\Enums\UserRole::getFormatted($roleName) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        نشط
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        معطل
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400">
                                {{ $user->created_at?->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        wire:click="openEditModal({{ $user->id }})"
                                        class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 rounded-xl text-xs font-bold transition-all border border-slate-300 dark:border-slate-700 cursor-pointer"
                                    >
                                        ✏️ تعديل
                                    </button>

                                    @if ($user->id !== auth()->id())
                                        <button
                                            wire:click="toggleUserStatus({{ $user->id }})"
                                            class="px-3 py-1.5 {{ $user->is_active ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 border-rose-500/30' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20 border-emerald-500/30' }} rounded-xl text-xs font-bold transition-all border cursor-pointer"
                                        >
                                            {{ $user->is_active ? '🚫 تعطيل' : '✅ تفعيل' }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                لا يوجد مستخدمين يطابقون خيارات البحث.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($users->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-950/40">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Create / Edit User Modal -->
    @if ($showUserModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" x-data="{ showPass: false }">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-6 relative">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white font-tajawal flex items-center gap-2">
                        <span>{{ $editingUserId ? '✏️ تعديل بيانات المستخدم' : '➕ إضافة كاشير / مستخدم جديد' }}</span>
                    </h3>
                    <button wire:click="$set('showUserModal', false)" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-lg cursor-pointer">
                        ✕
                    </button>
                </div>

                <!-- Modal Form -->
                <form wire:submit.prevent="saveUser" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            اسم المستخدم الكامل <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model="name"
                            type="text"
                            required
                            placeholder="مثال: أحمد محمود (كاشير)"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                        @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            رقم الهاتف للدخول <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model="phone"
                            type="text"
                            required
                            dir="ltr"
                            placeholder="مثال: 01012316954"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm font-mono placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                        @error('phone') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            البريد الإلكتروني (اختياري)
                        </label>
                        <input
                            wire:model="email"
                            type="email"
                            dir="ltr"
                            placeholder="cashier@sroor.com"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                        @error('email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            الصلاحية والدور <span class="text-rose-500">*</span>
                        </label>
                        <select
                            wire:model="role"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                            @foreach($availableRoles as $ar)
                                <option value="{{ $ar->name }}">
                                    {{ \App\Enums\UserRole::getFormatted($ar->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ $editingUserId ? 'كلمة المرور الجديدة (اتركها فارغة إذا لم ترغب في التغيير)' : 'كلمة المرور' }}
                            @if (!$editingUserId) <span class="text-rose-500">*</span> @endif
                        </label>
                        <div class="relative">
                            <input
                                wire:model="password"
                                :type="showPass ? 'text' : 'password'"
                                {{ $editingUserId ? '' : 'required' }}
                                dir="ltr"
                                placeholder="••••••••"
                                class="w-full pr-4 pl-10 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:outline-none"
                            >
                            <button
                                type="button"
                                @click="showPass = !showPass"
                                class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 hover:text-amber-500 cursor-pointer"
                            >
                                <span x-show="!showPass">👁️</span>
                                <span x-show="showPass" style="display: none;">🙈</span>
                            </button>
                        </div>
                        @error('password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input
                                wire:model="is_active"
                                type="checkbox"
                                class="w-4 h-4 rounded bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-700 text-amber-500 focus:ring-amber-500"
                            >
                            <span class="text-xs text-slate-700 dark:text-slate-300 font-bold">الحساب نشط ومصرح له بالدخول</span>
                        </label>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-800">
                        <button
                            type="button"
                            wire:click="$set('showUserModal', false)"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                        >
                            إلغاء
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-amber-600/30 transition-all font-tajawal cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="saveUser">💾 حفظ بيانات المستخدم</span>
                            <span wire:loading wire:target="saveUser">جاري الحفظ...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
