<div>
    <!-- Breathing in, I calm body and mind. Breathing out, I smile. - Thich Nhat Hanh -->
@extends('layouts.app')

@section('title', 'Survey responses')

@section('content')
<div class="mx-auto w-full max-w-5xl px-5 pb-16 pt-8 sm:px-8">
    <x-back-link :href="route('admin.surveys.index')" label="Survey manager" class="mb-5" />
    <header class="mb-6 border-b border-hairline pb-6"><p class="eyebrow">Survey responses</p><h1 class="mt-3 text-display font-semibold">{{ $survey->title }}</h1><p class="mt-3 text-caption text-ink-tertiary">{{ $responses->total() }} submitted</p></header>
    <div class="mt-7 grid gap-4">
        @forelse ($responses as $response)
            <article class="panel p-5 sm:p-7">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line pb-4"><div><h2 class="font-semibold">{{ $response->user->name }}</h2><p class="mt-1 text-xs text-muted">{{ $response->user->student_number }} · {{ $response->user->username }}</p></div><time class="text-xs text-muted" datetime="{{ $response->submitted_at->toIso8601String() }}">{{ $response->submitted_at->format('M j, Y g:i A') }}</time></div>
                <dl class="mt-5 grid gap-5">@foreach ($survey->normalizedQuestions() as $index => $question)@php($answer = $response->answers[$index] ?? '')<div>@if ($question['section_label'])<p class="text-[10px] font-semibold uppercase tracking-wider text-muted">{{ $question['section_label'] }}</p>@endif<dt class="mt-1 text-xs font-semibold uppercase tracking-wider text-terracotta">{{ $question['prompt'] }}</dt><dd class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-ink">{{ is_array($answer) ? implode(', ', $answer) : ($answer !== '' ? $answer : 'No response') }}</dd></div>@endforeach</dl>
            </article>
        @empty
            <p class="border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No responses have been submitted yet.</p>
        @endforelse
    </div>
    <div class="mt-5">{{ $responses->links() }}</div>
</div>
@endsection
