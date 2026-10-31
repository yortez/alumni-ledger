@extends('layouts.app')

@section('title', 'Create announcement')

@section('content')
<div class="mx-auto w-full max-w-5xl px-5 pb-16 pt-8 sm:px-8">
    <div class="mb-5 flex items-center justify-between gap-4 border-b border-line pb-5">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="mt-1 text-display font-semibold">Create announcement</h1>
        </div>
        <a href="{{ route('admin.announcements.index') }}" class="text-xs font-medium text-forest underline underline-offset-4">Cancel</a>
    </div>

    <section class="panel overflow-hidden">
        <h2 class="border-b border-line px-4 py-3 text-sm font-semibold">Announcement Details</h2>
        <form method="POST" action="{{ route('admin.announcements.store') }}">
            @csrf
            <div class="grid gap-4 p-4 sm:p-5">
                <div class="field"><label for="title">Title <span class="text-danger">*</span></label><input id="title" name="title" maxlength="180" value="{{ old('title') }}" placeholder="Announcement title" required>@error('title')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="body">Content <span class="text-danger">*</span></label><textarea id="body" name="body" rows="6" maxlength="10000" placeholder="Write your announcement here..." required>{{ old('body') }}</textarea>@error('body')<p class="field-error">{{ $message }}</p>@enderror</div>
            </div>
            <section class="border-t border-line">
                <h2 class="border-b border-line px-4 py-3 text-sm font-semibold">Publish Settings</h2>
                <div class="grid gap-x-3 gap-y-4 p-4 sm:grid-cols-2 sm:p-5">
                    <div class="field"><label for="category">Category</label><select id="category" name="category" required>@foreach (\App\Models\Announcement::CATEGORIES as $value => $label)<option value="{{ $value }}" @selected(old('category', 'general') === $value)>{{ $label }}</option>@endforeach</select>@error('category')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="status">Status</label><select id="status" name="status" required><option value="published" @selected(old('status', 'published') === 'published')>Published</option><option value="draft" @selected(old('status') === 'draft')>Draft</option></select>@error('status')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="publish_date">Publish date</label><input id="publish_date" type="date" name="publish_date" value="{{ old('publish_date', now()->toDateString()) }}">@error('publish_date')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="expiry_date">Expiry date</label><input id="expiry_date" type="date" name="expiry_date" value="{{ old('expiry_date') }}">@error('expiry_date')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="sm:col-span-2"><input type="hidden" name="is_pinned" value="0"><label class="flex items-center gap-2 rounded-md border border-line bg-surface-raised p-3 text-xs font-medium"><input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned'))> Pin this announcement</label><p class="mt-1 pl-6 text-[10px] text-muted">Pinned announcements appear at the top of the list.</p>@error('is_pinned')<p class="field-error mt-1">{{ $message }}</p>@enderror</div>
                </div>
            </section>
            <div class="flex justify-end border-t border-line px-4 py-3 sm:px-5"><button type="submit" class="inline-flex items-center gap-2 rounded-md bg-forest px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-forest-dark">Post Announcement</button></div>
        </form>
    </section>
</div>
@endsection
