<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder;

use Illuminate\Support\Facades\Route;
use SoftLand\ThemeBuilder\Builder\BlockRenderer;
use SoftLand\ThemeBuilder\Models\Theme;
use SoftLand\ThemeBuilder\Support\ThemePackage;
use SoftLand\ThemeBuilder\Support\ThemePackageManager;

/**
 * Registers the editor + picker routes, and exposes the small set of
 * storefront helpers hosts use (`pages()`, `renderTemplate()`). The host
 * typically calls `ThemeBuilder::routes(['middleware' => [...]])` (or mounts
 * it inside its own domain/auth group). Defaults come from
 * `config('theme-builder.routes')`.
 */
class ThemeBuilderManager
{
    public function __construct(
        private readonly BlockRenderer $renderer,
        private readonly ThemePackageManager $themePackages,
    ) {}

    /**
     * @param  array{prefix?: string|null, name?: string|null, middleware?: array, domain?: string|null}  $options
     */
    public function routes(array $options = []): void
    {
        $config = (array) config('theme-builder.routes', []);

        $prefix = $options['prefix'] ?? ($config['path'] ?? 'builder');
        $name = $options['name'] ?? ($config['name'] ?? 'theme-builder');
        $middleware = $options['middleware'] ?? ($config['middleware'] ?? ['web']);
        $domain = $options['domain'] ?? null;

        $group = Route::middleware($middleware)
            ->prefix($prefix)
            ->name($name.'.');

        if ($domain !== null) {
            $group = $group->domain($domain);
        }

        $group->group(function (): void {
            require __DIR__.'/../routes/builder.php';
        });
    }

    /**
     * The page templates the builder edits (key => {label}), resolved from
     * config / theme packages (see ThemePackageManager::pages()).
     *
     * @return array<string, array{label?: mixed}>
     */
    public function pages(): array
    {
        return $this->themePackages->pages();
    }

    /**
     * @return array<string, ThemePackage>
     */
    public function packages(): array
    {
        return $this->themePackages->packages();
    }

    /**
     * Render one slot of a page template for the active (or given) theme.
     * Hosts embed this around their own page content:
     *
     *   {!! ThemeBuilder::renderTemplate('product', 'before') !!}
     *
     * Unknown pages/slots render as an empty string, never throw.
     */
    public function renderTemplate(string $page, string $slot = 'before', ?Theme $theme = null): string
    {
        return $this->renderer->renderTemplate($page, $slot, $theme);
    }
}
