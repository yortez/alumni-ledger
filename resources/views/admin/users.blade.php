@extends('layouts.app')

@section('title', 'Administrator access')

@section('content')
<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="flex flex-col justify-between gap-6 border-b border-line pb-8 sm:flex-row sm:items-end">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="font-display mt-3 text-4xl sm:text-5xl">Administrator access</h1>
        </div>
    </div>

    <div class="mt-8 grid items-start gap-8 lg:grid-cols-[0.8fr_1.2fr]">
        <section class="border border-line bg-white p-5 sm:p-6">
            <p class="eyebrow">New account</p>
            <h2 class="font-display mt-2 text-2xl">Create an administrator</h2>
            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-5 grid gap-5">
                @csrf
                <div class="field">
                    <label for="name">Full name</label>
                    <input id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" name="username" value="{{ old('username') }}" required>
                    @error('username')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                    @error('email')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="password">Temporary password</label>
                    <input id="password" name="password" type="password" required>
                    @error('password')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required>
                </div>
                <div class="field">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role', 'content_admin') === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="bg-forest px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-forest-dark">Create administrator</button>
            </form>
        </section>

        <section class="min-w-0">
            <div class="mb-4 flex items-end justify-between gap-4">
                <h2 class="font-display text-2xl">Administrators</h2>
                <span class="text-xs uppercase tracking-wider text-muted">{{ $admins->total() }} accounts</span>
            </div>
            <div class="grid gap-3">
                @forelse ($admins as $admin)
                    <article class="border border-line bg-white p-5 sm:p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="break-words font-display text-xl">{{ $admin->name }}</h3>
                                <p class="mt-1 break-all text-sm text-muted">{{ $admin->username }} · {{ $admin->email }}</p>
                            </div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-forest">{{ $admin->adminRole()?->label() ?? 'Unassigned' }}</span>
                        </div>
                        @if ($admin->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.update', $admin) }}" class="mt-5 flex flex-col gap-3 border-t border-line pt-4 sm:flex-row sm:items-end">
                                @csrf
                                @method('PATCH')
                                <div class="field flex-1">
                                    <label for="role-{{ $admin->id }}">Assign role</label>
                                    <select id="role-{{ $admin->id }}" name="role" required>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->value }}" @selected($admin->adminRole() === $role)>{{ $role->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="border border-forest px-4 py-3 text-sm font-semibold text-forest transition hover:bg-mint">Save role</button>
                            </form>
                            @error('role')<p class="field-error mt-2">{{ $message }}</p>@enderror
                        @else
                            <p class="mt-4 border-t border-line pt-4 text-xs text-muted">Your own role cannot be changed here.</p>
                        @endif
                        <p class="mt-3 text-xs leading-5 text-muted">{{ $admin->adminRole()?->description() ?? 'No module access is assigned.' }}</p>
                    </article>
                @empty
                    <p class="border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No administrator accounts found.</p>
                @endforelse
            </div>
            <div class="mt-5">{{ $admins->links() }}</div>
        </section>
    </div>
</div>
@endsection
