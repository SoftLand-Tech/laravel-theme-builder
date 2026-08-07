<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Builder;

enum BlockCategory: string
{
    case Hero = 'hero';
    case Products = 'products';
    case Banners = 'banners';
    case Categories = 'categories';
    case Content = 'content';
    case Marketing = 'marketing';
    case Layout = 'layout';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Hero',
            self::Products => 'Products',
            self::Banners => 'Banners',
            self::Categories => 'Categories',
            self::Content => 'Content',
            self::Marketing => 'Marketing',
            self::Layout => 'Layout',
        };
    }
}
