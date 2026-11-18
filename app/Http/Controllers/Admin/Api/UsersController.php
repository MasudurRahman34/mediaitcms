<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsersController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => User::query()
                ->with('role:id,name,slug')
                ->orderBy('name')
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role?->name,
                    'role_id' => $user->role_id,
                    'is_active' => $user->is_active,
                    'last_login_at' => $user->last_login_at,
                    'created_at' => $user->created_at,
                ]),
            'roles' => Role::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'password' => ['required', Password::min(10)->letters()->numbers()],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create($data);

        return response()->json(['data' => ['id' => $user->id]], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', Password::min(10)->letters()->numbers()],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ]);

        if ($request->user()->is($user) && ($data['is_active'] ?? true) === false) {
            return response()->json(['message' => 'You cannot deactivate your own account.'], 422);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json(['data' => ['ok' => true]]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->is($user)) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        if ($user->isSuperAdmin() && User::query()->whereHas('role', fn ($q) => $q->where('slug', Role::SUPER_ADMIN))->count() <= 1) {
            return response()->json(['message' => 'At least one Super Admin must remain.'], 422);
        }

        $user->delete();

        return response()->json(['data' => ['ok' => true]]);
    }
}
