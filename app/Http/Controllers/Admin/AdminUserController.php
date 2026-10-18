<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function create(): View
    {
        return view('admin.user-create', [
            'roles' => AdminRole::query()->orderBy('name')->get(),
        ]);
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $roleFilter = (string) $request->query('role', '');
        $role = $roleFilter !== '' ? AdminRole::query()->find($roleFilter) : null;
        $query = User::query()->where('is_admin', true)
            ->with('assignedAdminRole')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role !== null, fn ($query) => $query->where(fn ($query) => $query->where('role_id', $role->id)->orWhere('admin_role', $role->type)))
            ->orderBy('name');

        return view('admin.users', [
            'admins' => $query->paginate(20)->withQueryString(),
            'adminCount' => (clone $query)->count(),
            'filters' => ['search' => $search, 'role' => $roleFilter],
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'username' => ['sometimes', 'required', 'string', 'max:40', 'alpha_dash', Rule::unique('users', 'username')->ignore($user)],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role_id' => ['sometimes', 'required', 'integer', 'exists:admin_roles,id'],
        ]);

        $user->fill(Arr::only($validated, ['name', 'username', 'email']));

        if (isset($validated['role_id'])) {
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
            ]);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('status', 'Administrator account updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->is_admin, 404);
        abort_if($user->is($request->user()), 403);

        if ($user->hasAdminPermission('manage_roles')) {
            $anotherRoleManagerExists = User::query()
                ->where('is_admin', true)
                ->whereKeyNot($user->id)
                ->get()
                ->contains(fn (User $administrator): bool => $administrator->hasAdminPermission('manage_roles'));

            if (! $anotherRoleManagerExists) {
                return back()->withErrors(['admin' => 'At least one administrator with role-management access must remain.']);
            }
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Administrator account deleted.');
    }
}
