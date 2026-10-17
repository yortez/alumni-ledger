@extends('layouts.app')

@section('title', 'Alumni profile')

@section('content')
<div class="mx-auto max-w-6xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="mb-8 flex flex-col gap-4 border-b border-line pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="mt-3 font-display text-4xl sm:text-5xl">Alumni profile</h1>
        </div>
        <a href="{{ route('admin.graduates.index') }}" class="text-sm font-semibold text-forest underline underline-offset-4">Back to roster</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <section class="border border-line bg-white p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wider text-muted">Graduate record</p>
                    <h2 class="mt-2 font-display text-3xl">{{ $graduate->fullName() }}</h2>
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
                    <dt class="text-xs uppercase tracking-wider text-muted">Program code</dt>
                    <dd class="mt-2 font-medium text-ink">{{ $graduate->program_code ?: 'Not listed' }}</dd>
                </div>
                <div class="rounded-lg border border-line bg-paper p-4">
                    <dt class="text-xs uppercase tracking-wider text-muted">Graduation year</dt>
                    <dd class="mt-2 font-medium text-ink">{{ $graduate->batch_year ?: 'Not listed' }}</dd>
                </div>
                <div class="rounded-lg border border-line bg-paper p-4">
                    <dt class="text-xs uppercase tracking-wider text-muted">Latin honor</dt>
                    <dd class="mt-2 font-medium text-ink">{{ ucwords(str_replace('_', ' ', $graduate->latin_honor)) }}</dd>
                </div>
            </dl>
        </section>

        <section class="border border-line bg-white p-6">
            <h2 class="font-display text-2xl">Personal Information</h2>

            @if ($profile)
                <dl class="mt-6 grid gap-4">
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Location</dt>
                        <dd class="mt-2 font-medium text-ink">{{ $profile->city ?: 'Not provided' }}{{ $profile->city && $profile->country ? ', ' : '' }}{{ $profile->country ?: '' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Phone</dt>
                        <dd class="mt-2 font-medium text-ink">{{ $profile->phone ?: 'Not provided' }}</dd>
                    </div>
                </dl>
                <div class="mt-7 border-t border-line pt-5">
                    <h3 class="font-display text-xl">Employment history</h3>
                    @forelse ($profile->employments as $employment)
                        <article class="border-b border-line py-4 last:border-b-0">
                            <div class="flex flex-col justify-between gap-1 sm:flex-row sm:items-start">
                                <div>
                                    <h4 class="font-semibold text-ink">{{ $employment->job_title }}</h4>
                                    <p class="mt-1 text-sm text-muted">{{ $employment->employer }}{{ $employment->location ? ' · '.$employment->location : '' }}</p>
                                </div>
                                <p class="text-sm text-muted">{{ $employment->start_date->format('M Y') }} – {{ $employment->is_current ? 'Present' : ($employment->end_date?->format('M Y') ?? 'Date not provided') }}</p>
                            </div>
                            @if ($employment->description)
                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-muted">{{ $employment->description }}</p>
                            @endif
                            <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-forest">{{ $employment->is_current ? 'Current role' : 'Past role' }}</p>
                        </article>
                    @empty
                        <p class="mt-3 text-sm text-muted">No employment history has been added.</p>
                    @endforelse
                </div>
            @else
                <div class="mt-6 rounded-lg border border-dashed border-line bg-paper p-5 text-sm text-muted">
                    This applicant has not completed their alumni profile yet.
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
