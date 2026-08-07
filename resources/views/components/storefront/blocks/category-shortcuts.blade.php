@props(['props' => [], 'categoryLinks' => null])

@php
    $locale = app()->getLocale();
    $title = tb_bi($props['title']);
    $links = $categoryLinks ?? [];
@endphp

<x-theme-builder::storefront.section-heading :title="$title" />

@if (! empty($links))
    <div class="grid grid-cols-2 gap-3 @sm:grid-cols-3 @md:grid-cols-6">
        @foreach ($links as $link)
            <a href="{{ tb_route('storefront.category', ['slug' => $link['slug'] ?? '']) }}" class="group flex flex-col items-center gap-2 text-center">
                <span class="flex h-16 w-16 items-center justify-center rounded-pill bg-clay-50 text-clay-700 transition-colors duration-(--duration-base) group-hover:bg-clay-100">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-7 w-7" aria-hidden="true">
                        <path d="{{ \SoftLand\ThemeBuilder\Support\IconSvg::path($link['icon'] ?? 'box') }}"></path>
                    </svg>
                </span>
                <span class="text-caption font-medium text-ink-soft">{{ $link['label'][$locale] ?? $link['name'] }}</span>
            </a>
        @endforeach
    </div>
@endif
