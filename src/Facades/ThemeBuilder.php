<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void routes(array $options = [])
 */
class ThemeBuilder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'theme-builder';
    }
}
