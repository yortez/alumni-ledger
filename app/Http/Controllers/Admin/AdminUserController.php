<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.users', [
            'admins' => User::query()->where('is_admin', true)->orderBy('name')->paginate(20),
            'roles' => AdminRole::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:40', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(array_column(AdminRole::cases(), 'value'))],
        ]);

        $admin = User::query()->create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);
        $admin->forceFill([
            'is_admin' => true,
            'admin_role' => $validated['role'],
        ])->save();

        return redirect()->route('admin.users.index')->with('status', 'Administrator account created.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->is_admin, 404);
        abort_if($user->is($request->user()), 403);

        $validated = $request->validate([
            'role' => ['required', Rule::in(array_column(AdminRole::cases(), 'value'))],
        ]);
        $newRole = AdminRole::from($validated['role']);

        $updated = DB::transaction(function () use ($newRole, $user): bool {
            $administrators = User::query()
                ->where('is_admin', true)
                ->lockForUpdate()
                ->get(['id', 'is_admin', 'admin_role']);
            $superAdminCount = $administrators
                ->filter(fn (User $administrator): bool => $administrator->adminRole() === AdminRole::SuperAdmin)
                ->count();

            if ($user->adminRole() === AdminRole::SuperAdmin && $newRole !== AdminRole::SuperAdmin && $superAdminCount <= 1) {
                return false;
            }

            $user->forceFill(['admin_role' => $newRole->value])->save();

            return true;
        });

        if (! $updated) {
            return back()->withErrors(['role' => 'At least one super administrator must remain.']);
        }

        return redirect()->route('admin.users.index')->with('status', 'Administrator role updated.');
    }
}
