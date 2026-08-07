@props(['post'])

@php
    $p = is_array($post) ? $post : [];
    $title = tb_bi($p['title'] ?? '');
    $excerpt = isset($p['excerpt']) ? tb_bi($p['excerpt']) : null;
    $url = $p['url'] ?? '#';
    $image = $p['image'] ?? null;
    $category = $p['category'] ?? null;
    $catName = is_array($category) ? tb_bi($category['name'] ?? '') : null;
    $catUrl = is_array($category) ? ($category['url'] ?? '#') : '#';
    $publishedAt = $p['publishedAt'] ?? null;
@endphp

<article class="group flex flex-col overflow-hidden rounded-(--radius-cadi) border border-line bg-surface">
    <a href="{{ $url }}" class="block aspect-[16/10] overflow-hidden bg-stone/10">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $title }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
        @endif
    </a>

    <div class="flex flex-1 flex-col gap-2 p-5">
        @if ($catName)
            <a href="{{ $catUrl }}" class="eyebrow text-clay-600">{{ $catName }}</a>
        @endif

        <h3 class="font-display text-lg font-medium leading-snug text-ink">
            <a href="{{ $url }}">{{ $title }}</a>
        </h3>

        @if ($excerpt)
            <p class="line-clamp-3 text-sm text-stone">{{ $excerpt }}</p>
        @endif

        @if ($publishedAt)
            <div class="mt-auto pt-2 text-xs text-stone">
                <time datetime="{{ $publishedAt }}">{{ $publishedAt }}</time>
            </div>
        @endif
    </div>
</article>
