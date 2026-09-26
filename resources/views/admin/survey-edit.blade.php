@extends('layouts.app')

@section('title', 'Edit survey')

@section('content')
<div class="mx-auto max-w-5xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="mb-6 flex items-center justify-between gap-4 border-b border-line pb-6">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="font-display mt-3 text-4xl sm:text-5xl">Edit survey</h1>
        </div>
        <a href="{{ route('admin.surveys.index') }}" class="text-sm font-semibold text-forest underline underline-offset-4">Back to surveys</a>
    </div>

    <section class="border border-line bg-white p-5 sm:p-6">
        <form method="POST" action="{{ route('admin.surveys.update', $survey) }}" class="grid gap-5">
            @csrf
            @method('PATCH')

            <div class="field">
                <label for="title">Survey title</label>
                <input id="title" name="title" maxlength="180" value="{{ old('title', $survey->title) }}" required>
                @error('title')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" maxlength="5000">{{ old('description', $survey->description) }}</textarea>
                @error('description')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="questions_text">Questions</label>
                <textarea id="questions_text" name="questions_text" rows="6" placeholder="One question per line" required>{{ old('questions_text', implode("\n", $survey->questions)) }}</textarea>
                <p class="text-xs text-muted">Each line becomes a required long-answer question. Maximum 20.</p>
                @error('questions')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <fieldset class="grid gap-3">
                <legend class="text-sm font-semibold">Who should respond?</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-muted">Programs</p>
                        <div class="max-h-44 overflow-y-auto border border-line p-3">
                            @foreach ($programs as $program)
                                <label class="flex items-start gap-2 py-1.5 text-sm">
                                    <input class="mt-1 accent-[#245b49]" type="checkbox" name="target_programs[]" value="{{ $program }}" @checked(in_array($program, old('target_programs', $survey->target_programs ?? [])))>
                                    <span>{{ $program }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-muted">Graduation years</p>
                        <div class="max-h-44 overflow-y-auto border border-line p-3">
                            @foreach ($graduationYears as $year)
                                <label class="flex items-start gap-2 py-1.5 text-sm">
                                    <input class="mt-1 accent-[#245b49]" type="checkbox" name="target_graduation_years[]" value="{{ $year }}" @checked(in_array((int) $year, old('target_graduation_years', $survey->target_graduation_years ?? [])))>
                                    <span>{{ $year }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="field">
                <label for="closes_at">Closes at</label>
                <input id="closes_at" type="datetime-local" name="closes_at" value="{{ old('closes_at', $survey->closes_at?->format('Y-m-d\TH:i')) }}">
                @error('closes_at')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" @selected(old('status', $survey->is_active ? 'active' : 'draft') === 'active')>Open for responses</option>
                    <option value="draft" @selected(old('status', $survey->is_active ? 'active' : 'draft') === 'draft')>Save as draft</option>
                </select>
            </div>

            <button type="submit" class="bg-forest px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-forest-dark">Save changes</button>
        </form>
    </section>
</div>
@endsection
