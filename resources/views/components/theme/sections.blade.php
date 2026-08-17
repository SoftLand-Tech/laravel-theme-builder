@props(['resolved'])

@foreach ($resolved as $block)
    @php
        $visibilityClass = '';
        $desktopVisible = $block['props']['visibleOnDesktop'] ?? true;
        $mobileVisible = $block['props']['visibleOnMobile'] ?? true;
        $hidden = (bool) ($block['props']['hidden'] ?? false);

        // A "hidden" section is skipped entirely on the live storefront (it is
        // still editable in the builder, just not rendered to customers).
        if ($hidden) {
            continue;
        }

        if (! $desktopVisible) {
            // Mobile-only: visible below lg, hidden on desktop.
            $visibilityClass = 'lg:hidden';
        } elseif (! $mobileVisible) {
            // Desktop-only: hidden below lg, visible from lg up.
            $visibilityClass = 'hidden lg:block';
        }

        // Per-section style contract (applies to every block).
        $props = $block['props'] ?? [];
        $padTop = match ($props['sectionPaddingTop'] ?? 'medium') {
            'none' => '',
            'small' => 'pt-(--space-section-sm)',
            'large' => 'pt-(--space-section-lg)',
            default => 'pt-(--space-section-md)',
        };
        $padBottom = match ($props['sectionPaddingBottom'] ?? 'medium') {
            'none' => '',
            'small' => 'pb-(--space-section-sm)',
            'large' => 'pb-(--space-section-lg)',
            default => 'pb-(--space-section-md)',
        };
        $scheme = $props['sectionBackground'] ?? '';
        $schemeClass = is_string($scheme) && $scheme !== '' ? "scheme-{$scheme}" : '';
        $width = $props['sectionWidth'] ?? 'contained';
        $container = $width === 'full'
            ? 'w-full'
            : 'mx-auto w-full max-w-content px-4 sm:px-6 lg:px-8';
        $align = $props['sectionTextAlign'] ?? '';
        $alignClass = $align === 'start' ? 'text-start' : ($align === 'end' ? 'text-end' : ($align === 'center' ? 'text-center' : ''));
    @endphp
    <div class="{{ $visibilityClass }} block-section @container {{ $schemeClass }} {{ $padTop }} {{ $padBottom }}">
        <div class="{{ $container }} {{ $alignClass }}">
            <x-dynamic-component
                :component="$block['component']"
                :props="$block['props']"
                :products="$block['products'] ?? null"
                :bentoProducts="$block['bentoProducts'] ?? null"
                :compactProducts="$block['compactProducts'] ?? null"
                :tabProducts="$block['tabProducts'] ?? null"
                :categories="$block['categories'] ?? null"
                :categoryLinks="$block['categoryLinks'] ?? null"
                :product="$block['product'] ?? null"
                :post="$block['post'] ?? null"
                :posts="$block['posts'] ?? null"
                :blogCategories="$block['blogCategories'] ?? null"
            />
        </div>
    </div>
@endforeach
