@props(['props' => [], 'products' => null])

@php
    $locale = app()->getLocale();
    $title = tb_bi($props['title']);
    $columns = (int) ($props['columns'] ?? 4);
    $mobile = (int) ($props['mobileColumns'] ?? 2);
    $mobileClass = $mobile === 1 ? 'grid-cols-1' : 'grid-cols-2';
    $colClass = match (true) {
        $columns <= 1 => '',
        $columns === 2 => '@sm:grid-cols-2',
        $columns === 3 => '@sm:grid-cols-2 @lg:grid-cols-3',
        $columns === 5 => '@sm:grid-cols-2 @lg:grid-cols-5',
        $columns === 6 => '@sm:grid-cols-3 @lg:grid-cols-6',
        default => '@sm:grid-cols-2 @lg:grid-cols-4',
    };
    $showAddToCart = ($props['showAddToCart'] ?? true) === true;
@endphp

<x-theme-builder::storefront.section-heading :title="$title" />

<div class="grid {{ $mobileClass }} gap-4 {{ $colClass }}">
    @foreach ($products ?? [] as $product)
        <x-theme-builder::product.card :product="$product" :show-add-to-cart="$showAddToCart" />
    @endforeach
</div>
