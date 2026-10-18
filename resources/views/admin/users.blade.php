@extends('layouts.app')

@section('title', 'Administrator access')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-7">
    <header class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-xl font-semibold">Administrator Access</h1><p class="mt-1 text-xs text-muted">{{ $adminCount }} administrator accounts</p></div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.roles.index') }}" class="inline-flex min-h-9 items-center rounded-md border border-line bg-surface px-3 py-2 text-xs font-medium text-muted transition hover:text-ink">Role Definitions</a>
            <a href="{{ route('admin.users.create') }}" class="inline-flex min-h-9 items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark"><span aria-hidden="true">+</span>Add Administrator</a>
        </div>
    </header>
    @if ($errors->any())<p class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800" role="alert">{{ $errors->first() }}</p>@endif

    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4 flex flex-wrap items-center gap-2 rounded-md border border-line bg-surface p-3 sm:p-4">
        <label class="relative block w-full sm:max-w-72"><span class="sr-only">Search administrators</span><svg aria-hidden="true" class="absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="m16 16 4 4" /></svg><input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Search name, username, email..." class="w-full rounded-md border border-line bg-surface-raised py-2 pl-9 pr-3 text-xs text-ink placeholder:text-muted focus:border-forest focus:outline-none"></label>
        <label><span class="sr-only">Role</span><select name="role" class="min-w-40 rounded-md border border-line bg-surface-raised px-3 py-2 text-xs text-ink focus:border-forest focus:outline-none"><option value="">All Roles</option>@foreach ($roles as $role)<option value="{{ $role->id }}" @selected($filters['role'] === (string) $role->id)>{{ $role->name }}</option>@endforeach</select></label>
        <button type="submit" class="inline-flex min-h-9 items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark">Filter</button><a href="{{ route('admin.users.index') }}" class="inline-flex min-h-9 items-center rounded-md border border-line bg-surface-raised px-4 py-2 text-xs font-medium text-muted transition hover:text-ink">Reset</a>
    </form>

    <section class="grid gap-3">
        @forelse ($admins as $admin)
            @php($adminRole = $admin->adminRole())
            <article class="overflow-hidden rounded-md border border-line bg-surface">
                <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-start sm:px-5">
                    <div class="flex min-w-0 items-center gap-3 sm:w-48 sm:shrink-0"><span class="grid size-9 shrink-0 place-items-center rounded-full bg-forest text-xs font-bold text-white">{{ strtoupper(substr($admin->name, 0, 1)) }}</span><div class="min-w-0"><h2 class="truncate text-sm font-semibold">{{ $admin->name }}</h2><p class="mt-1 text-[10px] text-muted">{{ $adminRole?->description ?? 'No module access is assigned.' }}</p></div></div>
                    @if ($admin->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.update', $admin) }}" class="grid min-w-0 flex-1 gap-2 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_1fr_auto]">@csrf @method('PATCH')
                            <label class="sr-only" for="name-{{ $admin->id }}">Administrator name</label><input id="name-{{ $admin->id }}" name="name" value="{{ $admin->name }}" required maxlength="255" aria-label="Administrator name" class="min-w-0 rounded-md border border-line bg-surface-raised px-2.5 py-2 text-xs text-ink">
                            <label class="sr-only" for="username-{{ $admin->id }}">Username</label><input id="username-{{ $admin->id }}" name="username" value="{{ $admin->username }}" required maxlength="40" aria-label="Username" class="min-w-0 rounded-md border border-line bg-surface-raised px-2.5 py-2 text-xs text-ink">
                            <label class="sr-only" for="email-{{ $admin->id }}">Email address</label><input id="email-{{ $admin->id }}" type="email" name="email" value="{{ $admin->email }}" required maxlength="255" aria-label="Email address" class="min-w-0 rounded-md border border-line bg-surface-raised px-2.5 py-2 text-xs text-ink">
                            <label class="sr-only" for="role-{{ $admin->id }}">Administrator role</label><select id="role-{{ $admin->id }}" name="role_id" required aria-label="Administrator role" class="min-w-0 rounded-md border border-line bg-surface-raised px-2.5 py-2 text-xs text-ink">@foreach ($roles as $role)<option value="{{ $role->id }}" @selected($adminRole?->id === $role->id)>{{ $role->name }}</option>@endforeach</select>
                            <button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-md border border-line px-3 py-2 text-xs font-medium text-muted transition hover:text-ink">Save</button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.destroy', $admin) }}" onsubmit="return confirm('Delete this administrator account?')" class="sm:self-center">@csrf @method('DELETE')<button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-md border border-red-200 px-3 py-2 text-xs font-medium text-red-700 transition hover:bg-red-50">Delete</button></form>
                    @else
                        <div class="min-w-0 flex-1 text-xs text-muted"><p>{{ $admin->username }} · {{ $admin->email }}</p><p class="mt-2">Your administrator account cannot be changed or deleted here.</p></div>
                    @endif
                </div>
            </article>
        @empty
            <p class="rounded-md border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No administrator accounts match these filters.</p>
        @endforelse
    </section>
    <div class="mt-4">{{ $admins->links() }}</div>
</div>
@endsection
