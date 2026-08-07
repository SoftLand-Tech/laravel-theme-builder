@props(['props' => [], 'blogCategories' => null])

@php
    $title = tb_bi($props['title']);
    $columns = (int) ($props['columns'] ?? 4);
    $colClass = match (true) {
        $columns <= 2 => '',
        $columns === 3 => '@sm:grid-cols-3',
        $columns === 5 => '@lg:grid-cols-5',
        $columns === 6 => '@lg:grid-cols-6',
        default => '@lg:grid-cols-4',
    };
@endphp

<x-theme-builder::storefront.section-heading :title="$title" />

<div class="grid grid-cols-2 gap-4 {{ $colClass }}">
    @foreach ($blogCategories ?? [] as $category)
        @php $c = is_array($category) ? $category : []; @endphp
        <a
            href="{{ tb_route('storefront.blog.category', ['slug' => $c['slug'] ?? '']) }}"
            class="group block rounded-card focus-visible:outline-(length:--focus-ring-width) focus-visible:outline-(--focus-ring-color)"
        >
            <x-theme-builder::ui.media
                :src="$c['image'] ?? null"
                :alt="tb_bi($c['name'] ?? '')"
                :placeholder="mb_strtoupper(mb_substr(tb_bi($c['name'] ?? ''), 0, 1))"
                ratio="square"
                class="transition-shadow duration-(--duration-base) group-hover:shadow-elevation-2"
            />
            <div class="mt-3 text-center">
                <span class="block text-body font-medium text-ink">{{ tb_bi($c['name'] ?? '') }}</span>
                @if (! empty($c['postCount']))
                    <span class="block text-caption text-stone">{{ $c['postCount'] }} {{ __('storefront.blog.posts') }}</span>
                @endif
            </div>
        </a>
    @endforeach
</div>
