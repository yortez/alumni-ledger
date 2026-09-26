@extends('layouts.app')

@section('title', 'Create your account')

@section('content')
<div class="mx-auto grid max-w-7xl gap-12 px-5 py-10 sm:px-8 lg:grid-cols-[0.8fr_1.2fr] lg:py-16">
    <section class="max-w-md"><p class="eyebrow">A new chapter</p><h1 class="font-display mt-5 text-5xl leading-tight">Find your people again.</h1><p class="mt-5 leading-7 text-muted">Create your account with the student number on your graduation record.</p><p class="mt-8 text-sm text-muted">Already have an account? <a href="{{ route('login') }}" class="font-semibold text-forest underline underline-offset-4">Sign in</a></p></section>
    <form method="POST" action="{{ route('register.store') }}" class="form-surface mx-auto w-full max-w-2xl">
        @csrf
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="field sm:col-span-2"><label for="student_number">Student number</label><input id="student_number" name="student_number" value="{{ old('student_number') }}" autocomplete="off" required autofocus>@error('student_number')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="username">Username</label><input id="username" name="username" value="{{ old('username') }}" autocomplete="username" required>@error('username')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="new-password" required>@error('password')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required></div>
        </div>
        <button class="mt-7 flex w-full items-center justify-between bg-forest px-5 py-4 text-left text-sm font-semibold text-white transition hover:bg-forest-dark" type="submit"><span>Create account</span><span aria-hidden="true">&#8599;</span></button>
    </form>
</div>
@endsection
