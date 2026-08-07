<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Contracts;

use SoftLand\ThemeBuilder\Support\NullStorefrontDataProvider;

/**
 * Feeds catalog/blog data into the storefront block renderer AND the builder
 * editor pickers. The package ships a {@see NullStorefrontDataProvider}
 * that returns empty everywhere so static blocks render and product/category
 * blocks render empty without crashing.
 *
 * All methods return NORMALIZED ARRAYS (never Eloquent models) so the package's
 * Blade components stay host-agnostic.
 *
 * Value shapes (plain arrays):
 *
 * ProductView:        ['id'=>int, 'name'=>string|array, 'slug'=>string, 'url'=>string,
 *                      'price'=>int(cents), 'comparePrice'=>?int, 'currency'=>?string,
 *                      'image'=>?string, 'subtitle'=>?string, 'rating'=>?float,
 *                      'badge'=>?string, 'onSale'=>?bool, 'availability'=>?string]
 * CategoryView:       ['id'=>int, 'name'=>string|array, 'slug'=>string, 'url'=>?string,
 *                      'productCount'=>?int, 'image'=>?string]
 * CategoryLinkView:   ['id'=>int, 'name'=>string, 'slug'=>string, 'label'=>array, 'icon'=>string]
 * BlogPostView:       ['id'=>int, 'title'=>string|array, 'slug'=>string, 'url'=>?string,
 *                      'excerpt'=>?string, 'image'=>?string, 'publishedAt'=>?string, 'category'=>?array]
 * BlogCategoryView:   ['id'=>int, 'name'=>string|array, 'slug'=>string, 'postCount'=>?int]
 * PickerItem:         ['id'=>int, 'name'=>string, 'slug'=>?string, 'price'=>?int]
 *
 * Bilingual fields (name/title/label) may be a locale-resolved string OR an
 * ['ar'=>..., 'en'=>...] array; the tb_bi() helper resolves both.
 */
interface StorefrontDataProvider
{
    /** @return array<int, array> ProductView[] — ProductsGrid / ProductsCarousel */
    public function products(array $props): array;

    /** @return array<int, array{label: array, products: array<int, array>}> — ProductsTabs */
    public function productsForTabs(array $props): array;

    /** @return array<int, array> CategoryView[] — CategoryGrid */
    public function categories(array $props): array;

    /** @return array<int, array> CategoryLinkView[] — CategoryShortcuts */
    public function categoryLinks(array $props): array;

    /**
     * Featured product for the ProductsFeatured block. Returns a ProductView
     * array by convention (for the package's generic block), but hosts that
     * keep their own product-featured view may return their domain model
     * instead — the renderer passes the value through untouched.
     */
    public function featuredProduct(array $props): mixed;

    /**
     * Single blog post for the BlogArticle block. Same passthrough rule as
     * featuredProduct(): a BlogPostView array by default, or a host model.
     */
    public function blogPost(array $props): mixed;

    /** @return array<int, array> BlogCategoryView[] — BlogCategoriesGrid */
    public function blogCategories(array $props): array;

    /** @return array<int, array> BlogPostView[] — BlogPostsGrid */
    public function blogPosts(array $props): array;

    /** Editor picker: search published products. @return array<int, array> PickerItem[] */
    public function searchProducts(?string $q, array $ids = []): array;

    /** Editor picker: search categories. @return array<int, array> PickerItem[] */
    public function searchCategories(?string $q, array $ids = []): array;

    /** Editor picker: search blog posts. @return array<int, array> PickerItem[] */
    public function searchBlogPosts(?string $q, array $ids = []): array;
}
