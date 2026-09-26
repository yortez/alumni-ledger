<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.users', [
            'admins' => User::query()->where('is_admin', true)->with('assignedAdminRole')->orderBy('name')->paginate(20),
            'roles' => AdminRole::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:40', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'integer', 'exists:admin_roles,id'],
        ]);

        $role = AdminRole::query()->findOrFail($validated['role_id']);
        abort_unless($request->user()->canDelegateAdminPermissions($role->permissions ?? []), 403);

        $admin = User::query()->create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);
        $admin->forceFill([
            'is_admin' => true,
            'admin_role' => $role->type,
            'role_id' => $role->id,
        ])->save();

        return redirect()->route('admin.users.index')->with('status', 'Administrator account created.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->is_admin, 404);
        abort_if($user->is($request->user()), 403);

        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:admin_roles,id'],
        ]);
        $role = AdminRole::query()->findOrFail($validated['role_id']);
        abort_unless($request->user()->canDelegateAdminPermissions($role->permissions ?? []), 403);

        if ($user->hasAdminPermission('manage_roles') && ! $role->hasPermission('manage_roles')) {
            $anotherRoleManagerExists = User::query()
                ->where('is_admin', true)
                ->whereKeyNot($user->id)
                ->get()
                ->contains(fn (User $administrator): bool => $administrator->hasAdminPermission('manage_roles'));

            if (! $anotherRoleManagerExists) {
                return back()->withErrors(['role_id' => 'At least one administrator with role-management access must remain.']);
            }
        }

        $user->forceFill([
            'admin_role' => $role->type,
            'role_id' => $role->id,
        ])->save();

        return redirect()->route('admin.users.index')->with('status', 'Administrator role updated.');
    }
}
