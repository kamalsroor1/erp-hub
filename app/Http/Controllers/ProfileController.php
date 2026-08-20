<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

final class ProfileController extends Controller
{
    public function show(): Response
    {
        $user = Auth::user();

        return Inertia::render('Profile/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'theme_preference' => $user->theme_preference ?: 'dark',
                'role' => $user->roles->first()?->name ?: 'cashier',
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|string|min:6|confirmed',
            'theme_preference' => 'required|in:dark,light',
        ]);

        if (!empty($validated['new_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return redirect()->back()->withErrors(['current_password' => __('auth.current_password_incorrect')]);
            }
            $user->password = Hash::make($validated['new_password']);
        }

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        $user->email = $validated['email'] ?? null;
        $user->theme_preference = $validated['theme_preference'];
        $user->save();

        return redirect()->back()->with('success', __('auth.profile_updated'));
    }
}