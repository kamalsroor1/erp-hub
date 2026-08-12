<div class="space-y-6">
    <!-- Header & Navigation Tabs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>🛡️ إدارة الأدوار ومصفوفة الصلاحيات</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">تحديد صلاحيات الكاشير، أمين المخازن، المحاسب، وضبط ما يمكن لكل موظف تنفيذه</p>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2">
            <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-all flex items-center gap-1.5">
                <span>👥 إدارة المستخدمين</span>
            </a>
            <span class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-black shadow-lg shadow-emerald-600/30 flex items-center gap-1.5">
                <span>🛡️ مصفوفة الصلاحيات</span>
            </span>
        </div>
    </div>

    <!-- Main Content: Roles Sidebar + Permissions Matrix -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        
        <!-- Left Column: Roles Selector -->
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 space-y-3 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <span class="text-xs font-black text-slate-800 dark:text-slate-200">الأدوار المتاحة ({{ count($roles) }})</span>
                    <button 
                        type="button" 
                        wire:click="$set('showNewRoleModal', true)" 
                        class="px-2 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-600 text-emerald-700 dark:text-emerald-400 hover:text-white text-[11px] font-bold border border-emerald-500/30 transition-all cursor-pointer flex items-center gap-1"
                    >
                        <span>+ دور جديد</span>
                    </button>
                </div>

                <div class="space-y-2">
                    @foreach($roles as $r)
                    @php
                        $isSelected = $selectedRoleId === $r->id;
                    @endphp
                    <button 
                        type="button" 
                        wire:click="selectRole({{ $r->id }})" 
                        class="w-full text-right p-3 rounded-xl border transition-all cursor-pointer flex items-center justify-between {{ $isSelected ? 'bg-emerald-500/10 border-emerald-500 text-emerald-900 dark:text-emerald-300 font-black shadow-sm' : 'bg-slate-50 dark:bg-slate-950/60 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700' }}"
                    >
                        <div>
                            <div class="text-xs font-bold">{{ $this->getRoleDisplayName($r->name) }}</div>
                            <div class="text-[10px] font-mono opacity-70 mt-0.5">{{ $r->name }}</div>
                        </div>
                        <div class="text-left">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                {{ $r->permissions_count }} صلاحية
                            </span>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Column: Permissions Matrix for Selected Role -->
        <div class="lg:col-span-3 space-y-4">
            @if($selectedRole)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 space-y-6 shadow-sm">
                
                <!-- Action Bar for Selected Role -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <span>صلاحيات دور:</span>
                            <span class="text-emerald-600 dark:text-emerald-400">{{ $this->getRoleDisplayName($selectedRole->name) }}</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">حدد الصلاحيات المسموح بها لهذا الدور في مختلف شاشات وعمليات النظام</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if($selectedRole->name !== 'admin')
                        <button 
                            type="button" 
                            wire:click="selectAll" 
                            class="px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors cursor-pointer"
                        >
                            ✓ تحديد الكل
                        </button>
                        <button 
                            type="button" 
                            wire:click="unselectAll" 
                            class="px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors cursor-pointer"
                        >
                            ✕ إلغاء الكل
                        </button>
                        @endif

                        <button 
                            type="button" 
                            wire:click="savePermissions" 
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-lg shadow-emerald-600/30 transition-all cursor-pointer flex items-center gap-1.5"
                        >
                            <span>💾 حفظ الصلاحيات</span>
                        </button>
                    </div>
                </div>

                @if($selectedRole->name === 'admin')
                <div class="p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-700 dark:text-indigo-300 text-xs flex items-center gap-2 font-bold">
                    <span>👑 دور المدير العام يمتلك كافة الصلاحيات بشكل تلقائي ولا يمكن تقييده.</span>
                </div>
                @endif

                <!-- Permission Groups Grid -->
                <div class="space-y-6">
                    @foreach($permissionGroups as $groupKey => $group)
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800/80 overflow-hidden bg-slate-50/50 dark:bg-slate-950/40">
                        <div class="px-4 py-2.5 bg-slate-100 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <span class="text-xs font-black text-slate-800 dark:text-slate-200">{{ $group['title'] }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">({{ count($group['items']) }} صلاحيات)</span>
                        </div>

                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($group['items'] as $permKey => $permLabel)
                            @php
                                $isChecked = in_array($permKey, $selectedPermissions);
                            @endphp
                            <label class="flex items-start gap-3 p-3 rounded-xl border transition-all cursor-pointer {{ $isChecked ? 'bg-white dark:bg-slate-900 border-emerald-500/50 shadow-sm' : 'bg-slate-50 dark:bg-slate-950/60 border-slate-200 dark:border-slate-800 opacity-75 hover:opacity-100' }}">
                                <input 
                                    type="checkbox" 
                                    wire:model="selectedPermissions" 
                                    value="{{ $permKey }}"
                                    {{ $selectedRole->name === 'admin' ? 'disabled' : '' }}
                                    class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer h-4 w-4"
                                >
                                <div class="text-xs">
                                    <div class="font-bold text-slate-900 dark:text-white {{ $isChecked ? 'text-emerald-700 dark:text-emerald-400' : '' }}">
                                        {{ $permLabel }}
                                    </div>
                                    <div class="font-mono text-[10px] text-slate-400 mt-0.5">
                                        {{ $permKey }}
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Bottom Save Button -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button 
                        type="button" 
                        wire:click="savePermissions" 
                        class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-lg shadow-emerald-600/30 transition-all cursor-pointer flex items-center gap-2"
                    >
                        <span>💾 حفظ واعتماد الصلاحيات لدور [{{ $selectedRole->name }}]</span>
                    </button>
                </div>

            </div>
            @endif
        </div>

    </div>

    <!-- Modal: Create New Role -->
    @if($showNewRoleModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 w-full max-w-md space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span>🛡️ إنشاء دور مخصص جديد</span>
                </h3>
                <button wire:click="$set('showNewRoleModal', false)" class="text-slate-400 hover:text-slate-600 cursor-pointer font-bold">✕</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">اسم الدور (باللغة الإنجليزية أو العربية):</label>
                    <input 
                        type="text" 
                        wire:model="newRoleName" 
                        placeholder="مثال: supervisor, van_driver, assistant" 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 font-mono"
                    >
                    @error('newRoleName') <span class="text-rose-500 text-[11px] font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button 
                    type="button" 
                    wire:click="$set('showNewRoleModal', false)" 
                    class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold cursor-pointer"
                >
                    إلغاء
                </button>
                <button 
                    type="button" 
                    wire:click="createRole" 
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-md cursor-pointer"
                >
                    إنشاء الدور
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
