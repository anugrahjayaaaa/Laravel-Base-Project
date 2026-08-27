<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        return view('profile.show', ['user' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|unique:users,phone,' . $user->id,
        ]);
        $user->update($data);
        return redirect()->route('profile.show')->with('success', 'Profile updated.');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:12|confirmed',
        ]);
        $user->update(['password' => bcrypt($data['password'])]);
        // revoke other sessions
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }
        auth()->logoutOtherDevices($data['password']);
        return redirect()->route('profile.show')->with('success', 'Password changed.');
    }
}
