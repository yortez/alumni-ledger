@extends('layouts.app')

@section('title', 'Alumni profile')

@section('content')
@php($displayName = implode(' ', array_filter([
    $profile?->first_name ?: $graduate->first_name,
    $profile?->middle_name ?: $graduate->middle_name,
    $profile?->last_name ?: $graduate->last_name,
    $profile?->suffix,
])))
@php($displayName = $displayName ?: $graduate->fullName())
@php($initial = strtoupper(substr($displayName, 0, 1)))
<div class="mx-auto w-full max-w-[1440px] px-5 pb-16 pt-8 sm:px-8 xl:px-20">
    <x-back-link :href="route('admin.graduates.index')" label="All alumni" class="mb-4" />
    <div class="mb-4 flex items-center justify-between gap-4">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="mt-1 text-display font-semibold">Alumni profile</h1>
        </div>
    </div>

    <div class="grid items-start gap-4 lg:grid-cols-[19rem_minmax(0,1fr)]">
        <aside class="grid gap-3">
            <section class="panel overflow-hidden">
                <div class="h-16 bg-forest"></div>
                <div class="px-4 pb-4">
                    @if ($profile?->profile_photo_path)
                        <img src="{{ route('admin.graduates.files', [$graduate, 'photo']) }}" alt="Alumni profile photo" class="-mt-8 size-16 rounded-full border-4 border-surface object-cover">
                    @else
                        <div class="-mt-8 grid size-16 place-items-center rounded-full border-4 border-surface bg-forest text-lg font-bold text-white">{{ $initial ?: 'A' }}</div>
                    @endif
                    <h2 class="mt-3 font-semibold leading-tight">{{ strtoupper($displayName) }}</h2>
                    <p class="mt-1 text-xs text-muted">{{ $graduate->program_code ?: 'Program not listed' }}</p>
                    <p class="mt-1 text-xs text-muted">Batch {{ $graduate->batch_year ?: 'not listed' }}</p>
                    <span class="mt-3 inline-flex rounded-full bg-mint px-2.5 py-1 text-[10px] font-semibold text-forest">{{ $applicant ? 'Verified' : 'Not registered' }}</span>
                </div>
            </section>

            <section class="panel overflow-hidden">
                <h2 class="border-b border-line px-4 py-3 text-sm font-semibold">Contact Information</h2>
                <dl class="grid gap-3 p-4 text-xs">
                    <div><dt class="uppercase tracking-wide text-muted">Email</dt><dd class="mt-1 break-all text-ink">{{ $applicant?->email ?? 'Not available' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Phone</dt><dd class="mt-1 text-ink">{{ $profile?->phone ?: 'Not provided' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Current address</dt><dd class="mt-1 whitespace-pre-line text-ink">{{ $profile?->current_address ?: trim(($profile?->city ?: '').', '.($profile?->country ?: ''), ', ') ?: 'Not provided' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Permanent address</dt><dd class="mt-1 whitespace-pre-line text-ink">{{ $profile?->permanent_address ?: 'Not provided' }}</dd></div>
                </dl>
            </section>

            <section class="panel overflow-hidden">
                <h2 class="border-b border-line px-4 py-3 text-sm font-semibold">Academic Details</h2>
                <dl class="grid gap-3 p-4 text-xs">
                    <div><dt class="uppercase tracking-wide text-muted">Student no.</dt><dd class="mt-1 font-semibold text-ink">{{ $graduate->student_number }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Program</dt><dd class="mt-1 text-ink">{{ $graduate->program_code ?: 'Not listed' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Batch</dt><dd class="mt-1 text-ink">{{ $graduate->batch_year ?: 'Not listed' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Latin honor</dt><dd class="mt-1 text-ink">{{ ucwords(str_replace('_', ' ', $graduate->latin_honor)) }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Birthdate</dt><dd class="mt-1 text-ink">{{ $profile?->birthdate?->format('M j, Y') ?? 'Not provided' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Sex / civil status</dt><dd class="mt-1 text-ink">{{ $profile?->sex ? ucwords(str_replace('_', ' ', $profile->sex)) : 'Not provided' }} · {{ $profile?->civil_status ? ucfirst($profile->civil_status) : 'Not provided' }}</dd></div>
                    <div><dt class="uppercase tracking-wide text-muted">Profile status</dt><dd class="mt-1 text-ink">{{ $profile?->completed_at ? 'Complete' : 'Incomplete' }}</dd></div>
                    <div>
                        <dt class="uppercase tracking-wide text-muted">Diploma / TOR</dt>
                        <dd class="mt-1">@if ($profile?->diploma_document_path)<a href="{{ route('admin.graduates.files', [$graduate, 'diploma']) }}" class="font-medium text-forest underline underline-offset-4">Download document</a>@else<span class="text-ink">Not provided</span>@endif</dd>
                    </div>
                </dl>
            </section>
        </aside>

        <div class="grid min-w-0 gap-4">
            <section class="panel overflow-hidden" aria-labelledby="career-history-heading">
                <header class="flex items-center justify-between gap-3 border-b border-line px-4 py-3 sm:px-5">
                    <h2 id="career-history-heading" class="flex items-center gap-2 text-sm font-semibold"><svg aria-hidden="true" class="size-4 text-info" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="14" rx="2" /><path d="M8 7V4h8v3M3 12h18" /></svg>Career History</h2>
                    <a href="{{ route('admin.graduates.edit', $graduate) }}" class="inline-flex min-h-8 items-center gap-2 rounded-md bg-forest px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-forest-dark"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m16 4 4 4L9 19l-5 1 1-5L16 4zM14 6l4 4" /></svg>Manage</a>
                </header>
                @if ($profile?->employments->isNotEmpty())
                    <div class="divide-y divide-line px-4 sm:px-5">
                        @foreach ($profile->employments as $employment)
                            <article class="py-4">
                                <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-start">
                                    <div class="min-w-0">
                                        <h3 class="font-semibold">{{ $employment->job_title }}</h3>
                                        <p class="mt-1 text-xs text-muted">{{ $employment->employer }}{{ $employment->location ? ' · '.$employment->location : '' }}{{ $employment->country ? ' · '.$employment->country : '' }}</p>
                                    </div>
                                    <div class="flex shrink-0 flex-wrap items-center gap-2 text-xs text-muted">
                                        <span>{{ $employment->start_date->format('M Y') }} – {{ $employment->is_current ? 'Present' : ($employment->end_date?->format('M Y') ?? 'Date not provided') }}</span>
                                        <span class="rounded-full bg-mint px-2 py-1 font-semibold text-forest">{{ $employment->is_current ? 'Current' : 'Past' }}</span>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-muted">{{ \App\Models\Employment::EMPLOYMENT_STATUSES[$employment->employment_status] ?? 'Status not provided' }}{{ $employment->sector ? ' · '.(\App\Models\Employment::SECTORS[$employment->sector] ?? $employment->sector) : '' }}{{ $employment->industry ? ' · '.$employment->industry : '' }}</p>
                                <p class="mt-1 text-xs text-muted">{{ $employment->job_level ? (\App\Models\Employment::JOB_LEVELS[$employment->job_level] ?? $employment->job_level) : 'Level not provided' }}{{ $employment->monthly_income ? ' · '.(\App\Models\Employment::MONTHLY_INCOME_RANGES[$employment->monthly_income] ?? $employment->monthly_income) : '' }}{{ $employment->months_to_employment !== null ? ' · '.$employment->months_to_employment.' months to employment' : '' }}{{ $employment->course_relevance ? ' · '.(\App\Models\Employment::COURSE_RELEVANCE_OPTIONS[$employment->course_relevance] ?? $employment->course_relevance) : '' }}</p>
                                @if ($employment->description)<p class="mt-2 whitespace-pre-line text-xs leading-5 text-muted">{{ $employment->description }}</p>@endif
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="grid min-h-40 place-items-center px-4 py-8 text-center">
                        <div><svg aria-hidden="true" class="mx-auto size-9 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="14" rx="2" /><path d="M8 7V4h8v3M3 12h18M10 12v2h4v-2" /></svg><p class="mt-3 text-sm font-medium text-muted">No employment records</p></div>
                    </div>
                @endif
            </section>

            <section class="panel overflow-hidden" aria-labelledby="survey-responses-heading">
                <header class="border-b border-line px-4 py-3 sm:px-5">
                    <h2 id="survey-responses-heading" class="flex items-center gap-2 text-sm font-semibold"><svg aria-hidden="true" class="size-4 text-info" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 4h8l2 2h3v15H3V6h3l2-2z" /><path d="M8 10h8M8 14h8M8 18h5" /></svg>Survey Responses</h2>
                </header>
                @if ($applicant?->surveyResponses->isNotEmpty())
                    <div class="divide-y divide-line px-4 sm:px-5">
                        @foreach ($applicant->surveyResponses as $response)
                            <article class="flex flex-col justify-between gap-2 py-4 sm:flex-row sm:items-center">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
                                        <div><h3 class="text-sm font-semibold">{{ $response->survey?->title ?? 'Survey' }}</h3><p class="mt-1 text-xs text-muted">Submitted {{ $response->submitted_at?->format('F j, Y') ?? 'date unavailable' }}</p></div>
                                        <span class="w-fit rounded-full bg-mint px-2.5 py-1 text-[10px] font-semibold text-forest">Submitted</span>
                                    </div>
                                    @if ($response->survey)
                                        <dl class="mt-3 grid gap-2">
                                            @foreach ($response->survey->normalizedQuestions() as $questionIndex => $question)
                                                <div class="border-l-2 border-line pl-3">
                                                    @if ($question['section_label'])<p class="text-[10px] font-semibold uppercase tracking-wider text-muted">{{ $question['section_label'] }}</p>@endif
                                                    <dt class="mt-1 text-xs text-muted">{{ $question['prompt'] }}</dt>
                                                    @php($answer = $response->answers[$questionIndex] ?? null)
                                                    <dd class="mt-1 whitespace-pre-line text-sm text-ink">{{ is_array($answer) ? implode(', ', $answer) : ($answer !== null && $answer !== '' ? $answer : 'No response') }}</dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="grid min-h-40 place-items-center px-4 py-8 text-center">
                        <div><svg aria-hidden="true" class="mx-auto size-9 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M8 4h8l2 2h3v15H3V6h3l2-2z" /><path d="M8 10h8M8 14h8M8 18h5" /></svg><p class="mt-3 text-sm font-medium text-muted">No survey responses yet</p></div>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
