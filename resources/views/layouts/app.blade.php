<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f3eb">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,400;6..72,500;6..72,600&display=swap" rel="stylesheet">
    <title>@yield('title', 'Alumni Ledger') · Alumni Ledger</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-ink">
    <header class="border-b border-line bg-paper/95">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Alumni Ledger home"><span class="grid size-9 place-items-center bg-forest text-base font-semibold text-white">A</span><span class="font-display text-lg">Alumni Ledger</span></a>
            @auth
                <nav class="flex items-center gap-2 sm:gap-5">
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('dashboard') }}" class="hidden text-sm font-medium text-muted hover:text-ink sm:block">Overview</a>
                        @if (auth()->user()->hasAdminRole(\App\Enums\AdminRole::RecordsAdmin))<a href="{{ route('admin.graduates.index') }}" class="hidden text-sm font-medium text-muted hover:text-ink sm:block">Graduate list</a>@endif
                        @if (auth()->user()->hasAdminRole(\App\Enums\AdminRole::ContentAdmin))<a href="{{ route('admin.announcements.index') }}" class="hidden text-sm font-medium text-muted hover:text-ink sm:block">Announcements</a><a href="{{ route('admin.surveys.index') }}" class="hidden text-sm font-medium text-muted hover:text-ink sm:block">Surveys</a><a href="{{ route('admin.jobs.index') }}" class="hidden text-sm font-medium text-muted hover:text-ink sm:block">Jobs</a>@endif
                        @if (auth()->user()->hasAdminRole(\App\Enums\AdminRole::SuperAdmin))<a href="{{ route('admin.users.index') }}" class="hidden text-sm font-medium text-muted hover:text-ink sm:block">Administrators</a>@endif
                    @else
                        <a href="{{ route('dashboard') }}" class="hidden text-sm font-medium text-muted hover:text-ink sm:block">My dashboard</a><a href="{{ route('profile.edit') }}" class="hidden text-sm font-medium text-muted hover:text-ink sm:block">My profile</a><a href="{{ route('notifications.index') }}" class="hidden text-sm font-medium text-muted hover:text-ink sm:block">Notifications @if (auth()->user()->unreadNotifications->count() > 0)<span class="ml-1 rounded-full bg-forest px-1.5 py-0.5 text-[10px] font-bold text-white">{{ auth()->user()->unreadNotifications->count() }}</span>@endif</a><a href="{{ route('surveys.index') }}" class="hidden text-sm font-medium text-muted hover:text-ink sm:block">Surveys</a><a href="{{ route('jobs.index') }}" class="hidden text-sm font-medium text-muted hover:text-ink sm:block">Jobs</a>
                    @endif
                    <span class="hidden border-l border-line pl-5 text-sm text-muted md:block">{{ auth()->user()->username }}</span>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="border border-line px-3 py-2 text-sm font-medium transition hover:border-ink" type="submit">Sign out</button></form>
                </nav>
            @else
                <nav class="flex items-center gap-3 text-sm font-medium"><a href="{{ route('login') }}" class="px-3 py-2 hover:text-forest">Sign in</a><a href="{{ route('register') }}" class="bg-forest px-4 py-2.5 text-white hover:bg-forest-dark">Join</a></nav>
            @endauth
        </div>
    </header>
    @if (session('status'))
        <div class="mx-auto mt-5 max-w-7xl px-5 sm:px-8" role="status"><p class="border-l-4 border-forest bg-white px-4 py-3 text-sm text-forest">{{ session('status') }}</p></div>
    @endif
    <main>@yield('content')</main>
    <footer class="mx-auto mt-16 flex max-w-7xl justify-between border-t border-line px-5 py-5 text-xs text-muted sm:px-8"><span>Alumni Ledger</span><span>Classmates for life.</span></footer>
</body>
</html>
