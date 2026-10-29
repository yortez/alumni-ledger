@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
<div class="mx-auto grid w-full max-w-[1440px] items-start gap-12 px-5 py-12 sm:px-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:py-16 xl:px-20">
    <section class="max-w-md">
        <h1 class="text-display font-semibold">Sign in to your record</h1>
        <p class="mt-4 text-body text-ink-secondary">Alumni keep their own career details current. Staff manage graduate records, surveys, announcements, and job postings.</p>
        <p class="mt-8 text-caption text-ink-tertiary">New to the network? <a href="{{ route('register') }}" class="font-semibold text-accent transition hover:text-accent-strong">Create an account</a></p>
    </section>

    <form method="POST" action="{{ route('login.store') }}" class="form-surface w-full max-w-md">
        @csrf
        <div class="grid gap-5">
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                @error('email')<p class="field-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" autocomplete="current-password" required>
                @error('password')<p class="field-error" role="alert">{{ $message }}</p>@enderror
            </div>
        </div>
        <button class="mt-7 flex h-11 w-full items-center justify-center rounded-full bg-accent text-caption font-semibold text-white transition hover:bg-accent-strong" type="submit">Sign in</button>
    </form>
</div>
@endsection
