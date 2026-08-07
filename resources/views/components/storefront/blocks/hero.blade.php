@props(['props' => [], 'products' => null, 'tabProducts' => null, 'categories' => null, 'product' => null])

@php
    $locale = app()->getLocale();
    $title = $props['title'][$locale] ?? ($props['title']['ar'] ?? '');
    $subtitle = $props['subtitle'][$locale] ?? ($props['subtitle']['ar'] ?? '');
    $ctaText = $props['ctaText'][$locale] ?? ($props['ctaText']['ar'] ?? '');
    $ctaUrl = $props['ctaUrl'] ?: '#';
    $layout = $props['layout'] ?? 'center';
    $bg = $props['backgroundImage'] ?? null;
    $overlay = (int) ($props['overlayOpacity'] ?? 30);
    $height = $props['height'] ?? 'medium';
    $align = match ($layout) {
        'start', 'left' => 'items-start text-start',
        'end', 'right' => 'items-end text-end',
        default => 'items-center text-center',
    };
    $minH = match ($height) {
        'small' => 'min-h-[320px]',
        'large' => 'min-h-[560px]',
        default => 'min-h-[420px]',
    };
@endphp

<section class="relative flex {{ $minH }} flex-col justify-center overflow-hidden bg-clay-700 px-6 py-16 text-paper">
    @if ($bg)
        <img src="{{ $bg }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="eager" fetchpriority="high" decoding="async">
        <div class="absolute inset-0 bg-ink" style="opacity: {{ $overlay / 100 }};"></div>
    @endif

    <div class="relative z-10 flex w-full flex-col gap-4 {{ $align }}">
        <h1 class="font-display text-display tracking-tight">{{ $title }}</h1>
        @if ($subtitle)
            <p class="max-w-prose text-lead text-paper/90">{{ $subtitle }}</p>
        @endif
        @if ($ctaText)
            <a href="{{ $ctaUrl }}" class="mt-4 inline-block w-fit rounded-pill bg-paper px-7 py-3 font-semibold text-clay-700 shadow-elevation-2 transition-colors duration-(--duration-base) hover:bg-paper-deep focus-visible:outline-(length:--focus-ring-width) focus-visible:outline-(--focus-ring-color)">
                {{ $ctaText }}
            </a>
        @endif
    </div>
</section>
