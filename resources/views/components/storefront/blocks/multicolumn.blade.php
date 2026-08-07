@props(['props' => []])

@php
    $locale = app()->getLocale();
    $title = $props['title'][$locale] ?? ($props['title']['ar'] ?? '');
    $columns = (int) ($props['columns'] ?? 3);
    $items = $props['items'] ?? [];
    $colClass = match (true) {
        $columns <= 2 => 'sm:grid-cols-2',
        $columns === 4 => 'sm:grid-cols-2 lg:grid-cols-4',
        $columns >= 5 => 'sm:grid-cols-3 lg:grid-cols-5',
        default => 'sm:grid-cols-3',
    };
@endphp

<x-theme-builder::storefront.section-heading :title="$title" />

@if (! empty($items))
    <div class="grid grid-cols-1 gap-6 {{ $colClass }}">
        @foreach ($items as $item)
            @php
                $label = $item['label'][$locale] ?? ($item['label']['ar'] ?? '');
                $text = $item['text'][$locale] ?? ($item['text']['ar'] ?? '');
                $icon = $item['icon'] ?? 'check';
                $image = $item['image'] ?? null;
                $url = $item['url'] ?? '';
            @endphp
            <div class="flex flex-col items-center rounded-card border border-line bg-surface p-6 text-center">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $label }}" loading="lazy" decoding="async" class="mb-4 h-16 w-16 object-contain">
                @else
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-pill bg-clay-50 text-clay-700">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6" aria-hidden="true">
                            <path d="{{ \SoftLand\ThemeBuilder\Support\IconSvg::path($icon) }}"></path>
                        </svg>
                    </div>
                @endif
                @if ($label)
                    <h3 class="mb-1 font-display text-h4 text-ink">{{ $label }}</h3>
                @endif
                @if ($text)
                    <p class="text-caption text-stone">{{ $text }}</p>
                @endif
                @if (! empty($item['ctaText']))
                    <a href="{{ $url ?: '#' }}" class="mt-3 text-caption font-semibold text-clay-700 hover:underline">
                        {{ $item['ctaText'][$locale] ?? ($item['ctaText']['ar'] ?? '') }}
                    </a>
                @endif
            </div>
        @endforeach
    </div>
@endif
