<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f3eb">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,400;6..72,500;6..72,600&display=swap" rel="stylesheet">
    <title>Alumni Ledger</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-ink">
    <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-10">
        <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Alumni Ledger home">
            <span class="grid size-10 place-items-center bg-forest text-lg font-semibold text-white">A</span>
            <span class="font-display text-xl">Alumni Ledger</span>
        </a>
        <nav class="flex items-center gap-3 text-sm font-medium">
            <a href="{{ route('login') }}" class="px-3 py-2 hover:text-forest">Sign in</a>
            <a href="{{ route('register') }}" class="bg-forest px-4 py-2.5 text-white transition hover:bg-forest-dark">Create account <span aria-hidden="true">&#8599;</span></a>
        </nav>
    </header>
    <main class="mx-auto grid max-w-7xl gap-12 px-6 pb-16 pt-10 lg:grid-cols-[1.08fr_0.92fr] lg:items-center lg:px-10 lg:pb-24 lg:pt-16">
        <section class="max-w-2xl">
            <p class="eyebrow mb-6 flex items-center gap-3"><span class="h-px w-10 bg-terracotta"></span>{{ config('app.college_name', 'The Alumni Network') }}</p>
            <h1 class="font-display max-w-xl text-5xl leading-[1.03] sm:text-6xl lg:text-7xl">The years after graduation <em class="text-forest">belong here.</em></h1>
            <p class="mt-7 max-w-lg text-lg leading-8 text-muted">Find your place in a community that keeps growing, long after commencement.</p>
            <div class="mt-9 flex flex-wrap items-center gap-4">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-4 bg-forest px-6 py-4 text-sm font-semibold text-white transition hover:bg-forest-dark">Join the alumni network <span aria-hidden="true">&#8599;</span></a>
                <span class="text-sm text-muted">Already registered? <a href="{{ route('login') }}" class="font-semibold text-ink underline decoration-terracotta underline-offset-4">Sign in</a></span>
            </div>
        </section>
        <section class="relative min-h-[420px] overflow-hidden bg-forest px-8 py-9 text-white sm:px-12 sm:py-12" aria-label="Alumni community">
            <div class="absolute inset-0 opacity-30" aria-hidden="true" style="background-image: repeating-linear-gradient(135deg, transparent 0, transparent 24px, rgba(255,255,255,.14) 25px, transparent 26px); background-size: 180px 180px;"></div>
            <div class="relative flex min-h-[340px] flex-col justify-between">
                <div class="flex items-start justify-between gap-4"><p class="max-w-[12rem] text-sm leading-6 text-white/75">A shared record of where we came from and where we are going.</p><span class="border border-white/30 px-3 py-2 text-xs uppercase tracking-[0.15em] text-white/80">Est. together</span></div>
                <div><p class="font-display text-[clamp(4rem,10vw,8rem)] leading-none">Always<br><em class="text-lime">connected.</em></p><div class="mt-8 flex items-center justify-between border-t border-white/25 pt-5 text-xs uppercase tracking-[0.14em] text-white/70"><span>One community</span><span>Many chapters</span><span aria-hidden="true">&#10038;</span></div></div>
            </div>
        </section>
    </main>
    <footer class="mx-auto flex max-w-7xl justify-between border-t border-line px-6 py-5 text-xs text-muted lg:px-10"><span>Alumni Ledger</span><span>Classmates for life.</span></footer>
</body>
</html>