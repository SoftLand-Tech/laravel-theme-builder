@props(['props' => []])

@php
    $locale = app()->getLocale();
    $image = $props['image'] ?? null;
    $title = $props['title'][$locale] ?? ($props['title']['ar'] ?? '');
    $subtitle = $props['subtitle'][$locale] ?? ($props['subtitle']['ar'] ?? '');
    $ctaText = $props['ctaText'][$locale] ?? ($props['ctaText']['ar'] ?? '');
    $ctaUrl = $props['ctaUrl'] ?: '#';
    $overlay = (int) ($props['overlayOpacity'] ?? 40);
@endphp

<section
    @class([
        'relative flex min-h-[400px] flex-col items-center justify-center overflow-hidden px-6 py-20 text-center text-paper',
        'bg-clay-800' => ! $image,
    ])
    @if ($image) style="background-image: url('{{ $image }}'); background-attachment: fixed; background-size: cover; background-position: center;" @endif
>
    @if ($image)
        <div class="absolute inset-0 bg-ink" style="opacity: {{ $overlay / 100 }};"></div>
    @endif
    <div class="relative z-10 max-w-prose">
        @if ($title)
            <h2 class="mb-3 font-display text-h1">{{ $title }}</h2>
        @endif
        @if ($subtitle)
            <p class="mb-6 text-lead text-paper/85">{{ $subtitle }}</p>
        @endif
        @if ($ctaText)
            <a href="{{ $ctaUrl }}" class="inline-block rounded-pill bg-paper px-7 py-3 font-semibold text-ink transition-colors duration-(--duration-base) hover:bg-paper-deep focus-visible:outline-(length:--focus-ring-width) focus-visible:outline-(--focus-ring-color)">
                {{ $ctaText }}
            </a>
        @endif
    </div>
</section>
