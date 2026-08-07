@props(['props' => []])

@php
    $locale = app()->getLocale();
    $image = $props['image'] ?? null;
    $position = $props['imagePosition'] ?? 'start';
    $title = $props['title'][$locale] ?? ($props['title']['ar'] ?? '');
    $content = $props['content'][$locale] ?? ($props['content']['ar'] ?? '');
    $ctaText = $props['ctaText'][$locale] ?? ($props['ctaText']['ar'] ?? '');
    $ctaUrl = $props['ctaUrl'] ?: '#';
@endphp

<div class="grid grid-cols-1 items-center gap-8 md:grid-cols-2">
    <x-theme-builder::ui.media :src="$image" :alt="$title" ratio="landscape" class="{{ $position === 'end' ? 'md:order-2' : '' }}" />

    <div class="{{ $position === 'end' ? 'md:order-1' : '' }}">
        @if ($title)
            <h2 class="mb-3 font-display text-h2 text-ink">{{ $title }}</h2>
        @endif
        <div class="rich-text max-w-prose text-body text-ink-soft">
            {!! $content !!}
        </div>
        @if ($ctaText)
            <a href="{{ $ctaUrl }}" class="mt-5 inline-block rounded-pill bg-clay-600 px-6 py-2.5 text-caption font-semibold text-paper transition-colors duration-(--duration-base) hover:bg-clay-700 focus-visible:outline-(length:--focus-ring-width) focus-visible:outline-(--focus-ring-color)">
                {{ $ctaText }}
            </a>
        @endif
    </div>
</div>
