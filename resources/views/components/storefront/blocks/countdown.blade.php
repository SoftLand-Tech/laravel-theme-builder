@props(['props' => []])

@php
    $locale = app()->getLocale();
    $title = $props['title'][$locale] ?? ($props['title']['ar'] ?? '');
    $endsAt = $props['endsAt'] ?? null;
    $ctaText = $props['ctaText'][$locale] ?? ($props['ctaText']['ar'] ?? '');
    $ctaUrl = $props['ctaUrl'] ?: '#';
    $timestamp = $endsAt ? \Carbon\Carbon::parse($endsAt)->timestamp * 1000 : null;
@endphp

{{-- warning-600 with an alpha modifier: the 50/200/700 steps of this ramp are
     not declared in @theme, so `bg-warning-50` would compile to nothing. --}}
<div @if ($timestamp) x-data="countdownTimer({{ $timestamp }})" @endif class="rounded-card border border-warning-600/30 bg-warning-600/10 p-8 text-center">
    @if ($title)
        <h2 class="mb-4 font-display text-h2 text-ink">{{ $title }}</h2>
    @endif

    <div class="flex justify-center gap-4">
        @foreach (['days', 'hours', 'minutes', 'seconds'] as $unit)
            <div class="rounded-card bg-surface px-4 py-3 shadow-elevation-1">
                <div class="font-display text-h3 text-ink" @if ($timestamp) x-text="{{ $unit }}" @endif>00</div>
                <div class="text-caption text-stone">{{ __('storefront.countdown.'.$unit) }}</div>
            </div>
        @endforeach
    </div>

    @if ($ctaText)
        <a href="{{ $ctaUrl }}" class="mt-5 inline-block rounded-pill bg-warning-600 px-6 py-2.5 text-caption font-semibold text-ink transition-opacity duration-(--duration-base) hover:opacity-90 focus-visible:outline-(length:--focus-ring-width) focus-visible:outline-(--focus-ring-color)">
            {{ $ctaText }}
        </a>
    @endif
</div>
