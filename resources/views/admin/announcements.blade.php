@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="flex flex-col justify-between gap-6 border-b border-line pb-8 sm:flex-row sm:items-end">
        <div><p class="eyebrow">Administration</p><h1 class="font-display mt-3 text-4xl sm:text-5xl">Announcements</h1></div>
        <div class="flex items-center gap-6"><p class="text-sm text-muted"><span class="font-display text-3xl text-ink">{{ $publishedCount }}</span> published</p><a class="text-sm font-semibold text-forest underline underline-offset-4" href="{{ route('admin.graduates.index') }}">Graduate list</a></div>
    </div>

    <div class="mt-8 grid items-start gap-8 lg:grid-cols-[0.8fr_1.2fr]">
        <section class="border border-line bg-white p-5 sm:p-6">
            <p class="eyebrow">Compose</p>
            <h2 class="font-display mt-2 text-2xl">New announcement</h2>
            <form method="POST" action="{{ route('admin.announcements.store') }}" class="mt-5 grid gap-5">
                @csrf
                <div class="field"><label for="title">Title</label><input id="title" name="title" maxlength="180" value="{{ old('title') }}" required>@error('title')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="body">Message</label><textarea id="body" name="body" rows="7" maxlength="10000" required>{{ old('body') }}</textarea>@error('body')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="status">Status</label><select id="status" name="status" required><option value="published" @selected(old('status', 'published') === 'published')>Publish now</option><option value="draft" @selected(old('status') === 'draft')>Save as draft</option></select>@error('status')<p class="field-error">{{ $message }}</p>@enderror</div>
                <button type="submit" class="flex items-center justify-between bg-forest px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-forest-dark"><span>Create announcement</span><span aria-hidden="true">&#8599;</span></button>
            </form>
        </section>

        <section class="min-w-0">
            <div class="mb-4 flex items-end justify-between gap-4"><h2 class="font-display text-2xl">All announcements</h2><span class="text-xs uppercase tracking-wider text-muted">{{ $announcements->total() }} total</span></div>
            <div class="grid gap-3">
                @forelse ($announcements as $announcement)
                    <article class="border border-line bg-white p-5 sm:p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><span class="text-xs font-semibold uppercase tracking-wider {{ $announcement->published_at ? 'text-forest' : 'text-terracotta' }}">{{ $announcement->published_at ? 'Published' : 'Draft' }}</span><span class="text-xs text-muted">{{ $announcement->published_at?->format('M j, Y') ?? 'Not published' }}</span></div><h3 class="mt-2 break-words font-display text-2xl">{{ $announcement->title }}</h3></div>
                            <span class="text-xs text-muted">{{ $announcement->creator?->username ?? 'Administrator' }}</span>
                        </div>
                        <p class="mt-4 whitespace-pre-line break-words text-sm leading-6 text-muted">{{ $announcement->body }}</p>
                        <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-line pt-4">
                            <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $announcement->published_at ? 'draft' : 'published' }}">
                                <button type="submit" class="text-sm font-semibold text-forest underline underline-offset-4">{{ $announcement->published_at ? 'Move to draft' : 'Publish' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-medium text-terracotta underline underline-offset-4">Delete</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No announcements yet.</p>
                @endforelse
            </div>
            <div class="mt-5">{{ $announcements->links() }}</div>
        </section>
    </div>
</div>
@endsection
