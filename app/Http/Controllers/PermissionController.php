<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::withTrashed()->orderBy('name')->paginate(10);
        return view('rbac.permissions.index', compact('permissions'));
    }

    public function create(): View
    {
        return view('rbac.permissions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        Permission::create(['name' => $data['name'], 'guard_name' => 'web']);

        return redirect()->route('permissions.index')->with('success', 'Permission created.');
    }

    public function edit(Permission $permission): View
    {
        return view('rbac.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update(['name' => $data['name']]);

        return redirect()->route('permissions.index')->with('success', 'Permission updated.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();
        return redirect()->route('permissions.index')->with('success', 'Permission deleted.');
    }

    public function restore(int $id): RedirectResponse
    {
        Permission::withTrashed()->findOrFail($id)->restore();
        return redirect()->route('permissions.index')->with('success', 'Permission restored.');
    }
}
