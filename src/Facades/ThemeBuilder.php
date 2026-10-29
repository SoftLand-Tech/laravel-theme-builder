<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Facades;

use Illuminate\Support\Facades\Facade;
use SoftLand\ThemeBuilder\Models\Theme;
use SoftLand\ThemeBuilder\Support\ThemePackageManager;
use SoftLand\ThemeBuilder\ThemeBuilderManager;

/**
 * @method static void routes(array $options = [])
 * @method static array<string, array{label?: mixed}> pages()
 * @method static string renderTemplate(string $page, string $slot = 'before', ?Theme $theme = null)
 * @method static array<string, \SoftLand\ThemeBuilder\Support\ThemePackage> packages()
 *
 * @see ThemeBuilderManager
 * @see ThemePackageManager
 */
class ThemeBuilder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'theme-builder';
    }
}
