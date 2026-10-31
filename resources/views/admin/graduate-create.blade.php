@extends('layouts.app')

@section('title', 'Add alumni record')

@section('content')
<div class="mx-auto w-full max-w-5xl px-5 pb-16 pt-8 sm:px-8">
    <div class="mb-5 flex items-center justify-between gap-4 border-b border-line pb-5">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="mt-1 text-display font-semibold">Add alumni record</h1>
        </div>
        <a href="{{ route('admin.graduates.index') }}" class="text-xs font-medium text-forest underline underline-offset-4">Cancel</a>
    </div>

    <section class="panel overflow-hidden">
        <h2 class="border-b border-line px-4 py-3 text-sm font-semibold">Alumni Details</h2>
        <form method="POST" action="{{ route('admin.graduates.store') }}" class="grid gap-4 p-4 sm:grid-cols-2 sm:p-5">
            @csrf
            <div class="field"><label for="student_number">Student number</label><input id="student_number" name="student_number" value="{{ old('student_number') }}" required>@error('student_number')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="first_name">First name</label><input id="first_name" name="first_name" value="{{ old('first_name') }}" required>@error('first_name')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="middle_name">Middle name</label><input id="middle_name" name="middle_name" value="{{ old('middle_name') }}">@error('middle_name')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="last_name">Last name</label><input id="last_name" name="last_name" value="{{ old('last_name') }}" required>@error('last_name')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="program_code">Program code</label><input id="program_code" name="program_code" value="{{ old('program_code') }}">@error('program_code')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="batch_year">Batch year</label><input id="batch_year" type="number" min="1900" max="2100" name="batch_year" value="{{ old('batch_year') }}">@error('batch_year')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field sm:col-span-2"><label for="latin_honor">Latin honor</label><select id="latin_honor" name="latin_honor" required>@foreach (\App\Models\Graduate::LATIN_HONORS as $honor)<option value="{{ $honor }}" @selected(old('latin_honor', 'none') === $honor)>{{ ucwords(str_replace('_', ' ', $honor)) }}</option>@endforeach</select>@error('latin_honor')<p class="field-error">{{ $message }}</p>@enderror</div>
            <button class="bg-forest px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-forest-dark sm:col-span-2" type="submit">Add to records</button>
        </form>
    </section>

    <section class="panel mt-5 overflow-hidden">
        <h2 class="border-b border-line px-4 py-3 text-sm font-semibold">Import records from CSV</h2>
        <form method="POST" action="{{ route('admin.graduates.import') }}" enctype="multipart/form-data" class="grid gap-3 p-4 sm:p-5">
            @csrf
            <div class="field"><label for="file">CSV file</label><input id="file" type="file" name="file" accept=".csv,text/csv" required>@error('file')<p class="field-error">{{ $message }}</p>@enderror</div>
            <p class="text-xs leading-5 text-muted">Headers: student_number, first_name, last_name, middle_name, program_code, batch_year, latin_honor</p>
            <a href="{{ route('admin.graduates.import-template') }}" class="text-xs font-medium text-forest underline underline-offset-4">Download sample CSV template</a>
            <button class="justify-self-start border border-forest px-4 py-2.5 text-sm font-semibold text-forest transition hover:bg-mint" type="submit">Import CSV</button>
        </form>
    </section>
</div>
@endsection
