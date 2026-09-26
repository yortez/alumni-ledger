@extends('layouts.app')

@section('title', 'Edit graduate')

@section('content')
<div class="mx-auto max-w-3xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="mb-6 flex items-center justify-between gap-4 border-b border-line pb-6">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="font-display mt-3 text-4xl sm:text-5xl">Edit graduate</h1>
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
                <label for="name">Full name</label>
                <input id="name" name="name" value="{{ old('name', $graduate->name) }}" required>
                @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="program">Program</label>
                <input id="program" name="program" value="{{ old('program', $graduate->program) }}">
                @error('program')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="graduation_year">Graduation year</label>
                <input id="graduation_year" type="number" min="1900" max="2100" name="graduation_year" value="{{ old('graduation_year', $graduate->graduation_year) }}">
                @error('graduation_year')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="bg-forest px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-forest-dark">Save changes</button>
        </form>
    </section>
</div>
@endsection
