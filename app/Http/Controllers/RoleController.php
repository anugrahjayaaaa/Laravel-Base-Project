<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rbac\RoleStoreRequest;
use App\Http\Requests\Rbac\RoleUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Permission;
use App\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::withTrashed()->with('permissions')->paginate(10);
        return view('rbac.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = Permission::orderBy('name')->get();
        return view('rbac.roles.create', compact('permissions'));
    }

    public function store(RoleStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions(array_map('intval', $data['permissions'] ?? []));

        return redirect()->route('roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::orderBy('name')->get();
        $role->load('permissions');
        return view('rbac.roles.edit', compact('role', 'permissions'));
    }

    public function update(RoleUpdateRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();

        $role->update(['name' => $data['name']]);
        $role->syncPermissions(array_map('intval', $data['permissions'] ?? []));

        return redirect()->route('roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'super-admin') {
            return redirect()->route('roles.index')->with('error', 'Cannot delete super-admin.');
        }
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted.');
    }

    public function restore(int $id): RedirectResponse
    {
        Role::withTrashed()->findOrFail($id)->restore();
        return redirect()->route('roles.index')->with('success', 'Role restored.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        if (Role::withTrashed()->findOrFail($id)->name === 'super-admin') {
            return redirect()->route('roles.index')->with('error', 'Cannot permanently delete super-admin.');
        }
        Role::withTrashed()->findOrFail($id)->forceDelete();
        return redirect()->route('roles.index')->with('success', 'Role permanently deleted.');
    }
}
