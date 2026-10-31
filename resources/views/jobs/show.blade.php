@extends('layouts.app')

@section('title', $job->title)

@section('content')
<div class="mx-auto w-full max-w-[1440px] px-5 pb-16 pt-8 sm:px-8 xl:px-20">
    <x-back-link :href="route('jobs.index')" label="All opportunities" />

    <div class="mt-5 border-b border-hairline pb-6">
        <h1 class="text-display font-semibold wrap-break-word">{{ $job->title }}</h1>
        <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-body text-ink-tertiary">
            <span class="text-ink-secondary">{{ $job->company }}</span>
            <span>{{ $job->location }}</span>
            <span>{{ $job->employment_type }}</span>
            @if ($job->application_deadline)
                <span>Apply by {{ $job->application_deadline->format('j F Y') }}</span>
            @endif
        </p>
    </div>

    @if ($errors->has('application'))
        <p class="mt-5 rounded-chip border border-danger/20 bg-[#fff5f5] px-4 py-3 text-caption text-danger">{{ $errors->first('application') }}</p>
    @endif

    <div class="mt-6 grid items-start gap-6 lg:grid-cols-[minmax(0,760fr)_minmax(0,400fr)]">
        <div class="panel p-6 sm:p-8">
            <section aria-labelledby="about-role">
                <h2 id="about-role" class="text-title font-semibold">About the role</h2>
                <p class="mt-3 max-w-[68ch] whitespace-pre-line text-body text-ink-secondary">{{ $job->description }}</p>
            </section>

            @if ($job->requirements)
                <section aria-labelledby="role-requirements" class="mt-6 border-t border-divider-soft pt-6">
                    <h2 id="role-requirements" class="text-title font-semibold">What you bring</h2>
                    <p class="mt-3 max-w-[68ch] whitespace-pre-line text-body text-ink-secondary">{{ $job->requirements }}</p>
                </section>
            @endif

            <p class="mt-6 border-t border-divider-soft pt-5 text-meta text-ink-tertiary">Posted by {{ $job->creator?->username ?? 'an administrator' }}</p>
        </div>

        <aside aria-label="Application" class="panel p-6">
            @if ($application)
                <h2 class="text-title font-semibold">You applied for this role</h2>
                <p class="mt-2 text-caption text-ink-tertiary">Submitted {{ $application->submitted_at->format('j F Y') }}</p>

                <dl class="mt-5 space-y-4 border-t border-divider-soft pt-4">
                    <div>
                        <dt class="text-meta text-ink-tertiary">Status</dt>
                        <dd class="mt-1 text-small font-semibold text-ink">{{ $statusLabels[$application->status] ?? ucfirst($application->status) }}</dd>
                    </div>
                    <div>
                        <dt class="text-meta text-ink-tertiary">Hiring team</dt>
                        <dd class="mt-1 text-small text-ink-secondary">Reviews applications in the order they arrive.</dd>
                    </div>
                </dl>
            @elseif (! auth()->user()->profile?->completed_at)
                <h2 class="text-title font-semibold">Complete your profile first</h2>
                <p class="mt-2 text-caption text-ink-secondary">Your alumni profile is required before you can submit an application.</p>
                <a href="{{ route('profile.edit') }}" class="mt-5 flex h-11 items-center justify-center rounded-full bg-accent text-caption font-semibold text-white transition hover:bg-accent-strong">Complete profile</a>
            @else
                <h2 class="text-title font-semibold">Apply for this role</h2>
                <p class="mt-2 text-caption text-ink-tertiary">The hiring team sees your alumni profile alongside this note.</p>

                <form method="POST" action="{{ route('jobs.applications.store', $job) }}" class="mt-4 grid gap-4">
                    @csrf
                    <div class="field">
                        <label for="cover_letter">Cover letter <span class="font-normal text-ink-tertiary">Optional</span></label>
                        <textarea id="cover_letter" name="cover_letter" rows="7" maxlength="5000" placeholder="Tell the hiring team why this role interests you.">{{ old('cover_letter') }}</textarea>
                        @error('cover_letter')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="flex h-11 items-center justify-center rounded-full bg-accent text-caption font-semibold text-white transition hover:bg-accent-strong">Submit application</button>
                </form>
            @endif
        </aside>
    </div>
</div>
@endsection
