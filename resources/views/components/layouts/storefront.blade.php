@props([
    'title' => null,
    'bodyClass' => 'min-h-full bg-paper text-ink font-sans antialiased',
])

@php
    use SoftLand\ThemeBuilder\Models\Theme;

    $locale = str_replace('_', '-', app()->getLocale());
    $rtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">

    <title>{{ $title ? $title.' · '.config('app.name') : config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @php
        $settings = $themeSettings ?? Theme::defaultSettings();
        $fontsUrl = Theme::googleFontsUrl($settings);
    @endphp
    @if ($fontsUrl)
        <link href="{{ $fontsUrl }}" rel="stylesheet">
    @endif

    {{-- The host's published storefront stylesheet (Tailwind v4 + tokens). --}}
    @vite(['resources/css/storefront.css'])

    {{-- Inject the active theme's design tokens as the storefront's real CSS
         custom properties (provided by the global view composer). --}}
    @php
        $cssVars = Theme::cssVarsFromSettings($settings);
        $schemeRules = Theme::colorSchemeRules($settings);
        $headerAccent = Theme::cssColor(data_get(
            $headerSettings ?? (Theme::active()?->resolvedHeader() ?? Theme::headerDefaults()),
            'accent_color',
        ));
    @endphp
    <style>
        :root { {!! $cssVars !!}; --header-accent: {{ $headerAccent }}; }
        {!! $schemeRules !!}
    </style>
    @php
        $activeTheme = $theme ?? Theme::active();
        $themeCssUrl = $activeTheme?->themeCssAssetUrl();
        $scopeClass = $activeTheme?->themeScopeClass() ?? '';
    @endphp
    @if ($themeCssUrl)
        <link rel="stylesheet" href="{{ $themeCssUrl }}">
    @endif

    {{-- Alpine.js powers the storefront's interactive blocks (ProductsTabs tab
         switching, Countdown, etc.). `defer` so it boots after this document is
         parsed — i.e. after the alpine:init listener near </body> registers. --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>

    @stack('head')
</head>
<body {{ $attributes->class([$bodyClass, $themeBodyClass ?? '', $scopeClass]) }}>
    {{ $slot }}

    {{-- Alpine plugin: countdown timer for the Countdown block. Requires Alpine
         to be loaded by the host (standard Laravel scaffolding provides it). --}}
    <script>
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('countdownTimer', (targetTimestamp) => ({
                target: targetTimestamp,
                days: '00', hours: '00', minutes: '00', seconds: '00',
                init() {
                    this.tick();
                    this.interval = setInterval(() => this.tick(), 1000);
                },
                destroy() { clearInterval(this.interval); },
                tick() {
                    const diff = Math.max(0, this.target - Date.now());
                    if (diff === 0) return;
                    this.days = String(Math.floor(diff / 86400000)).padStart(2, '0');
                    this.hours = String(Math.floor((diff % 86400000) / 3600000)).padStart(2, '0');
                    this.minutes = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
                    this.seconds = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
                },
            }));
        });
    </script>
    @stack('scripts')
</body>
</html>
