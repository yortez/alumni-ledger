@extends('layouts.app')

@section('title', 'Available surveys')

@section('content')
<div class="mx-auto w-full max-w-[1440px] px-5 pb-16 pt-8 sm:px-8 xl:px-20">
    <x-back-link :href="route('dashboard')" label="Dashboard" class="mb-5" />
    <div class="flex flex-wrap items-baseline gap-x-4 gap-y-1">
        <h1 class="text-display font-semibold">Surveys</h1>
        <p class="text-body text-ink-tertiary tabular-nums">
            {{ $surveys->count() }} {{ $surveys->count() === 1 ? 'survey' : 'surveys' }} to complete
        </p>
    </div>

    <section aria-label="Surveys open to you" class="panel mt-6 overflow-hidden">
        <ul>
            @forelse ($surveys as $survey)
                @php($questions = $survey->normalizedQuestions())

                <li class="ledger-row grid grid-cols-[52px_minmax(0,1fr)] gap-x-3 border-t border-divider-soft first:border-t-0 sm:grid-cols-[68px_minmax(0,1fr)] sm:gap-x-4"
                    data-emphasis="true">

                    <div class="py-3 text-right">
                        @if ($survey->closes_at)
                            <p class="text-micro font-semibold uppercase tracking-wide text-ink-tertiary">{{ $survey->closes_at->format('M') }}</p>
                            <p class="text-small font-semibold tabular-nums text-ink">{{ $survey->closes_at->format('j') }}</p>
                        @else
                            <p class="text-micro font-semibold uppercase tracking-wide text-ink-tertiary">Open</p>
                        @endif
                    </div>

                    <details class="group min-w-0">
                        <summary class="flex cursor-pointer list-none items-center gap-3 py-3 transition-colors [&::-webkit-details-marker]:hidden hover:bg-canvas">
                            <span aria-hidden="true" class="ledger-glyph shrink-0">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6h11M9 12h11M9 18h11"/><path d="m3 6 1.5 1.5L7 5"/><path d="M4.5 12.5 6 14l2-2"/></svg>
                            </span>

                            <span class="ledger-title min-w-0 flex-1 truncate text-small">{{ $survey->title }}</span>

                            <span class="hidden shrink-0 text-meta text-ink-tertiary sm:block">{{ count($questions) }} {{ count($questions) === 1 ? 'question' : 'questions' }}</span>
                            @if ($survey->closes_at)
                                <span class="hidden shrink-0 text-meta tabular-nums text-ink-tertiary md:block">Closes {{ $survey->closes_at->format('j M') }}</span>
                            @endif

                            <x-chevron class="shrink-0 text-ink-tertiary transition-transform group-open:rotate-90" />
                        </summary>

                        <div class="mb-3 grid gap-3 rounded-chip bg-surface-pearl p-4">
                            @if ($survey->description)
                                <p class="max-w-[60ch] whitespace-pre-line text-caption text-ink-secondary">{{ $survey->description }}</p>
                            @else
                                <p class="max-w-[60ch] text-caption text-ink-secondary">{{ count($questions) }} {{ count($questions) === 1 ? 'question' : 'questions' }} about your graduate experience.</p>
                            @endif

                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="text-meta text-ink-tertiary">
                                    @if ($survey->closes_at)
                                        Closes {{ $survey->closes_at->format('j F Y') }}
                                    @else
                                        Open until further notice
                                    @endif
                                </p>
                                <a href="{{ route('surveys.show', $survey) }}" class="inline-flex items-center gap-1 text-caption font-semibold text-accent transition hover:text-accent-strong">Open survey<x-chevron /></a>
                            </div>
                        </div>
                    </details>
                </li>
            @empty
                <li class="px-5 py-12 text-center">
                    <p class="text-caption text-ink-tertiary">There are no surveys available for you right now.</p>
                </li>
            @endforelse
        </ul>
    </section>
</div>
@endsection
