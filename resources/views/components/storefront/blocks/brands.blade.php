@props(['props' => []])

@php
    $locale = app()->getLocale();
    $title = tb_bi($props['title']);
    $items = $props['items'] ?? [];
@endphp

<x-theme-builder::storefront.section-heading :title="$title" />

@if (! empty($items))
    <div class="grid grid-cols-2 items-center gap-6 @sm:grid-cols-3 @md:grid-cols-4 @lg:grid-cols-6">
        @foreach ($items as $item)
            @php
                $image = $item['image'] ?? null;
                $label = tb_bi($item['label']);
                $url = $item['url'] ?? '';
                $tag = $url ? 'a' : 'div';
            @endphp
            <{{ $tag }}
                @if ($url) href="{{ $url }}" @endif
                @class([
                    'flex flex-col items-center gap-2 text-center opacity-70',
                    'transition-opacity duration-(--duration-base) hover:opacity-100' => (bool) $url,
                ])
            >
                @if ($image)
                    {{-- Logos must not be cropped, so this stays object-contain rather than <x-theme-builder::ui.media>. --}}
                    <img src="{{ $image }}" alt="{{ $label }}" loading="lazy" decoding="async" class="h-16 max-w-full object-contain">
                @endif
                @if ($label)
                    <span class="text-caption text-stone">{{ $label }}</span>
                @endif
            </{{ $tag }}>
        @endforeach
    </div>
@endif
