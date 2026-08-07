<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Tests;

use Illuminate\Foundation\Application;
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
        // Tests don't need the editor routes mounted.
        $app['config']->set('theme-builder.routes.enabled', false);
        $app['config']->set('theme-builder.currency', 'SAR');
    }
}
