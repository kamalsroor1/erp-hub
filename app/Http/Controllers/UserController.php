<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;
use Inertia\Response;

final class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string)$request->input('search', ''));
        $role = $request->input('role', 'all');

        $query = User::with(['roles', 'defaultStore']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role !== 'all') {
            $query->role($role);
        }

        $users = $query->latest('id')->paginate(15)->withQueryString();
        $roles = Role::select('id', 'name')->get();
        $stores = Store::where('is_active', true)->select('id', 'name')->get();

        return Inertia::render('Users/Index', [
            'users' => $users->through(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'phone' => $u->phone,
                'email' => $u->email,
                'is_active' => (bool)$u->is_active,
                'default_store_id' => $u->default_store_id,
                'default_store_name' => $u->defaultStore?->name,
                'roles' => $u->roles->pluck('name')->toArray(),
                'primary_role' => $u->roles->first()?->name ?: 'cashier',
                'created_at' => $u->created_at ? $u->created_at->toDateString() : '',
            ]),
            'roles' => $roles->map(fn($r) => [
                'id' => $r->name,
                'name' => match ($r->name) {
                    'admin' => 'مدير النظام (كامل الصلاحيات) 👑',
                    'cashier' => 'كاشير مبيعات ونقطة بيع 🛒',
                    'storekeeper' => 'أمين مخزن وتوريدات 📦',
                    'accountant' => 'محاسب ومدقق مالي 💼',
                    default => $r->name,
                },
            ]),
            'stores' => $stores,
            'filters' => [
                'search' => $search,
                'role' => $role,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'default_store_id' => 'nullable|exists:stores,id',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'password' => Hash::make($validated['password']),
                'default_store_id' => $validated['default_store_id'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $user->syncRoles([$validated['role']]);
        });

        return redirect()->back()->with('success', 'تم إنشاء حساب المستخدم وتعيين الصلاحيات بنجاح');
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'default_store_id' => 'nullable|exists:stores,id',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($user, $validated) {
            $data = [
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'default_store_id' => $validated['default_store_id'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ];

            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);
            $user->syncRoles([$validated['role']]);
        });

        return redirect()->back()->with('success', 'تم تحديث بيانات المستخدم بنجاح');
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'لا يمكنك حذف حسابك الشخصي الحالي');
        }

        $user->delete();
        return redirect()->back()->with('success', 'تم نقل المستخدم إلى سلة المحذوفات بنجاح');
    }

    public function toggleActive(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        return redirect()->back()->with('success', 'تم تغيير حالة الحساب بنجاح');
    }
}