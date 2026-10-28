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
                    <div class="flex min-w-0 items-center gap-3 sm:w-48 sm:shrink-0"><span class="grid size-9 shrink-0 place-items-center rounded-full bg-forest text-xs font-bold text-white">{{ strtoupper(substr($admin->name, 0, 1)) }}</span><div class="min-w-0">
                        @if ($admin->id !== auth()->id())
                            <h2 class="truncate text-sm font-semibold"><button type="button" data-admin-edit-open data-admin-id="{{ $admin->id }}" data-admin-name="{{ $admin->name }}" data-admin-username="{{ $admin->username }}" data-admin-email="{{ $admin->email }}" data-admin-role="{{ $adminRole?->id }}" data-update-url="{{ route('admin.users.update', $admin) }}" data-delete-url="{{ route('admin.users.destroy', $admin) }}" aria-haspopup="dialog" aria-controls="admin-edit-dialog" class="text-left text-ink underline decoration-transparent underline-offset-4 transition hover:text-forest hover:decoration-current focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest">{{ $admin->name }}</button></h2>
                        @else
                            <h2 class="truncate text-sm font-semibold">{{ $admin->name }}</h2>
                        @endif
                        <p class="mt-1 text-[10px] text-muted">{{ $adminRole?->description ?? 'No module access is assigned.' }}</p>
                    </div></div>
                    @if ($admin->id !== auth()->id())
                        <div class="min-w-0 flex-1 text-xs text-muted"><p>{{ $admin->username }} · {{ $admin->email }}</p><p class="mt-2">Select the administrator name to edit this account.</p></div>
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

    <dialog id="admin-edit-dialog" data-reopen-for="{{ old('admin_id') }}" aria-labelledby="admin-edit-title" class="m-auto w-[calc(100%-2rem)] max-w-xl rounded-md border border-line bg-surface p-0 text-ink shadow-2xl backdrop:bg-black/45">
        <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
            <div><p class="eyebrow">Administrator access</p><h2 id="admin-edit-title" class="mt-1 text-lg font-semibold">Edit administrator</h2></div>
            <button type="button" data-admin-edit-close aria-label="Close edit dialog" title="Close" class="grid size-9 shrink-0 place-items-center rounded-md border border-line text-muted transition hover:bg-paper hover:text-ink"><svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m18 6-12 12M6 6l12 12" /></svg></button>
        </div>
        <form method="POST" action="{{ route('admin.users.index') }}" data-admin-edit-form class="grid gap-4 px-5 py-5 sm:grid-cols-2">@csrf @method('PATCH')
            <input type="hidden" name="admin_id" value="{{ old('admin_id') }}">
            <label class="grid gap-1.5 text-xs font-medium text-muted">Administrator name<input name="name" value="{{ old('name') }}" required maxlength="255" class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"></label>
            <label class="grid gap-1.5 text-xs font-medium text-muted">Username<input name="username" value="{{ old('username') }}" required maxlength="40" class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"></label>
            <label class="grid gap-1.5 text-xs font-medium text-muted">Email address<input type="email" name="email" value="{{ old('email') }}" required maxlength="255" class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"></label>
            <label class="grid gap-1.5 text-xs font-medium text-muted">Administrator role<select name="role_id" required class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink">@foreach ($roles as $role)<option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>{{ $role->name }}</option>@endforeach</select></label>
            <div class="flex justify-end gap-2 border-t border-line pt-4 sm:col-span-2"><button type="button" data-admin-edit-close class="inline-flex min-h-9 items-center justify-center rounded-md border border-line px-3 py-2 text-xs font-medium text-muted transition hover:text-ink">Cancel</button><button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark">Save changes</button></div>
        </form>
        <form method="POST" action="{{ route('admin.users.index') }}" data-admin-delete-form onsubmit="return confirm('Delete this administrator account?')" class="border-t border-line px-5 py-3">@csrf @method('DELETE')<button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-md border border-red-300 px-3 py-2 text-xs font-medium text-red-700 transition hover:bg-red-50">Delete</button></form>
    </dialog>
</div>
@endsection
