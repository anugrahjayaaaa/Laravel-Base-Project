<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::withTrashed()
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('username', 'like', '%' . $request->q . '%')
                  ->orWhere('email', 'like', '%' . $request->q . '%');
            }))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'password' => 'required|string|min:12|confirmed',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => bcrypt($data['password']),
        ]);
        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();
        $user->load('roles');
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:12|confirmed',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->update([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);
        if (! empty($data['password'])) {
            $user->update(['password' => bcrypt($data['password'])]);
        }
        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Cannot delete yourself.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted.');
    }

    public function restore(int $id): RedirectResponse
    {
        User::withTrashed()->findOrFail($id)->restore();
        return redirect()->route('users.index')->with('success', 'User restored.');
    }
}
