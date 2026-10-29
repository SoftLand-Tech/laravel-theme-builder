<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Vite;
use Orchestra\Testbench\TestCase as BaseTestCase;
use SoftLand\ThemeBuilder\ThemeBuilderServiceProvider;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ThemeBuilderServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        // Editor routes are mounted automatically by the service provider
        // (routes.enabled defaults to true) so builder endpoints are testable.
        // The `web` middleware needs a valid encryption key for the session.
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('theme-builder.currency', 'SAR');

        // Keep tests hermetic: don't auto-discover theme packages that happen
        // to be checked out beside this package (dev-monorepo layout), and
        // don't offer to git-clone catalog themes from GitHub.
        $app['config']->set('theme-builder.discover_siblings', false);
        $app['config']->set('theme-builder.offer_remote_themes', false);

        // The storefront layout calls @vite('resources/css/storefront.css');
        // fake a manifest so rendering doesn't need a frontend build.
        $app->singleton(Vite::class, function (): Vite {
            return new class extends Vite
            {
                public function __invoke($entrypoints, $buildDirectory = null): string
                {
                    return '<link rel="stylesheet" href="/build/storefront.css">';
                }
            };
        });
    }
}
