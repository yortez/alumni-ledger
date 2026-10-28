@extends('layouts.app')

@section('title', 'Applicant profile')

@section('content')
@php($displayName = implode(' ', array_filter([
    $profile?->first_name,
    $profile?->middle_name,
    $profile?->last_name,
    $profile?->suffix,
])) ?: $application->user->name)
@php($initial = strtoupper(substr($displayName, 0, 1)))
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-7 lg:py-8">
    <div class="mb-4 flex items-center justify-between gap-4">
        <div>
            <p class="eyebrow">{{ $job->title }} · {{ $job->company }}</p>
            <h1 class="mt-1 text-lg font-semibold">Applicant profile</h1>
        </div>
        <a href="{{ route('admin.jobs.applications.index', $job) }}" class="text-sm font-medium text-forest underline underline-offset-4">Back to applicants</a>
    </div>

    <div class="grid items-start gap-4 lg:grid-cols-[19rem_minmax(0,1fr)]">
        <aside class="grid gap-4">
            <section class="overflow-hidden rounded-md border border-line bg-surface">
                <div class="h-16 bg-forest"></div>
                <div class="px-4 pb-4">
                    <div class="-mt-8 grid size-16 place-items-center rounded-full border-4 border-surface bg-surface-raised text-xl font-semibold text-ink">{{ $initial ?: 'A' }}</div>
                    <h2 class="mt-3 break-words text-lg font-semibold leading-tight">{{ $displayName }}</h2>
                    <p class="mt-1 text-xs text-muted">{{ $profile?->job_title ?: 'Professional role not listed' }}</p>
                    <span class="mt-3 inline-flex rounded-full bg-mint px-2.5 py-1 text-xs font-semibold text-forest">{{ $statusLabels[$application->status] ?? ucfirst(str_replace('_', ' ', $application->status)) }}</span>
                </div>
            </section>

            <section class="overflow-hidden rounded-md border border-line bg-surface">
                <h2 class="border-b border-line px-4 py-3 text-sm font-semibold">Contact & personal details</h2>
                <dl class="grid gap-3 p-4 text-xs">
                    <div><dt class="uppercase tracking-wide text-muted">Email</dt><dd class="mt-1 break-all text-ink">{{ $application->user->email }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Phone</dt><dd class="mt-1 text-ink">{{ $profile?->phone ?: 'Not provided' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Student number</dt><dd class="mt-1 text-ink">{{ $application->user->student_number ?? $graduate?->student_number ?? 'Not available' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Username</dt><dd class="mt-1 text-ink">{{ $application->user->username ?: 'Not available' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Current address</dt><dd class="mt-1 whitespace-pre-line text-ink">{{ $profile?->current_address ?: trim(($profile?->city ?: '').', '.($profile?->country ?: ''), ', ') ?: 'Not provided' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Permanent address</dt><dd class="mt-1 whitespace-pre-line text-ink">{{ $profile?->permanent_address ?: 'Not provided' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Birthdate</dt><dd class="mt-1 text-ink">{{ $profile?->birthdate?->format('M j, Y') ?? 'Not provided' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Sex / civil status</dt><dd class="mt-1 text-ink">{{ $profile?->sex ? ucwords(str_replace('_', ' ', $profile->sex)) : 'Not provided' }} · {{ $profile?->civil_status ? ucfirst($profile->civil_status) : 'Not provided' }}</dd></div>
                </dl>
            </section>

            <section class="overflow-hidden rounded-md border border-line bg-surface">
                <h2 class="border-b border-line px-4 py-3 text-sm font-semibold">Academic details</h2>
                <dl class="grid gap-3 p-4 text-xs">
                    <div><dt class="uppercase tracking-wide text-muted">Program</dt><dd class="mt-1 text-ink">{{ $graduate?->program_code ?: 'Not listed' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Graduation year</dt><dd class="mt-1 text-ink">{{ $graduate?->batch_year ?: 'Not listed' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Latin honor</dt><dd class="mt-1 text-ink">{{ $graduate?->latin_honor ? ucwords(str_replace('_', ' ', $graduate->latin_honor)) : 'Not listed' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Profile status</dt><dd class="mt-1 text-ink">{{ $profile?->completed_at ? 'Complete' : 'Incomplete' }}</dd></div>
                </dl>
            </section>

            <section class="overflow-hidden rounded-md border border-line bg-surface">
                <h2 class="border-b border-line px-4 py-3 text-sm font-semibold">Application decision</h2>
                <form method="POST" action="{{ route('admin.jobs.applications.update', [$job, $application]) }}" class="grid gap-3 p-4">@csrf @method('PATCH')
                    <div class="field">
                        <label for="status">Application status</label>
                        <select id="status" name="status">
                            @foreach ($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected($application->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-forest px-4 py-3 text-sm font-semibold text-white hover:bg-forest-dark">Update status</button>
                </form>
                <p class="border-t border-line px-4 py-3 text-xs text-muted">Applied {{ $application->submitted_at->format('F j, Y g:i A') }}</p>
            </section>
        </aside>

        <main class="grid min-w-0 gap-4">
            @if ($profile)
                <section class="overflow-hidden rounded-md border border-line bg-surface">
                    <header class="border-b border-line px-4 py-3 sm:px-5">
                        <h2 class="text-sm font-semibold">Professional profile</h2>
                    </header>
                    <dl class="grid gap-4 p-4 sm:grid-cols-2 sm:p-5">
                        <div><dt class="text-xs uppercase tracking-wide text-muted">Current position</dt><dd class="mt-1 text-sm font-medium text-ink">{{ $profile->job_title ?: 'Not provided' }}</dd></div>
                        <div><dt class="text-xs uppercase tracking-wide text-muted">Employer</dt><dd class="mt-1 text-sm font-medium text-ink">{{ $profile->employer ?: 'Not provided' }}</dd></div>
                        <div><dt class="text-xs uppercase tracking-wide text-muted">Industry</dt><dd class="mt-1 text-sm font-medium text-ink">{{ $profile->industry ?: 'Not provided' }}</dd></div>
                        <div><dt class="text-xs uppercase tracking-wide text-muted">Employment status</dt><dd class="mt-1 text-sm font-medium text-ink">{{ $profile->employment_status ? ucwords(str_replace('_', ' ', $profile->employment_status)) : 'Not provided' }}</dd></div>
                        <div><dt class="text-xs uppercase tracking-wide text-muted">Location</dt><dd class="mt-1 text-sm font-medium text-ink">{{ $profile->city ?: 'Not provided' }}{{ $profile->city && $profile->country ? ', ' : '' }}{{ $profile->country ?: '' }}</dd></div>
                        <div><dt class="text-xs uppercase tracking-wide text-muted">LinkedIn</dt><dd class="mt-1 break-all text-sm text-ink">{{ $profile->linkedin_url ?: 'Not provided' }}</dd></div>
                    </dl>
                    @if ($profile->bio)
                        <div class="border-t border-line px-4 py-4 sm:px-5">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-muted">About</h3>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-ink">{{ $profile->bio }}</p>
                        </div>
                    @endif
                </section>
            @else
                <section class="rounded-md border border-line bg-surface p-5">
                    <h2 class="text-sm font-semibold">Professional profile</h2>
                    <p class="mt-2 text-sm text-muted">This applicant has not completed a profile yet.</p>
                </section>
            @endif

            <section class="overflow-hidden rounded-md border border-line bg-surface" aria-labelledby="employment-history-heading">
                <header class="border-b border-line px-4 py-3 sm:px-5">
                    <h2 id="employment-history-heading" class="text-sm font-semibold">Employment history</h2>
                </header>
                @if ($profile?->employments->isNotEmpty())
                    <div class="divide-y divide-line px-4 sm:px-5">
                        @foreach ($profile->employments as $employment)
                            <article class="py-4">
                                <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-start">
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-ink">{{ $employment->job_title }}</h3>
                                        <p class="mt-1 text-sm text-muted">{{ $employment->employer }}{{ $employment->location ? ' · '.$employment->location : '' }}{{ $employment->country ? ' · '.$employment->country : '' }}</p>
                                    </div>
                                    <div class="flex shrink-0 flex-wrap items-center gap-2 text-xs text-muted">
                                        <span>{{ $employment->start_date->format('M Y') }} – {{ $employment->is_current ? 'Present' : ($employment->end_date?->format('M Y') ?? 'Date not provided') }}</span>
                                        <span class="rounded-full bg-mint px-2 py-1 font-semibold text-forest">{{ $employment->is_current ? 'Current' : 'Past' }}</span>
                                    </div>
                                </div>
                                @if ($employment->description)
                                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-muted">{{ $employment->description }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="px-4 py-5 text-sm text-muted sm:px-5">No employment history has been added.</p>
                @endif
            </section>

            <section class="overflow-hidden rounded-md border border-line bg-surface">
                <header class="border-b border-line px-4 py-3 sm:px-5">
                    <h2 class="text-sm font-semibold">Cover letter</h2>
                </header>
                <p class="whitespace-pre-line px-4 py-4 text-sm leading-6 text-ink sm:px-5">{{ $application->cover_letter ?: 'No cover letter submitted.' }}</p>
            </section>
        </main>
    </div>
</div>
@endsection
