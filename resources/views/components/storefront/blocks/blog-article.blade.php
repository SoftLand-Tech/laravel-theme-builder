@props(['props' => [], 'post' => null])

@php
    $title = tb_bi($props['title']);
    $ctaText = tb_bi($props['ctaText']);
    $showExcerpt = ($props['showExcerpt'] ?? true) === true;

    $p = is_array($post) ? $post : [];
    $cover = $p['image'] ?? null;
    $slug = $p['slug'] ?? null;
    $category = $p['category'] ?? null;
    $catSlug = is_array($category) ? ($category['slug'] ?? null) : null;
@endphp

@if ($post)
    <div class="grid items-center gap-8 @lg:grid-cols-2">
        @if ($cover)
            <a href="{{ tb_route('storefront.blog.show', ['slug' => $slug]) }}" class="block overflow-hidden rounded-card">
                <img src="{{ $cover }}" alt="{{ tb_bi($p['title'] ?? '') }}" class="aspect-[4/3] w-full object-cover">
            </a>
        @endif

        <div class="flex flex-col gap-4">
            @if ($title)
                <x-theme-builder::storefront.section-heading :title="$title" />
            @endif

            @if ($catSlug)
                <a href="{{ tb_route('storefront.blog.category', ['slug' => $catSlug]) }}" class="w-fit text-caption font-medium text-clay-600">{{ tb_bi($category['name'] ?? '') }}</a>
            @endif

            <h2 class="font-display text-3xl font-medium text-ink">
                <a href="{{ tb_route('storefront.blog.show', ['slug' => $slug]) }}">{{ tb_bi($p['title'] ?? '') }}</a>
            </h2>

            @if ($showExcerpt && ! empty($p['excerpt']))
                <p class="text-body text-stone">{{ tb_bi($p['excerpt']) }}</p>
            @endif

            <div>
                <a href="{{ tb_route('storefront.blog.show', ['slug' => $slug]) }}" class="inline-flex items-center rounded-pill bg-clay-600 px-6 py-2.5 text-sm font-medium text-paper transition-colors duration-(--duration-base) hover:bg-clay-700">
                    {{ $ctaText ?: __('storefront.blog.read_more') }}
                </a>
            </div>
        </div>
    </div>
@endif
