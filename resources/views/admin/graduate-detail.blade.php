@extends('layouts.app')

@section('title', 'Applicant details')

@section('content')
<div class="mx-auto max-w-6xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="mb-8 flex flex-col gap-4 border-b border-line pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="mt-3 font-display text-4xl sm:text-5xl">Applicant details</h1>
        </div>
        <a href="{{ route('admin.graduates.index') }}" class="text-sm font-semibold text-forest underline underline-offset-4">Back to roster</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <section class="border border-line bg-white p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wider text-muted">Applicant</p>
                    <h2 class="mt-2 font-display text-3xl">{{ $graduate->name }}</h2>
                </div>
                @if ($profile?->completed_at)
                    <span class="rounded-full border border-forest bg-mint px-3 py-1 text-xs font-semibold uppercase tracking-wide text-forest">Profile complete</span>
                @else
                    <span class="rounded-full border border-amber-300 bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-700">Profile incomplete</span>
                @endif
            </div>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-lg border border-line bg-paper p-4">
                    <dt class="text-xs uppercase tracking-wider text-muted">Student number</dt>
                    <dd class="mt-2 font-medium text-ink">{{ $graduate->student_number }}</dd>
                </div>
                <div class="rounded-lg border border-line bg-paper p-4">
                    <dt class="text-xs uppercase tracking-wider text-muted">Username</dt>
                    <dd class="mt-2 font-medium text-ink">{{ $applicant?->username ?? 'Not available' }}</dd>
                </div>
                <div class="rounded-lg border border-line bg-paper p-4">
                    <dt class="text-xs uppercase tracking-wider text-muted">Email</dt>
                    <dd class="mt-2 font-medium text-ink">{{ $applicant?->email ?? 'Not available' }}</dd>
                </div>
                <div class="rounded-lg border border-line bg-paper p-4">
                    <dt class="text-xs uppercase tracking-wider text-muted">Account status</dt>
                    <dd class="mt-2 font-medium text-ink">{{ $applicant ? 'Claimed' : 'Not registered' }}</dd>
                </div>
                <div class="rounded-lg border border-line bg-paper p-4">
                    <dt class="text-xs uppercase tracking-wider text-muted">Program</dt>
                    <dd class="mt-2 font-medium text-ink">{{ $graduate->program ?: 'Not listed' }}</dd>
                </div>
                <div class="rounded-lg border border-line bg-paper p-4">
                    <dt class="text-xs uppercase tracking-wider text-muted">Graduation year</dt>
                    <dd class="mt-2 font-medium text-ink">{{ $graduate->graduation_year ?: 'Not listed' }}</dd>
                </div>
            </dl>
        </section>

        <section class="border border-line bg-white p-6">
            <h2 class="font-display text-2xl">Professional profile</h2>

            @if ($profile)
                <dl class="mt-6 grid gap-4">
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Job title</dt>
                        <dd class="mt-2 font-medium text-ink">{{ $profile->job_title ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Employer</dt>
                        <dd class="mt-2 font-medium text-ink">{{ $profile->employer ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Industry</dt>
                        <dd class="mt-2 font-medium text-ink">{{ $profile->industry ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Employment status</dt>
                        <dd class="mt-2 font-medium text-ink">{{ ucfirst((string) ($profile->employment_status ?? 'not provided')) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Location</dt>
                        <dd class="mt-2 font-medium text-ink">{{ $profile->city ?: 'Not provided' }}{{ $profile->city && $profile->country ? ', ' : '' }}{{ $profile->country ?: '' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Phone</dt>
                        <dd class="mt-2 font-medium text-ink">{{ $profile->phone ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">LinkedIn</dt>
                        <dd class="mt-2 break-all font-medium text-ink">{{ $profile->linkedin_url ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Bio</dt>
                        <dd class="mt-2 whitespace-pre-line text-sm leading-6 text-ink">{{ $profile->bio ?: 'No bio added yet.' }}</dd>
                    </div>
                </dl>
            @else
                <div class="mt-6 rounded-lg border border-dashed border-line bg-paper p-5 text-sm text-muted">
                    This applicant has not completed their alumni profile yet.
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
