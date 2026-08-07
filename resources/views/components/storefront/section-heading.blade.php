@props([
    'eyebrow' => null,
    'title' => null,
    'subtitle' => null,
    'level' => 2,
    'size' => 'h2',
])

@php
    $sizes = [
        'h1' => 'text-h1',
        'h2' => 'text-h2',
        'h3' => 'text-h3',
        'h4' => 'text-h4',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['h2'];
@endphp

@if ($eyebrow || $title || $subtitle || isset($action))
    <div {{ $attributes->class(['mb-8 flex flex-wrap items-end justify-between gap-4']) }}>
        <div class="space-y-2">
            @if ($eyebrow)
                <p class="eyebrow text-clay-700">{{ $eyebrow }}</p>
            @endif

            @if ($title)
                <h{{ $level }} class="font-display {{ $sizeClass }} text-ink">{{ $title }}</h{{ $level }}>
            @endif

            @if ($subtitle)
                <p class="max-w-prose text-body text-ink-soft">{{ $subtitle }}</p>
            @endif
        </div>

        @isset($action)
            <div class="text-caption">{{ $action }}</div>
        @endisset
    </div>
@endif
