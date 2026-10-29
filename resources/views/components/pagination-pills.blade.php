@props(['paginator'])

@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $from = max(1, $current - 2);
    $to = min($last, $current + 2);
@endphp

<nav aria-label="Pagination" class="flex items-center gap-1">
    @if ($paginator->onFirstPage())
        <span aria-disabled="true" class="grid size-8 place-items-center rounded-full text-ink-tertiary/50">
            <span class="sr-only">Previous page</span>
            <svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 6-6 6 6 6"/></svg>
        </span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="grid size-8 place-items-center rounded-full text-ink-secondary transition hover:bg-divider-soft">
            <span class="sr-only">Previous page</span>
            <svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 6-6 6 6 6"/></svg>
        </a>
    @endif

    @foreach ($paginator->getUrlRange($from, $to) as $page => $url)
        @if ($page === $current)
            <span aria-current="page" class="grid size-8 place-items-center rounded-full bg-ink text-meta font-semibold tabular-nums text-white">{{ $page }}</span>
        @else
            <a href="{{ $url }}" class="grid size-8 place-items-center rounded-full text-meta tabular-nums text-ink-secondary transition hover:bg-divider-soft">{{ $page }}</a>
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="grid size-8 place-items-center rounded-full text-ink-secondary transition hover:bg-divider-soft">
            <span class="sr-only">Next page</span>
            <svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
        </a>
    @else
        <span aria-disabled="true" class="grid size-8 place-items-center rounded-full text-ink-tertiary/50">
            <span class="sr-only">Next page</span>
            <svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
        </span>
    @endif
</nav>
