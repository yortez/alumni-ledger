@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
<div class="mx-auto grid max-w-7xl gap-12 px-5 py-10 sm:px-8 lg:grid-cols-[0.8fr_1.2fr] lg:py-16">
    <section class="max-w-md"><p class="eyebrow">Welcome back</p><h1 class="font-display mt-5 text-5xl leading-tight">Your community is here.</h1><p class="mt-5 leading-7 text-muted">Sign in to continue to your alumni profile.</p><p class="mt-8 text-sm text-muted">New to the network? <a href="{{ route('register') }}" class="font-semibold text-forest underline underline-offset-4">Create an account</a></p></section>
    <form method="POST" action="{{ route('login.store') }}" class="form-surface mx-auto w-full max-w-2xl">
        @csrf
        <div class="grid gap-5">
            <div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="current-password" required>@error('password')<p class="field-error">{{ $message }}</p>@enderror</div>
        </div>
        <button class="mt-7 flex w-full items-center justify-between bg-forest px-5 py-4 text-left text-sm font-semibold text-white transition hover:bg-forest-dark" type="submit"><span>Sign in</span><span aria-hidden="true">&#8599;</span></button>
    </form>
</div>
@endsection
