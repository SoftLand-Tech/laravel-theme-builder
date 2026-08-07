@props(['props' => [], 'products' => null])

@php
    $locale = app()->getLocale();
    $title = $props['title'][$locale] ?? ($props['title']['ar'] ?? '');
    $autoplay = ($props['autoplay'] ?? false) === true;
    $interval = max(3, min(15, (int) ($props['interval'] ?? 5)));
    $hasProducts = $products !== null && count($products) > 0;
@endphp

@if ($title !== '' || $hasProducts)
    @if ($title !== '')
        <x-theme-builder::storefront.section-heading :title="$title" />
    @endif

    @if ($hasProducts)
        <x-theme-builder::ui.carousel :label="$title ?: null" :autoplay="$autoplay" :interval="$interval">
            @foreach ($products as $product)
                <div class="w-[220px] shrink-0 snap-start sm:w-[260px]">
                    <x-theme-builder::product.card :product="$product" />
                </div>
            @endforeach
        </x-theme-builder::ui.carousel>
    @elseif ($title !== '')
        <p class="text-body text-stone">{{ __('storefront.no_products_in_collection') }}</p>
    @endif
@endif
