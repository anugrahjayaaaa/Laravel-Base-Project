<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rbac\RoleStoreRequest;
use App\Http\Requests\Rbac\RoleUpdateRequest;
use App\Http\Resources\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Roles
 *
 * Role management (admin).
 */
class RoleApiController extends Controller
{
    /**
     * Filter permission IDs through the plan's allowed_permissions snapshot.
     * Mirrors RoleController::filterPermissions — server-side enforcement, not
     * just UI filtering. When $plan is null (feature.manage holder), all pass.
     * When allowed_permissions is empty, all are denied (deny by default).
     */
    private function filterPermissions(array $permIds, ?PlanService $plan): array
    {
        if (! $plan) {
            return array_map('intval', $permIds);
        }
        $allowedNames = $plan->allowedPermissions();
        if ($allowedNames === []) {
            return [];
        }
        $allowedIds = Permission::whereIn('name', $allowedNames)->pluck('id')->all();

        return array_values(array_intersect($permIds, $allowedIds));
    }

    public function index(Request $request): JsonResponse
    {
        $roles = Role::withTrashed()->with('permissions')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', "%{$request->q}%"))
            ->orderBy('name')->paginate(10);

        return response()->json(RoleResource::collection($roles)->response()->getData(true));
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json(new RoleResource($role->load('permissions')));
    }

    public function store(RoleStoreRequest $request): JsonResponse
    {
        $plan = $request->user()->can('feature.manage') ? null : PlanService::for();
        $data = $request->validated();

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $perms = $this->filterPermissions($data['permissions'] ?? [], $plan);
        $role->syncPermissions($perms);

        return response()->json(new RoleResource($role->load('permissions')), 201);
    }

    /**
     * Update a role.
     *
     * @authenticated
     */
    public function update(RoleUpdateRequest $request, Role $role): JsonResponse
    {
        $plan = $request->user()->can('feature.manage') ? null : PlanService::for();
        $data = $request->validated();

        $role->update(['name' => $data['name']]);
        $perms = $this->filterPermissions($data['permissions'] ?? [], $plan);
        $role->syncPermissions($perms);

        return response()->json(new RoleResource($role->load('permissions')));
    }

    public function destroy(Role $role): JsonResponse
    {
        abort_if($role->name === 'super-admin', 403, __('messages.cannot_delete_super_admin'));
        $role->delete();

        return response()->json(['message' => __('messages.role_deleted')]);
    }

    public function restore(int $id): JsonResponse
    {
        Role::withTrashed()->findOrFail($id)->restore();

        return response()->json(['message' => __('messages.role_restored')]);
    }

    public function forceDelete(int $id): JsonResponse
    {
        abort_if(Role::withTrashed()->findOrFail($id)->name === 'super-admin', 403, __('messages.cannot_permanently_delete_super_admin'));
        Role::withTrashed()->findOrFail($id)->forceDelete();

        return response()->json(['message' => __('messages.role_permanently_deleted')]);
    }
}
