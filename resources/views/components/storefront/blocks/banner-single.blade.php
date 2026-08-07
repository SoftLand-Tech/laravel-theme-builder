@props(['props' => []])

@php
    $locale = app()->getLocale();
    $image = $props['image'] ?? null;
    $title = $props['title'][$locale] ?? ($props['title']['ar'] ?? '');
    $subtitle = $props['subtitle'][$locale] ?? ($props['subtitle']['ar'] ?? '');
    $ctaText = $props['ctaText'][$locale] ?? ($props['ctaText']['ar'] ?? '');
    $ctaUrl = $props['ctaUrl'] ?: '#';
    $align = $props['align'] ?? 'center';
    $overlay = (int) ($props['overlayOpacity'] ?? 40);
    $height = $props['height'] ?? 'medium';
    $alignClass = $align === 'start' ? 'items-start text-start' : ($align === 'end' ? 'items-end text-end' : 'items-center text-center');
    $minH = match ($height) {
        'small' => 'min-h-[220px]',
        'large' => 'min-h-[380px]',
        default => 'min-h-[280px]',
    };
@endphp

<div class="relative flex {{ $minH }} flex-col justify-center overflow-hidden rounded-card bg-clay-800 px-8 py-12 text-paper {{ $alignClass }}">
    @if ($image)
        <img src="{{ $image }}" alt="" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-ink" style="opacity: {{ $overlay / 100 }};"></div>
    @endif
    <div class="relative z-10 max-w-prose">
        @if ($title)
            <h3 class="mb-2 font-display text-h2">{{ $title }}</h3>
        @endif
        @if ($subtitle)
            <p class="text-lead text-paper/85">{{ $subtitle }}</p>
        @endif
        @if ($ctaText)
            <a href="{{ $ctaUrl }}" class="mt-4 inline-block rounded-pill bg-paper px-6 py-2.5 text-caption font-semibold text-ink transition-colors duration-(--duration-base) hover:bg-paper-deep focus-visible:outline-(length:--focus-ring-width) focus-visible:outline-(--focus-ring-color)">
                {{ $ctaText }}
            </a>
        @endif
    </div>
</div>
