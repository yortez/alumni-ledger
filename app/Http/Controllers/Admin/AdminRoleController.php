<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminRoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles', [
            'roles' => AdminRole::query()->withCount('users')->orderBy('name')->paginate(15),
            'permissions' => AdminRole::PERMISSIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string', 'alpha_dash', 'max:60', 'unique:admin_roles,type'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['required', 'string', Rule::in(array_keys(AdminRole::PERMISSIONS))],
        ]);

        abort_unless($request->user()->canDelegateAdminPermissions($validated['permissions']), 403);

        AdminRole::query()->create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'permissions' => array_values(array_unique($validated['permissions'])),
        ]);

        return redirect()->route('admin.roles.index')->with('status', 'Administrator role created.');
    }

    public function update(Request $request, AdminRole $role): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];

        if (! $role->is_system) {
            $rules['type'] = ['required', 'string', 'alpha_dash', 'max:60', Rule::unique('admin_roles', 'type')->ignore($role->id)];
            $rules['permissions'] = ['required', 'array', 'min:1'];
            $rules['permissions.*'] = ['required', 'string', Rule::in(array_keys(AdminRole::PERMISSIONS))];
        }

        $validated = $request->validate($rules);
        $permissions = array_values(array_unique($validated['permissions'] ?? $role->permissions));

        abort_unless($request->user()->canDelegateAdminPermissions($permissions), 403);

        if ($role->hasPermission('manage_roles') && ! in_array('manage_roles', $permissions, true)) {
            $anotherRoleManagerExists = User::query()
                ->where('is_admin', true)
                ->where(fn ($query) => $query->whereNull('role_id')->orWhere('role_id', '<>', $role->id))
                ->get()
                ->contains(fn (User $user): bool => $user->hasAdminPermission('manage_roles'));

            if (! $anotherRoleManagerExists) {
                return back()->withErrors(['permissions' => 'At least one administrator with role-management access must remain.']);
            }
        }

        $role->fill([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            ...($role->is_system ? [] : [
                'type' => $validated['type'],
                'permissions' => $permissions,
            ]),
        ])->save();

        return redirect()->route('admin.roles.index')->with('status', 'Administrator role updated.');
    }

    public function destroy(AdminRole $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->withErrors(['role' => 'System roles cannot be deleted.']);
        }

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'Reassign administrators before deleting this role.']);
        }

        if ($role->hasPermission('manage_roles')) {
            $anotherRoleManagerExists = User::query()
                ->where('is_admin', true)
                ->get()
                ->contains(fn (User $user): bool => $user->hasAdminPermission('manage_roles'));

            if (! $anotherRoleManagerExists) {
                return back()->withErrors(['role' => 'At least one administrator with role-management access must remain.']);
            }
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('status', 'Administrator role deleted.');
    }
}
