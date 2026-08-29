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
    public function index(Request $request): View
    {
        $roles = Role::withTrashed()->with('permissions')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->q . '%'))
            ->orderBy('name')->paginate(10)->withQueryString();
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

        return redirect()->route('roles.index')->with('success', __('messages.role_created'));
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

        return redirect()->route('roles.index')->with('success', __('messages.role_updated'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'super-admin') {
            return redirect()->route('roles.index')->with('error', __('messages.cannot_delete_super_admin'));
        }
        $role->delete();
        return redirect()->route('roles.index')->with('success', __('messages.role_deleted'));
    }

    public function restore(int $id): RedirectResponse
    {
        Role::withTrashed()->findOrFail($id)->restore();
        return redirect()->route('roles.index')->with('success', __('messages.role_restored'));
    }

    public function forceDelete(int $id): RedirectResponse
    {
        if (Role::withTrashed()->findOrFail($id)->name === 'super-admin') {
            return redirect()->route('roles.index')->with('error', __('messages.cannot_permanently_delete_super_admin'));
        }
        Role::withTrashed()->findOrFail($id)->forceDelete();
        return redirect()->route('roles.index')->with('success', __('messages.role_permanently_deleted'));
    }
}
