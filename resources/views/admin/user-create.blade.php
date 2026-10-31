@extends('layouts.app')

@section('title', 'Create administrator')

@section('content')
<div class="mx-auto w-full max-w-3xl px-5 pb-16 pt-8 sm:px-8">
    <div class="mb-5 flex items-center justify-between gap-4 border-b border-line pb-5">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="mt-1 text-display font-semibold">Create an administrator</h1>
        </div>
        <a href="{{ route('admin.users.index') }}" class="text-xs font-medium text-forest underline underline-offset-4">Cancel</a>
    </div>

    <section class="panel p-4 sm:p-6">
        <form method="POST" action="{{ route('admin.users.store') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            <div class="field"><label for="name">Full name</label><input id="name" name="name" value="{{ old('name') }}" required>@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="username">Username</label><input id="username" name="username" value="{{ old('username') }}" required>@error('username')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field sm:col-span-2"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required>@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="password">Temporary password</label><input id="password" name="password" type="password" required>@error('password')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" required></div>
            <div class="field sm:col-span-2"><label for="role_id">Role</label><select id="role_id" name="role_id" required>@foreach ($roles as $role)<option value="{{ $role->id }}" @selected((int) old('role_id', $roles->firstWhere('type', 'content_admin')?->id) === $role->id)>{{ $role->name }}</option>@endforeach</select>@error('role_id')<p class="field-error">{{ $message }}</p>@enderror</div>
            <button type="submit" class="bg-forest px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-forest-dark sm:col-span-2">Create administrator</button>
        </form>
    </section>
</div>
@endsection
