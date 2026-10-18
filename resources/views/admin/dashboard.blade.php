@extends('layouts.app')

@section('title', $adminRole->name.' dashboard')

@section('content')
<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="flex flex-col justify-between gap-6 border-b border-line pb-8 sm:flex-row sm:items-end">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="font-display mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">{{ $adminRole->name }} dashboard</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-muted">{{ $adminRole->description }}</p>
        </div>
        <p class="text-sm text-muted">Signed in as <span class="font-semibold text-ink">{{ auth()->user()->username }}</span></p>
    </div>

    <section class="mt-6" aria-label="Dashboard overview">
        <h2 class="sr-only">Overview</h2>
        <div class="dashboard-metrics">
            @foreach ($metrics as $metric)
                <article class="dashboard-metric dashboard-metric-{{ $metric['tone'] }}">
                    <span class="dashboard-metric-icon" aria-hidden="true">
                        @switch($metric['icon'])
                            @case('users')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>@break
                            @case('graduates')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m2 10 10-5 10 5-10 5-10-5Z"/><path d="M6 12v5c3.5 3 8.5 3 12 0v-5M22 10v6"/></svg>@break
                            @case('verified')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/><path d="m9 12 2 2 4-4"/></svg>@break
                            @case('profile')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M6 16c.7-1.5 1.7-2 3-2s2.3.5 3 2M15 9h3M15 13h3"/></svg>@break
                            @case('announcement')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4zM8 8h8M8 12h8M8 16h4"/></svg>@break
                            @case('survey')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 4H5v16h14V4h-3M9 2h6v4H9zM8 11h8M8 15h5"/></svg>@break
                            @case('building')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4v18M19 21V11l-7-4"/><path d="M9 9v.01M9 12v.01M9 15v.01M9 18v.01M15 14v.01M15 17v.01"/></svg>@break
                            @case('briefcase')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V4h8v3M3 12h18M10 12v2h4v-2"/></svg>@break
                        @endswitch
                    </span>

                    <div class="min-w-0">
                        <p class="dashboard-metric-value">{{ number_format($metric['value']) }}</p>
                        <h3 class="dashboard-metric-label">{{ $metric['label'] }}</h3>
                        <p class="dashboard-metric-detail">{{ $metric['detail'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mt-10" aria-labelledby="workspaces-heading">
        <div class="mb-4 flex items-end justify-between gap-4">
            <h2 id="workspaces-heading" class="font-display text-2xl">Workspaces</h2>
            <span class="text-xs uppercase tracking-wider text-muted">{{ count($actions) }} available</span>
        </div>
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($actions as $action)
                <a href="{{ route($action['route']) }}" class="group flex min-h-32 items-start justify-between gap-5 rounded-md border border-line bg-white p-5 transition-colors hover:border-forest sm:p-6">
                    <span class="min-w-0">
                        <span class="block font-display text-xl text-ink">{{ $action['title'] }}</span>
                        <span class="mt-2 block text-sm leading-6 text-muted">{{ $action['detail'] }}</span>
                    </span>
                    <span class="shrink-0 text-lg text-forest transition group-hover:translate-x-0.5" aria-hidden="true">&#8599;</span>
                </a>
            @endforeach
        </div>
    </section>
</div>
@endsection
