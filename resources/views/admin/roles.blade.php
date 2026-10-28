@extends('layouts.app')

@section('title', 'Role definitions')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-7">
    <header class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-xl font-semibold">Role Definitions</h1><p class="mt-1 text-xs text-muted">{{ $roleCount }} roles</p></div>
        <div class="flex flex-wrap gap-2"><a href="{{ route('admin.users.index') }}" class="inline-flex min-h-9 items-center rounded-md border border-line bg-surface px-3 py-2 text-xs font-medium text-muted transition hover:text-ink">Administrator Accounts</a>
            <a href="{{ route('admin.roles.create') }}" class="inline-flex min-h-9 items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark"><span aria-hidden="true">+</span>Add Role</a>
        </div>
    </header>

    <form method="GET" action="{{ route('admin.roles.index') }}" class="mb-4 flex flex-wrap items-center gap-2 rounded-md border border-line bg-surface p-3 sm:p-4">
        <label class="relative block w-full sm:max-w-72"><span class="sr-only">Search roles</span><svg aria-hidden="true" class="absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="m16 16 4 4" /></svg><input type="search" name="search" value="{{ $search }}" placeholder="Search role name or type..." class="w-full rounded-md border border-line bg-surface-raised py-2 pl-9 pr-3 text-xs text-ink placeholder:text-muted focus:border-forest focus:outline-none"></label>
        <button type="submit" class="inline-flex min-h-9 items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark">Filter</button><a href="{{ route('admin.roles.index') }}" class="inline-flex min-h-9 items-center rounded-md border border-line bg-surface-raised px-4 py-2 text-xs font-medium text-muted transition hover:text-ink">Reset</a>
    </form>

    <section class="grid gap-3">
        @forelse ($roles as $role)
            <article class="rounded-md border border-line bg-surface">
                <div class="flex flex-col justify-between gap-3 p-4 sm:flex-row sm:items-center sm:px-5">
                    <div class="flex min-w-0 items-center gap-3"><span class="grid size-9 shrink-0 place-items-center rounded-full bg-forest/15 text-xs font-bold text-forest">{{ strtoupper(substr($role->name, 0, 1)) }}</span><div class="min-w-0"><h2 class="truncate text-sm font-semibold"><button type="button" data-role-edit-open data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}" data-role-type="{{ $role->type }}" data-role-description="{{ $role->description }}" data-role-permissions="{{ implode(',', $role->permissions ?? []) }}" data-role-system="{{ $role->is_system ? 'true' : 'false' }}" data-update-url="{{ route('admin.roles.update', $role) }}" data-delete-url="{{ route('admin.roles.destroy', $role) }}" aria-haspopup="dialog" aria-controls="role-edit-dialog" class="text-left underline decoration-transparent underline-offset-4 transition hover:text-forest hover:decoration-current focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest">{{ $role->name }}</button></h2><p class="mt-1 text-[10px] text-muted">{{ $role->type }} · {{ $role->users_count }} assigned administrators</p></div>@if ($role->is_system)<span class="rounded-full bg-warning/15 px-2.5 py-1 text-[10px] font-semibold text-warning">Protected</span>@endif</div>
                </div>
            </article>
        @empty
            <p class="rounded-md border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No roles match this search.</p>
        @endforelse
    </section>
    <div class="mt-4">{{ $roles->links() }}</div>
    <dialog id="role-edit-dialog" data-reopen-for="{{ old('role_id') }}" aria-labelledby="role-edit-title" class="m-auto max-h-[calc(100vh-2rem)] w-[calc(100%-2rem)] max-w-xl overflow-y-auto rounded-md border border-line bg-surface p-0 text-ink shadow-2xl backdrop:bg-black/45">
        <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
            <div><p class="eyebrow">Role definitions</p><h2 id="role-edit-title" class="mt-1 text-lg font-semibold">Edit role</h2></div>
            <button type="button" data-role-edit-close aria-label="Close edit dialog" title="Close" class="grid size-9 shrink-0 place-items-center rounded-md border border-line text-muted transition hover:bg-paper hover:text-ink"><svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m18 6-12 12M6 6l12 12" /></svg></button>
        </div>
        @if ($errors->any())<p class="mx-5 mt-4 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-xs text-red-800" role="alert">{{ $errors->first() }}</p>@endif
        <form method="POST" action="{{ route('admin.roles.index') }}" data-role-edit-form class="grid gap-4 px-5 py-5">@csrf @method('PATCH')
            <input type="hidden" name="role_id" value="{{ old('role_id') }}">
            <label class="grid gap-1.5 text-xs font-medium text-muted">Role name<input name="name" maxlength="120" value="{{ old('name') }}" required class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"></label>
            <label class="grid gap-1.5 text-xs font-medium text-muted">Description<textarea name="description" rows="3" maxlength="1000" class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink">{{ old('description') }}</textarea></label>
            <div data-role-custom-fields class="grid gap-4">
                <label class="grid gap-1.5 text-xs font-medium text-muted">Role type<input name="type" maxlength="60" pattern="[A-Za-z0-9_-]+" value="{{ old('type') }}" required class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"></label>
                <fieldset class="grid gap-2"><legend class="text-sm font-semibold">Module permissions</legend>@foreach ($permissions as $permission => $label)<label class="flex items-start gap-2 py-1 text-xs"><input class="mt-1" type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, old('permissions', []), true))><span>{{ $label }}</span></label>@endforeach</fieldset>
            </div>
            <p data-role-system-note hidden class="rounded-md bg-surface-raised p-3 text-xs leading-5 text-muted">This protected system role has access to all modules and role-management controls. Its type and permissions cannot be changed.</p>
            <div class="flex justify-end gap-2 border-t border-line pt-4"><button type="button" data-role-edit-close class="inline-flex min-h-9 items-center justify-center rounded-md border border-line px-3 py-2 text-xs font-medium text-muted transition hover:text-ink">Cancel</button><button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark">Save role</button></div>
        </form>
        <form method="POST" action="{{ route('admin.roles.index') }}" data-role-delete-form onsubmit="return confirm('Delete this role?')" class="border-t border-line px-5 py-3">@csrf @method('DELETE')<button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-md border border-red-300 px-3 py-2 text-xs font-medium text-red-700 transition hover:bg-red-50">Delete role</button></form>
    </dialog>
</div>
@endsection
