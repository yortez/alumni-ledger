<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#141a32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Alumni Tracer Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#1e293b] text-white antialiased">
    <main class="grid min-h-screen md:grid-cols-[minmax(0,1.68fr)_minmax(280px,1fr)]">
        <section class="flex items-center justify-center bg-[radial-gradient(ellipse_at_50%_20%,#202849_0%,#171d37_48%,#141a32_100%)] px-5 py-10 sm:px-8 sm:py-12 md:min-h-screen" aria-labelledby="product-title">
            <div class="w-full max-w-80">
                <header class="flex flex-col items-center text-center">
                    <span class="grid size-13 place-items-center rounded-2xl bg-[#7256ed] text-2xl shadow-lg shadow-violet-950/25" aria-hidden="true">&#127891;</span>
                    <h1 id="product-title" class="mt-5 text-xl font-semibold leading-tight tracking-tight text-slate-50">
                        <span class="block">Alumni Tracer</span>
                        <span class="block">Management System</span>
                    </h1>
                    <p class="mt-3 max-w-sm text-xs leading-5 text-[#8795b3]">A centralized platform for tracking, managing, and analyzing graduate outcomes in support of institutional excellence and CHED accreditation.</p>
                </header>

                <section class="mt-5" aria-label="Platform capabilities">
                    <ul class="grid gap-3">
                        <li class="flex min-h-[74px] items-center gap-3 rounded-[10px] border border-[#343c58] bg-[#202741]/90 px-3.5 py-3">
                            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-indigo-500/20 text-base" aria-hidden="true">&#128202;</span>
                            <div class="min-w-0">
                                <h2 class="text-xs font-semibold text-slate-200">Real-time Analytics</h2>
                                <p class="mt-0.5 text-[11px] leading-4 text-[#8795b3]">Employment rates, salary trends, and program relevance at a glance</p>
                            </div>
                        </li>
                        <li class="flex min-h-[74px] items-center gap-3 rounded-[10px] border border-[#343c58] bg-[#202741]/90 px-3.5 py-3">
                            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-emerald-500/15 text-base" aria-hidden="true">&#128203;</span>
                            <div class="min-w-0">
                                <h2 class="text-xs font-semibold text-slate-200">CHED-aligned Surveys</h2>
                                <p class="mt-0.5 text-[11px] leading-4 text-[#8795b3]">Digitized tracer study instruments with automated distribution</p>
                            </div>
                        </li>
                        <li class="flex min-h-[74px] items-center gap-3 rounded-[10px] border border-[#343c58] bg-[#202741]/90 px-3.5 py-3">
                            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-cyan-500/15 text-base" aria-hidden="true">&#127891;</span>
                            <div class="min-w-0">
                                <h2 class="text-xs font-semibold text-slate-200">Alumni Self-Service Portal</h2>
                                <p class="mt-0.5 text-[11px] leading-4 text-[#8795b3]">Graduates update their own profiles, careers, and respond to surveys</p>
                            </div>
                        </li>
                    </ul>
                </section>
            </div>
        </section>

        <section class="flex min-h-[440px] items-center justify-center border-t border-white/10 bg-[#1e293b] px-6 py-12 sm:px-10 md:min-h-screen md:border-l md:border-t-0" aria-labelledby="sign-in-heading">
            <div class="w-full max-w-80">
                <header class="mb-8">
                    <h2 id="sign-in-heading" class="text-xl font-semibold tracking-tight text-slate-50">Welcome back <span aria-hidden="true">&#128075;</span></h2>
                    <p class="mt-2 text-xs text-slate-400">Sign in to your account to continue</p>
                </header>

                <form method="POST" action="{{ route('login.store') }}" class="grid gap-5">
                    @csrf
                    <div class="grid gap-2">
                        <label for="email" class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Email address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.edu" autocomplete="email" required autofocus @error('email') aria-describedby="email-error" @enderror class="min-h-10 w-full rounded-md border border-slate-300 bg-slate-100 px-3 text-xs text-slate-900 placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400">
                        @error('email')<p id="email-error" class="text-xs text-rose-300" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-2">
                        <label for="password" class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Password</label>
                        <input id="password" type="password" name="password" autocomplete="current-password" required @error('password') aria-describedby="password-error" @enderror class="min-h-10 w-full rounded-md border border-slate-300 bg-slate-100 px-3 text-xs text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400">
                        @error('password')<p id="password-error" class="text-xs text-rose-300" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="mt-1 min-h-10 w-full rounded-md bg-gradient-to-r from-indigo-500 to-violet-500 px-4 text-xs font-semibold text-white shadow-md shadow-indigo-950/20 transition hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-300 focus-visible:ring-offset-2 focus-visible:ring-offset-[#1e293b]">Sign in</button>
                </form>

                <p class="mt-6 text-center text-[11px] text-slate-400">Are you an alumnus? <a href="{{ route('register') }}" class="font-semibold text-violet-300 underline-offset-4 hover:text-white hover:underline">Create an account</a></p>
            </div>
        </section>
    </main>
</body>
</html>