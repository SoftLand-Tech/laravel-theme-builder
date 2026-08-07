@props(['product', 'showAddToCart' => null])

@php
    $p = is_array($product) ? $product : [];
    $name = tb_bi($p['name'] ?? '');
    $subtitle = isset($p['subtitle']) ? tb_bi($p['subtitle']) : null;
    $url = $p['url'] ?? '#';
    $price = $p['price'] ?? null;
    $compare = $p['comparePrice'] ?? null;
    $image = $p['image'] ?? null;
    $currency = $p['currency'] ?? null;
    $onSale = $p['onSale'] ?? ($price !== null && $compare !== null && $compare > $price);
    $availability = $p['availability'] ?? null;

    $card = $themeSettings['product_card'] ?? [];
    $show = fn ($key, $default = true): bool => ($card[$key] ?? $default) === true;
@endphp

<div class="product-card group relative flex flex-col">
    <a href="{{ $url }}" class="block">
        @if ($show('show_image'))
            <div class="relative aspect-[3/4] overflow-hidden rounded-(--radius-cadi) bg-paper-deep">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $name }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 {{ $show('hover_zoom') ? 'group-hover:scale-[1.03]' : '' }}">
                @else
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="font-display text-5xl text-stone/40">{{ mb_substr($name, 0, 1) }}</span>
                    </div>
                @endif

                <div class="absolute start-3 top-3 flex flex-col gap-1.5">
                    @if ($show('show_sale_badge') && $onSale)
                        <span class="eyebrow w-fit rounded-full bg-clay-600 px-2.5 py-1 text-paper">{{ __('storefront.sale') }}</span>
                    @endif
                    @if ($show('show_out_of_stock_badge') && $availability === 'out_of_stock')
                        <span class="eyebrow w-fit rounded-full bg-ink/80 px-2.5 py-1 text-paper">{{ __('storefront.out_of_stock') }}</span>
                    @endif
                </div>

                <div class="absolute inset-0 bg-ink/0 transition-colors duration-300 group-hover:bg-ink/[0.03]"></div>
            </div>
        @endif

        <div class="mt-4 space-y-1">
            @if ($show('show_title'))
                <h3 class="text-sm font-medium text-ink transition-colors group-hover:text-clay-700">{{ $name }}</h3>
            @endif
            @if ($subtitle)
                <p class="text-xs text-stone">{{ $subtitle }}</p>
            @endif
            @if ($price !== null)
                <div class="flex items-baseline gap-2">
                    @if ($show('show_price'))
                        <span class="text-sm {{ $onSale ? 'text-clay-700' : 'text-ink-soft' }}">{{ tb_money((int) $price, $currency) }}</span>
                    @endif
                    @if ($show('show_compare_price') && $onSale && $compare)
                        <span class="text-xs text-stone line-through">{{ tb_money((int) $compare, $currency) }}</span>
                    @endif
                </div>
            @endif
        </div>
    </a>
</div>
