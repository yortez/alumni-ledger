@extends('layouts.app')

@section('title', 'Role definitions')

@section('content')
<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="flex flex-col justify-between gap-6 border-b border-line pb-8 sm:flex-row sm:items-end">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="font-display mt-3 text-4xl sm:text-5xl">Role definitions</h1>
        </div>
        <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-forest underline underline-offset-4">Administrator accounts</a>
    </div>

    <div class="mt-8 grid items-start gap-8 lg:grid-cols-[0.75fr_1.25fr]">
        <section class="border border-line bg-white p-5 sm:p-6">
            <p class="eyebrow">New role</p>
            <h2 class="font-display mt-2 text-2xl">Create a role</h2>
            <form method="POST" action="{{ route('admin.roles.store') }}" class="mt-5 grid gap-5">
                @csrf
                <div class="field">
                    <label for="name">Role name</label>
                    <input id="name" name="name" maxlength="120" value="{{ old('name') }}" required>
                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="type">Role type</label>
                    <input id="type" name="type" maxlength="60" pattern="[A-Za-z0-9_-]+" value="{{ old('type') }}" required>
                    <p class="mt-1 text-xs text-muted">Use letters, numbers, hyphens, or underscores.</p>
                    @error('type')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="description">Description <span class="font-normal text-muted">Optional</span></label>
                    <textarea id="description" name="description" rows="3" maxlength="1000">{{ old('description') }}</textarea>
                    @error('description')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <fieldset class="grid gap-2">
                    <legend class="text-sm font-semibold">Module permissions</legend>
                    @foreach ($permissions as $permission => $label)
                        <label class="flex items-start gap-2 py-1 text-sm">
                            <input class="mt-1 accent-[#245b49]" type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, old('permissions', [])))>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                    @error('permissions')<p class="field-error">{{ $message }}</p>@enderror
                    @error('permissions.*')<p class="field-error">{{ $message }}</p>@enderror
                </fieldset>
                <button type="submit" class="bg-forest px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-forest-dark">Create role</button>
            </form>
        </section>

        <section class="min-w-0">
            <div class="mb-4 flex items-end justify-between gap-4">
                <h2 class="font-display text-2xl">Existing roles</h2>
                <span class="text-xs uppercase tracking-wider text-muted">{{ $roles->total() }} roles</span>
            </div>
            <div class="grid gap-3">
                @forelse ($roles as $role)
                    <article class="border border-line bg-white p-5 sm:p-6">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="font-display text-xl">{{ $role->name }}</h3>
                                <p class="mt-1 text-xs text-muted">{{ $role->type }} · {{ $role->users_count }} assigned administrators{{ $role->is_system ? ' · System role' : '' }}</p>
                            </div>
                            @if ($role->is_system)<span class="text-xs font-semibold uppercase tracking-wider text-forest">Protected</span>@endif
                        </div>
                        <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="grid gap-4 border-t border-line pt-4">
                            @csrf
                            @method('PATCH')
                            <div class="field">
                                <label for="name-{{ $role->id }}">Role name</label>
                                <input id="name-{{ $role->id }}" name="name" maxlength="120" value="{{ old('name', $role->name) }}" required>
                                @error('name')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            @unless ($role->is_system)
                                <div class="field">
                                    <label for="type-{{ $role->id }}">Role type</label>
                                    <input id="type-{{ $role->id }}" name="type" maxlength="60" pattern="[A-Za-z0-9_-]+" value="{{ old('type', $role->type) }}" required>
                                    @error('type')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                            @endunless
                            <div class="field">
                                <label for="description-{{ $role->id }}">Description</label>
                                <textarea id="description-{{ $role->id }}" name="description" rows="2" maxlength="1000">{{ old('description', $role->description) }}</textarea>
                                @error('description')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            @if ($role->is_system)
                                <p class="text-sm leading-6 text-muted">This protected system role has access to all modules and role-management controls.</p>
                            @else
                                <fieldset class="grid gap-2">
                                    <legend class="text-sm font-semibold">Module permissions</legend>
                                    @foreach ($permissions as $permission => $label)
                                        <label class="flex items-start gap-2 py-1 text-sm">
                                            <input class="mt-1 accent-[#245b49]" type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, $role->permissions ?? []))>
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                    @error('permissions')<p class="field-error">{{ $message }}</p>@enderror
                                    @error('permissions.*')<p class="field-error">{{ $message }}</p>@enderror
                                </fieldset>
                            @endif
                            <div class="flex flex-wrap items-center gap-4">
                                <button type="submit" class="border border-forest px-4 py-3 text-sm font-semibold text-forest transition hover:bg-mint">Save role</button>
                            </div>
                        </form>
                        @unless ($role->is_system)
                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="mt-3">@csrf @method('DELETE')<button type="submit" class="text-sm font-medium text-terracotta underline underline-offset-4">Delete role</button></form>
                        @endunless
                        @error('role')<p class="field-error mt-3">{{ $message }}</p>@enderror
                    </article>
                @empty
                    <p class="border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No administrator roles configured.</p>
                @endforelse
            </div>
            <div class="mt-5">{{ $roles->links() }}</div>
        </section>
    </div>
</div>
@endsection
