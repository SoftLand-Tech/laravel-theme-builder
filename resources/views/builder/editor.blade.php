<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-builder-api" content="{{ config('theme-builder.routes.path', 'builder') }}">

    <title>{{ __('Theme editor') }} — {{ tb_bi($theme->name) }}</title>

    {{-- Vite dev/build: builder CSS (Tailwind + tokens) + React entry (vendor path). --}}
    @vite(['resources/css/builder.css', 'vendor/softland/theme-builder/resources/js/builder/main.tsx'])
</head>
<body class="h-full bg-neutral-100 text-neutral-900 font-sans">
    <div
        id="builder-root"
        data-theme-id="{{ $theme->id }}"
        data-theme-name="{{ tb_bi($theme->name) }}"
        data-theme-slug="{{ $theme->packageSlug() }}"
        data-theme-css-url="{{ $theme->themeCssAssetUrl() }}"
        data-uses-draft-overlay="{{ $theme->usesDraftOverlay() ? '1' : '0' }}"
        data-has-unpublished-draft="{{ $theme->hasUnpublishedDraft() ? '1' : '0' }}"
        data-store-name="{{ $storeName }}"
        data-csrf-token="{{ csrf_token() }}"
        data-api-endpoints='@json($apiToken)'
        data-block-registry='@json($blockRegistry)'
        data-initial-blocks='@json($theme->builderResolvedBlocks())'
        data-initial-settings='@json($theme->builderResolvedSettings())'
        data-initial-header='@json($theme->builderResolvedHeader())'
        data-initial-footer='@json($theme->builderResolvedFooter())'
        data-initial-templates='@json($theme->builderResolvedTemplates())'
        data-current-version="{{ $currentVersion }}"
        data-locale="{{ app()->getLocale() }}"
    ></div>
</body>
</html>
