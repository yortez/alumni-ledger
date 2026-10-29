<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#000000">
    <title>Alumni Tracer Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-canvas text-ink">
    <header class="bg-black">
        <div class="mx-auto flex h-14 max-w-[1440px] items-center gap-3 px-5 sm:px-8">
            <span class="grid size-7 shrink-0 place-items-center rounded-[6px] bg-white text-micro font-semibold text-black">AT</span>
            <span class="truncate text-meta text-white"><span class="sm:hidden">Alumni Tracer</span><span class="hidden sm:inline">Alumni Tracer Management System</span></span>
            <a href="{{ route('register') }}" class="ml-auto hidden shrink-0 rounded-full bg-white px-4 py-1.5 text-meta font-semibold text-black transition hover:bg-chip-gray sm:inline-flex">Create an account</a>
        </div>
    </header>

    <main class="grid flex-1 lg:grid-cols-[minmax(0,1.45fr)_minmax(0,1fr)]">
        <section class="flex items-center border-b border-hairline px-5 py-14 sm:px-8 lg:border-b-0 lg:border-r lg:px-16" aria-labelledby="product-title">
            <div class="w-full max-w-2xl">
                <h1 id="product-title" class="text-display font-semibold text-balance">
                    Alumni Tracer<br>Management System
                </h1>
                <p class="mt-5 max-w-xl text-body text-ink-secondary">
                    A centralized platform for tracking, managing, and analyzing graduate outcomes in support of institutional excellence and CHED accreditation.
                </p>

                <dl class="mt-12 grid gap-x-10 gap-y-8 border-t border-hairline pt-8 sm:grid-cols-3">
                    <div>
                        <dt class="text-caption font-semibold text-ink">Program outcomes</dt>
                        <dd class="mt-2 text-meta leading-5 text-ink-tertiary">Employment status, salary range, and program relevance per graduation year.</dd>
                    </div>
                    <div>
                        <dt class="text-caption font-semibold text-ink">Tracer surveys</dt>
                        <dd class="mt-2 text-meta leading-5 text-ink-tertiary">Instruments targeted by program and batch, distributed to the right graduates.</dd>
                    </div>
                    <div>
                        <dt class="text-caption font-semibold text-ink">Graduate records</dt>
                        <dd class="mt-2 text-meta leading-5 text-ink-tertiary">Import from the master list, then graduates maintain their own career details.</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="flex items-center bg-surface px-5 py-14 sm:px-10 lg:px-12" aria-labelledby="sign-in-heading">
            <div class="mx-auto w-full max-w-sm">
                <h2 id="sign-in-heading" class="text-headline font-semibold">Sign in</h2>
                <p class="mt-2 text-caption text-ink-tertiary">Use the account issued by the alumni office.</p>

                <form method="POST" action="{{ route('login.store') }}" class="mt-8 grid gap-5">
                    @csrf
                    <div class="field">
                        <label for="email">Email address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.edu" autocomplete="email" required autofocus @error('email') aria-describedby="email-error" @enderror>
                        @error('email')<p id="email-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" autocomplete="current-password" required @error('password') aria-describedby="password-error" @enderror>
                        @error('password')<p id="password-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="mt-1 flex h-11 w-full items-center justify-center rounded-full bg-accent text-caption font-semibold text-white transition hover:bg-accent-strong">Sign in</button>
                </form>

                <p class="mt-6 text-caption text-ink-tertiary">New to the network? <a href="{{ route('register') }}" class="font-semibold text-accent transition hover:text-accent-strong">Create an account</a></p>
            </div>
        </section>
    </main>

    <footer class="border-t border-hairline bg-canvas">
        <div class="mx-auto flex max-w-[1440px] flex-col justify-between gap-1 px-5 py-4 text-micro text-ink-tertiary sm:flex-row sm:gap-4 sm:px-8">
            <span>Alumni Tracer Management System</span>
            <span>Data handled under the Data Privacy Act (RA 10173)</span>
        </div>
    </footer>
</body>
</html>
