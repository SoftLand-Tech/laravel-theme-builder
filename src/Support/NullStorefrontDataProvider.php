<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Support;

use SoftLand\ThemeBuilder\Contracts\StorefrontDataProvider;

/**
 * Default provider: returns empty/null everywhere. Static blocks render
 * normally; product/category/blog blocks render empty. Hosts bind a real
 * StorefrontDataProvider to surface catalog data.
 */
final class NullStorefrontDataProvider implements StorefrontDataProvider
{
    public function products(array $props): array
    {
        return [];
    }

    public function productsForTabs(array $props): array
    {
        return [];
    }

    public function categories(array $props): array
    {
        return [];
    }

    public function categoryLinks(array $props): array
    {
        return [];
    }

    public function featuredProduct(array $props): mixed
    {
        return null;
    }

    public function blogPost(array $props): mixed
    {
        return null;
    }

    public function blogCategories(array $props): array
    {
        return [];
    }

    public function blogPosts(array $props): array
    {
        return [];
    }

    public function searchProducts(?string $q, array $ids = []): array
    {
        return [];
    }

    public function searchCategories(?string $q, array $ids = []): array
    {
        return [];
    }

    public function searchBlogPosts(?string $q, array $ids = []): array
    {
        return [];
    }
}
