<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use SoftLand\ThemeBuilder\Builder\BlockRenderer;
use SoftLand\ThemeBuilder\Models\Theme;

/**
 * Renders the storefront. The host manually registers a route to
 * `home()` (e.g. `Route::get('/', [StorefrontController::class, 'home'])`).
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
}
