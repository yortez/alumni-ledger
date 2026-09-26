@extends('layouts.app')

@section('title', 'My dashboard')

@section('content')
<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="flex flex-col justify-between gap-6 border-b border-line pb-8 sm:flex-row sm:items-end"><div><p class="eyebrow">Alumni directory</p><h1 class="font-display mt-3 text-4xl sm:text-5xl">Welcome, {{ auth()->user()->name }}.</h1></div><a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-between gap-8 bg-forest px-5 py-3 text-sm font-semibold text-white hover:bg-forest-dark">{{ $profile?->completed_at ? 'Update profile' : 'Complete profile' }} <span aria-hidden="true">&#8599;</span></a></div>
    @if ($announcements->isNotEmpty())
        <section class="mt-8" aria-labelledby="announcements-heading">
            <div class="mb-4 flex items-end justify-between gap-4"><div><p class="eyebrow">From the college</p><h2 id="announcements-heading" class="font-display mt-2 text-3xl">Announcements</h2></div><span class="text-xs uppercase tracking-wider text-muted">{{ $announcements->count() }} new</span></div>
            <div class="grid gap-3">
                @foreach ($announcements as $announcement)
                    <article class="border-l-4 border-terracotta bg-white px-5 py-5 sm:px-7">
                        <p class="text-xs font-semibold uppercase tracking-wider text-forest">{{ $announcement->published_at->format('F j, Y') }}</p>
                        <h3 class="mt-2 font-display text-2xl">{{ $announcement->title }}</h3>
                        <p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-muted">{{ $announcement->body }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
    @if ($surveys->isNotEmpty())
        <section class="mt-8" aria-labelledby="surveys-heading">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-4"><div><p class="eyebrow">Your voice matters</p><h2 id="surveys-heading" class="font-display mt-2 text-3xl">Surveys for you</h2></div><a href="{{ route('surveys.index') }}" class="text-sm font-semibold text-forest underline underline-offset-4">All available surveys</a></div>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($surveys as $survey)
                    <article class="flex flex-col justify-between gap-5 border border-line bg-white p-5"><div><p class="text-xs font-semibold uppercase tracking-wider text-terracotta">{{ count($survey->questions) }} questions</p><h3 class="mt-2 font-display text-2xl">{{ $survey->title }}</h3>@if ($survey->description)<p class="mt-2 text-sm leading-6 text-muted">{{ $survey->description }}</p>@endif</div><a class="text-sm font-semibold text-forest underline underline-offset-4" href="{{ route('surveys.show', $survey) }}">Respond <span aria-hidden="true">&#8594;</span></a></article>
                @endforeach
            </div>
        </section>
    @endif
    <div class="mt-8 grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
        <section class="border border-line bg-white p-6 sm:p-8"><div class="flex items-start justify-between gap-4"><div><p class="eyebrow">Your record</p><h2 class="font-display mt-2 text-2xl">{{ auth()->user()->username }}</h2></div><span class="bg-mint px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-forest">Verified graduate</span></div><dl class="mt-8 grid gap-x-8 gap-y-5 border-t border-line pt-6 sm:grid-cols-2"><div><dt class="text-xs uppercase tracking-wider text-muted">Student number</dt><dd class="mt-1 font-medium">{{ auth()->user()->student_number }}</dd></div><div><dt class="text-xs uppercase tracking-wider text-muted">Email</dt><dd class="mt-1 break-all font-medium">{{ auth()->user()->email }}</dd></div></dl></section>
        <section class="border border-line bg-white p-6 sm:p-8"><p class="eyebrow">Profile status</p>@if ($profile?->completed_at)<p class="font-display mt-4 text-3xl">Your profile is complete.</p><p class="mt-3 text-sm leading-6 text-muted">Last updated {{ $profile->updated_at->format('F j, Y') }}.</p><a href="{{ route('profile.edit') }}" class="mt-6 inline-flex items-center gap-3 text-sm font-semibold text-forest">Review your details <span aria-hidden="true">&#8594;</span></a>@else<p class="font-display mt-4 text-3xl">Add the next chapter.</p><p class="mt-3 text-sm leading-6 text-muted">Your graduate record is ready. Add your current details to complete your alumni profile.</p><a href="{{ route('profile.edit') }}" class="mt-6 inline-flex items-center gap-3 text-sm font-semibold text-terracotta">Complete your profile <span aria-hidden="true">&#8594;</span></a>@endif</section>
    </div>
</div>
@endsection
