@php
    use SoftLand\ThemeBuilder\Models\Theme;

    $chrome = isset($theme) && $theme instanceof Theme
        ? $theme->footerComponent()
        : null;
@endphp

@if ($chrome)
    <x-dynamic-component :component="$chrome" :footer-settings="$footerSettings" />
@else
    <x-theme-builder::storefront.chrome.default-footer :footer-settings="$footerSettings" />
@endif
