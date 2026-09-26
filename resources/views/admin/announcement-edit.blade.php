@extends('layouts.app')

@section('title', 'Edit announcement')

@section('content')
<div class="mx-auto max-w-3xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="mb-6 flex items-center justify-between gap-4 border-b border-line pb-6">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="font-display mt-3 text-4xl sm:text-5xl">Edit announcement</h1>
        </div>
        <a href="{{ route('admin.announcements.index') }}" class="text-sm font-semibold text-forest underline underline-offset-4">Back to announcements</a>
    </div>

    <section class="border border-line bg-white p-5 sm:p-6">
        <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" class="grid gap-5">
            @csrf
            @method('PATCH')

            <div class="field">
                <label for="title">Title</label>
                <input id="title" name="title" maxlength="180" value="{{ old('title', $announcement->title) }}" required>
                @error('title')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="body">Message</label>
                <textarea id="body" name="body" rows="7" maxlength="10000" required>{{ old('body', $announcement->body) }}</textarea>
                @error('body')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="published" @selected(old('status', $announcement->published_at ? 'published' : 'draft') === 'published')>Publish now</option>
                    <option value="draft" @selected(old('status', $announcement->published_at ? 'published' : 'draft') === 'draft')>Save as draft</option>
                </select>
            </div>

            <button type="submit" class="bg-forest px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-forest-dark">Save announcement</button>
        </form>
    </section>
</div>
@endsection
