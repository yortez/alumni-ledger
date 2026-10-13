<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#141a32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>@yield('title', 'Alumni Tracer Management System') · Alumni Tracer Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell min-h-screen bg-paper text-ink">
    <header class="border-b border-line bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-5 py-3 sm:px-8">
            <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-3" aria-label="Alumni Tracer Management System home"><span class="grid size-9 place-items-center rounded-sm bg-forest text-xs font-bold text-white">AT</span><span class="text-sm font-semibold leading-tight tracking-tight sm:hidden">Alumni Tracer</span><span class="hidden font-semibold tracking-tight sm:inline">Alumni Tracer Management System</span></a>
            @auth
                <div class="flex shrink-0 items-center gap-3">
                    <span class="hidden max-w-40 truncate text-sm text-muted sm:block">{{ auth()->user()->username }}</span>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-md border border-line px-3 py-2 text-sm font-medium text-ink transition hover:border-ink hover:bg-paper" type="submit">Sign out</button></form>
                </div>
            @else
                <nav class="flex items-center gap-2 text-sm font-medium"><a href="{{ route('home') }}" class="rounded-md px-3 py-2 text-muted transition hover:bg-paper hover:text-ink">Sign in</a><a href="{{ route('register') }}" class="rounded-md bg-forest px-4 py-2.5 text-white transition hover:bg-forest-dark">Join</a></nav>
            @endauth
        </div>
        @auth
            <div class="border-t border-line">
                <nav aria-label="Main navigation" class="mx-auto flex max-w-7xl justify-start gap-1 overflow-x-auto px-4 py-1.5 sm:px-7 md:justify-center">
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>Overview</a>
                        @if (auth()->user()->hasAdminPermission('graduates'))<a href="{{ route('admin.graduates.index') }}" class="nav-link {{ request()->routeIs('admin.graduates.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.graduates.*')) aria-current="page" @endif>Graduates</a>@endif
                        @if (auth()->user()->hasAdminPermission('announcements'))<a href="{{ route('admin.announcements.index') }}" class="nav-link {{ request()->routeIs('admin.announcements.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.announcements.*')) aria-current="page" @endif>Announcements</a>@endif
                        @if (auth()->user()->hasAdminPermission('surveys'))<a href="{{ route('admin.surveys.index') }}" class="nav-link {{ request()->routeIs('admin.surveys.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.surveys.*')) aria-current="page" @endif>Surveys</a>@endif
                        @if (auth()->user()->hasAdminPermission('jobs'))<a href="{{ route('admin.jobs.index') }}" class="nav-link {{ request()->routeIs('admin.jobs.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.jobs.*')) aria-current="page" @endif>Jobs</a>@endif
                        @if (auth()->user()->hasAdminPermission('manage_admins'))<a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.users.*')) aria-current="page" @endif>Administrators</a>@endif
                        @if (auth()->user()->hasAdminPermission('manage_roles'))<a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.roles.*')) aria-current="page" @endif>Roles</a>@endif
                    @else
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a><a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'nav-link-active' : '' }}" @if (request()->routeIs('profile.edit')) aria-current="page" @endif>My profile</a><a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('notifications.*')) aria-current="page" @endif>Notifications @if (auth()->user()->unreadNotifications->count() > 0)<span class="ml-1 rounded-full bg-forest px-1.5 py-0.5 text-[10px] font-bold text-white">{{ auth()->user()->unreadNotifications->count() }}</span>@endif</a><a href="{{ route('surveys.index') }}" class="nav-link {{ request()->routeIs('surveys.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('surveys.*')) aria-current="page" @endif>Surveys</a><a href="{{ route('jobs.index') }}" class="nav-link {{ request()->routeIs('jobs.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('jobs.*')) aria-current="page" @endif>Jobs</a>
                    @endif
                </nav>
            </div>
        @endauth
    </header>
    @if (session('status'))
        <div class="mx-auto mt-5 max-w-7xl px-5 sm:px-8" role="status"><p class="rounded-md border border-forest/15 bg-mint px-4 py-3 text-sm font-medium text-forest">{{ session('status') }}</p></div>
    @endif
    <main>@yield('content')</main>
    <footer class="mx-auto mt-16 flex max-w-7xl flex-col justify-between gap-2 border-t border-line px-5 py-5 text-xs text-muted sm:flex-row sm:gap-4 sm:px-8"><span>Alumni Tracer Management System</span><span>Classmates for life.</span></footer>
</body>
</html>
