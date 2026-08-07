@props(['props' => [], 'posts' => null])

@php
    $title = tb_bi($props['title']);
    $columns = (int) ($props['columns'] ?? 3);
    $mobile = (int) ($props['mobileColumns'] ?? 1);
    $mobileClass = $mobile === 1 ? 'grid-cols-1' : 'grid-cols-2';
    $colClass = match (true) {
        $columns <= 1 => '',
        $columns === 2 => '@sm:grid-cols-2',
        $columns === 4 => '@sm:grid-cols-2 @lg:grid-cols-4',
        default => '@sm:grid-cols-2 @lg:grid-cols-3',
    };
@endphp

<x-theme-builder::storefront.section-heading :title="$title" />

<div class="grid {{ $mobileClass }} gap-4 {{ $colClass }}">
    @foreach ($posts ?? [] as $post)
        <x-theme-builder::storefront.blog.card :post="$post" />
    @endforeach
</div>
