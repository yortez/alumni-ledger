@extends('layouts.app')

@section('title', 'Job opportunities')

@section('content')
@php
    $visibleTypeCounts = array_filter($typeCounts, fn (int $count): bool => $count > 0);
@endphp

<div class="mx-auto w-full max-w-[1440px] px-5 pb-16 pt-8 sm:px-8 xl:px-20">
    <div class="flex flex-wrap items-baseline gap-x-4 gap-y-1">
        <h1 class="text-display font-semibold">Job opportunities</h1>
        <p class="text-body text-ink-tertiary tabular-nums">{{ $openRoles }} open {{ $openRoles === 1 ? 'role' : 'roles' }}</p>
    </div>

    <section aria-label="Open roles" class="panel mt-6 overflow-hidden">
        @if (count($visibleTypeCounts) > 1)
            <div class="border-b border-hairline px-4 py-3">
                <nav aria-label="Filter roles by employment type" class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('jobs.index') }}"
                       class="inline-flex h-8 items-center gap-1.5 rounded-chip px-3 text-meta font-medium transition {{ $activeType === null ? 'bg-ink text-white' : 'border border-hairline text-ink-secondary hover:border-chip-gray hover:text-ink' }}"
                       @if ($activeType === null) aria-current="page" @endif>
                        All <span class="tabular-nums {{ $activeType === null ? 'text-white/70' : 'text-ink-tertiary' }}">{{ $openRoles }}</span>
                    </a>

                    @foreach ($visibleTypeCounts as $type => $count)
                        <a href="{{ route('jobs.index', ['type' => $type]) }}"
                           class="inline-flex h-8 max-w-[15rem] items-center gap-1.5 rounded-chip px-3 text-meta font-medium transition {{ $activeType === $type ? 'bg-ink text-white' : 'border border-hairline text-ink-secondary hover:border-chip-gray hover:text-ink' }}"
                           @if ($activeType === $type) aria-current="page" @endif>
                            <span class="truncate">{{ $type }}</span> <span class="shrink-0 tabular-nums {{ $activeType === $type ? 'text-white/70' : 'text-ink-tertiary' }}">{{ $count }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        @endif

        <ul>
            @forelse ($jobs as $job)
                @php
                    $applicationStatus = $applications[$job->id] ?? null;
                    $hasApplied = $applicationStatus !== null;
                    $gutterDate = $job->application_deadline ?? $job->created_at;
                @endphp

                <li class="ledger-row grid grid-cols-[52px_16px_minmax(0,1fr)] gap-x-3 border-t border-divider-soft first:border-t-0 sm:grid-cols-[68px_16px_minmax(0,1fr)] sm:gap-x-4"
                    data-emphasis="{{ $hasApplied ? 'false' : 'true' }}">

                    <div class="py-3 text-right">
                        <p class="text-micro font-semibold uppercase tracking-wide text-ink-tertiary">{{ $gutterDate->format('M') }}</p>
                        <p class="text-small font-semibold tabular-nums text-ink">{{ $gutterDate->format('j') }}</p>
                    </div>

                    <div aria-hidden="true" class="relative">
                        <span class="absolute inset-y-0 left-1/2 w-px -translate-x-1/2 bg-hairline"></span>
                        <span class="ledger-node absolute left-1/2 top-[22px] size-1.5 -translate-x-1/2 -translate-y-1/2"></span>
                    </div>

                    <details class="group min-w-0">
                        <summary class="flex cursor-pointer list-none items-center gap-3 py-3 transition-colors [&::-webkit-details-marker]:hidden hover:bg-canvas">
                            <span aria-hidden="true" class="ledger-glyph shrink-0">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 12h18"/></svg>
                            </span>

                            <span class="ledger-title min-w-0 flex-1 truncate text-small">{{ $job->title }}</span>

                            <span class="hidden shrink-0 text-meta text-ink-tertiary sm:block">{{ $job->company }}</span>
                            @if ($hasApplied)
                                <span class="shrink-0 rounded-full bg-mint px-2 py-0.5 text-micro font-semibold text-accent">Applied</span>
                            @endif

                            <x-chevron class="shrink-0 text-ink-tertiary transition-transform group-open:rotate-90" />
                        </summary>

                        <div class="mb-3 grid gap-3 rounded-chip bg-surface-pearl p-4">
                            <p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-meta text-ink-tertiary">
                                <span>{{ $job->employment_type }}</span>
                                <span>{{ $job->location }}</span>
                                @if ($job->application_deadline)
                                    <span>Apply by {{ $job->application_deadline->format('j F Y') }}</span>
                                @else
                                    <span>Posted {{ $job->created_at->format('j F Y') }}</span>
                                @endif
                            </p>

                            <p class="line-clamp-3 max-w-[60ch] whitespace-pre-line text-caption text-ink-secondary">{{ $job->description }}</p>

                            <div class="flex flex-wrap items-center justify-between gap-3">
                                @if ($hasApplied)
                                    <p class="text-meta text-ink-tertiary">
                                        Your application
                                        <span class="font-semibold text-ink">{{ $statusLabels[$applicationStatus] ?? ucfirst($applicationStatus) }}</span>
                                    </p>
                                @else
                                    <p class="text-meta text-ink-tertiary">You have not applied yet.</p>
                                @endif

                                <a href="{{ route('jobs.show', $job) }}" class="inline-flex items-center gap-1 text-caption font-semibold text-accent transition hover:text-accent-strong">View role<x-chevron /></a>
                            </div>
                        </div>
                    </details>
                </li>
            @empty
                <li class="px-5 py-12 text-center">
                    <p class="text-caption text-ink-tertiary">
                        @if ($activeType !== null)
                            No {{ $activeType }} roles are open right now.
                        @else
                            No job opportunities are available right now.
                        @endif
                    </p>
                    @if ($activeType !== null)
                        <a href="{{ route('jobs.index') }}" class="mt-3 inline-block text-caption font-semibold text-accent transition hover:text-accent-strong">Show all roles</a>
                    @endif
                </li>
            @endforelse
        </ul>

        @if ($jobs->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-hairline px-4 py-3 sm:px-5">
                <p class="text-meta text-ink-tertiary">Page {{ $jobs->currentPage() }} of {{ $jobs->lastPage() }}</p>
                <x-pagination-pills :paginator="$jobs" />
            </div>
        @endif
    </section>
</div>
@endsection
