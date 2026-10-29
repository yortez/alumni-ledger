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
                <div class="flex shrink-0 items-center gap-2">
                    <a href="{{ route('notifications.index') }}" data-notifications-open aria-controls="notification-dialog" aria-haspopup="dialog" aria-label="Notifications{{ $unreadNotificationCount > 0 ? ', '.$unreadNotificationCount.' unread' : '' }}" title="Notifications" class="relative grid size-10 place-items-center rounded-md border border-line bg-surface-raised text-muted transition hover:border-ink hover:text-ink">
                        <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                        @if ($unreadNotificationCount > 0)<span class="absolute -right-1 -top-1 grid min-h-4 min-w-4 place-items-center rounded-full bg-red-600 px-1 text-[9px] font-bold leading-none text-white">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>@endif
                    </a>
                    <details class="relative">
                        <summary aria-label="Open account menu" title="Account menu" class="grid size-10 cursor-pointer list-none place-items-center rounded-full bg-forest text-xs font-bold text-white transition hover:bg-forest-dark">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</summary>
                        <div class="absolute right-0 top-full z-30 mt-2 w-56 overflow-hidden rounded-md border border-line bg-surface shadow-lg">
                            <div class="border-b border-line px-3 py-2"><p class="truncate text-xs font-semibold text-ink">{{ auth()->user()->name }}</p><p class="truncate text-[10px] text-muted">{{ auth()->user()->email }}</p></div>
                            <form method="POST" action="{{ route('logout') }}" class="p-1">@csrf<button class="flex w-full items-center gap-2 rounded-sm px-2 py-2 text-left text-xs font-medium text-red-700 transition hover:bg-red-50" type="submit"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg>Logout</button></form>
                        </div>
                    </details>
                </div>
            @else
                <nav class="flex items-center gap-2 text-sm font-medium"><a href="{{ route('home') }}" class="rounded-md px-3 py-2 text-muted transition hover:bg-paper hover:text-ink">Sign in</a><a href="{{ route('register') }}" class="rounded-md bg-forest px-4 py-2.5 text-white transition hover:bg-forest-dark">Join</a></nav>
            @endauth
        </div>
        @auth
            <div class="border-t border-line">
                <div class="mx-auto max-w-7xl px-4 py-1.5 sm:px-7 md:hidden">
                    <button type="button" class="ml-auto grid size-10 place-items-center rounded-md text-ink transition hover:bg-paper" aria-label="Toggle navigation" aria-controls="main-navigation" aria-expanded="false" data-navigation-toggle>
                        <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
                <nav id="main-navigation" aria-label="Main navigation" class="mx-auto hidden flex max-w-7xl flex-col gap-1 px-4 pb-2 sm:px-7 md:flex md:flex-row md:justify-center md:py-1.5">
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('filament.admin.pages.dashboard') }}" class="nav-link {{ request()->routeIs('filament.admin.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('filament.admin.*')) aria-current="page" @endif>Overview</a>
                        @if (auth()->user()->hasAdminPermission('graduates'))<a href="{{ route('admin.graduates.index') }}" class="nav-link {{ request()->routeIs('admin.graduates.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.graduates.*')) aria-current="page" @endif>Graduates</a>@endif
                        @if (auth()->user()->hasAdminPermission('announcements'))<a href="{{ route('admin.announcements.index') }}" class="nav-link {{ request()->routeIs('admin.announcements.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.announcements.*')) aria-current="page" @endif>Announcements</a>@endif
                        @if (auth()->user()->hasAdminPermission('surveys'))<a href="{{ route('admin.surveys.index') }}" class="nav-link {{ request()->routeIs('admin.surveys.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.surveys.*')) aria-current="page" @endif>Surveys</a>@endif
                        @if (auth()->user()->hasAdminPermission('jobs'))<a href="{{ route('admin.jobs.index') }}" class="nav-link {{ request()->routeIs('admin.jobs.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.jobs.*')) aria-current="page" @endif>Jobs</a>@endif
                        @if (auth()->user()->hasAdminPermission('manage_admins'))<a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.users.*')) aria-current="page" @endif>Administrators</a>@endif
                        @if (auth()->user()->hasAdminPermission('manage_roles'))<a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.roles.*')) aria-current="page" @endif>Roles</a>@endif
                    @else
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a><a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'nav-link-active' : '' }}" @if (request()->routeIs('profile.edit')) aria-current="page" @endif>My profile</a><a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('notifications.*')) aria-current="page" @endif>Notifications @if ($unreadNotificationCount > 0)<span class="ml-1 rounded-full bg-forest px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>@endif</a><a href="{{ route('surveys.index') }}" class="nav-link {{ request()->routeIs('surveys.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('surveys.*')) aria-current="page" @endif>Surveys</a><a href="{{ route('jobs.index') }}" class="nav-link {{ request()->routeIs('jobs.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('jobs.*')) aria-current="page" @endif>Jobs</a>
                    @endif
                </nav>
            </div>
        @endauth
    </header>
    @auth
        <dialog id="notification-dialog" aria-labelledby="notification-dialog-title" class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-md border border-line bg-surface p-0 text-ink shadow-2xl backdrop:bg-black/45">
            <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
                <div><p class="eyebrow">Inbox</p><h2 id="notification-dialog-title" class="mt-1 text-lg font-semibold">Notifications</h2></div>
                <button type="button" data-notification-dialog-close aria-label="Close notifications" title="Close" class="grid size-9 shrink-0 place-items-center rounded-md border border-line text-muted transition hover:bg-paper hover:text-ink"><svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m18 6-12 12M6 6l12 12"/></svg></button>
            </div>
            <div class="max-h-[min(65vh,32rem)] overflow-y-auto px-5">
                @forelse ($headerNotifications as $notification)
                    <article class="border-b border-line py-4 last:border-0">
                        <div class="flex items-start justify-between gap-3"><h3 class="text-sm font-semibold">{{ $notification->data['title'] ?? 'Update' }}</h3>@if ($notification->read_at === null)<span class="shrink-0 rounded-full bg-mint px-2 py-1 text-[10px] font-semibold text-forest">New</span>@endif</div>
                        <p class="mt-2 whitespace-pre-line text-xs leading-5 text-muted">{{ $notification->data['message'] ?? 'You have a new update.' }}</p>
                        <p class="mt-2 text-[10px] text-muted">{{ $notification->created_at->diffForHumans() }}</p>
                    </article>
                @empty
                    <p class="py-10 text-center text-sm text-muted">You do not have any notifications yet.</p>
                @endforelse
            </div>
            <div class="flex justify-end border-t border-line px-5 py-3"><a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-forest underline underline-offset-4">View all notifications</a></div>
        </dialog>
    @endauth
    @if (session('status'))
        <div class="mx-auto mt-5 max-w-7xl px-5 sm:px-8" role="status"><p class="rounded-md border border-forest/15 bg-mint px-4 py-3 text-sm font-medium text-forest">{{ session('status') }}</p></div>
    @endif
    <main>@yield('content')</main>
    <footer class="mx-auto mt-16 flex max-w-7xl flex-col justify-between gap-2 border-t border-line px-5 py-5 text-xs text-muted sm:flex-row sm:gap-4 sm:px-8"><span>Alumni Tracer Management System</span></footer>
</body>
</html>
