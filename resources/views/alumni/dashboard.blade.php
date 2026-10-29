@extends('layouts.app')

@section('title', 'My dashboard')

@section('content')
<div class="mx-auto w-full max-w-[1440px] px-5 pb-16 pt-8 sm:px-8 xl:px-20">
    <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6">
        <h1 class="text-display font-semibold">Dashboard</h1>
        @if ($graduate?->program_code || $graduate?->batch_year)
            <p class="flex flex-wrap items-center gap-x-3 text-body text-ink-tertiary">
                @if ($graduate?->program_code)<span>{{ $graduate->program_code }}</span>@endif
                @if ($graduate?->batch_year)<span>Batch {{ $graduate->batch_year }}</span>@endif
            </p>
        @endif
    </div>

    <div class="mt-6 grid items-start gap-6 xl:grid-cols-[264px_minmax(0,624fr)_344px]">
        @php($currentEmployment = $profile?->currentEmployment)

        <aside aria-label="Your alumni profile" class="panel h-fit p-6 xl:col-start-1 xl:row-start-1">
            @if ($profile?->profile_photo_path)
                <img src="{{ route('profile.files', 'photo') }}" alt="Profile photo of {{ auth()->user()->name }}" class="size-[88px] rounded-full object-cover" loading="lazy" decoding="async">
            @else
                <span aria-hidden="true" class="grid size-[88px] place-items-center rounded-full bg-canvas text-headline font-semibold text-ink-tertiary">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            @endif

            <h2 class="mt-5 text-subhead font-semibold wrap-break-word">{{ auth()->user()->name }}</h2>
            <p class="mt-2 text-body text-ink-secondary wrap-break-word">{{ $currentEmployment?->job_title ?: 'Alumni' }}</p>
            @if ($currentEmployment?->employer)
                <p class="mt-0.5 text-caption text-ink-tertiary wrap-break-word">{{ $currentEmployment->employer }}</p>
            @endif
            @if ($currentEmployment?->employment_status && in_array($currentEmployment->employment_status, ['unemployed', 'studying', 'other'], true))
                <p class="mt-1 text-meta text-ink-tertiary">{{ \App\Models\Employment::EMPLOYMENT_STATUSES[$currentEmployment->employment_status] ?? '' }}</p>
            @endif

            <dl class="mt-6 space-y-4 border-t border-hairline pt-5">
                <div>
                    <dt class="text-meta text-ink-tertiary">Program</dt>
                    <dd class="mt-1 text-small font-semibold wrap-break-word">{{ $graduate?->program_code ?: 'Not listed' }}</dd>
                </div>
                <div>
                    <dt class="text-meta text-ink-tertiary">Graduation year</dt>
                    <dd class="mt-1 text-small font-semibold">{{ $graduate?->batch_year ?: 'Not listed' }}</dd>
                </div>
                <div>
                    <dt class="text-meta text-ink-tertiary">Graduate record</dt>
                    <dd class="mt-1 text-small font-semibold">{{ $graduate ? 'Verified' : 'Pending' }}</dd>
                </div>
                <div>
                    <dt class="text-meta text-ink-tertiary">Profile</dt>
                    <dd class="mt-1 text-small font-semibold">{{ $profile?->completed_at ? 'Complete' : 'In progress' }}</dd>
                </div>
            </dl>

            <a href="{{ route('profile.edit') }}" class="mt-6 flex h-11 items-center justify-center rounded-full bg-accent text-caption font-semibold text-white transition hover:bg-accent-strong">Edit profile</a>
            <a href="{{ route('profile.edit') }}#employment-history" class="mt-3 flex h-11 items-center justify-center text-caption font-semibold text-accent transition hover:text-accent-strong">Update career</a>
        </aside>

        <aside aria-labelledby="announcements-heading" class="panel order-last p-6 xl:order-none xl:col-start-3 xl:row-start-1">
            <h2 id="announcements-heading" class="text-title font-semibold">Announcements</h2>

            <div class="mt-3 border-t border-hairline">
                @forelse ($announcements->take(2) as $announcement)
                    <article class="border-b border-divider-soft py-5 last:border-0 last:pb-0">
                        <p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-meta text-ink-tertiary">
                            <span>{{ $announcement->published_at->format('F j, Y') }}</span>
                            <span>{{ \App\Models\Announcement::CATEGORIES[$announcement->category] ?? ucfirst($announcement->category) }}</span>
                            @if ($announcement->is_pinned)
                                <span class="inline-flex items-center gap-1 text-ink-secondary">
                                    <svg aria-hidden="true" class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 17v5"/><path d="M9 10.8V4h6v6.8l2.5 3.2h-11L9 10.8Z"/></svg>Pinned
                                </span>
                            @endif
                        </p>
                        <h3 class="mt-2 text-caption font-semibold text-ink wrap-break-word">{{ $announcement->title }}</h3>
                        <p class="mt-2 line-clamp-10 whitespace-pre-line text-caption text-ink-secondary wrap-break-word">{{ $announcement->body }}</p>
                    </article>
                @empty
                    <p class="py-8 text-caption text-ink-tertiary">There are no announcements right now.</p>
                @endforelse
            </div>
        </aside>

        <div class="space-y-6 xl:col-start-2 xl:row-start-1">
            <section aria-labelledby="surveys-heading" class="panel p-6">
                <div class="flex flex-wrap items-baseline justify-between gap-3">
                    <h2 id="surveys-heading" class="text-title font-semibold">Surveys</h2>
                    <a href="{{ route('surveys.index') }}" class="inline-flex items-center gap-1 text-caption font-semibold text-accent transition hover:text-accent-strong">All surveys<x-chevron /></a>
                </div>

                <div class="mt-3 border-t border-hairline">
                    @forelse ($surveys->take(2) as $survey)
                        <article class="flex flex-wrap items-start justify-between gap-4 border-b border-divider-soft py-5 last:border-0 last:pb-0">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-caption font-semibold text-ink wrap-break-word">{{ $survey->title }}</h3>
                                <p class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-meta text-ink-tertiary">
                                    <span>{{ count($survey->questions) }} questions</span>
                                    @if ($survey->closes_at)<span>Closes {{ $survey->closes_at->format('F j, Y') }}</span>@endif
                                </p>
                                @if ($survey->description)
                                    <p class="mt-2 line-clamp-2 text-caption text-ink-secondary wrap-break-word">{{ $survey->description }}</p>
                                @endif
                            </div>
                            <a href="{{ route('surveys.show', $survey) }}" class="inline-flex shrink-0 items-center gap-1 text-caption font-semibold text-accent transition hover:text-accent-strong">Respond<x-chevron /></a>
                        </article>
                    @empty
                        <p class="py-8 text-caption text-ink-tertiary">There are no surveys available for you right now.</p>
                    @endforelse
                </div>
            </section>

            <section aria-labelledby="jobs-heading" class="panel p-6">
                <div class="flex flex-wrap items-baseline justify-between gap-3">
                    <h2 id="jobs-heading" class="text-title font-semibold">Jobs</h2>
                    <a href="{{ route('jobs.index') }}" class="inline-flex items-center gap-1 text-caption font-semibold text-accent transition hover:text-accent-strong">Browse all jobs<x-chevron /></a>
                </div>

                <div class="mt-3 border-t border-hairline">
                    @forelse ($jobs as $job)
                        <article class="flex flex-wrap items-start justify-between gap-4 border-b border-divider-soft py-4 last:border-0 last:pb-0">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-caption font-semibold text-ink wrap-break-word">{{ $job->title }}</h3>
                                <p class="mt-1 text-caption text-ink-secondary wrap-break-word">{{ $job->company }}</p>
                                <p class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-meta text-ink-tertiary">
                                    <span>{{ $job->employment_type }}</span>
                                    <span>{{ $job->location }}</span>
                                    @if ($job->application_deadline)<span>Apply by {{ $job->application_deadline->format('F j, Y') }}</span>@endif
                                </p>
                            </div>
                            <a href="{{ route('jobs.show', $job) }}" class="inline-flex shrink-0 items-center gap-1 text-caption font-semibold text-accent transition hover:text-accent-strong">View role<x-chevron /></a>
                        </article>
                    @empty
                        <p class="py-8 text-caption text-ink-tertiary">No job opportunities are available right now.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
