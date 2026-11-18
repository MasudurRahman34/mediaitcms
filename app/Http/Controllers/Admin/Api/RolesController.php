<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Role::query()
                ->with(['permissions:id,slug', 'users:id,role_id'])
                ->withCount('users')
                ->orderBy('name')
                ->get()
                ->map(fn (Role $role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'description' => $role->description,
                    'is_system' => $role->is_system,
                    'users_count' => $role->users_count,
                    'permissions' => $role->permissions->pluck('slug')->values(),
                ]),
            'permissions' => Permission::query()
                ->orderBy('group')
                ->orderBy('name')
                ->get()
                ->groupBy('group')
                ->map(fn ($permissions, $group) => [
                    'group' => $group,
                    'items' => $permissions->map(fn (Permission $permission) => [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'slug' => $permission->slug,
                    ])->values(),
                ])
                ->values(),
        ]);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        abort_if($role->slug === Role::SUPER_ADMIN, 422, 'The Super Admin role always has full access.');

        $data = $request->validate([
            'permission_ids' => ['present', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->permissions()->sync($data['permission_ids']);

        return response()->json(['data' => ['ok' => true]]);
    }
}
