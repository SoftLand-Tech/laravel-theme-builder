@php
    use SoftLand\ThemeBuilder\Models\Theme;

    $chrome = isset($theme) && $theme instanceof Theme
        ? $theme->headerComponent()
        : null;
@endphp

@if ($chrome)
    <x-dynamic-component :component="$chrome" :categories="$categories" :header-settings="$headerSettings" />
@else
    <x-theme-builder::storefront.chrome.default-header :categories="$categories" :header-settings="$headerSettings" />
@endif
