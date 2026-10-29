<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use SoftLand\ThemeBuilder\Support\ThemePackageManager;

beforeEach(function (): void {
    $this->files = new Filesystem;
    $this->fixtureRoot = sys_get_temp_dir().'/theme-builder-package-'.uniqid();

    $this->files->ensureDirectoryExists("{$this->fixtureRoot}/presets");

    $this->files->put("{$this->fixtureRoot}/theme-builder.json", json_encode([
        'slug' => 'fixture',
        'name' => ['en' => 'Fixture', 'ar' => 'تجربة'],
        'site_types' => ['cms'],
        'pages' => ['post' => ['label' => ['en' => 'Article', 'ar' => 'مقال']]],
        'routes_map' => ['post' => '/posts'],
        'settings' => ['fixture' => ['enabled' => true]],
    ]));

    $this->files->put("{$this->fixtureRoot}/presets/fixture.json", json_encode([
        'name' => ['en' => 'Fixture'],
        'settings' => [
            'colors' => ['primary' => '#000000', 'background' => '#FFFFFF', 'surface' => '#F0F0F0', 'text' => '#111111', 'muted' => '#666666'],
            'color_schemes' => [[
                'key' => '1', 'name' => 'Default', 'background' => '#FFFFFF', 'surface' => '#F0F0F0',
                'text' => '#111111', 'muted' => '#666666', 'primary' => '#000000',
                'button' => '#000000', 'button_text' => '#FFFFFF', 'accent' => '#FF0000',
            ]],
            'typography' => ['heading_font' => 'Inter', 'body_font' => 'Inter'],
            'radius' => ['card' => 8, 'button' => 8],
        ],
        'header' => [],
        'footer' => [],
        'blocks' => [],
        'templates' => [],
    ]));

    $this->manager = new ThemePackageManager($this->files);
});

afterEach(function (): void {
    (new Filesystem)->deleteDirectory($this->fixtureRoot);
});

it('falls back to the legacy ecommerce page trio without packages or config', function (): void {
    expect(array_keys($this->manager->pages()))->toBe(['product', 'cart', 'collection']);
});

it('falls back to starter pages for the configured site type', function (string $siteType, array $pages): void {
    config(['theme-builder.site_type' => $siteType]);

    expect(array_keys($this->manager->pages()))->toBe($pages);
})->with([
    'lms' => ['lms', ['course', 'lesson']],
    'cms' => ['cms', ['post', 'page']],
    'personal' => ['personal', ['about', 'services', 'contact']],
    'empty' => ['empty', ['about', 'services', 'contact']],
    'ecommerce' => ['ecommerce', ['product', 'cart', 'collection']],
]);

it('registers a theme package and exposes its manifest', function (): void {
    $package = $this->manager->register($this->fixtureRoot);

    expect($package)->not->toBeNull()
        ->and($package->slug)->toBe('fixture')
        ->and($package->siteTypes())->toBe(['cms'])
        ->and(array_keys($package->pages()))->toBe(['post'])
        ->and($package->routesMap())->toBe(['post' => '/posts'])
        ->and(array_keys($package->presets()))->toBe(['fixture'])
        ->and($this->manager->package('fixture')->slug)->toBe('fixture');
});

it('serves package pages through pages()', function (): void {
    $this->manager->register($this->fixtureRoot);

    expect(array_keys($this->manager->pages()))->toBe(['post']);
});

it('filters packages by site type', function (): void {
    $this->manager->register($this->fixtureRoot);

    expect(array_keys($this->manager->forSiteTypes(['cms'])))->toBe(['fixture'])
        ->and($this->manager->forSiteTypes(['ecommerce']))->toBeEmpty();
});

it('includes package presets in the preset index', function (): void {
    $this->manager->register($this->fixtureRoot);

    $presets = $this->manager->presets();

    expect($presets)->toHaveKey('fixture')
        ->and($presets)->toHaveKey('default')
        ->and($presets['fixture'])->toBe("{$this->fixtureRoot}/presets/fixture.json");
});

it('deep-merges package settings for presets the package ships', function (): void {
    $this->manager->register($this->fixtureRoot);

    $merged = $this->manager->mergeSettings(['colors' => ['primary' => '#123456']], 'fixture');

    expect($merged['fixture'])->toBe(['enabled' => true])
        ->and($merged['colors']['primary'])->toBe('#123456');

    // Unrelated preset slugs are left untouched.
    expect($this->manager->mergeSettings(['colors' => []], 'unrelated'))->not->toHaveKey('fixture');
});

it('prefers config-declared pages over package pages', function (): void {
    config(['theme-builder.pages' => ['course' => ['label' => 'Course'], 'lesson' => 'Lesson']]);

    expect(array_keys($this->manager->pages()))->toBe(['course', 'lesson'])
        ->and($this->manager->pages()['lesson'])->toBe(['label' => 'Lesson']);
});

it('resolves clone repos: config first, official pattern fallback', function (): void {
    config(['theme-builder.theme_repos' => ['fixture' => 'https://example.com/my-fork.git']]);

    expect($this->manager->repoFor('fixture'))->toBe('https://example.com/my-fork.git')
        ->and($this->manager->repoFor('lms'))->toBe('https://github.com/SoftLand-Tech/theme-builder-lms')
        ->and($this->manager->repoFor('made-up'))->toBeNull();
});

it('catalogs the official themes plus config repos', function (): void {
    config(['theme-builder.theme_repos' => ['fixture' => 'https://example.com/my-fork.git']]);

    $catalog = $this->manager->catalog();

    expect(array_keys($catalog))->toBe(['personal', 'ecommerce', 'lms', 'cms', 'fixture'])
        ->and($catalog['cms'])->toBe('https://github.com/SoftLand-Tech/theme-builder-cms');
});

it('resolves the themes path from config with a base-path default', function (): void {
    expect($this->manager->themesPath())->toBe(base_path('theme-builder-themes'));

    config(['theme-builder.themes_path' => '/tmp/my-themes/']);

    expect($this->manager->themesPath())->toBe('/tmp/my-themes');
});

it('discovers packages cloned into the themes directory', function (): void {
    $themesDir = sys_get_temp_dir().'/theme-builder-themes-'.uniqid();
    $this->files->ensureDirectoryExists("{$themesDir}/theme-builder-fixture");
    $this->files->copyDirectory($this->fixtureRoot, "{$themesDir}/theme-builder-fixture");

    config(['theme-builder.themes_path' => $themesDir]);

    expect($this->manager->package('fixture'))->not->toBeNull()
        ->and($this->manager->package('fixture')->path)->toBe("{$themesDir}/theme-builder-fixture");

    (new Filesystem)->deleteDirectory($themesDir);
});

it('ignores directories without a manifest', function (): void {
    $empty = sys_get_temp_dir().'/theme-builder-empty-'.uniqid();
    $this->files->ensureDirectoryExists($empty);

    expect($this->manager->register($empty))->toBeNull()
        ->and($this->manager->packages())->toBeEmpty();

    (new Filesystem)->deleteDirectory($empty);
});
