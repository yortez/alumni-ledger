<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#141a32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Create account · Alumni Tracer Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#1e293b] text-white antialiased">
<main class="grid min-h-screen md:grid-cols-[minmax(0,1.5fr)_minmax(310px,1fr)]">
    <section class="flex items-center justify-center bg-[radial-gradient(ellipse_at_50%_20%,#202849_0%,#171d37_48%,#141a32_100%)] px-5 py-12 sm:px-8 md:min-h-screen" aria-labelledby="registration-title">
        <div class="w-full max-w-80">
            <header class="flex flex-col items-center text-center">
                <span class="grid size-13 place-items-center rounded-2xl bg-[#7256ed] text-2xl shadow-lg shadow-violet-950/25" aria-hidden="true">&#127891;</span>
                <h1 id="registration-title" class="mt-5 text-xl font-semibold tracking-tight text-slate-50">Alumni Registration</h1>
                <p class="mt-3 text-xs leading-5 text-[#8795b3]">Create your alumni account to access the tracer portal. Your student number will be verified against our graduate records.</p>
            </header>

            <ul class="mt-7 grid gap-3" aria-label="Registration information">
                <li class="flex items-center gap-3 rounded-[10px] border border-[#343c58] bg-[#202741]/90 px-3.5 py-3">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-indigo-500/20 text-sm" aria-hidden="true">&#128737;&#65039;</span>
                    <div>
                        <h2 class="text-xs font-semibold text-slate-200">Verification Required</h2>
                        <p class="mt-0.5 text-[11px] leading-4 text-[#8795b3]">Your student number must match an official graduate record.</p>
                    </div>
                </li>
                <li class="flex items-center gap-3 rounded-[10px] border border-[#343c58] bg-[#202741]/90 px-3.5 py-3">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-emerald-500/15 text-sm" aria-hidden="true">&#128274;</span>
                    <div>
                        <h2 class="text-xs font-semibold text-slate-200">Secure &amp; Private</h2>
                        <p class="mt-0.5 text-[11px] leading-4 text-[#8795b3]">Registration details are used to verify your record and create your account.</p>
                    </div>
                </li>
            </ul>

            <p class="mt-6 text-center text-xs"><a href="{{ route('home') }}" class="font-medium text-violet-300 transition hover:text-white">&larr; Back to Login</a></p>
        </div>
    </section>

    <section class="flex items-start justify-center border-t border-white/10 bg-[#1e293b] px-5 py-8 sm:px-8 md:min-h-screen md:border-l md:border-t-0 md:px-6 md:py-7" aria-labelledby="create-account-heading">
        <div class="w-full max-w-80">
            <header class="mb-5">
                <h2 id="create-account-heading" class="text-lg font-semibold tracking-tight text-slate-50">Create Account</h2>
                <p class="mt-1 text-[11px] text-slate-400">Fill in your details to register as an alumnus</p>
            </header>

            <form method="POST" action="{{ route('register.store') }}" class="grid gap-3.5">
                @csrf
                <div class="grid gap-1.5">
                    <label for="student_number" class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Student number <span class="text-rose-400">*</span></label>
                    <input id="student_number" name="student_number" value="{{ old('student_number') }}" placeholder="e.g. 2018-00001" autocomplete="off" required autofocus @error('student_number') aria-describedby="student-number-hint student-number-error" @else aria-describedby="student-number-hint" @enderror class="min-h-10 w-full rounded-md border border-slate-700 bg-slate-800 px-3 text-xs text-slate-100 placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400">
                    <p id="student-number-hint" class="text-[10px] leading-4 text-slate-500">Must match your official graduate record.</p>
                    @error('student_number')<p id="student-number-error" class="text-xs text-rose-300" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-1.5">
                    <label for="username" class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Username <span class="text-rose-400">*</span></label>
                    <input id="username" name="username" value="{{ old('username') }}" autocomplete="username" required @error('username') aria-describedby="username-error" @enderror class="min-h-10 w-full rounded-md border border-slate-300 bg-slate-100 px-3 text-xs text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400">
                    @error('username')<p id="username-error" class="text-xs text-rose-300" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-1.5">
                    <label for="email" class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Email address <span class="text-rose-400">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Your active email address" autocomplete="email" required @error('email') aria-describedby="email-error" @enderror class="min-h-10 w-full rounded-md border border-slate-700 bg-slate-800 px-3 text-xs text-slate-100 placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400">
                    @error('email')<p id="email-error" class="text-xs text-rose-300" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-1.5">
                    <label for="password" class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Password <span class="text-rose-400">*</span></label>
                    <input id="password" type="password" name="password" autocomplete="new-password" required @error('password') aria-describedby="password-error" @enderror class="min-h-10 w-full rounded-md border border-slate-300 bg-slate-100 px-3 text-xs text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400">
                    @error('password')<p id="password-error" class="text-xs text-rose-300" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-1.5">
                    <label for="password_confirmation" class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Confirm password <span class="text-rose-400">*</span></label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required class="min-h-10 w-full rounded-md border border-slate-700 bg-slate-800 px-3 text-xs text-slate-100 placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400">
                </div>

                <p class="rounded-md border border-amber-500/25 bg-amber-500/5 px-3 py-2.5 text-[10px] leading-4 text-amber-300">By registering, you consent to the collection and processing of your personal data in accordance with the Data Privacy Act (RA 10173).</p>
                <button type="submit" class="mt-0.5 min-h-10 w-full rounded-md bg-gradient-to-r from-indigo-500 to-violet-500 px-4 text-xs font-semibold text-white shadow-md shadow-indigo-950/20 transition hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-300 focus-visible:ring-offset-2 focus-visible:ring-offset-[#1e293b]">Create account</button>
            </form>

            <p class="mt-4 text-center text-[11px] text-slate-400">Already have an account? <a href="{{ route('home') }}" class="font-semibold text-violet-300 transition hover:text-white">Sign In</a></p>
        </div>
    </section>
</main>
</body>
</html>
