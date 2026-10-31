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
@php($currentEmployment = $profile?->currentEmployment)
@php($statusLabel = $statusLabels[$application->status] ?? ucfirst(str_replace('_', ' ', $application->status)))
@php($isRejected = $application->status === 'rejected')

<div class="mx-auto w-full max-w-[1440px] px-5 pb-16 pt-8 sm:px-8 xl:px-20">
    <x-back-link :href="route('admin.jobs.applications.index', $job)" label="All applicants" />

    <div class="mt-5 border-b border-hairline pb-6">
        <h1 class="text-display font-semibold">Applicant profile</h1>
        <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-body text-ink-tertiary">
            <span class="text-ink-secondary">{{ $displayName }}</span>
            <span>{{ $job->title }}</span>
            <span>{{ $job->company }}</span>
        </p>
    </div>

    <div class="mt-6 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <main class="grid gap-6">
            @if ($profile)
                <section class="panel" aria-labelledby="professional-profile">
                    <div class="border-b border-hairline px-5 py-4 sm:px-6">
                        <h2 id="professional-profile" class="text-title font-semibold">Professional profile</h2>
                    </div>

                    <dl class="grid gap-x-6 gap-y-5 p-5 sm:grid-cols-2 sm:p-6">
                        <div>
                            <dt class="text-meta text-ink-tertiary">Current position</dt>
                            <dd class="mt-1 text-small font-semibold text-ink">{{ $currentEmployment?->job_title ?: 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-meta text-ink-tertiary">Employer</dt>
                            <dd class="mt-1 text-small font-semibold text-ink">{{ $currentEmployment?->employer ?: 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-meta text-ink-tertiary">Industry</dt>
                            <dd class="mt-1 text-small font-semibold text-ink">{{ $currentEmployment?->industry ?: 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-meta text-ink-tertiary">Employment status</dt>
                            <dd class="mt-1 text-small font-semibold text-ink">{{ $currentEmployment?->employment_status ? (\App\Models\Employment::EMPLOYMENT_STATUSES[$currentEmployment->employment_status] ?? ucwords(str_replace('_', ' ', $currentEmployment->employment_status))) : 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-meta text-ink-tertiary">Location</dt>
                            <dd class="mt-1 text-small font-semibold text-ink">{{ $profile->city ?: 'Not provided' }}{{ $profile->city && $profile->country ? ', ' : '' }}{{ $profile->country ?: '' }}</dd>
                        </div>
                        <div>
                            <dt class="text-meta text-ink-tertiary">LinkedIn</dt>
                            <dd class="mt-1 break-all text-small text-ink-secondary">{{ $profile->linkedin_url ?: 'Not provided' }}</dd>
                        </div>
                    </dl>

                    @if ($profile->bio)
                        <div class="border-t border-hairline px-5 py-4 sm:px-6">
                            <h3 class="text-meta text-ink-tertiary">About</h3>
                            <p class="mt-2 max-w-[68ch] whitespace-pre-line text-body text-ink-secondary">{{ $profile->bio }}</p>
                        </div>
                    @endif
                </section>
            @else
                <section class="panel p-5 sm:p-6">
                    <h2 class="text-title font-semibold">Professional profile</h2>
                    <p class="mt-2 text-caption text-ink-tertiary">This applicant has not completed a profile yet.</p>
                </section>
            @endif

            <section class="panel" aria-labelledby="employment-history-heading">
                <div class="border-b border-hairline px-5 py-4 sm:px-6">
                    <h2 id="employment-history-heading" class="text-title font-semibold">Employment history</h2>
                </div>

                @if ($profile?->employments->isNotEmpty())
                    <ul class="divide-y divide-divider-soft">
                        @foreach ($profile->employments as $employment)
                            <li class="px-5 py-4 sm:px-6">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-caption font-semibold text-ink wrap-break-word">{{ $employment->job_title }}</p>
                                        <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-meta text-ink-tertiary">
                                            <span class="truncate">{{ $employment->employer }}</span>
                                            @if ($employment->location)<span class="truncate">{{ $employment->location }}</span>@endif
                                            @if ($employment->country)<span>{{ $employment->country }}</span>@endif
                                        </p>
                                    </div>

                                    <div class="flex shrink-0 flex-wrap items-center gap-2 text-meta text-ink-tertiary">
                                        <span class="tabular-nums">{{ $employment->start_date->format('M Y') }} – {{ $employment->is_current ? 'Present' : ($employment->end_date?->format('M Y') ?? 'Date not provided') }}</span>
                                        <span class="rounded-full px-2 py-0.5 text-micro font-semibold {{ $employment->is_current ? 'bg-mint text-accent' : 'bg-canvas text-ink-tertiary' }}">{{ $employment->is_current ? 'Current' : 'Past' }}</span>
                                    </div>
                                </div>

                                @if ($employment->description)
                                    <p class="mt-2 max-w-[68ch] whitespace-pre-line text-caption text-ink-secondary">{{ $employment->description }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="px-5 py-5 text-caption text-ink-tertiary sm:px-6">No employment history has been added.</p>
                @endif
            </section>

            <section class="panel" aria-labelledby="cover-letter-heading">
                <div class="border-b border-hairline px-5 py-4 sm:px-6">
                    <h2 id="cover-letter-heading" class="text-title font-semibold">Cover letter</h2>
                </div>
                <p class="max-w-[68ch] whitespace-pre-line px-5 py-4 text-body text-ink-secondary sm:px-6">{{ $application->cover_letter ?: 'No cover letter submitted.' }}</p>
            </section>
        </main>

        <aside class="grid gap-6 lg:sticky lg:top-6">
            <section class="panel p-5" aria-labelledby="applicant-summary">
                <h2 id="applicant-summary" class="sr-only">Applicant</h2>

                <div class="flex items-center gap-4">
                    <span aria-hidden="true" class="grid size-14 shrink-0 place-items-center rounded-full bg-canvas text-subhead font-semibold text-ink-tertiary">{{ $initial ?: 'A' }}</span>
                    <div class="min-w-0">
                        <p class="text-small font-semibold wrap-break-word">{{ $displayName }}</p>
                        <p class="mt-1 truncate text-meta text-ink-tertiary">{{ $currentEmployment?->job_title ?: 'Professional role not listed' }}</p>
                    </div>
                </div>

                <p class="mt-4">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-micro font-semibold {{ $isRejected ? 'bg-danger/10 text-danger' : 'bg-mint text-accent' }}">{{ $statusLabel }}</span>
                </p>
            </section>

            <section class="panel p-5" aria-labelledby="application-decision">
                <h2 id="application-decision" class="text-title font-semibold">Application decision</h2>

                <form method="POST" action="{{ route('admin.jobs.applications.update', [$job, $application]) }}" class="mt-4 grid gap-4">
                    @csrf
                    @method('PATCH')

                    <div class="field">
                        <label for="status">Application status</label>
                        <select id="status" name="status">
                            @foreach ($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected($application->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="flex h-11 items-center justify-center rounded-full bg-accent text-caption font-semibold text-white transition hover:bg-accent-strong">Update status</button>
                </form>

                <p class="mt-4 border-t border-divider-soft pt-3 text-meta text-ink-tertiary">Applied {{ $application->submitted_at->format('j F Y, H:i') }}</p>
            </section>

            <section class="panel" aria-labelledby="contact-details">
                <div class="border-b border-hairline px-5 py-4">
                    <h2 id="contact-details" class="text-title font-semibold">Contact & personal details</h2>
                </div>

                <dl class="grid gap-4 p-5">
                    <div>
                        <dt class="text-meta text-ink-tertiary">Email</dt>
                        <dd class="mt-1 break-all text-caption text-ink">{{ $application->user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-meta text-ink-tertiary">Phone</dt>
                        <dd class="mt-1 text-caption text-ink">{{ $profile?->phone ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-meta text-ink-tertiary">Student number</dt>
                        <dd class="mt-1 text-caption tabular-nums text-ink">{{ $application->user->student_number ?? $graduate?->student_number ?? 'Not available' }}</dd>
                    </div>
                    <div>
                        <dt class="text-meta text-ink-tertiary">Username</dt>
                        <dd class="mt-1 text-caption text-ink">{{ $application->user->username ?: 'Not available' }}</dd>
                    </div>
                    <div>
                        <dt class="text-meta text-ink-tertiary">Current address</dt>
                        <dd class="mt-1 whitespace-pre-line text-caption text-ink">{{ $profile?->current_address ?: trim(($profile?->city ?: '').', '.($profile?->country ?: ''), ', ') ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-meta text-ink-tertiary">Permanent address</dt>
                        <dd class="mt-1 whitespace-pre-line text-caption text-ink">{{ $profile?->permanent_address ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-meta text-ink-tertiary">Birthdate</dt>
                        <dd class="mt-1 text-caption text-ink">{{ $profile?->birthdate?->format('j F Y') ?? 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-meta text-ink-tertiary">Sex / civil status</dt>
                        <dd class="mt-1 flex flex-wrap items-center gap-x-3 text-caption text-ink">
                            <span>{{ $profile?->sex ? ucwords(str_replace('_', ' ', $profile->sex)) : 'Not provided' }}</span>
                            <span>{{ $profile?->civil_status ? ucfirst($profile->civil_status) : 'Not provided' }}</span>
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="panel" aria-labelledby="academic-details">
                <div class="border-b border-hairline px-5 py-4">
                    <h2 id="academic-details" class="text-title font-semibold">Academic details</h2>
                </div>

                <dl class="grid gap-4 p-5">
                    <div>
                        <dt class="text-meta text-ink-tertiary">Program</dt>
                        <dd class="mt-1 text-caption text-ink">{{ $graduate?->program_code ?: 'Not listed' }}</dd>
                    </div>
                    <div>
                        <dt class="text-meta text-ink-tertiary">Graduation year</dt>
                        <dd class="mt-1 text-caption tabular-nums text-ink">{{ $graduate?->batch_year ?: 'Not listed' }}</dd>
                    </div>
                    <div>
                        <dt class="text-meta text-ink-tertiary">Latin honor</dt>
                        <dd class="mt-1 text-caption text-ink">{{ $graduate?->latin_honor ? ucwords(str_replace('_', ' ', $graduate->latin_honor)) : 'Not listed' }}</dd>
                    </div>
                    <div>
                        <dt class="text-meta text-ink-tertiary">Profile status</dt>
                        <dd class="mt-1 text-caption text-ink">{{ $profile?->completed_at ? 'Complete' : 'Incomplete' }}</dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>
</div>
@endsection
