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
        @foreach ($survey->normalizedQuestions() as $index => $question)
            @php($oldAnswer = old("answers.{$index}", $question['type'] === 'checkbox' ? [] : ''))
            <section class="form-surface">
                @if ($question['section_label'])<p class="eyebrow">{{ $question['section_label'] }}</p>@endif
                <div class="grid gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-terracotta">Question {{ $index + 1 }} @if ($question['required'])<span aria-label="required">*</span>@else<span class="font-normal normal-case tracking-normal text-muted">Optional</span>@endif</p>
                        <h2 class="mt-2 text-base font-semibold leading-6 text-ink">{{ $question['prompt'] }}</h2>
                    </div>
                    @if ($question['type'] === 'short_text')
                        <input id="answer-{{ $index }}" type="text" name="answers[{{ $index }}]" value="{{ $oldAnswer }}" maxlength="1000" @if ($question['required']) required @endif>
                    @elseif ($question['type'] === 'long_text')
                        <textarea id="answer-{{ $index }}" name="answers[{{ $index }}]" rows="5" maxlength="5000" @if ($question['required']) required @endif>{{ $oldAnswer }}</textarea>
                    @elseif ($question['type'] === 'radio')
                        <fieldset class="grid gap-2"><legend class="sr-only">{{ $question['prompt'] }}</legend>@foreach ($question['options'] as $optionIndex => $option)<label class="flex items-center gap-2 text-sm text-ink"><input id="answer-{{ $index }}-{{ $optionIndex }}" type="radio" name="answers[{{ $index }}]" value="{{ $option }}" @checked($oldAnswer === $option) @if ($question['required']) required @endif>{{ $option }}</label>@endforeach</fieldset>
                    @elseif ($question['type'] === 'checkbox')
                        <fieldset class="grid gap-2"><legend class="sr-only">{{ $question['prompt'] }}</legend>@foreach ($question['options'] as $optionIndex => $option)<label class="flex items-center gap-2 text-sm text-ink"><input id="answer-{{ $index }}-{{ $optionIndex }}" type="checkbox" name="answers[{{ $index }}][]" value="{{ $option }}" @checked(is_array($oldAnswer) && in_array($option, $oldAnswer, true))>{{ $option }}</label>@endforeach</fieldset>
                    @else
                        <select id="answer-{{ $index }}" name="answers[{{ $index }}]" @if ($question['required']) required @endif><option value="">Select an option</option>@foreach ($question['options'] as $option)<option value="{{ $option }}" @selected($oldAnswer === $option)>{{ $option }}</option>@endforeach</select>
                    @endif
                    @error("answers.{$index}")<span class="field-error">{{ $message }}</span>@enderror
                    @error("answers.{$index}.*")<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </section>
        @endforeach
        @error('answers')<p class="field-error">{{ $message }}</p>@enderror
        <div class="flex flex-col-reverse justify-between gap-4 border-t border-line pt-5 sm:flex-row sm:items-center"><a href="{{ route('surveys.index') }}" class="text-sm font-medium text-muted">Cancel</a><button class="inline-flex items-center justify-between gap-8 bg-forest px-5 py-3.5 text-sm font-semibold text-white hover:bg-forest-dark" type="submit">Submit response <span aria-hidden="true">&#8599;</span></button></div>
    </form>
</div>
@endsection
