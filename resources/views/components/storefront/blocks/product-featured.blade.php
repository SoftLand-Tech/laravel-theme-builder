@props(['props' => [], 'product' => null])

@php
    $title = tb_bi($props['title'] ?? null);
    $p = is_array($product) ? $product : [];
    $image = $p['image'] ?? null;
    $name = tb_bi($p['name'] ?? '');
    $description = $p['description'] ?? $p['subtitle'] ?? '';
@endphp

@if ($title !== '')
    <x-theme-builder::storefront.section-heading :title="$title" />
@endif

@if ($product)
    <x-theme-builder::ui.card class="p-8">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            <x-theme-builder::ui.media :src="$image ?: null" :alt="$name" ratio="square" />

            <div class="flex flex-col justify-center">
                <h3 class="mb-2 font-display text-h3 text-ink">{{ $name }}</h3>
                @if ($description)
                    <p class="mb-4 text-body text-ink-soft">{{ is_array($description) ? tb_bi($description) : $description }}</p>
                @endif
                <a
                    href="{{ tb_route('storefront.product', ['slug' => $p['slug'] ?? '']) }}"
                    class="inline-block w-fit rounded-pill bg-clay-600 px-6 py-2.5 text-caption font-semibold text-paper transition-colors duration-(--duration-base) hover:bg-clay-700 focus-visible:outline-(length:--focus-ring-width) focus-visible:outline-(--focus-ring-color)"
                >
                    {{ __('storefront.view_product') }}
                </a>
            </div>
        </div>
    </x-theme-builder::ui.card>
@else
    <p class="rounded-(--radius-cadi) border border-dashed border-line bg-paper-deep px-6 py-10 text-center text-body text-stone">
        {{ __('storefront.select_product_in_theme_builder') }}
    </p>
@endif
