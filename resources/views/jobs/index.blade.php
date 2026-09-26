@extends('layouts.app')

@section('title', 'Job opportunities')

@section('content')
<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="flex flex-col justify-between gap-6 border-b border-line pb-8 sm:flex-row sm:items-end">
        <div><p class="eyebrow">Alumni network</p><h1 class="font-display mt-3 text-4xl sm:text-5xl">Job opportunities</h1></div>
        <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-forest underline underline-offset-4">Back to dashboard</a>
    </div>
    <div class="mt-8 grid gap-3">
        @forelse ($jobs as $job)
            <article class="flex flex-col justify-between gap-5 border border-line bg-white p-5 sm:flex-row sm:items-center sm:p-6">
                <div class="min-w-0"><div class="flex flex-wrap gap-x-3 gap-y-1 text-xs font-semibold uppercase tracking-wider text-terracotta"><span>{{ $job->employment_type }}</span><span>{{ $job->location }}</span></div><h2 class="mt-2 break-words font-display text-3xl">{{ $job->title }}</h2><p class="mt-1 text-sm text-muted">{{ $job->company }} @if ($job->application_deadline) · Apply by {{ $job->application_deadline->format('F j, Y') }} @endif</p></div>
                <a href="{{ route('jobs.show', $job) }}" class="inline-flex shrink-0 items-center justify-between gap-8 border border-forest px-4 py-3 text-sm font-semibold text-forest hover:bg-mint">View role <span aria-hidden="true">&#8599;</span></a>
            </article>
        @empty
            <p class="border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No job opportunities are available right now.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $jobs->links() }}</div>
</div>
@endsection