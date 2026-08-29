<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\Sortable;
use App\Http\Requests\BulkActionRequest;
use App\Http\Requests\Rbac\RoleStoreRequest;
use App\Http\Requests\Rbac\RoleUpdateRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\BulkDeleteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    use Sortable;

    public function __construct(private BulkDeleteService $bulk) {}

    public function index(Request $request): View
    {
        $roles = Role::withTrashed()->with('permissions')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->when(true, fn ($q) => $this->sortIndex($q, $request, 'name', ['name']))
            ->paginate(10)->withQueryString();

        return view('access.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = Permission::orderBy('name')->get();

        return view('access.roles.create', compact('permissions'));
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

        return view('access.roles.edit', compact('role', 'permissions'));
    }

    public function update(RoleUpdateRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();

        $role->update(['name' => $data['name']]);
        $role->syncPermissions(array_map('intval', $data['permissions'] ?? []));

        return redirect()->route('roles.index')->with('success', __('messages.role_updated'));
    }

    public function bulk(BulkActionRequest $request): RedirectResponse
    {
        $force = $request->input('action') === 'force';
        $done = $this->bulk->run(
            Role::class,
            $request->input('ids'),
            $force,
            'role',
            fn (Role $role) => $role->name === 'super-admin', // protect super-admin
        );

        $key = $force ? 'roles_permanently_deleted_count' : 'roles_deleted_count';

        return redirect()->route('roles.index')->with('success', __('messages.'.$key, ['count' => $done]));
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
