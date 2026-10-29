@php
    $storeName = is_array($theme?->name ?? null) ? tb_bi($theme->name) : ($theme?->name ?? config('app.name'));
    $pageTitle = ucfirst(str_replace(['-', '_'], ' ', $page));
@endphp

<x-theme-builder::layouts.storefront :title="$storeName.' — '.$pageTitle">
    <x-theme-builder::storefront.header />

    @if ($resolved->isEmpty())
        <div class="mx-auto max-w-content px-4 py-20 text-center text-ink-soft">
            <p class="text-body">{{ __('storefront.page_empty') }}</p>
        </div>
    @else
        <x-theme-builder::theme.sections :resolved="$resolved" />
    @endif

    <x-theme-builder::storefront.footer />
</x-theme-builder::layouts.storefront>
