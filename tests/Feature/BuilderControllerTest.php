<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use SoftLand\ThemeBuilder\Contracts\Store;
use SoftLand\ThemeBuilder\Enums\ThemeStatus;
use SoftLand\ThemeBuilder\Models\Theme;
use SoftLand\ThemeBuilder\Models\ThemeRevision;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
});

function makeTheme(array $overrides = []): Theme
{
    return Theme::create(array_merge([
        'name' => ['ar' => 'متجر', 'en' => 'Store'],
        'status' => ThemeStatus::Draft,
        'blocks' => [],
        'settings' => Theme::defaultSettings(),
    ], $overrides));
}

it('saves a builder payload and returns the normalized theme', function (): void {
    $theme = makeTheme();

    $response = $this->postJson(route('theme-builder.themes.save', $theme), [
        'blocks' => [
            ['type' => 'Hero', 'props' => ['title' => ['ar' => 'أ', 'en' => 'A']]],
            ['type' => 'Bogus', 'props' => ['whatever' => 1]],
        ],
        'settings' => ['colors' => ['primary' => '#123456']],
    ]);

    $response->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonCount(1, 'theme.blocks');

    $theme->refresh();

    expect($theme->blocks[0]['type'])->toBe('Hero')
        ->and($theme->resolvedSettings()['colors']['primary'])->toBe('#123456');
});

it('accepts a block with empty props (all defaults)', function (): void {
    $theme = makeTheme();

    $this->postJson(route('theme-builder.themes.save', $theme), [
        'blocks' => [['type' => 'Hero', 'props' => []]],
    ])->assertOk();

    $theme->refresh();

    expect($theme->blocks)->toHaveCount(1)
        ->and($theme->blocks[0]['props'])->toHaveKey('title'); // defaults merged
});

it('publishes a revision with semantic version bumps', function (): void {
    $theme = makeTheme();

    $first = $this->postJson(route('theme-builder.themes.publish', $theme), [
        'blocks' => [['type' => 'Hero', 'props' => []]],
    ]);

    $first->assertOk()->assertJsonPath('version', '1.0');

    $second = $this->postJson(route('theme-builder.themes.publish', $theme), [
        'blocks' => [['type' => 'Hero', 'props' => []]],
    ]);

    $second->assertOk()->assertJsonPath('version', '1.1');

    $major = $this->postJson(route('theme-builder.themes.publish', $theme), [
        'blocks' => [['type' => 'Hero', 'props' => []]],
        'major' => true,
    ]);

    $major->assertOk()->assertJsonPath('version', '2.0');

    expect(ThemeRevision::count())->toBe(3);
});

it('promotes draft state to live columns for published themes', function (): void {
    $theme = makeTheme(['status' => ThemeStatus::Published, 'published_at' => now()]);

    $this->postJson(route('theme-builder.themes.save', $theme), [
        'blocks' => [['type' => 'PromoBar', 'props' => []]],
    ])->assertOk();

    $theme->refresh();

    expect($theme->blocks)->toBe([]) // live columns untouched
        ->and($theme->draft_blocks)->toHaveCount(1);

    $this->postJson(route('theme-builder.themes.promote', $theme), [
        'blocks' => [['type' => 'PromoBar', 'props' => []]],
    ])->assertOk();

    $theme->refresh();

    expect($theme->blocks[0]['type'])->toBe('PromoBar')
        ->and($theme->draft_blocks)->toBeNull()
        ->and(ThemeRevision::count())->toBe(1);
});

it('rejects promoting a non-published theme', function (): void {
    $theme = makeTheme();

    $this->postJson(route('theme-builder.themes.promote', $theme), [
        'blocks' => [['type' => 'Hero', 'props' => []]],
    ])->assertStatus(422);
});

it('restores a revision into the working copy', function (): void {
    $theme = makeTheme();
    $revision = $theme->revisions()->create([
        'revision_number' => 1,
        'version' => '1.0',
        'blocks' => [['type' => 'FaqAccordion', 'props' => []]],
    ]);

    $response = $this->postJson(
        route('theme-builder.themes.restore-revision', [$theme, $revision])
    );

    $response->assertOk()
        ->assertJsonPath('theme.blocks.0.type', 'FaqAccordion');
});

it('rejects restoring a revision that belongs to another theme', function (): void {
    $themeA = makeTheme();
    $themeB = makeTheme(['name' => ['ar' => 'ب', 'en' => 'B']]);
    $revision = $themeB->revisions()->create([
        'revision_number' => 1,
        'version' => '1.0',
        'blocks' => [['type' => 'Hero', 'props' => []]],
    ]);

    $this->postJson(
        route('theme-builder.themes.restore-revision', [$themeA, $revision])
    )->assertStatus(404);
});

it('hides another tenant theme from the builder via the tenant scope', function (): void {
    $theme = makeTheme(['store_id' => 99]);

    app('config')->set('theme-builder.store_resolver', fn () => new class implements Store
    {
        public function getThemeBuilderTenantKey(): int|string|null
        {
            return 1;
        }

        public function getThemeBuilderStoreName(): ?string
        {
            return 'Store 1';
        }

        public function getThemeBuilderCurrencyCode(): string
        {
            return 'SAR';
        }

        public function getThemeBuilderLocale(): string
        {
            return 'en';
        }
    });

    // Route-model binding runs through PresetOrTenantScope, so another
    // tenant's theme is not even resolvable (404) before authorization (403).
    $this->postJson(route('theme-builder.themes.save', $theme), [
        'blocks' => [],
    ])->assertStatus(404);
});
