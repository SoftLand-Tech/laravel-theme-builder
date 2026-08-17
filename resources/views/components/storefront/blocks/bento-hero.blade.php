@props(['props' => [], 'bentoProducts' => null])

@php
    $ctaText = tb_bi($props['ctaText'] ?? null);
    $eyebrow = tb_bi($props['eyebrow'] ?? null);
    $products = is_array($bentoProducts) ? array_slice($bentoProducts, 0, 6) : [];
@endphp

@if (! empty($products))
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        @foreach ($products as $index => $product)
            @php
                $name = tb_bi($product['name'] ?? null);
                $image = $product['image'] ?? null;
                $url = $product['url'] ?? '#';
                $price = $product['price'] ?? null;
                $currency = $product['currency'] ?? null;
                $isLead = $index === 0;
            @endphp
            <a
                href="{{ $url }}"
                class="group relative flex flex-col justify-between overflow-hidden rounded-(--radius-card) bg-clay-600 p-5 text-paper {{ $isLead ? 'col-span-2 row-span-2 min-h-[260px]' : 'min-h-[140px] }}"
            >
                @if ($eyebrow && $isLead)
                    <span class="text-xs font-semibold uppercase tracking-wide opacity-80">{{ $eyebrow }}</span>
                @endif
                <div class="relative z-10">
                    @if ($name)
                        <h3 class="font-display {{ $isLead ? 'text-h2' : 'text-body' }} font-bold">{{ $name }}</h3>
                    @endif
                    @if ($isLead && $price !== null)
                        <p class="mt-1 text-sm opacity-90">{{ tb_money($price, $currency) }}</p>
                    @endif
                    @if ($ctaText)
                        <span class="mt-2 inline-block text-sm font-semibold underline-offset-4 group-hover:underline">{{ $ctaText }} →</span>
                    @endif
                </div>
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $name }}" class="pointer-events-none absolute bottom-2 right-2 z-0 h-20 w-20 rounded-lg object-cover opacity-90 {{ $isLead ? 'md:h-40 md:w-40' : '' }}">
                @endif
            </a>
        @endforeach
    </div>
@else
    <p class="text-ink-soft">{{ __('storefront.no_products_in_collection') }}</p>
@endif
