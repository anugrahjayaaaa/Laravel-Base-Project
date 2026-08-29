<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\Sortable;
use App\Http\Requests\BulkActionRequest;
use App\Http\Requests\Rbac\PermissionStoreRequest;
use App\Http\Requests\Rbac\PermissionUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Permission;

class PermissionController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $permissions = Permission::withTrashed()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->q . '%'))
            ->when(true, fn ($q) => $this->sortIndex($q, $request, 'name', ['name', 'guard_name']))
            ->paginate(10)->withQueryString();
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

        return redirect()->route('permissions.index')->with('success', __('messages.permission_created'));
    }

    public function edit(Permission $permission): View
    {
        return view('rbac.permissions.edit', compact('permission'));
    }

    public function update(PermissionUpdateRequest $request, Permission $permission): RedirectResponse
    {
        $data = $request->validated();

        $permission->update(['name' => $data['name']]);

        return redirect()->route('permissions.index')->with('success', __('messages.permission_updated'));
    }

    public function bulk(BulkActionRequest $request): RedirectResponse
    {
        $force = $request->input('action') === 'force';
        $done = 0;
        foreach ($request->input('ids') as $id) {
            $permission = Permission::withTrashed()->find($id);
            if (! $permission || ! auth()->user()->can($force ? 'permission.force-delete' : 'permission.delete', $permission)) {
                continue;
            }
            $force ? $permission->forceDelete() : $permission->delete();
            $done++;
        }

        $key = $force ? 'permissions_permanently_deleted_count' : 'permissions_deleted_count';
        return redirect()->route('permissions.index')->with('success', __('messages.' . $key, ['count' => $done]));
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();
        return redirect()->route('permissions.index')->with('success', __('messages.permission_deleted'));
    }

    public function restore(int $id): RedirectResponse
    {
        Permission::withTrashed()->findOrFail($id)->restore();
        return redirect()->route('permissions.index')->with('success', __('messages.permission_restored'));
    }

    public function forceDelete(int $id): RedirectResponse
    {
        Permission::withTrashed()->findOrFail($id)->forceDelete();
        return redirect()->route('permissions.index')->with('success', __('messages.permission_permanently_deleted'));
    }
}
