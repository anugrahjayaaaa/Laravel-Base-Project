<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rbac\PermissionStoreRequest;
use App\Http\Requests\Rbac\PermissionUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        $permissions = Permission::withTrashed()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->q . '%'))
            ->orderBy('name')->paginate(10)->withQueryString();
        return view('rbac.permissions.index', compact('permissions'));
    }

    public function create(): View
    {
        return view('rbac.permissions.create');
    }

    public function store(PermissionStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Permission::create(['name' => $data['name'], 'guard_name' => 'web']);

        return redirect()->route('permissions.index')->with('success', 'Permission created.');
    }

    public function edit(Permission $permission): View
    {
        return view('rbac.permissions.edit', compact('permission'));
    }

    public function update(PermissionUpdateRequest $request, Permission $permission): RedirectResponse
    {
        $data = $request->validated();

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

    public function forceDelete(int $id): RedirectResponse
    {
        Permission::withTrashed()->findOrFail($id)->forceDelete();
        return redirect()->route('permissions.index')->with('success', 'Permission permanently deleted.');
    }
}
