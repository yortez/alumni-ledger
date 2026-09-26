@extends('layouts.app')

@section('title', $job->title)

@section('content')
<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:py-14">
    <a href="{{ route('jobs.index') }}" class="text-sm font-semibold text-forest underline underline-offset-4">&#8592; All opportunities</a>
    <div class="mt-8 border-b border-line pb-8"><p class="eyebrow">{{ $job->company }} · {{ $job->location }}</p><h1 class="font-display mt-3 text-4xl sm:text-5xl">{{ $job->title }}</h1><div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-sm text-muted"><span>{{ $job->employment_type }}</span>@if ($job->application_deadline)<span>Applications close {{ $job->application_deadline->format('F j, Y') }}</span>@endif</div></div>
    @if ($errors->has('application'))<p class="mt-6 border-l-4 border-terracotta bg-white px-4 py-3 text-sm text-terracotta">{{ $errors->first('application') }}</p>@endif
    <div class="mt-10 grid gap-10 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-8"><section><h2 class="font-display text-3xl">About the role</h2><p class="mt-4 whitespace-pre-line text-sm leading-7 text-muted">{{ $job->description }}</p></section>@if ($job->requirements)<section><h2 class="font-display text-3xl">What you bring</h2><p class="mt-4 whitespace-pre-line text-sm leading-7 text-muted">{{ $job->requirements }}</p></section>@endif<p class="border-t border-line pt-5 text-xs text-muted">Posted by {{ $job->creator?->username ?? 'an administrator' }}.</p></div>
        <aside class="h-fit border border-line bg-white p-6 sm:p-8">
            @if ($application)
                <p class="eyebrow">Application received</p><h2 class="font-display mt-3 text-3xl">You applied for this role.</h2><p class="mt-3 text-sm leading-6 text-muted">Submitted {{ $application->submitted_at->format('F j, Y') }}. The hiring team can review your application.</p>
            @elseif (! auth()->user()->profile?->completed_at)
                <p class="eyebrow">One step first</p><h2 class="font-display mt-3 text-3xl">Complete your profile.</h2><p class="mt-3 text-sm leading-6 text-muted">Your alumni profile is required before you can submit a job application.</p><a href="{{ route('profile.edit') }}" class="mt-6 inline-flex w-full items-center justify-between bg-forest px-5 py-3.5 text-sm font-semibold text-white hover:bg-forest-dark">Complete profile <span aria-hidden="true">&#8599;</span></a>
            @else
                <p class="eyebrow">Ready when you are</p><h2 class="font-display mt-3 text-3xl">Apply for this role.</h2><form method="POST" action="{{ route('jobs.applications.store', $job) }}" class="mt-5 grid gap-5">@csrf<div class="field"><label for="cover_letter">Cover letter <span class="font-normal text-muted">Optional</span></label><textarea id="cover_letter" name="cover_letter" rows="7" maxlength="5000" placeholder="Tell the hiring team why this role interests you.">{{ old('cover_letter') }}</textarea>@error('cover_letter')<p class="field-error">{{ $message }}</p>@enderror</div><button type="submit" class="flex items-center justify-between bg-forest px-5 py-3.5 text-sm font-semibold text-white hover:bg-forest-dark"><span>Submit application</span><span aria-hidden="true">&#8599;</span></button></form>
            @endif
        </aside>
    </div>
</div>
@endsection