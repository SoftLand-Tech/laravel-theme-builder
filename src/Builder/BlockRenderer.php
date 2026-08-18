<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Builder;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use SoftLand\ThemeBuilder\Contracts\StorefrontDataProvider;
use SoftLand\ThemeBuilder\Models\Theme;

/**
 * Resolves a block JSON tree into render-ready Blade component payloads for
 * the storefront.
 *
 * This is the server-side counterpart to the React editor: the editor produces
 * JSON, and this class turns that JSON into enriched component payloads
 * (products, categories, etc. — loaded by the host's StorefrontDataProvider,
 * never by the package itself).
 */
class BlockRenderer
{
    public function __construct(
        private BlockRegistry $registry,
        private StorefrontDataProvider $provider,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return Collection<int, array{component: string, props: array<string, mixed>}>
     */
    public function resolve(array $blocks, ?Theme $theme = null): Collection
    {
        $theme ??= Theme::active();

        return collect($blocks)
            ->map(fn (array $block) => $this->registry->normalize($block))
            ->filter()
            ->values()
            ->map(function (array $block) use ($theme): array {
                $type = $block['type'];
                $props = $block['props'];
                $definition = $this->registry->get($type);

                $component = $theme?->componentFor($type)
                    ?? $definition?->bladeComponent
                    ?? BlockRegistry::blocksPrefix().'.missing';

                return [
                    'component' => $component,
                    'props' => $props,
                    ...$this->dataFor($type, $props),
                ];
            });
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    public function render(array $blocks, ?Theme $theme = null): string
    {
        return $this->resolve($blocks, $theme)
            ->map(function (array $resolved): string {
                $view = view(self::viewNameFor($resolved['component']), $resolved);

                return $view instanceof View ? $view->render() : (string) $view;
            })
            ->join('');
    }

    /**
     * The resolved `component` values are Blade component names (consumed by
     * `<x-dynamic-component>`), e.g. `theme-builder::storefront.blocks.hero`.
     * Anonymous components live under `resources/views/components/…`, so the
     * equivalent plain view name inserts `components.` after the namespace:
     * `theme-builder::components.storefront.blocks.hero`.
     */
    public static function viewNameFor(string $component): string
    {
        if (! str_contains($component, '::')) {
            return 'components.'.$component;
        }

        [$namespace, $name] = explode('::', $component, 2);

        return "{$namespace}::components.{$name}";
    }

    /**
     * Enrich a block with the catalog/blog data its type needs, sourced from
     * the host's StorefrontDataProvider. Static blocks (no data) get nothing.
     *
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    private function dataFor(string $type, array $props): array
    {
        return match ($type) {
            'ProductsGrid', 'ProductsCarousel' => [
                'products' => $this->provider->products($props),
            ],
            'BentoHero' => [
                'bentoProducts' => $this->provider->products($props),
            ],
            'CompactRow' => [
                'compactProducts' => $this->provider->products($props),
            ],
            'ProductsTabs' => [
                'tabProducts' => $this->provider->productsForTabs($props),
            ],
            'CategoryGrid' => [
                'categories' => $this->provider->categories($props),
            ],
            'CategoryShortcuts' => [
                'categoryLinks' => $this->provider->categoryLinks($props),
            ],
            'ProductsFeatured' => [
                'product' => $this->provider->featuredProduct($props),
            ],
            'BlogArticle' => [
                'post' => $this->provider->blogPost($props),
            ],
            'BlogCategoriesGrid' => [
                'blogCategories' => $this->provider->blogCategories($props),
            ],
            'BlogPostsGrid' => [
                'posts' => $this->provider->blogPosts($props),
            ],
            default => [],
        };
    }
}
