@props(['props' => []])

@php
    $locale = app()->getLocale();
    $title = tb_bi($props['title']);
    $items = $props['items'] ?? [];
@endphp

<x-theme-builder::storefront.section-heading :title="$title" />

@if (! empty($items))
    <div class="grid grid-cols-1 gap-4 @sm:grid-cols-2 @md:grid-cols-3">
        @foreach ($items as $item)
            @php
                $quote = tb_bi($item['quote']);
                $author = tb_bi($item['author']);
            @endphp
            <figure class="rounded-card border border-line bg-surface p-6">
                <blockquote class="text-body text-ink-soft">{{ $quote }}</blockquote>
                @if ($author)
                    <figcaption class="mt-3 text-caption text-stone">{{ $author }}</figcaption>
                @endif
            </figure>
        @endforeach
    </div>
@else
    <p class="text-body text-stone">{{ __('storefront.no_testimonials') }}</p>
@endif
