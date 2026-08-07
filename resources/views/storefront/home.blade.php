@php
    $storeName = is_array($theme?->name ?? null) ? tb_bi($theme->name) : ($theme?->name ?? config('app.name'));
@endphp

<x-theme-builder::layouts.storefront :title="$storeName">
    <x-theme-builder::storefront.header />

    @if ($resolved->isEmpty())
        <div class="mx-auto max-w-content px-4 py-20 text-center text-ink-soft">
            <p class="text-body">{{ __('storefront.no_products_in_collection') }}</p>
        </div>
    @else
        <x-theme-builder::theme.sections :resolved="$resolved" />
    @endif

    <x-theme-builder::storefront.footer />
</x-theme-builder::layouts.storefront>
