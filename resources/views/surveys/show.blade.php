<div>
    <!-- You must be the change you wish to see in the world. - Mahatma Gandhi -->
@extends('layouts.app')

@section('title', $survey->title)

@section('content')
<div class="mx-auto max-w-3xl px-5 py-10 sm:px-8 lg:py-14">
    <a href="{{ route('surveys.index') }}" class="text-sm font-semibold text-forest underline underline-offset-4">Back to surveys</a>
    <header class="mt-6 border-b border-line pb-7"><p class="eyebrow">Alumni survey</p><h1 class="font-display mt-3 text-4xl">{{ $survey->title }}</h1>@if ($survey->description)<p class="mt-4 leading-7 text-muted">{{ $survey->description }}</p>@endif</header>
    <form method="POST" action="{{ route('surveys.responses.store', $survey) }}" class="mt-7 grid gap-5">
        @csrf
        @foreach ($survey->questions as $index => $question)
            <div class="form-surface"><label class="field" for="answer-{{ $index }}"><span class="text-xs font-semibold uppercase tracking-wider text-terracotta">Question {{ $index + 1 }}</span><span class="text-base font-semibold leading-6 text-ink">{{ $question }}</span><textarea id="answer-{{ $index }}" name="answers[{{ $index }}]" rows="5" maxlength="5000" required>{{ old("answers.{$index}") }}</textarea>@error("answers.{$index}")<span class="field-error">{{ $message }}</span>@enderror</label></div>
        @endforeach
        @error('answers')<p class="field-error">{{ $message }}</p>@enderror
        <div class="flex flex-col-reverse justify-between gap-4 border-t border-line pt-5 sm:flex-row sm:items-center"><a href="{{ route('surveys.index') }}" class="text-sm font-medium text-muted">Cancel</a><button class="inline-flex items-center justify-between gap-8 bg-forest px-5 py-3.5 text-sm font-semibold text-white hover:bg-forest-dark" type="submit">Submit response <span aria-hidden="true">&#8599;</span></button></div>
    </form>
</div>
@endsection
