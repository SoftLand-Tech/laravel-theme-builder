<?php

declare(strict_types=1);

use SoftLand\ThemeBuilder\Builder\BlockCategory;
use SoftLand\ThemeBuilder\Builder\BlockDefinition;
use SoftLand\ThemeBuilder\Builder\BlockRegistry;
use SoftLand\ThemeBuilder\Builder\BlockRenderer;
use SoftLand\ThemeBuilder\Contracts\StorefrontDataProvider;
use SoftLand\ThemeBuilder\Models\Theme;
use SoftLand\ThemeBuilder\Support\NullStorefrontDataProvider;

it('registers all core block types', function (): void {
    $registry = app(BlockRegistry::class);

    expect($registry->all())->toHaveCount(28)
        ->and($registry->has('Hero'))->toBeTrue()
        ->and($registry->has('ProductsGrid'))->toBeTrue()
        ->and($registry->has('CustomHtml'))->toBeTrue()
        ->and($registry->has('ThisDoesNotExist'))->toBeFalse();
});

it('points every block at the package view namespace', function (): void {
    $registry = app(BlockRegistry::class);

    foreach ($registry->all() as $definition) {
        expect($definition->bladeComponent)->toStartWith('theme-builder::storefront.blocks.');
    }
});

it('normalizes and drops unknown block types', function (): void {
    $registry = app(BlockRegistry::class);

    $normalized = $registry->normalizeBlockTree([
        ['type' => 'Hero', 'props' => ['title' => ['ar' => 'a', 'en' => 'b']]],
        ['type' => 'Bogus', 'props' => []],
    ]);

    expect($normalized)->toHaveCount(1)
        ->and($normalized[0]['type'])->toBe('Hero');
});

it('sanitizes block props: keeps allowed keys, strips scripts', function (): void {
    $definition = BlockDefinition::make('CustomHtml', 'Custom', BlockCategory::Layout)
        ->defaults(['html' => '', 'hidden' => false]);

    $clean = $definition->sanitize([
        'html' => '<script>alert(1)</script><b>ok</b>',
        'unknown' => 'dropped',
    ]);

    expect($clean)->toHaveKey('html')
        ->and($clean['html'])->not->toContain('<script>')
        ->and($clean['html'])->toContain('<b>ok</b>')
        ->and($clean)->not->toHaveKey('unknown');
});

it('binds a null storefront data provider by default', function (): void {
    $provider = app(StorefrontDataProvider::class);

    expect($provider)->toBeInstanceOf(NullStorefrontDataProvider::class)
        ->and($provider->products([]))->toBe([])
        ->and($provider->categories([]))->toBe([])
        ->and($provider->featuredProduct([]))->toBeNull()
        ->and($provider->searchProducts(null))->toBe([]);
});

it('resolves a block tree to renderable components without a provider', function (): void {
    $renderer = app(BlockRenderer::class);

    $resolved = $renderer->resolve([
        ['type' => 'Hero', 'props' => []],
        ['type' => 'ProductsGrid', 'props' => ['source' => 'latest']],
    ]);

    expect($resolved)->toHaveCount(2)
        ->and($resolved[0]['component'])->toBe('theme-builder::storefront.blocks.hero')
        ->and($resolved[1]['component'])->toBe('theme-builder::storefront.blocks.products-grid')
        ->and($resolved[1]['products'])->toBe([]); // null provider → empty
});

it('compiles CSS vars and color-scheme rules from settings', function (): void {
    $settings = Theme::defaultSettings();

    $vars = Theme::cssVarsFromSettings($settings);
    $rules = Theme::colorSchemeRules($settings);

    expect($vars)->toContain('--color-clay-500')
        ->and($vars)->toContain('--color-ink')
        ->and($rules)->toContain('.scheme-1')
        ->and($rules)->toContain('.scheme-2');
});

it('guards CSS injection via cssColor()', function (): void {
    expect(Theme::cssColor('red;} html{display:none}', '#FFFFFF'))->toBe('#FFFFFF')
        ->and(Theme::cssColor('#2F7AB5'))->toBe('#2F7AB5')
        ->and(Theme::cssColor('#fff'))->toBe('#FFFFFF');
});

it('resolves bilingual values, currency, and routes via helpers', function (): void {
    expect(tb_bi(['ar' => 'مرحبا', 'en' => 'Hi']))->toBe('Hi')
        ->and(tb_bi('plain'))->toBe('plain')
        ->and(tb_money(9900))->toEndWith('SAR')
        ->and(tb_route('product', ['slug' => 'shirt']))->toEndWith('/products/shirt')
        ->and(tb_route('cart'))->toBe('/cart')
        ->and(tb_route('storefront.home'))->toBe('/'); // 'storefront.' prefix stripped
});
