<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use SoftLand\ThemeBuilder\Builder\BlockRenderer;
use SoftLand\ThemeBuilder\Models\Theme;

/**
 * Renders the storefront. The host manually registers routes to `home()`
 * (and optionally `page()`) — e.g.
 *
 *   Route::get('/', [StorefrontController::class, 'home']);
 *   Route::get('/services', fn () => app(StorefrontController::class)->page('services'));
 */
class StorefrontController extends Controller
{
    public function __construct(
        private BlockRenderer $renderer,
    ) {}

    public function home(Request $request): View
    {
        $theme = Theme::active() ?? Theme::defaultPreset();

        $resolved = $theme instanceof Theme
            ? $this->renderer->resolve($theme->resolvedBlocks(), $theme)
            : new Collection;

        return view(config('theme-builder.storefront.home_view'), [
            'theme' => $theme,
            'resolved' => $resolved,
        ]);
    }

    /**
     * Render a full page from a theme's page template (its `before` blocks,
     * then its `after` blocks) inside the storefront chrome. Useful for
     * marketing pages the builder owns outright — landing, services, about —
     * where there is no host content to wrap.
     */
    public function page(Request $request, string $page): View
    {
        $theme = Theme::active() ?? Theme::defaultPreset();

        return view('theme-builder::storefront.page', [
            'theme' => $theme,
            'page' => $page,
            'resolved' => $theme instanceof Theme
                ? $this->renderer->resolve(
                    array_merge(
                        $theme->resolvedTemplates()[$page]['before'] ?? [],
                        $theme->resolvedTemplates()[$page]['after'] ?? [],
                    ),
                    $theme,
                )
                : new Collection,
        ]);
    }
}
