<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\View;

use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;
use SoftLand\ThemeBuilder\Contracts\StorefrontDataProvider;
use SoftLand\ThemeBuilder\Models\Theme;

/**
 * Package counterpart of the host's storefront view composers. Shares the
 * active theme, resolved settings, and body classes with every storefront
 * view (package-namespaced AND host storefront.*) so the design tokens and
 * "what to show" toggles apply without each controller passing `$theme`.
 */
class StorefrontViewComposers
{
    public function __construct(
        private StorefrontDataProvider $provider,
    ) {}

    public function register(): void
    {
        $chromeViews = [
            // Package chrome (namespace)…
            'theme-builder::components.storefront.header',
            'theme-builder::components.storefront.bottom-nav',
            // …and the conventional host chrome names, so a host that keeps its
            // own storefront header/footer (e.g. an app migrated onto the package)
            // still receives $categories + $headerSettings.
            'components.storefront.header',
            'components.storefront.bottom-nav',
        ];

        ViewFacade::composer($chromeViews, function (View $view): void {
            $theme = Theme::active() ?? Theme::defaultPreset();
            $header = $theme instanceof Theme ? $theme->resolvedHeader() : Theme::headerDefaults();

            $view->with([
                'categories' => $this->provider->categories(['limit' => $header['category_count'] ?? 5]),
                'headerSettings' => $header,
            ]);
        });

        ViewFacade::composer(['theme-builder::components.storefront.footer', 'components.storefront.footer'], function (View $view): void {
            $theme = Theme::active() ?? Theme::defaultPreset();

            $view->with('footerSettings', $theme instanceof Theme ? $theme->resolvedFooter() : Theme::footerDefaults());
        });

        ViewFacade::composer([
            'theme-builder::*',
            'storefront.*',
            'components.storefront.*',
            'components.product.*',
            'components.layouts.storefront',
            'livewire.storefront.*',
        ], function (View $view): void {
            $theme = Theme::active() ?? Theme::defaultPreset();
            $settings = $theme instanceof Theme ? $theme->resolvedSettings() : Theme::defaultSettings();

            $scopeClass = $theme instanceof Theme ? $theme->themeScopeClass() : '';
            $bodyClasses = trim(Theme::storefrontBodyClass($settings).' '.$scopeClass);

            $view->with([
                'theme' => $theme,
                'themeSettings' => $settings,
                'themeBodyClass' => $bodyClasses,
            ]);
        });
    }
}
