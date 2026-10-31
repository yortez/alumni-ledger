@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
@php
    $visibleTypeCounts = array_filter($typeCounts, fn (int $count): bool => $count > 0);
@endphp

<div class="mx-auto w-full max-w-[1440px] px-5 pb-16 pt-8 sm:px-8 xl:px-20">
    <x-back-link :href="route('dashboard')" label="Dashboard" class="mb-5" />
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-baseline gap-x-4 gap-y-1">
            <h1 class="text-display font-semibold">Notifications</h1>
            @if ($unreadCount > 0)
                <p class="text-body text-ink-tertiary tabular-nums" data-unread-summary>{{ $unreadCount }} unread</p>
            @endif
        </div>

        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}" data-unread-form>
                @csrf
                @if ($activeType !== null)
                    <input type="hidden" name="type" value="{{ $activeType }}">
                @endif
                <button type="submit" class="inline-flex h-9 items-center rounded-full border border-hairline bg-surface px-4 text-caption font-semibold text-ink transition hover:border-chip-gray">Mark all as read</button>
            </form>
        @endif
    </div>

    <section aria-label="Notification inbox" class="panel mt-6 overflow-hidden">
        <div class="border-b border-hairline px-4 py-3">
            <nav aria-label="Filter notifications by type" class="flex flex-wrap items-center gap-2">
                <a href="{{ route('notifications.index') }}"
                   class="inline-flex h-8 items-center gap-1.5 rounded-chip px-3 text-meta font-medium transition {{ $activeType === null ? 'bg-ink text-white' : 'border border-hairline text-ink-secondary hover:border-chip-gray hover:text-ink' }}"
                   @if ($activeType === null) aria-current="page" @endif>
                    All <span class="tabular-nums {{ $activeType === null ? 'text-white/70' : 'text-ink-tertiary' }}">{{ $totalCount }}</span>
                </a>

                @foreach ($visibleTypeCounts as $typeKey => $typeCount)
                    <a href="{{ route('notifications.index', ['type' => $typeKey]) }}"
                       class="inline-flex h-8 items-center gap-1.5 rounded-chip px-3 text-meta font-medium transition {{ $activeType === $typeKey ? 'bg-ink text-white' : 'border border-hairline text-ink-secondary hover:border-chip-gray hover:text-ink' }}"
                       @if ($activeType === $typeKey) aria-current="page" @endif>
                        {{ $types[$typeKey]['label'] }} <span class="tabular-nums {{ $activeType === $typeKey ? 'text-white/70' : 'text-ink-tertiary' }}">{{ $typeCount }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <ul>
            @forelse ($notifications as $notification)
                @php
                    $type = $notification->data['type'] ?? null;
                    $isUnread = $notification->read_at === null;
                    $time = $notification->created_at;

                    $action = match ($type) {
                        'announcement' => ['label' => 'View announcement', 'url' => route('dashboard')],
                        'survey' => ['label' => 'Open survey', 'url' => route('surveys.index')],
                        'job_application' => ['label' => 'View job posting', 'url' => route('jobs.index')],
                        'alumni_registered' => auth()->user()->hasAdminPermission('graduates')
                            ? ['label' => 'View graduate records', 'url' => route('admin.graduates.index')]
                            : null,
                        default => null,
                    };
                @endphp

                <li class="ledger-row grid grid-cols-[52px_16px_minmax(0,1fr)] gap-x-3 border-t border-divider-soft first:border-t-0 sm:grid-cols-[68px_16px_minmax(0,1fr)] sm:gap-x-4"
                    data-emphasis="{{ $isUnread ? 'true' : 'false' }}">

                    <div class="py-3 text-right">
                        <p class="text-micro font-semibold uppercase tracking-wide text-ink-tertiary">{{ $time->format('M') }}</p>
                        <p class="text-small font-semibold tabular-nums text-ink">{{ $time->format('j') }}</p>
                        @if ($loop->first)
                            <p class="text-micro tabular-nums text-ink-tertiary">{{ $time->format('Y') }}</p>
                        @endif
                    </div>

                    <div aria-hidden="true" class="relative">
                        <span class="absolute inset-y-0 left-1/2 w-px -translate-x-1/2 bg-hairline"></span>
                        <span class="ledger-node absolute left-1/2 top-[22px] size-1.5 -translate-x-1/2 -translate-y-1/2"></span>
                    </div>

                    <details class="group min-w-0"
                             data-notification-row
                             @if ($isUnread) data-mark-read-url="{{ route('notifications.read', $notification->id) }}" @endif>
                        <summary class="flex cursor-pointer list-none items-center gap-3 py-3 transition-colors [&::-webkit-details-marker]:hidden hover:bg-canvas">
                            <span aria-hidden="true" class="ledger-glyph shrink-0">
                                @switch($type)
                                    @case('announcement')
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 9v6a1 1 0 0 0 1 1h3l5 4V4L8 8H5a1 1 0 0 0-1 1Z"/><path d="M17 9a4 4 0 0 1 0 6"/></svg>
                                        @break
                                    @case('survey')
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6h11M9 12h11M9 18h11"/><path d="m3 6 1.5 1.5L7 5"/><path d="M4.5 12.5 6 14l2-2"/></svg>
                                        @break
                                    @case('job_application')
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 12h18"/></svg>
                                        @break
                                    @case('alumni_registered')
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0M18 8v6M21 11h-6"/></svg>
                                        @break
                                    @default
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 8v5M12 16h.01"/></svg>
                                @endswitch
                                <span class="sr-only">{{ $types[$type]['singular'] ?? 'Notification' }}: </span>
                            </span>

                            <span class="ledger-title min-w-0 flex-1 truncate text-small">{{ $notification->data['title'] ?? 'Update' }}</span>

                            <span class="shrink-0 text-meta tabular-nums text-ink-tertiary">{{ $time->format('H:i') }}</span>
                            <x-chevron class="shrink-0 text-ink-tertiary transition-transform group-open:rotate-90" />
                        </summary>

                        <div class="mb-3 grid gap-3 rounded-chip bg-surface-pearl p-4">
                            <p class="max-w-[60ch] whitespace-pre-line text-body text-ink-secondary">{{ $notification->data['message'] ?? 'You have a new update.' }}</p>
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="text-meta text-ink-tertiary">{{ $time->format('j F Y, H:i') }}</p>
                                @if ($action !== null)
                                    <a href="{{ $action['url'] }}" class="inline-flex items-center gap-1 text-caption font-semibold text-accent transition hover:text-accent-strong">{{ $action['label'] }}<x-chevron /></a>
                                @endif
                            </div>
                        </div>
                    </details>
                </li>
            @empty
                <li class="px-5 py-12 text-center">
                    <p class="text-caption text-ink-tertiary">
                        @if ($activeType !== null)
                            No {{ strtolower($types[$activeType]['label']) }} right now.
                        @else
                            You do not have any notifications yet.
                        @endif
                    </p>
                    @if ($activeType !== null)
                        <a href="{{ route('notifications.index') }}" class="mt-3 inline-block text-caption font-semibold text-accent transition hover:text-accent-strong">Show all notifications</a>
                    @endif
                </li>
            @endforelse
        </ul>

        @if ($notifications->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-hairline px-4 py-3 sm:px-5">
                <p class="text-meta text-ink-tertiary">Page {{ $notifications->currentPage() }} of {{ $notifications->lastPage() }}</p>
                <x-pagination-pills :paginator="$notifications" />
            </div>
        @endif
    </section>
</div>
@endsection
