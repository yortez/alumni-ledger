@extends('layouts.app')

@section('title', 'Applicant profile')

@section('content')
<div class="mx-auto max-w-6xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="mb-8 flex flex-col gap-4 border-b border-line pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">Applications</p>
            <h1 class="mt-3 font-display text-4xl sm:text-5xl">Applicant profile</h1>
        </div>
        <a href="{{ route('admin.jobs.applications.index', $job) }}" class="text-sm font-semibold text-forest underline underline-offset-4">Back to applicants</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <section class="border border-line bg-white p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wider text-muted">Candidate</p>
                    <h2 class="mt-2 font-display text-3xl">{{ $application->user->name }}</h2>
                </div>
                <span class="rounded-full border border-line bg-paper px-3 py-1 text-xs font-semibold uppercase tracking-wide text-ink">
                    {{ $statusLabels[$application->status] ?? ucfirst(str_replace('_', ' ', $application->status)) }}
                </span>
            </div>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-lg border border-line bg-paper p-4">
                    <dt class="text-xs uppercase tracking-wider text-muted">Student number</dt>
                    <dd class="mt-2 font-medium text-ink">{{ $application->user->student_number ?? $graduate?->student_number ?? 'Not available' }}</dd>
                </div>
                <div class="rounded-lg border border-line bg-paper p-4">
                    <dt class="text-xs uppercase tracking-wider text-muted">Username</dt>
                    <dd class="mt-2 font-medium text-ink">{{ $application->user->username ?? 'Not available' }}</dd>
                </div>
                <div class="rounded-lg border border-line bg-paper p-4">
                    <dt class="text-xs uppercase tracking-wider text-muted">Email</dt>
                    <dd class="mt-2 font-medium text-ink">{{ $application->user->email }}</dd>
                </div>
                <div class="rounded-lg border border-line bg-paper p-4">
                    <dt class="text-xs uppercase tracking-wider text-muted">Program</dt>
                    <dd class="mt-2 font-medium text-ink">{{ $graduate?->program ?: 'Not listed' }}</dd>
                </div>
                <div class="rounded-lg border border-line bg-paper p-4">
                    <dt class="text-xs uppercase tracking-wider text-muted">Graduation year</dt>
                    <dd class="mt-2 font-medium text-ink">{{ $graduate?->graduation_year ?: 'Not listed' }}</dd>
                </div>
                <div class="rounded-lg border border-line bg-paper p-4">
                    <dt class="text-xs uppercase tracking-wider text-muted">Submitted</dt>
                    <dd class="mt-2 font-medium text-ink">{{ $application->submitted_at->format('F j, Y g:i A') }}</dd>
                </div>
            </dl>

            <div class="mt-8 border-t border-line pt-5">
                <h3 class="font-display text-2xl">Cover letter</h3>
                <p class="mt-4 whitespace-pre-line text-sm leading-6 text-muted">
                    {{ $application->cover_letter ?: 'No cover letter submitted.' }}
                </p>
            </div>
        </section>

        <section class="border border-line bg-white p-6">
            <h2 class="font-display text-2xl">Decision</h2>

            <form method="POST" action="{{ route('admin.jobs.applications.update', [$job, $application]) }}" class="mt-6 grid gap-4">@csrf @method('PATCH')
                <div class="field">
                    <label for="status">Application status</label>
                    <select id="status" name="status">
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($application->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="bg-forest px-4 py-3 text-sm font-semibold text-white hover:bg-forest-dark">
                    Update status
                </button>
            </form>

            <div class="mt-8 border-t border-line pt-5">
                <h3 class="font-display text-xl">Profile summary</h3>
                @if ($profile)
                    <dl class="mt-4 space-y-3 text-sm text-muted">
                        <div><dt class="text-xs uppercase tracking-wider text-muted">Job title</dt><dd class="mt-1 font-medium text-ink">{{ $profile->job_title ?: 'Not provided' }}</dd></div>
                        <div><dt class="text-xs uppercase tracking-wider text-muted">Employer</dt><dd class="mt-1 font-medium text-ink">{{ $profile->employer ?: 'Not provided' }}</dd></div>
                        <div><dt class="text-xs uppercase tracking-wider text-muted">Industry</dt><dd class="mt-1 font-medium text-ink">{{ $profile->industry ?: 'Not provided' }}</dd></div>
                        <div><dt class="text-xs uppercase tracking-wider text-muted">Location</dt><dd class="mt-1 font-medium text-ink">{{ $profile->city ?: 'Not provided' }}{{ $profile->city && $profile->country ? ', ' : '' }}{{ $profile->country ?: '' }}</dd></div>
                        <div><dt class="text-xs uppercase tracking-wider text-muted">Employment status</dt><dd class="mt-1 font-medium text-ink">{{ ucfirst((string) ($profile->employment_status ?? 'not provided')) }}</dd></div>
                    </dl>
                @else
                    <p class="mt-4 text-sm text-muted">This applicant has not completed a profile yet.</p>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection
