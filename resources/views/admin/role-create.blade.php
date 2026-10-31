@extends('layouts.app')

@section('title', 'Create role')

@section('content')
<div class="mx-auto w-full max-w-3xl px-5 pb-16 pt-8 sm:px-8">
    <x-back-link :href="route('admin.roles.index')" label="Role definitions" class="mb-5" />
    <div class="mb-5 flex items-center justify-between gap-4 border-b border-line pb-5">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="mt-1 text-display font-semibold">Create a role</h1>
        </div>
    </div>

    <section class="panel p-4 sm:p-6">
        <form method="POST" action="{{ route('admin.roles.store') }}" class="grid gap-4">
            @csrf
            <div class="field"><label for="name">Role name</label><input id="name" name="name" maxlength="120" value="{{ old('name') }}" required>@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="type">Role type</label><input id="type" name="type" maxlength="60" pattern="[A-Za-z0-9_-]+" value="{{ old('type') }}" required><p class="mt-1 text-xs text-muted">Use letters, numbers, hyphens, or underscores.</p>@error('type')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="description">Description <span class="font-normal text-muted">Optional</span></label><textarea id="description" name="description" rows="3" maxlength="1000">{{ old('description') }}</textarea>@error('description')<p class="field-error">{{ $message }}</p>@enderror</div>
            <fieldset class="grid gap-2">
                <legend class="text-sm font-semibold">Module permissions</legend>
                @foreach ($permissions as $permission => $label)
                    <label class="flex items-start gap-2 py-1 text-sm"><input class="mt-1" type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, old('permissions', [])))><span>{{ $label }}</span></label>
                @endforeach
                @error('permissions')<p class="field-error">{{ $message }}</p>@enderror
                @error('permissions.*')<p class="field-error">{{ $message }}</p>@enderror
            </fieldset>
            <button type="submit" class="bg-forest px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-forest-dark">Create role</button>
        </form>
    </section>
</div>
@endsection
