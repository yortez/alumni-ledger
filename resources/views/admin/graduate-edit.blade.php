@extends('layouts.app')

@section('title', 'Edit alumni record')

@section('content')
<div class="mx-auto max-w-5xl px-5 py-7 sm:px-8">
    <div class="mb-5 flex items-center justify-between gap-4 border-b border-line pb-5">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="mt-1 text-xl font-semibold">Edit alumni record</h1>
        </div>
        <a href="{{ route('admin.graduates.index') }}" class="text-sm font-semibold text-forest underline underline-offset-4">Back to roster</a>
    </div>

    <section class="border border-line bg-white p-5 sm:p-6">
        <form method="POST" action="{{ route('admin.graduates.update', $graduate) }}" class="grid gap-5">
            @csrf
            @method('PATCH')

            <div class="field">
                <label for="student_number">Student number</label>
                <input id="student_number" name="student_number" value="{{ old('student_number', $graduate->student_number) }}" required>
                @error('student_number')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="first_name">First name</label>
                <input id="first_name" name="first_name" value="{{ old('first_name', $graduate->first_name) }}" required>
                @error('first_name')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="middle_name">Middle name</label>
                <input id="middle_name" name="middle_name" value="{{ old('middle_name', $graduate->middle_name) }}">
                @error('middle_name')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="last_name">Last name</label>
                <input id="last_name" name="last_name" value="{{ old('last_name', $graduate->last_name) }}" required>
                @error('last_name')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="program_code">Program code</label>
                <input id="program_code" name="program_code" value="{{ old('program_code', $graduate->program_code) }}">
                @error('program_code')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="batch_year">Batch year</label>
                <input id="batch_year" type="number" min="1900" max="2100" name="batch_year" value="{{ old('batch_year', $graduate->batch_year) }}">
                @error('batch_year')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="latin_honor">Latin honor</label>
                <select id="latin_honor" name="latin_honor" required>
                    @foreach (\App\Models\Graduate::LATIN_HONORS as $honor)
                        <option value="{{ $honor }}" @selected(old('latin_honor', $graduate->latin_honor) === $honor)>{{ ucwords(str_replace('_', ' ', $honor)) }}</option>
                    @endforeach
                </select>
                @error('latin_honor')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="bg-forest px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-forest-dark">Save changes</button>
        </form>
    </section>
</div>
@endsection
