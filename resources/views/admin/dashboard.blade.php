@extends('layouts.app')

@section('title', $adminRole->name.' dashboard')

@section('content')
<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="flex flex-col justify-between gap-6 border-b border-line pb-8 sm:flex-row sm:items-end">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="font-display mt-3 text-4xl sm:text-5xl">{{ $adminRole->name }} dashboard</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-muted">{{ $adminRole->description }}</p>
        </div>
        <p class="text-sm text-muted">Signed in as <span class="font-semibold text-ink">{{ auth()->user()->username }}</span></p>
    </div>

    <section class="mt-8" aria-labelledby="overview-heading">
        <div class="mb-4 flex items-end justify-between gap-4">
            <h2 id="overview-heading" class="font-display text-2xl">Overview</h2>
            <span class="text-xs uppercase tracking-wider text-muted">Current totals</span>
        </div>
        <div class="grid gap-px border border-line bg-line sm:grid-cols-2 {{ count($metrics) > 3 ? 'xl:grid-cols-5' : 'xl:grid-cols-3' }}">
            @foreach ($metrics as $metric)
                <article class="min-w-0 bg-white p-5 sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">{{ $metric['label'] }}</p>
                    <p class="mt-3 font-display text-4xl text-ink">{{ number_format($metric['value']) }}</p>
                    <p class="mt-2 text-xs leading-5 text-muted">{{ $metric['detail'] }}</p>
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
                <a href="{{ route($action['route']) }}" class="group flex min-h-32 items-start justify-between gap-5 border border-line bg-white p-5 transition hover:border-forest sm:p-6">
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
