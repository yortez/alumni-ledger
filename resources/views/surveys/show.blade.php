@extends('layouts.app')

@section('title', $survey->title)

@section('content')
@php($questions = $survey->normalizedQuestions())

<div class="mx-auto w-full max-w-[1440px] px-5 pb-16 pt-8 sm:px-8 xl:px-20">
    <x-back-link :href="route('surveys.index')" label="All surveys" />

    <div class="mt-5 border-b border-hairline pb-6">
        <h1 class="text-display font-semibold wrap-break-word">{{ $survey->title }}</h1>
        @if ($survey->description)
            <p class="mt-3 max-w-[70ch] whitespace-pre-line text-body text-ink-secondary">{{ $survey->description }}</p>
        @endif
        <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-meta text-ink-tertiary">
            <span>{{ count($questions) }} {{ count($questions) === 1 ? 'question' : 'questions' }}</span>
            @if ($survey->closes_at)
                <span>Closes {{ $survey->closes_at->format('j F Y') }}</span>
            @endif
        </p>
    </div>

    <form method="POST" action="{{ route('surveys.responses.store', $survey) }}" class="mt-6 grid max-w-4xl gap-4">
        @csrf

        @foreach ($questions as $index => $question)
            @if ($question['section_label'] !== '' && $question['section_label'] !== ($currentSection ?? null))
                <h2 class="mt-4 text-title font-semibold text-ink">{{ $question['section_label'] }}</h2>
            @endif
            @php($currentSection = $question['section_label'])
            @php($oldAnswer = old("answers.{$index}", $question['type'] === 'checkbox' ? [] : ''))

            <section class="panel p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-baseline gap-2">
                        <span aria-hidden="true" class="shrink-0 text-micro tabular-nums text-ink-tertiary">{{ $index + 1 }}</span>
                        <h3 class="text-small font-semibold text-ink">
                            {{ $question['prompt'] }}
                            @if ($question['required'])
                                <span class="text-accent" aria-hidden="true">*</span>
                            @endif
                        </h3>
                    </div>
                    <p class="shrink-0 text-micro text-ink-tertiary">{{ $question['required'] ? 'Required' : 'Optional' }}</p>
                </div>

                @if ($question['type'] === 'short_text')
                    <div class="field mt-3">
                        <label class="sr-only" for="answer-{{ $index }}">{{ $question['prompt'] }}</label>
                        <input id="answer-{{ $index }}" type="text" name="answers[{{ $index }}]" value="{{ $oldAnswer }}" maxlength="1000" @if ($question['required']) required @endif>
                    </div>
                @elseif ($question['type'] === 'long_text')
                    <div class="field mt-3">
                        <label class="sr-only" for="answer-{{ $index }}">{{ $question['prompt'] }}</label>
                        <textarea id="answer-{{ $index }}" name="answers[{{ $index }}]" rows="5" maxlength="5000" @if ($question['required']) required @endif>{{ $oldAnswer }}</textarea>
                    </div>
                @elseif ($question['type'] === 'radio' || $question['type'] === 'checkbox')
                    <fieldset class="mt-4 grid gap-2.5">
                        <legend class="sr-only">{{ $question['prompt'] }}</legend>
                        @foreach ($question['options'] as $optionIndex => $option)
                            <label class="flex cursor-pointer items-center gap-2.5 text-small text-ink">
                                <input
                                    id="answer-{{ $index }}-{{ $optionIndex }}"
                                    type="{{ $question['type'] }}"
                                    name="answers[{{ $index }}]{{ $question['type'] === 'checkbox' ? '[]' : '' }}"
                                    value="{{ $option }}"
                                    class="size-4 shrink-0 accent-accent"
                                    @checked($question['type'] === 'checkbox' ? (is_array($oldAnswer) && in_array($option, $oldAnswer, true)) : $oldAnswer === $option)
                                    @if ($question['type'] === 'radio' && $question['required']) required @endif>
                                {{ $option }}
                            </label>
                        @endforeach
                    </fieldset>
                @else
                    <div class="field mt-3">
                        <label class="sr-only" for="answer-{{ $index }}">{{ $question['prompt'] }}</label>
                        <select id="answer-{{ $index }}" name="answers[{{ $index }}]" @if ($question['required']) required @endif>
                            <option value="">Select an option</option>
                            @foreach ($question['options'] as $option)
                                <option value="{{ $option }}" @selected($oldAnswer === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @error("answers.{$index}")
                    <p class="field-error mt-2">{{ $message }}</p>
                @enderror
                @error("answers.{$index}.*")
                    <p class="field-error mt-2">{{ $message }}</p>
                @enderror
            </section>
        @endforeach

        @error('answers')
            <p class="field-error">{{ $message }}</p>
        @enderror

        <div class="mt-2 flex flex-col-reverse items-stretch justify-between gap-3 border-t border-hairline pt-5 sm:flex-row sm:items-center">
            <a href="{{ route('surveys.index') }}" class="text-center text-caption font-semibold text-ink-tertiary transition hover:text-ink sm:text-left">Cancel</a>
            <button type="submit" class="flex h-11 items-center justify-center rounded-full bg-accent px-6 text-caption font-semibold text-white transition hover:bg-accent-strong">Submit response</button>
        </div>
    </form>
</div>
@endsection
