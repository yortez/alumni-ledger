<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#000000">
    <title>Create account · Alumni Tracer Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-canvas text-ink">
    <header class="bg-black">
        <div class="mx-auto flex h-14 max-w-[1440px] items-center gap-3 px-5 sm:px-8">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3" aria-label="Alumni Tracer Management System home">
                <span class="grid size-7 shrink-0 place-items-center rounded-[6px] bg-white text-micro font-semibold text-black">AT</span>
                <span class="truncate text-meta text-white"><span class="sm:hidden">Alumni Tracer</span><span class="hidden sm:inline">Alumni Tracer Management System</span></span>
            </a>
            <a href="{{ route('home') }}" class="ml-auto hidden shrink-0 rounded-full bg-white px-4 py-1.5 text-meta font-semibold text-black transition hover:bg-chip-gray sm:inline-flex">Sign in</a>
        </div>
    </header>

    <main class="grid flex-1 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]">
        <section class="flex items-center border-b border-hairline px-5 py-14 sm:px-8 lg:border-b-0 lg:border-r lg:px-16" aria-labelledby="registration-title">
            <div class="w-full max-w-xl">
                <x-back-link :href="route('home')" label="Sign in" class="mb-5" />
                <h1 id="registration-title" class="text-display font-semibold">Create your alumni account</h1>
                <p class="mt-5 max-w-lg text-body text-ink-secondary">
                    Your student number is matched against the graduate master list before the account is activated.
                </p>

                <dl class="mt-10 grid gap-x-10 gap-y-8 border-t border-hairline pt-8 sm:grid-cols-2">
                    <div>
                        <dt class="text-caption font-semibold text-ink">Verified against records</dt>
                        <dd class="mt-2 text-meta leading-5 text-ink-tertiary">The student number on this form must exist in the graduate master list.</dd>
                    </div>
                    <div>
                        <dt class="text-caption font-semibold text-ink">Kept to your record</dt>
                        <dd class="mt-2 text-meta leading-5 text-ink-tertiary">Details are used to verify your record and to build your alumni profile.</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="flex items-start bg-surface px-5 py-14 sm:px-10 lg:px-12" aria-labelledby="create-account-heading">
            <div class="mx-auto w-full max-w-sm">
                <h2 id="create-account-heading" class="text-headline font-semibold">Register</h2>
                <p class="mt-2 text-caption text-ink-tertiary">All fields are required.</p>

                <form method="POST" action="{{ route('register.store') }}" class="mt-8 grid gap-4">
                    @csrf
                    <div class="field">
                        <label for="student_number">Student number</label>
                        <input id="student_number" name="student_number" value="{{ old('student_number') }}" placeholder="2018-00001" autocomplete="off" required autofocus @error('student_number') aria-describedby="student-number-hint student-number-error" @else aria-describedby="student-number-hint" @enderror>
                        <p id="student-number-hint" class="text-micro text-ink-tertiary">Must match your official graduate record.</p>
                        @error('student_number')<p id="student-number-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="username">Username</label>
                        <input id="username" name="username" value="{{ old('username') }}" autocomplete="username" required @error('username') aria-describedby="username-error" @enderror>
                        @error('username')<p id="username-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="email">Email address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.edu" autocomplete="email" required @error('email') aria-describedby="email-error" @enderror>
                        @error('email')<p id="email-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" autocomplete="new-password" required @error('password') aria-describedby="password-error" @enderror>
                        @error('password')<p id="password-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirm password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                    </div>

                    <p class="rounded-chip bg-canvas px-4 py-3 text-micro leading-5 text-ink-tertiary">
                        By registering, you consent to the collection and processing of your personal data in accordance with the Data Privacy Act (RA 10173).
                    </p>

                    <button type="submit" class="flex h-11 w-full items-center justify-center rounded-full bg-accent text-caption font-semibold text-white transition hover:bg-accent-strong">Create account</button>
                </form>

                <p class="mt-6 text-caption text-ink-tertiary">Already have an account? <a href="{{ route('home') }}" class="font-semibold text-accent transition hover:text-accent-strong">Sign in</a></p>
            </div>
        </section>
    </main>
</body>
</html>
