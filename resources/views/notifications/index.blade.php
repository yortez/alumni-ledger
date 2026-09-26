@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="mx-auto max-w-4xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="mb-8 border-b border-line pb-6">
        <p class="eyebrow">Inbox</p>
        <h1 class="mt-3 font-display text-4xl sm:text-5xl">Notifications</h1>
    </div>

    <div class="space-y-4">
        @forelse ($notifications as $notification)
            <article class="border border-line bg-white p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-forest">{{ ucfirst($notification->data['type'] ?? 'general') }}</p>
                        <h2 class="mt-2 font-display text-2xl">{{ $notification->data['title'] ?? 'Update' }}</h2>
                    </div>
                    @if ($notification->read_at === null)
                        <span class="rounded-full border border-forest bg-mint px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-forest">New</span>
                    @endif
                </div>

                <p class="mt-4 whitespace-pre-line text-sm leading-6 text-muted">
                    {{ $notification->data['message'] ?? 'You have a new update.' }}
                </p>

                @if (($notification->data['announcement_id'] ?? null) !== null)
                    <a href="{{ route('dashboard') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-forest underline underline-offset-4">
                        View dashboard
                    </a>
                @elseif (($notification->data['survey_id'] ?? null) !== null)
                    <a href="{{ route('surveys.index') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-forest underline underline-offset-4">
                        View survey
                    </a>
                @elseif (($notification->data['job_id'] ?? null) !== null)
                    <a href="{{ route('jobs.index') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-forest underline underline-offset-4">
                        View jobs
                    </a>
                @endif

                <p class="mt-4 text-xs text-muted">
                    {{ $notification->created_at->format('F j, Y g:i A') }}
                </p>
            </article>
        @empty
            <div class="border border-dashed border-line bg-white px-5 py-12 text-center text-sm text-muted">
                You do not have any notifications yet.
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="mt-6">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
