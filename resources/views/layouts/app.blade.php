<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#000000">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Alumni Tracer Management System') · Alumni Tracer Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell flex min-h-screen flex-col bg-canvas text-ink">
    <header class="bg-black">
        <div class="mx-auto flex h-14 max-w-[1440px] items-center gap-3 px-5 sm:px-8">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 shrink items-center gap-3" aria-label="Alumni Tracer Management System home">
                <span class="grid size-7 shrink-0 place-items-center rounded-[6px] bg-white text-micro font-semibold text-black">AT</span>
                <span class="truncate text-meta text-white"><span class="sm:hidden">Alumni Tracer</span><span class="hidden sm:inline">Alumni Tracer Management System</span></span>
            </a>
            @auth
                <div class="ml-auto flex shrink-0 items-center gap-1">
                    <a href="{{ route('notifications.index') }}" data-notifications-open aria-controls="notification-dialog" aria-haspopup="dialog" aria-label="Notifications{{ $unreadNotificationCount > 0 ? ', '.$unreadNotificationCount.' unread' : '' }}" title="Notifications" class="relative grid size-11 place-items-center rounded-full text-[18px] text-white/85 transition hover:bg-white/10 hover:text-white">
                        <svg aria-hidden="true" class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                        @if ($unreadNotificationCount > 0)<span data-unread-count class="absolute right-2 top-2 grid min-w-[15px] place-items-center rounded-full bg-accent px-1 text-[9px] font-semibold leading-[15px] text-white tabular-nums">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>@else<span data-unread-count class="absolute right-2 top-2 hidden min-w-[15px] place-items-center rounded-full bg-accent px-1 text-[9px] font-semibold leading-[15px] text-white tabular-nums">0</span>@endif
                    </a>
                    <details class="relative">
                        <summary aria-label="Open account menu" title="Account menu" class="ml-1 grid size-8 cursor-pointer list-none place-items-center rounded-full bg-chip-gray text-micro font-semibold text-ink transition hover:bg-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</summary>
                        <div class="absolute right-0 top-full z-30 mt-3 w-60 overflow-hidden rounded-chip border border-hairline bg-surface shadow-lg">
                            <div class="border-b border-hairline px-4 py-3">
                                <p class="truncate text-caption font-semibold text-ink">{{ auth()->user()->name }}</p>
                                <p class="truncate text-micro text-ink-tertiary">{{ auth()->user()->email }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="p-1.5">@csrf<button class="flex w-full items-center gap-2 rounded-chip px-2.5 py-2 text-left text-caption text-danger transition hover:bg-[#fff2f3]" type="submit"><svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg>Log out</button></form>
                        </div>
                    </details>
                </div>
            @else
                <nav class="ml-auto flex items-center gap-2 text-caption"><a href="{{ route('home') }}" class="rounded-chip px-3 py-1.5 text-white/85 transition hover:text-white">Sign in</a><a href="{{ route('register') }}" class="rounded-full bg-white px-4 py-1.5 font-semibold text-black transition hover:bg-chip-gray">Join</a></nav>
            @endauth
        </div>
    </header>

    @auth
        <div class="border-b border-hairline">
            <div class="mx-auto flex max-w-[1440px] flex-col px-5 sm:px-8">
                <button type="button" class="my-1 ml-auto grid size-10 place-items-center rounded-chip text-ink transition hover:bg-divider-soft md:hidden" aria-label="Toggle navigation" aria-controls="main-navigation" aria-expanded="false" data-navigation-toggle>
                    <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <nav id="main-navigation" aria-label="Main navigation" class="hidden w-full flex-col gap-0.5 pb-3 md:flex md:h-[52px] md:flex-row md:items-center md:gap-1 md:pb-0">
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>Overview</a>
                        @if (auth()->user()->hasAdminPermission('graduates'))<a href="{{ route('admin.graduates.index') }}" class="nav-link {{ request()->routeIs('admin.graduates.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.graduates.*')) aria-current="page" @endif>Graduates</a>@endif
                        @if (auth()->user()->hasAdminPermission('announcements'))<a href="{{ route('admin.announcements.index') }}" class="nav-link {{ request()->routeIs('admin.announcements.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.announcements.*')) aria-current="page" @endif>Announcements</a>@endif
                        @if (auth()->user()->hasAdminPermission('surveys'))<a href="{{ route('admin.surveys.index') }}" class="nav-link {{ request()->routeIs('admin.surveys.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.surveys.*')) aria-current="page" @endif>Surveys</a>@endif
                        @if (auth()->user()->hasAdminPermission('jobs'))<a href="{{ route('admin.jobs.index') }}" class="nav-link {{ request()->routeIs('admin.jobs.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.jobs.*')) aria-current="page" @endif>Jobs</a>@endif
                        @if (auth()->user()->hasAdminPermission('manage_admins'))<a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.users.*')) aria-current="page" @endif>Administrators</a>@endif
                        @if (auth()->user()->hasAdminPermission('manage_roles'))<a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('admin.roles.*')) aria-current="page" @endif>Roles</a>@endif
                    @else
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
                        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'nav-link-active' : '' }}" @if (request()->routeIs('profile.edit')) aria-current="page" @endif>My profile</a>
                        <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('notifications.*')) aria-current="page" @endif>Notifications<span data-unread-count class="ml-1.5 rounded-full bg-accent px-1.5 py-0.5 text-[10px] font-semibold text-white tabular-nums {{ $unreadNotificationCount > 0 ? '' : 'hidden' }}">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span></a>
                        <a href="{{ route('surveys.index') }}" class="nav-link {{ request()->routeIs('surveys.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('surveys.*')) aria-current="page" @endif>Surveys</a>
                        <a href="{{ route('jobs.index') }}" class="nav-link {{ request()->routeIs('jobs.*') ? 'nav-link-active' : '' }}" @if (request()->routeIs('jobs.*')) aria-current="page" @endif>Jobs</a>
                    @endif
                </nav>
            </div>
        </div>
    @endauth

    @auth
        <dialog id="notification-dialog" aria-labelledby="notification-dialog-title" class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-card border border-hairline bg-surface p-0 text-ink shadow-2xl backdrop:bg-black/40">
            <div class="flex items-center justify-between gap-4 border-b border-hairline px-6 py-4">
                <h2 id="notification-dialog-title" class="text-title font-semibold">Notifications</h2>
                <button type="button" data-notification-dialog-close aria-label="Close notifications" title="Close" class="grid size-9 shrink-0 place-items-center rounded-full text-ink-tertiary transition hover:bg-divider-soft hover:text-ink"><svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m18 6-12 12M6 6l12 12"/></svg></button>
            </div>
            <div class="max-h-[min(65vh,32rem)] overflow-y-auto px-6">
                @forelse ($headerNotifications as $notification)
                    <article class="border-b border-divider-soft py-4 last:border-0">
                        <div class="flex items-start justify-between gap-3"><h3 class="text-caption font-semibold">{{ $notification->data['title'] ?? 'Update' }}</h3>@if ($notification->read_at === null)<span class="shrink-0 rounded-full bg-mint px-2 py-0.5 text-[10px] font-semibold text-accent">New</span>@endif</div>
                        <p class="mt-1.5 whitespace-pre-line text-meta leading-5 text-ink-secondary">{{ $notification->data['message'] ?? 'You have a new update.' }}</p>
                        <p class="mt-2 text-micro text-ink-tertiary">{{ $notification->created_at->diffForHumans() }}</p>
                    </article>
                @empty
                    <p class="py-12 text-center text-caption text-ink-tertiary">You do not have any notifications yet.</p>
                @endforelse
            </div>
            <div class="flex justify-end border-t border-hairline px-6 py-3"><a href="{{ route('notifications.index') }}" class="text-meta font-semibold text-accent">View all notifications</a></div>
        </dialog>
    @endauth

    @if (session('status'))
        <div class="mx-auto mt-6 w-full max-w-[1440px] px-5 sm:px-8" role="status"><p class="rounded-chip border border-accent/15 bg-mint px-4 py-3 text-caption font-medium text-accent">{{ session('status') }}</p></div>
    @endif

    <main class="flex-1">@yield('content')</main>

    <footer class="border-t border-hairline">
        <div class="mx-auto flex max-w-[1440px] flex-col justify-between gap-1 px-5 py-4 text-micro text-ink-tertiary sm:flex-row sm:gap-4 sm:px-8">
            <span>Alumni Tracer Management System</span>
        </div>
    </footer>
</body>
</html>
