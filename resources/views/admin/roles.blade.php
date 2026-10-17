@extends('layouts.app')

@section('title', 'Role definitions')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-7">
    <header class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-xl font-semibold">Role Definitions</h1><p class="mt-1 text-xs text-muted">{{ $roleCount }} roles</p></div>
        <div class="flex flex-wrap gap-2"><a href="{{ route('admin.users.index') }}" class="inline-flex min-h-9 items-center rounded-md border border-line bg-surface px-3 py-2 text-xs font-medium text-muted transition hover:text-ink">Administrator Accounts</a>
            <details class="relative">
                <summary class="inline-flex min-h-9 cursor-pointer list-none items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark"><span aria-hidden="true">+</span>Add Role</summary>
                <div class="absolute right-0 z-20 mt-2 max-h-[82vh] w-[min(92vw,34rem)] overflow-y-auto rounded-md border border-line bg-surface p-5 shadow-xl">
                    <h2 class="text-sm font-semibold">Create a role</h2>
                    <form method="POST" action="{{ route('admin.roles.store') }}" class="mt-4 grid gap-4">@csrf
                        <div class="field"><label for="name">Role name</label><input id="name" name="name" maxlength="120" value="{{ old('name') }}" required>@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="field"><label for="type">Role type</label><input id="type" name="type" maxlength="60" pattern="[A-Za-z0-9_-]+" value="{{ old('type') }}" required><p class="mt-1 text-xs text-muted">Use letters, numbers, hyphens, or underscores.</p>@error('type')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="field"><label for="description">Description <span class="font-normal text-muted">Optional</span></label><textarea id="description" name="description" rows="3" maxlength="1000">{{ old('description') }}</textarea>@error('description')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <fieldset class="grid gap-2"><legend class="text-sm font-semibold">Module permissions</legend>@foreach ($permissions as $permission => $label)<label class="flex items-start gap-2 py-1 text-sm"><input class="mt-1" type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, old('permissions', [])))><span>{{ $label }}</span></label>@endforeach @error('permissions')<p class="field-error">{{ $message }}</p>@enderror @error('permissions.*')<p class="field-error">{{ $message }}</p>@enderror</fieldset>
                        <button type="submit" class="bg-forest px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-forest-dark">Create role</button>
                    </form>
                </div>
            </details>
        </div>
    </header>

    <form method="GET" action="{{ route('admin.roles.index') }}" class="mb-4 flex flex-wrap items-center gap-2 rounded-md border border-line bg-surface p-3 sm:p-4">
        <label class="relative block w-full sm:max-w-72"><span class="sr-only">Search roles</span><svg aria-hidden="true" class="absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="m16 16 4 4" /></svg><input type="search" name="search" value="{{ $search }}" placeholder="Search role name or type..." class="w-full rounded-md border border-line bg-surface-raised py-2 pl-9 pr-3 text-xs text-ink placeholder:text-muted focus:border-forest focus:outline-none"></label>
        <button type="submit" class="inline-flex min-h-9 items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark">Filter</button><a href="{{ route('admin.roles.index') }}" class="inline-flex min-h-9 items-center rounded-md border border-line bg-surface-raised px-4 py-2 text-xs font-medium text-muted transition hover:text-ink">Reset</a>
    </form>

    <section class="grid gap-3">
        @forelse ($roles as $role)
            <article class="overflow-hidden rounded-md border border-line bg-surface">
                <div class="flex flex-col justify-between gap-3 p-4 sm:flex-row sm:items-center sm:px-5">
                    <div class="flex min-w-0 items-center gap-3"><span class="grid size-9 shrink-0 place-items-center rounded-full bg-forest/15 text-xs font-bold text-forest">{{ strtoupper(substr($role->name, 0, 1)) }}</span><div class="min-w-0"><h2 class="truncate text-sm font-semibold">{{ $role->name }}</h2><p class="mt-1 text-[10px] text-muted">{{ $role->type }} · {{ $role->users_count }} assigned administrators</p></div>@if ($role->is_system)<span class="rounded-full bg-warning/15 px-2.5 py-1 text-[10px] font-semibold text-warning">Protected</span>@endif</div>
                    <div class="flex shrink-0 items-center gap-2"><details class="relative"><summary class="inline-flex min-h-8 cursor-pointer list-none items-center gap-2 rounded-md border border-line px-3 py-2 text-[10px] font-medium text-muted hover:text-ink"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m16 4 4 4L9 19l-5 1 1-5L16 4zM14 6l4 4" /></svg>Edit</summary>
                        <div class="absolute right-0 z-20 mt-2 max-h-[75vh] w-[min(92vw,32rem)] overflow-y-auto rounded-md border border-line bg-surface p-4 shadow-xl">
                            <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="grid gap-4">@csrf @method('PATCH')
                                <div class="field"><label for="name-{{ $role->id }}">Role name</label><input id="name-{{ $role->id }}" name="name" maxlength="120" value="{{ old('name', $role->name) }}" required>@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
                                @unless ($role->is_system)<div class="field"><label for="type-{{ $role->id }}">Role type</label><input id="type-{{ $role->id }}" name="type" maxlength="60" pattern="[A-Za-z0-9_-]+" value="{{ old('type', $role->type) }}" required>@error('type')<p class="field-error">{{ $message }}</p>@enderror</div>@endunless
                                <div class="field"><label for="description-{{ $role->id }}">Description</label><textarea id="description-{{ $role->id }}" name="description" rows="2" maxlength="1000">{{ old('description', $role->description) }}</textarea>@error('description')<p class="field-error">{{ $message }}</p>@enderror</div>
                                @if ($role->is_system)<p class="text-xs leading-5 text-muted">This protected system role has access to all modules and role-management controls.</p>@else<fieldset class="grid gap-2"><legend class="text-sm font-semibold">Module permissions</legend>@foreach ($permissions as $permission => $label)<label class="flex items-start gap-2 py-1 text-xs"><input class="mt-1" type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, $role->permissions ?? []))><span>{{ $label }}</span></label>@endforeach @error('permissions')<p class="field-error">{{ $message }}</p>@enderror @error('permissions.*')<p class="field-error">{{ $message }}</p>@enderror</fieldset>@endif
                                <button type="submit" class="bg-forest px-4 py-2.5 text-xs font-semibold text-white hover:bg-forest-dark">Save role</button>
                            </form>
                            @unless ($role->is_system)<form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="mt-3">@csrf @method('DELETE')<button type="submit" class="text-xs font-medium text-danger underline underline-offset-4">Delete role</button></form>@endunless
                            @error('role')<p class="field-error mt-3">{{ $message }}</p>@enderror
                        </div>
                    </details></div>
                </div>
            </article>
        @empty
            <p class="rounded-md border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No roles match this search.</p>
        @endforelse
    </section>
    <div class="mt-4">{{ $roles->links() }}</div>
</div>
@endsection
