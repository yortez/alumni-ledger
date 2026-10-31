@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="mx-auto w-full max-w-[1440px] px-5 pb-16 pt-8 sm:px-8 xl:px-20">
    <header class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-display font-semibold">Announcements</h1><p class="mt-1 text-meta text-ink-tertiary">{{ $announcementCount }} announcements</p></div>
        <a href="{{ route('admin.announcements.create') }}" class="inline-flex min-h-9 items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark"><span aria-hidden="true">+</span>Post Announcement</a>
    </header>

    <form method="GET" action="{{ route('admin.announcements.index') }}" class="panel mb-4 flex flex-wrap items-center gap-2 p-3 sm:p-4">
        <label class="relative block w-full sm:max-w-72"><span class="sr-only">Search announcements</span><svg aria-hidden="true" class="absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="m16 16 4 4" /></svg><input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Search announcements..." class="w-full rounded-md border border-line bg-surface-raised py-2 pl-9 pr-3 text-xs text-ink placeholder:text-muted focus:border-forest focus:outline-none"></label>
        <label><span class="sr-only">Category</span><select name="category" class="min-w-32 rounded-md border border-line bg-surface-raised px-3 py-2 text-xs text-ink focus:border-forest focus:outline-none"><option value="">All Categories</option>@foreach ($categories as $value => $label)<option value="{{ $value }}" @selected($filters['category'] === $value)>{{ $label }}</option>@endforeach</select></label>
        <button type="submit" class="inline-flex min-h-9 items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16l-6 7v6l-4 2v-8L4 5z" /></svg>Filter</button>
        <a href="{{ route('admin.announcements.index') }}" class="inline-flex min-h-9 items-center rounded-md border border-line bg-surface-raised px-4 py-2 text-xs font-medium text-muted transition hover:text-ink">Reset</a>
    </form>

    <section class="grid gap-3">
        @forelse ($announcements as $announcement)
            @php($isExpired = $announcement->expires_at?->isPast() ?? false)
            <article class="panel overflow-hidden {{ $announcement->is_pinned ? 'border-t-2 border-t-amber-400' : '' }}">
                <div class="flex items-start justify-between gap-3 p-4 sm:px-5">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 text-[10px]">
                            @if ($announcement->is_pinned)<span class="font-semibold uppercase text-amber-400">&#9873; Pinned</span>@endif
                            <span class="rounded-full bg-surface-raised px-2.5 py-1 font-semibold text-ink">{{ \App\Models\Announcement::CATEGORIES[$announcement->category] ?? ucfirst($announcement->category) }}</span>
                            <span class="text-muted">{{ $announcement->published_at?->format('M j, Y') ?? 'Draft' }}</span>
                            @if ($isExpired)<span class="rounded-full bg-danger/15 px-2.5 py-1 font-semibold text-danger">Expired</span>@elseif (! $announcement->published_at)<span class="rounded-full bg-warning/15 px-2.5 py-1 font-semibold text-warning">Draft</span>@elseif ($announcement->published_at->isFuture())<span class="rounded-full bg-info/15 px-2.5 py-1 font-semibold text-info">Scheduled</span>@endif
                        </div>
                        <h2 class="mt-2 break-words text-sm font-semibold"><button type="button" data-announcement-edit-open data-announcement-id="{{ $announcement->id }}" data-announcement-title="{{ $announcement->title }}" data-announcement-body="{{ $announcement->body }}" data-announcement-category="{{ $announcement->category }}" data-announcement-status="{{ $announcement->published_at ? 'published' : 'draft' }}" data-announcement-publish-date="{{ $announcement->published_at?->toDateString() }}" data-announcement-expiry-date="{{ $announcement->expires_at?->toDateString() }}" data-announcement-pinned="{{ $announcement->is_pinned ? '1' : '0' }}" data-update-url="{{ route('admin.announcements.update', $announcement) }}" data-delete-url="{{ route('admin.announcements.destroy', $announcement) }}" aria-haspopup="dialog" aria-controls="announcement-edit-dialog" class="text-left underline decoration-transparent underline-offset-4 transition hover:text-forest hover:decoration-current focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest">{{ $announcement->title }}</button></h2>
                        <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-muted">{{ $announcement->body }}</p>
                        <p class="mt-2 text-[10px] text-muted">Posted by {{ $announcement->creator?->username ?? 'Administrator' }}@if ($announcement->expires_at) · Expires {{ $announcement->expires_at->format('M j, Y') }}@endif</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-1.5">
                    </div>
                </div>
            </article>
        @empty
            <p class="rounded-md border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No announcements match these filters.</p>
        @endforelse
    </section>
    <div class="mt-4">{{ $announcements->links() }}</div>
    <dialog id="announcement-edit-dialog" data-reopen-for="{{ old('announcement_id') }}" aria-labelledby="announcement-edit-title" class="m-auto max-h-[calc(100vh-2rem)] w-[calc(100%-2rem)] max-w-3xl overflow-y-auto rounded-card border border-hairline bg-surface p-0 text-ink shadow-2xl backdrop:bg-black/45">
        <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
            <div><p class="eyebrow">Announcements</p><h2 id="announcement-edit-title" class="mt-1 text-lg font-semibold">Edit announcement</h2></div>
            <button type="button" data-announcement-edit-close aria-label="Close edit dialog" title="Close" class="grid size-9 shrink-0 place-items-center rounded-md border border-line text-muted transition hover:bg-paper hover:text-ink"><svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m18 6-12 12M6 6l12 12" /></svg></button>
        </div>
        @if ($errors->any())<p class="mx-5 mt-4 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-xs text-red-800" role="alert">{{ $errors->first() }}</p>@endif
        <form method="POST" action="{{ route('admin.announcements.index') }}" data-announcement-edit-form class="grid gap-4 px-5 py-5">@csrf @method('PATCH')
            <input type="hidden" name="announcement_id" value="{{ old('announcement_id') }}">
            <label class="grid gap-1.5 text-xs font-medium text-muted">Title<input name="title" value="{{ old('title') }}" maxlength="180" required placeholder="Announcement title" class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"></label>
            <label class="grid gap-1.5 text-xs font-medium text-muted">Content<textarea name="body" rows="6" maxlength="10000" required placeholder="Write your announcement here..." class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink">{{ old('body') }}</textarea></label>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-1.5 text-xs font-medium text-muted">Category<select name="category" required class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink">@foreach (\App\Models\Announcement::CATEGORIES as $value => $label)<option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>@endforeach</select></label>
                <label class="grid gap-1.5 text-xs font-medium text-muted">Status<select name="status" required class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"><option value="published" @selected(old('status') === 'published')>Published</option><option value="draft" @selected(old('status') === 'draft')>Draft</option></select></label>
                <label class="grid gap-1.5 text-xs font-medium text-muted">Publish date<input type="date" name="publish_date" value="{{ old('publish_date') }}" class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"></label>
                <label class="grid gap-1.5 text-xs font-medium text-muted">Expiry date<input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"></label>
            </div>
            <div><input type="hidden" name="is_pinned" value="0"><label class="flex items-center gap-2 rounded-md border border-line bg-surface-raised p-3 text-xs font-medium"><input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned'))> Pin this announcement</label><p class="mt-1 pl-6 text-[10px] text-muted">Pinned announcements appear at the top of the alumni dashboard.</p></div>
            <div class="flex justify-end gap-2 border-t border-line pt-4"><button type="button" data-announcement-edit-close class="inline-flex min-h-9 items-center justify-center rounded-md border border-line px-3 py-2 text-xs font-medium text-muted transition hover:text-ink">Cancel</button><button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark">Save changes</button></div>
        </form>
        <form method="POST" action="{{ route('admin.announcements.index') }}" data-announcement-delete-form onsubmit="return confirm('Delete this announcement?')" class="border-t border-line px-5 py-3">@csrf @method('DELETE')<button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-md border border-red-300 px-3 py-2 text-xs font-medium text-red-700 transition hover:bg-red-50">Delete announcement</button></form>
    </dialog>
</div>
@endsection
