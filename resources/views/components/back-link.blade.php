@props(['href', 'label'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 text-caption font-semibold text-ink-tertiary transition hover:text-ink']) }}>
    <x-chevron class="rotate-180" />
    {{ $label }}
</a>