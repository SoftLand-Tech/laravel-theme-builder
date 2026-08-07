@props(['props' => []])

@php
    $locale = app()->getLocale();
    $slides = $props['slides'] ?? [];
    $autoplay = $props['autoplay'] ?? true;
    $interval = (int) ($props['interval'] ?? 5) * 1000;
    $height = $props['height'] ?? 'medium';
    $minH = match ($height) {
        'small' => 'min-h-[320px]',
        'large' => 'min-h-[560px]',
        default => 'min-h-[440px]',
    };
@endphp

@if (! empty($slides))
    <section
        x-data="{ i: 0, count: {{ count($slides) }}, timer: null, next() { this.i = (this.i + 1) % this.count }, go(n) { this.i = n } }"
        @if ($autoplay && count($slides) > 1) x-init="timer = setInterval(() => this.next(), {{ $interval }})" @endif
        class="relative overflow-hidden bg-clay-800 {{ $minH }}"
    >
        @foreach ($slides as $idx => $slide)
            @php
                $title = $slide['title'][$locale] ?? ($slide['title']['ar'] ?? '');
                $subtitle = $slide['subtitle'][$locale] ?? ($slide['subtitle']['ar'] ?? '');
                $ctaText = $slide['ctaText'][$locale] ?? ($slide['ctaText']['ar'] ?? '');
                $img = $slide['image'] ?? null;
                $overlay = (int) ($slide['overlayOpacity'] ?? 30);
            @endphp
            <div
                x-show="i === {{ $idx }}"
                x-transition:enter="transition-opacity duration-(--duration-slow)"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                class="absolute inset-0"
                style="display: {{ $idx === 0 ? 'block' : 'none' }};"
            >
                @if ($img)
                    <img src="{{ $img }}" alt="{{ $title }}" class="h-full w-full object-cover" loading="{{ $idx === 0 ? 'eager' : 'lazy' }}" decoding="async">
                @endif
                <div class="absolute inset-0 bg-ink" style="opacity: {{ $overlay / 100 }}"></div>
                <div class="relative z-10 flex h-full items-center justify-center px-6 text-center text-paper">
                    <div class="max-w-prose">
                        @if ($title)
                            <h2 class="mb-3 font-display text-h1">{{ $title }}</h2>
                        @endif
                        @if ($subtitle)
                            <p class="mb-5 text-lead text-paper/90">{{ $subtitle }}</p>
                        @endif
                        @if ($ctaText)
                            <a href="{{ $slide['ctaUrl'] ?: '#' }}" class="inline-block rounded-pill bg-paper px-7 py-3 font-semibold text-ink transition-colors duration-(--duration-base) hover:bg-paper-deep focus-visible:outline-(length:--focus-ring-width) focus-visible:outline-(--focus-ring-color)">
                                {{ $ctaText }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        @if (count($slides) > 1)
            <div class="absolute inset-x-0 bottom-4 z-20 flex justify-center gap-2">
                @foreach ($slides as $idx => $slide)
                    <button
                        type="button"
                        @click="go({{ $idx }})"
                        :class="i === {{ $idx }} ? 'bg-paper' : 'bg-paper/50'"
                        class="h-2 w-8 rounded-pill transition-colors duration-(--duration-base) focus-visible:outline-(length:--focus-ring-width) focus-visible:outline-(--focus-ring-color)"
                        :aria-current="i === {{ $idx }}"
                        aria-label="{{ __('storefront.slide_number', ['number' => $idx + 1]) }}"
                    ></button>
                @endforeach
            </div>
        @endif
    </section>
@endif
