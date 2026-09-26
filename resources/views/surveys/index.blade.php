<div>
    <!-- Breathing in, I calm body and mind. Breathing out, I smile. - Thich Nhat Hanh -->
@extends('layouts.app')

@section('title', 'Available surveys')

@section('content')
<div class="mx-auto max-w-5xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="border-b border-line pb-7"><p class="eyebrow">Alumni voice</p><h1 class="font-display mt-3 text-4xl sm:text-5xl">Available surveys</h1><p class="mt-3 max-w-2xl text-sm leading-6 text-muted">Surveys selected for your program and graduation year appear here.</p></div>
    <div class="mt-7 grid gap-3">
        @forelse ($surveys as $survey)
            <article class="flex flex-col justify-between gap-5 border border-line bg-white p-5 sm:flex-row sm:items-center sm:p-6">
                <div class="min-w-0"><p class="text-xs font-semibold uppercase tracking-wider text-forest">{{ count($survey->questions) }} questions{{ $survey->closes_at ? ' · Closes '.$survey->closes_at->format('M j, Y') : '' }}</p><h2 class="mt-2 break-words font-display text-2xl">{{ $survey->title }}</h2>@if ($survey->description)<p class="mt-2 text-sm leading-6 text-muted">{{ $survey->description }}</p>@endif</div>
                <a href="{{ route('surveys.show', $survey) }}" class="inline-flex shrink-0 items-center justify-between gap-8 bg-forest px-5 py-3 text-sm font-semibold text-white hover:bg-forest-dark">Respond <span aria-hidden="true">&#8599;</span></a>
            </article>
        @empty
            <p class="border border-dashed border-line px-5 py-12 text-center text-sm text-muted">There are no surveys available for you right now.</p>
        @endforelse
    </div>
</div>
@endsection
