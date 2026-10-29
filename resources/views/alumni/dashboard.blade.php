@extends('layouts.app')

@section('title', 'My dashboard')

@section('content')
<div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:py-10">
    <div class="grid items-start gap-6 lg:grid-cols-[minmax(15rem,0.8fr)_minmax(0,1.8fr)] xl:gap-8">
        <aside class="lg:sticky lg:top-4" aria-label="Your alumni profile">
            <section class="overflow-hidden rounded-md border border-line bg-surface">
                <div class="h-16 bg-forest"></div>
                <div class="px-5 pb-5">
                    @if ($profile?->profile_photo_path)
                        <img src="{{ route('profile.files', 'photo') }}" alt="Profile photo of {{ auth()->user()->name }}" class="-mt-9 size-20 rounded-full border-4 border-surface object-cover">
                    @else
                        <div aria-hidden="true" class="-mt-9 grid size-20 place-items-center rounded-full border-4 border-surface bg-surface-raised text-2xl font-semibold text-ink">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    @endif
                    <h2 class="mt-3 wrap-break-word text-xl font-semibold">{{ auth()->user()->name }}</h2>
                    <p class="mt-1 text-sm text-muted">{{ $profile?->job_title ?: 'Alumni' }}@if ($profile?->employer) · {{ $profile->employer }}@endif</p>

                    <dl class="mt-5 grid gap-3 border-t border-line pt-4 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-muted">Program</dt><dd class="text-right font-medium">{{ $graduate?->program_code ?: 'Not listed' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-muted">Graduation year</dt><dd class="text-right font-medium">{{ $graduate?->batch_year ?: 'Not listed' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-muted">Graduate record</dt><dd class="text-right font-medium">{{ $graduate ? 'Verified' : 'Pending' }}</dd></div>
                    </dl>

                    <div class="mt-5 border-t border-line pt-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted">Profile status</p>
                        <p class="mt-1 text-sm font-medium">{{ $profile?->completed_at ? 'Profile complete' : 'Profile in progress' }}</p>
                    </div>

                    <nav aria-label="Profile actions" class="mt-5 grid gap-2">
                        <a href="{{ route('profile.edit') }}" class="inline-flex min-h-10 items-center justify-center rounded-md bg-forest px-4 py-2 text-sm font-semibold text-white transition hover:bg-forest-dark">Edit profile</a>
                        <a href="{{ route('profile.edit') }}#employment-history" class="inline-flex min-h-10 items-center justify-center rounded-md border border-line px-4 py-2 text-sm font-medium text-ink transition hover:bg-surface-raised">Update career</a>
                    </nav>
                </div>
            </section>
        </aside>

        <div class="min-w-0">
            <header class="mb-6 border-b border-line pb-5">
                <p class="eyebrow">Alumni network</p>
                <h1 class="font-display mt-2 text-3xl font-semibold">Your alumni feed</h1>
                <p class="mt-2 text-sm text-muted">Surveys, opportunities, and updates selected for the alumni community.</p>
            </header>

            <section aria-labelledby="announcements-heading">
                <div class="mb-3"><p class="eyebrow">From the college</p><h2 id="announcements-heading" class="mt-1 text-xl font-semibold">Announcements</h2></div>
                <div class="grid gap-3">
                    @forelse ($announcements as $announcement)
                        <article class="border-l-4 border-terracotta bg-surface px-5 py-5 sm:px-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-xs font-semibold uppercase tracking-wider text-forest">{{ $announcement->published_at->format('F j, Y') }}</p>
                                <span class="bg-surface-raised px-2 py-0.5 text-[10px] font-medium text-muted">{{ \App\Models\Announcement::CATEGORIES[$announcement->category] ?? ucfirst($announcement->category) }}</span>
                                @if ($announcement->is_pinned)<span class="text-[10px] font-semibold text-forest">Pinned</span>@endif
                            </div>
                            <h3 class="mt-2 text-lg font-semibold">{{ $announcement->title }}</h3>
                            <p class="mt-2 whitespace-pre-line wrap-break-word text-sm leading-6 text-muted">{{ $announcement->body }}</p>
                        </article>
                    @empty
                        <p class="border border-dashed border-line px-5 py-8 text-center text-sm text-muted">There are no announcements right now.</p>
                    @endforelse
                </div>
            </section>

            <section class="mt-8" aria-labelledby="surveys-heading">
                <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                    <div><p class="eyebrow">Your voice matters</p><h2 id="surveys-heading" class="mt-1 text-xl font-semibold">Surveys</h2></div>
                    <a href="{{ route('surveys.index') }}" class="text-sm font-semibold text-forest underline underline-offset-4">All surveys</a>
                </div>
                <div class="grid gap-3">
                    @forelse ($surveys as $survey)
                        <article class="border border-line bg-surface p-5 sm:p-6">
                            <p class="text-xs font-semibold uppercase tracking-wider text-terracotta">{{ count($survey->questions) }} questions{{ $survey->closes_at ? ' · Closes '.$survey->closes_at->format('M j, Y') : '' }}</p>
                            <h3 class="mt-2 text-lg font-semibold">{{ $survey->title }}</h3>
                            @if ($survey->description)<p class="mt-2 text-sm leading-6 text-muted">{{ $survey->description }}</p>@endif
                            <a class="mt-4 inline-flex text-sm font-semibold text-forest underline underline-offset-4" href="{{ route('surveys.show', $survey) }}">Respond <span aria-hidden="true" class="ml-1">&#8594;</span></a>
                        </article>
                    @empty
                        <p class="border border-dashed border-line px-5 py-8 text-center text-sm text-muted">There are no surveys available for you right now.</p>
                    @endforelse
                </div>
            </section>

            <section class="mt-8" aria-labelledby="jobs-heading">
                <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                    <div><p class="eyebrow">Career opportunities</p><h2 id="jobs-heading" class="mt-1 text-xl font-semibold">Jobs</h2></div>
                    <a href="{{ route('jobs.index') }}" class="text-sm font-semibold text-forest underline underline-offset-4">Browse all jobs</a>
                </div>
                <div class="grid gap-3">
                    @forelse ($jobs as $job)
                        <article class="border border-line bg-surface p-5 sm:p-6">
                            <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs font-semibold uppercase tracking-wider text-terracotta"><span>{{ $job->employment_type }}</span><span>{{ $job->location }}</span></div>
                            <h3 class="mt-2 text-lg font-semibold">{{ $job->title }}</h3>
                            <p class="mt-1 text-sm text-muted">{{ $job->company }}@if ($job->application_deadline) · Apply by {{ $job->application_deadline->format('M j, Y') }}@endif</p>
                            <a href="{{ route('jobs.show', $job) }}" class="mt-4 inline-flex text-sm font-semibold text-forest underline underline-offset-4">View role <span aria-hidden="true" class="ml-1">&#8594;</span></a>
                        </article>
                    @empty
                        <p class="border border-dashed border-line px-5 py-8 text-center text-sm text-muted">No job opportunities are available right now.</p>
                    @endforelse
                </div>
            </section>

        </div>
    </div>
</div>
@endsection
