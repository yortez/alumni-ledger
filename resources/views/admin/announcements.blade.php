@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-7">
    <header class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-xl font-semibold">Announcements</h1><p class="mt-1 text-xs text-muted">{{ $announcementCount }} announcements</p></div>
        <details class="relative">
            <summary class="inline-flex min-h-9 cursor-pointer list-none items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark"><span aria-hidden="true">+</span>Post Announcement</summary>
            <div class="absolute right-0 z-20 mt-2 max-h-[82vh] w-[min(92vw,52rem)] overflow-y-auto rounded-md border border-line bg-surface shadow-xl">
                <h2 class="border-b border-line px-4 py-3 text-sm font-semibold">Announcement Details</h2>
                <form method="POST" action="{{ route('admin.announcements.store') }}">
                    @csrf
                    <div class="grid gap-x-3 gap-y-4 p-4 sm:p-5">
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
                    <div class="flex justify-end border-t border-line px-4 py-3 sm:px-5"><button type="submit" class="inline-flex items-center gap-2 rounded-md bg-forest px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-forest-dark"><span aria-hidden="true">&#10148;</span>Post Announcement</button></div>
                </form>
            </div>
        </details>
    </header>

    <form method="GET" action="{{ route('admin.announcements.index') }}" class="mb-4 flex flex-wrap items-center gap-2 rounded-md border border-line bg-surface p-3 sm:p-4">
        <label class="relative block w-full sm:max-w-72"><span class="sr-only">Search announcements</span><svg aria-hidden="true" class="absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="m16 16 4 4" /></svg><input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Search announcements..." class="w-full rounded-md border border-line bg-surface-raised py-2 pl-9 pr-3 text-xs text-ink placeholder:text-muted focus:border-forest focus:outline-none"></label>
        <label><span class="sr-only">Category</span><select name="category" class="min-w-32 rounded-md border border-line bg-surface-raised px-3 py-2 text-xs text-ink focus:border-forest focus:outline-none"><option value="">All Categories</option>@foreach ($categories as $value => $label)<option value="{{ $value }}" @selected($filters['category'] === $value)>{{ $label }}</option>@endforeach</select></label>
        <button type="submit" class="inline-flex min-h-9 items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16l-6 7v6l-4 2v-8L4 5z" /></svg>Filter</button>
        <a href="{{ route('admin.announcements.index') }}" class="inline-flex min-h-9 items-center rounded-md border border-line bg-surface-raised px-4 py-2 text-xs font-medium text-muted transition hover:text-ink">Reset</a>
    </form>

    <section class="grid gap-3">
        @forelse ($announcements as $announcement)
            @php($isExpired = $announcement->expires_at?->isPast() ?? false)
            <article class="overflow-hidden rounded-md border border-line bg-surface {{ $announcement->is_pinned ? 'border-t-2 border-t-amber-400' : '' }}">
                <div class="flex items-start justify-between gap-3 p-4 sm:px-5">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 text-[10px]">
                            @if ($announcement->is_pinned)<span class="font-semibold uppercase text-amber-400">&#9873; Pinned</span>@endif
                            <span class="rounded-full bg-surface-raised px-2.5 py-1 font-semibold text-ink">{{ \App\Models\Announcement::CATEGORIES[$announcement->category] ?? ucfirst($announcement->category) }}</span>
                            <span class="text-muted">{{ $announcement->published_at?->format('M j, Y') ?? 'Draft' }}</span>
                            @if ($isExpired)<span class="rounded-full bg-danger/15 px-2.5 py-1 font-semibold text-danger">Expired</span>@elseif (! $announcement->published_at)<span class="rounded-full bg-warning/15 px-2.5 py-1 font-semibold text-warning">Draft</span>@elseif ($announcement->published_at->isFuture())<span class="rounded-full bg-info/15 px-2.5 py-1 font-semibold text-info">Scheduled</span>@endif
                        </div>
                        <h2 class="mt-2 break-words text-sm font-semibold">{{ $announcement->title }}</h2>
                        <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-muted">{{ $announcement->body }}</p>
                        <p class="mt-2 text-[10px] text-muted">Posted by {{ $announcement->creator?->username ?? 'Administrator' }}@if ($announcement->expires_at) · Expires {{ $announcement->expires_at->format('M j, Y') }}@endif</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-1.5">
                        <a href="{{ route('admin.announcements.edit', $announcement) }}" title="Edit announcement" aria-label="Edit announcement" class="grid size-8 place-items-center rounded-md border border-line text-muted transition hover:bg-surface-raised hover:text-ink"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m16 4 4 4L9 19l-5 1 1-5L16 4zM14 6l4 4" /></svg></a>
                        <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}">@csrf @method('DELETE')<button type="submit" title="Delete announcement" aria-label="Delete announcement" class="grid size-8 place-items-center rounded-md bg-danger text-white transition hover:bg-danger/80"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6" /></svg></button></form>
                    </div>
                </div>
            </article>
        @empty
            <p class="rounded-md border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No announcements match these filters.</p>
        @endforelse
    </section>
    <div class="mt-4">{{ $announcements->links() }}</div>
</div>
@endsection
