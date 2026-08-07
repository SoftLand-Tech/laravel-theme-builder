<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder;

use Illuminate\Support\Facades\Route;

/**
 * Registers the editor + picker routes. The host typically calls
 * `ThemeBuilder::routes(['middleware' => [...]])` (or mounts it inside its own
 * domain/auth group). Defaults come from `config('theme-builder.routes')`.
 */
class ThemeBuilderManager
{
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
}
