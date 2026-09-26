@extends('layouts.app')

@section('title', 'Job applications')

@section('content')
<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:py-14">
    <a href="{{ route('admin.jobs.index') }}" class="text-sm font-semibold text-forest underline underline-offset-4">&#8592; All job postings</a>
    <div class="mt-8 border-b border-line pb-8">
        <p class="eyebrow">Applications</p>
        <h1 class="mt-3 font-display text-4xl sm:text-5xl">{{ $job->title }}</h1>
        <p class="mt-2 text-sm text-muted">{{ $job->company }} · {{ $applications->total() }} applications</p>
    </div>
    <div class="mt-8 grid gap-3">
        @forelse ($applications as $application)
            <article class="border border-line bg-white p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="font-display text-2xl">{{ $application->user->name }}</h2>
                        <p class="mt-1 text-sm text-muted">{{ $application->user->username }} · {{ $application->user->email }}</p>
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-forest">{{ $statusLabels[$application->status] ?? ucfirst(str_replace('_', ' ', $application->status)) }}</span>
                </div>

                @if ($application->cover_letter)
                    <p class="mt-5 whitespace-pre-line border-t border-line pt-5 text-sm leading-6 text-muted">{{ $application->cover_letter }}</p>
                @endif

                <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-line pt-5">
                    <a href="{{ route('admin.jobs.applications.show', [$job, $application]) }}" class="text-sm font-semibold text-forest underline underline-offset-4">View profile</a>
                    <form method="POST" action="{{ route('admin.jobs.applications.update', [$job, $application]) }}">@csrf @method('PATCH')
                        <input type="hidden" name="status" value="reviewing">
                        <button type="submit" class="border border-line px-3 py-2 text-xs font-semibold uppercase tracking-wide text-ink transition hover:border-forest hover:text-forest">Under review</button>
                    </form>
                    <form method="POST" action="{{ route('admin.jobs.applications.update', [$job, $application]) }}">@csrf @method('PATCH')
                        <input type="hidden" name="status" value="interview_scheduled">
                        <button type="submit" class="border border-line px-3 py-2 text-xs font-semibold uppercase tracking-wide text-ink transition hover:border-forest hover:text-forest">Schedule interview</button>
                    </form>
                    <form method="POST" action="{{ route('admin.jobs.applications.update', [$job, $application]) }}">@csrf @method('PATCH')
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="border border-forest bg-mint px-3 py-2 text-xs font-semibold uppercase tracking-wide text-forest transition hover:bg-forest hover:text-white">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('admin.jobs.applications.update', [$job, $application]) }}">@csrf @method('PATCH')
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="border border-terracotta px-3 py-2 text-xs font-semibold uppercase tracking-wide text-terracotta transition hover:bg-terracotta hover:text-white">Reject</button>
                    </form>
                </div>

                <p class="mt-4 text-xs text-muted">Submitted {{ $application->submitted_at->format('F j, Y g:i A') }}</p>
            </article>
        @empty
            <p class="border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No applications yet.</p>
        @endforelse
    </div>
    <div class="mt-5">{{ $applications->links() }}</div>
</div>
@endsection