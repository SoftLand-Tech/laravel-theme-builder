@props(['props' => [], 'compactProducts' => null])

@php
    $title = tb_bi($props['title'] ?? null);
    $ctaText = tb_bi($props['ctaText'] ?? null);
    $products = is_array($compactProducts) ? $compactProducts : [];
@endphp

@if ($title)
    <h2 class="mb-6 font-display text-h2 text-ink">{{ $title }}</h2>
@endif

@if (! empty($products))
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($products as $product)
            @php
                $name = tb_bi($product['name'] ?? null);
                $image = $product['image'] ?? null;
                $url = $product['url'] ?? '#';
                $price = $product['price'] ?? null;
                $currency = $product['currency'] ?? null;
                $availability = $product['availability'] ?? null;
                $inStock = $availability !== 'out_of_stock';
            @endphp
            <article class="flex gap-3 rounded-(--radius-card) border border-line bg-surface p-3">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $name }}" class="h-20 w-20 shrink-0 rounded-lg object-cover">
                @endif
                <div class="flex flex-col">
                    <a href="{{ $url }}" class="font-semibold text-ink hover:text-clay-600">{{ $name }}</a>
                    @if ($price !== null)
                        <span class="mt-1 text-sm font-bold text-clay-600">{{ tb_money($price, $currency) }}</span>
                    @endif
                    @if ($ctaText)
                        <span class="mt-auto pt-2 text-xs font-semibold text-ink-soft">{{ $ctaText }} →</span>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
@else
    <p class="text-ink-soft">{{ __('storefront.no_products_in_collection') }}</p>
@endif
