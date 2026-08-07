@props(['categories' => [], 'headerSettings' => []])

@php
    use SoftLand\ThemeBuilder\Models\Theme;

    $bg = Theme::cssColor($headerSettings['background_color'] ?? '#FFFFFF', '#FFFFFF');
    $text = Theme::cssColor($headerSettings['text_color'] ?? '#0F172A', '#0F172A');
    $logoText = $headerSettings['logo_text'] ?? null;
    $logoImage = $headerSettings['logo_image'] ?? null;
    $menu = $headerSettings['menu'] ?? [];
    $catCount = (int) ($headerSettings['category_count'] ?? 5);
    $showSearch = ($headerSettings['show_search'] ?? true) === true;
    $showAccount = ($headerSettings['show_account'] ?? true) === true;
    $showCart = ($headerSettings['show_cart'] ?? true) === true;
    $sticky = ($headerSettings['sticky'] ?? true) === true;

    $brandName = is_array($logoText) ? tb_bi($logoText) : (is_string($logoText) && $logoText !== '' ? $logoText : config('app.name'));
@endphp

<header class="{{ $sticky ? 'sticky top-0 z-40' : '' }} border-b border-line" style="background-color: {{ $bg }}; color: {{ $text }}">
    <div class="mx-auto flex h-16 max-w-content items-center gap-6 px-4 sm:px-6 lg:px-8">
        <a href="{{ tb_route('home') }}" class="flex items-center gap-2 font-display text-lg font-semibold">
            @if ($logoImage)
                <img src="{{ $logoImage }}" alt="{{ $brandName }}" class="h-8 w-auto">
            @else
                {{ $brandName }}
            @endif
        </a>

        <nav class="hidden items-center gap-5 text-sm md:flex">
            @foreach ($menu as $link)
                <a href="{{ $link['url'] ?? '#' }}" class="hover:opacity-75">{{ tb_bi($link['label'] ?? '') }}</a>
            @endforeach
            @foreach (($categories ?? []) as $cat)
                @if ($loop->index >= $catCount)
                    @break
                @endif
                <a href="{{ $cat['url'] ?? tb_route('category', ['slug' => $cat['slug'] ?? '']) }}" class="hover:opacity-75">{{ tb_bi($cat['name'] ?? '') }}</a>
            @endforeach
        </nav>

        <div class="ms-auto flex items-center gap-4 text-sm">
            @if ($showSearch)
                <a href="{{ tb_route('search') }}" aria-label="Search">Search</a>
            @endif
            @if ($showAccount)
                <a href="#" aria-label="Account">Account</a>
            @endif
            @if ($showCart)
                <a href="{{ tb_route('cart') }}" aria-label="Cart">Cart</a>
            @endif
        </div>
    </div>
</header>
