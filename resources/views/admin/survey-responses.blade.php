<div>
    <!-- Breathing in, I calm body and mind. Breathing out, I smile. - Thich Nhat Hanh -->
@extends('layouts.app')

@section('title', 'Survey responses')

@section('content')
<div class="mx-auto max-w-5xl px-5 py-10 sm:px-8 lg:py-14">
    <a href="{{ route('admin.surveys.index') }}" class="text-sm font-semibold text-forest underline underline-offset-4">Back to surveys</a>
    <header class="mt-6 border-b border-line pb-7"><p class="eyebrow">Survey responses</p><h1 class="font-display mt-3 text-4xl">{{ $survey->title }}</h1><p class="mt-3 text-sm text-muted">{{ $responses->total() }} submitted</p></header>
    <div class="mt-7 grid gap-4">
        @forelse ($responses as $response)
            <article class="border border-line bg-white p-5 sm:p-7">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line pb-4"><div><h2 class="font-semibold">{{ $response->user->name }}</h2><p class="mt-1 text-xs text-muted">{{ $response->user->student_number }} · {{ $response->user->username }}</p></div><time class="text-xs text-muted" datetime="{{ $response->submitted_at->toIso8601String() }}">{{ $response->submitted_at->format('M j, Y g:i A') }}</time></div>
                <dl class="mt-5 grid gap-5">@foreach ($survey->questions as $index => $question)<div><dt class="text-xs font-semibold uppercase tracking-wider text-terracotta">{{ $question }}</dt><dd class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-ink">{{ $response->answers[$index] ?? '' }}</dd></div>@endforeach</dl>
            </article>
        @empty
            <p class="border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No responses have been submitted yet.</p>
        @endforelse
    </div>
    <div class="mt-5">{{ $responses->links() }}</div>
</div>
@endsection
