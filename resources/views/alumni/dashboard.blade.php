@extends('layouts.app')

@section('title', 'My dashboard')

@section('content')
<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:py-14">
    <section class="dashboard-welcome flex flex-col justify-between gap-5 rounded-md border px-5 py-4 sm:flex-row sm:items-center sm:px-6" aria-label="Alumni welcome">
        <div class="min-w-0">
            <h1 class="font-display text-lg font-bold sm:text-xl">Welcome back, {{ strtoupper(auth()->user()->name) }}! <span aria-hidden="true">&#128075;</span></h1>
            <p class="mt-1 flex flex-wrap items-center gap-x-1.5 text-xs leading-5 text-white/75 sm:text-sm">
                <span>{{ $graduate?->program_code ?: 'Program not listed' }}</span>
                <span aria-hidden="true">&bull;</span>
                <span>Batch {{ $graduate?->batch_year ?: 'not listed' }}</span>
                <span aria-hidden="true">&bull;</span>
                <span class="font-semibold text-emerald-300">{{ $graduate ? 'Verified' : 'Verification pending' }}</span>
            </p>
        </div>
        <nav aria-label="Profile actions" class="flex shrink-0 flex-wrap gap-2">
            <a href="{{ route('profile.edit') }}" class="inline-flex min-h-9 items-center gap-2 rounded-md border border-white/30 px-3 py-2 text-xs font-medium text-white transition hover:bg-white/10">
                <svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4" /><path d="M5 21a7 7 0 0 1 14 0" /></svg>
                My Profile
            </a>
            <a href="{{ route('profile.edit') }}#employment-history" class="inline-flex min-h-9 items-center gap-2 rounded-md bg-forest px-3 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark">
                <svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="14" rx="2" /><path d="M8 7V4h8v3M3 12h18M10 12v2h4v-2" /></svg>
                Update Career
            </a>
        </nav>
    </section>
    @if ($announcements->isNotEmpty())
        <section class="mt-8" aria-labelledby="announcements-heading">
            <div class="mb-4 flex items-end justify-between gap-4"><div><p class="eyebrow">From the college</p><h2 id="announcements-heading" class="font-display mt-2 text-3xl">Announcements</h2></div><span class="text-xs uppercase tracking-wider text-muted">{{ $announcements->count() }} new</span></div>
            <div class="grid gap-3">
                @foreach ($announcements as $announcement)
                    <article class="rounded-r-md border-l-4 border-terracotta bg-white px-5 py-5 sm:px-7">
                        <div class="flex flex-wrap items-center gap-2"><p class="text-xs font-semibold uppercase tracking-wider text-forest">{{ $announcement->published_at->format('F j, Y') }}</p><span class="rounded-full bg-surface-raised px-2 py-0.5 text-[10px] font-medium text-muted">{{ \App\Models\Announcement::CATEGORIES[$announcement->category] ?? ucfirst($announcement->category) }}</span>@if ($announcement->is_pinned)<span class="text-[10px] font-semibold text-forest">Pinned</span>@endif</div>
                        <h3 class="mt-2 text-xl font-semibold tracking-tight">{{ $announcement->title }}</h3>
                        <p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-muted">{{ $announcement->body }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
    @if ($surveys->isNotEmpty())
        <section class="mt-8" aria-labelledby="surveys-heading">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-4"><div><p class="eyebrow">Your voice matters</p><h2 id="surveys-heading" class="font-display mt-2 text-3xl">Surveys for you</h2></div><a href="{{ route('surveys.index') }}" class="text-sm font-semibold text-forest underline underline-offset-4">All available surveys</a></div>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($surveys as $survey)
                    <article class="flex flex-col justify-between gap-5 rounded-md border border-line bg-white p-5"><div><p class="text-xs font-semibold uppercase tracking-wider text-terracotta">{{ count($survey->questions) }} questions</p><h3 class="mt-2 text-xl font-semibold tracking-tight">{{ $survey->title }}</h3>@if ($survey->description)<p class="mt-2 text-sm leading-6 text-muted">{{ $survey->description }}</p>@endif</div><a class="text-sm font-semibold text-forest underline underline-offset-4" href="{{ route('surveys.show', $survey) }}">Respond <span aria-hidden="true">&#8594;</span></a></article>
                @endforeach
            </div>
        </section>
    @endif
    <section class="mt-8 rounded-md border border-line bg-forest p-6 text-white sm:p-8" aria-labelledby="jobs-heading"><div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end"><div><p class="text-xs font-semibold uppercase tracking-wider text-mint">Alumni network</p><h2 id="jobs-heading" class="mt-2 text-2xl font-semibold tracking-tight">Find your next opportunity.</h2><p class="mt-3 max-w-xl text-sm leading-6 text-white/75">Explore roles shared with the alumni community and connect your experience to what comes next.</p></div><a href="{{ route('jobs.index') }}" class="inline-flex items-center justify-between gap-8 rounded-md bg-white px-5 py-3 text-sm font-semibold text-forest transition hover:bg-mint">Browse jobs <span aria-hidden="true">&#8599;</span></a></div></section>
    <div class="mt-8 grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
        <section class="border border-line bg-white p-6 sm:p-8"><div class="flex items-start justify-between gap-4"><div><p class="eyebrow">Your record</p><h2 class="font-display mt-2 text-2xl">{{ auth()->user()->username }}</h2></div><span class="bg-mint px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-forest">Verified graduate</span></div><dl class="mt-8 grid gap-x-8 gap-y-5 border-t border-line pt-6 sm:grid-cols-2"><div><dt class="text-xs uppercase tracking-wider text-muted">Student number</dt><dd class="mt-1 font-medium">{{ auth()->user()->student_number }}</dd></div><div><dt class="text-xs uppercase tracking-wider text-muted">Email</dt><dd class="mt-1 break-all font-medium">{{ auth()->user()->email }}</dd></div></dl></section>
        <section class="border border-line bg-white p-6 sm:p-8"><p class="eyebrow">Profile status</p>@if ($profile?->completed_at)<p class="font-display mt-4 text-3xl">Your profile is complete.</p><p class="mt-3 text-sm leading-6 text-muted">Last updated {{ $profile->updated_at->format('F j, Y') }}.</p><a href="{{ route('profile.edit') }}" class="mt-6 inline-flex items-center gap-3 text-sm font-semibold text-forest">Review your details <span aria-hidden="true">&#8594;</span></a>@else<p class="font-display mt-4 text-3xl">Add the next chapter.</p><p class="mt-3 text-sm leading-6 text-muted">Your graduate record is ready. Add your current details to complete your alumni profile.</p><a href="{{ route('profile.edit') }}" class="mt-6 inline-flex items-center gap-3 text-sm font-semibold text-terracotta">Complete your profile <span aria-hidden="true">&#8594;</span></a>@endif</section>
    </div>
</div>
@endsection
