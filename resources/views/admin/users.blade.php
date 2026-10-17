@extends('layouts.app')

@section('title', 'Administrator access')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-7">
    <header class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-xl font-semibold">Administrator Access</h1><p class="mt-1 text-xs text-muted">{{ $adminCount }} administrator accounts</p></div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.roles.index') }}" class="inline-flex min-h-9 items-center rounded-md border border-line bg-surface px-3 py-2 text-xs font-medium text-muted transition hover:text-ink">Role Definitions</a>
            <details class="relative">
                <summary class="inline-flex min-h-9 cursor-pointer list-none items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark"><span aria-hidden="true">+</span>Add Administrator</summary>
                <div class="absolute right-0 z-20 mt-2 max-h-[82vh] w-[min(92vw,34rem)] overflow-y-auto rounded-md border border-line bg-surface p-5 shadow-xl">
                    <h2 class="text-sm font-semibold">Create an administrator</h2>
                    <form method="POST" action="{{ route('admin.users.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">@csrf
                        <div class="field"><label for="name">Full name</label><input id="name" name="name" value="{{ old('name') }}" required>@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="field"><label for="username">Username</label><input id="username" name="username" value="{{ old('username') }}" required>@error('username')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="field sm:col-span-2"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required>@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="field"><label for="password">Temporary password</label><input id="password" name="password" type="password" required>@error('password')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" required></div>
                        <div class="field sm:col-span-2"><label for="role_id">Role</label><select id="role_id" name="role_id" required>@foreach ($roles as $role)<option value="{{ $role->id }}" @selected((int) old('role_id', $roles->firstWhere('type', 'content_admin')?->id) === $role->id)>{{ $role->name }}</option>@endforeach</select>@error('role_id')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <button type="submit" class="bg-forest px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-forest-dark sm:col-span-2">Create administrator</button>
                    </form>
                </div>
            </details>
        </div>
    </header>

    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4 flex flex-wrap items-center gap-2 rounded-md border border-line bg-surface p-3 sm:p-4">
        <label class="relative block w-full sm:max-w-72"><span class="sr-only">Search administrators</span><svg aria-hidden="true" class="absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="m16 16 4 4" /></svg><input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Search name, username, email..." class="w-full rounded-md border border-line bg-surface-raised py-2 pl-9 pr-3 text-xs text-ink placeholder:text-muted focus:border-forest focus:outline-none"></label>
        <label><span class="sr-only">Role</span><select name="role" class="min-w-40 rounded-md border border-line bg-surface-raised px-3 py-2 text-xs text-ink focus:border-forest focus:outline-none"><option value="">All Roles</option>@foreach ($roles as $role)<option value="{{ $role->id }}" @selected($filters['role'] === (string) $role->id)>{{ $role->name }}</option>@endforeach</select></label>
        <button type="submit" class="inline-flex min-h-9 items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark">Filter</button><a href="{{ route('admin.users.index') }}" class="inline-flex min-h-9 items-center rounded-md border border-line bg-surface-raised px-4 py-2 text-xs font-medium text-muted transition hover:text-ink">Reset</a>
    </form>

    <section class="grid gap-3">
        @forelse ($admins as $admin)
            @php($adminRole = $admin->adminRole())
            <article class="overflow-hidden rounded-md border border-line bg-surface">
                <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <div class="flex min-w-0 items-center gap-3"><span class="grid size-9 shrink-0 place-items-center rounded-full bg-forest text-xs font-bold text-white">{{ strtoupper(substr($admin->name, 0, 1)) }}</span><div class="min-w-0"><h2 class="truncate text-sm font-semibold">{{ $admin->name }}</h2><p class="mt-1 truncate text-xs text-muted">{{ $admin->username }} · {{ $admin->email }}</p><p class="mt-1 text-[10px] text-muted">{{ $adminRole?->description ?? 'No module access is assigned.' }}</p></div></div>
                    <div class="flex shrink-0 flex-col gap-2 sm:items-end"><span class="rounded-full bg-mint px-2.5 py-1 text-[10px] font-semibold text-forest">{{ $adminRole?->name ?? 'Unassigned' }}</span>
                        @if ($admin->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.update', $admin) }}" class="flex items-center gap-2">@csrf @method('PATCH')<label for="role-{{ $admin->id }}" class="sr-only">Assign role</label><select id="role-{{ $admin->id }}" name="role_id" required class="max-w-48 rounded-md border border-line bg-surface-raised px-2.5 py-2 text-xs text-ink">@foreach ($roles as $role)<option value="{{ $role->id }}" @selected($adminRole?->id === $role->id)>{{ $role->name }}</option>@endforeach</select><button type="submit" class="rounded-md border border-line px-3 py-2 text-[10px] font-medium text-muted hover:text-ink">Save</button></form>
                            @error('role_id')<p class="field-error">{{ $message }}</p>@enderror
                        @else
                            <p class="text-[10px] text-muted">Your role cannot be changed here.</p>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <p class="rounded-md border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No administrator accounts match these filters.</p>
        @endforelse
    </section>
    <div class="mt-4">{{ $admins->links() }}</div>
</div>
@endsection
