@props([
    'src' => null,
    'alt' => '',
    'ratio' => 'square',
    'placeholder' => null,
    'eager' => false,
    'srcset' => null,
    'sizes' => null,
])

@php
    $ratios = [
        'square' => 'aspect-square',
        'portrait' => 'aspect-[3/4]',
        'landscape' => 'aspect-[4/3]',
        'wide' => 'aspect-[16/9]',
        'ultrawide' => 'aspect-[21/9]',
    ];
    $ratioClass = $ratios[$ratio] ?? $ratios['square'];
@endphp

<div {{ $attributes->class(['relative overflow-hidden rounded-(--radius-card) bg-paper-deep', $ratioClass]) }}>
    @if ($src)
        <img
            src="{{ $src }}"
            @if ($srcset) srcset="{{ $srcset }}" @endif
            @if ($sizes) sizes="{{ $sizes }}" @endif
            alt="{{ $alt }}"
            loading="{{ $eager ? 'eager' : 'lazy' }}"
            @if ($eager) fetchpriority="high" @endif
            decoding="async"
            class="absolute inset-0 h-full w-full object-cover"
        >
    @else
        <div class="absolute inset-0 flex items-center justify-center" aria-hidden="true">
            @if ($placeholder)
                <span class="font-display text-5xl text-stone/40">{{ $placeholder }}</span>
            @else
                <svg class="h-10 w-10 text-stone/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                    <circle cx="8.5" cy="8.5" r="1.5" />
                    <path d="m21 15-5-5L5 21" />
                </svg>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
