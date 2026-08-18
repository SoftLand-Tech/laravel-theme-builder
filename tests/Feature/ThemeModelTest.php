<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use SoftLand\ThemeBuilder\Enums\ThemeStatus;
use SoftLand\ThemeBuilder\Models\Theme;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
});

it('provides sensible defaults for settings, header, and footer', function (): void {
    $defaults = Theme::defaultSettings();

    expect($defaults)->toHaveKeys(['colors', 'color_schemes', 'typography', 'radius', 'semantic', 'layout', 'effects', 'product_card', 'product_page', 'cart'])
        ->and(Theme::headerDefaults())->toHaveKey('logo_text')
        ->and(Theme::footerDefaults())->toHaveKey('payment_icons')
        ->and(Theme::defaultSettings()['colors']['primary'])->toBe('#458FCC');
});

it('deep-merges stored settings over the defaults', function (): void {
    $theme = Theme::create([
        'name' => ['ar' => 'متجر', 'en' => 'Store'],
        'status' => ThemeStatus::Draft,
        'settings' => [
            'colors' => ['primary' => '#123456'],
        ],
    ]);

    $resolved = $theme->resolvedSettings();

    expect($resolved['colors']['primary'])->toBe('#123456')
        ->and($resolved['colors']['background'])->toBe('#FFFFFF'); // default preserved
});

it('uses the draft overlay for published themes and promotes it on publish', function (): void {
    $theme = Theme::create([
        'name' => ['ar' => 'متجر', 'en' => 'Store'],
        'status' => ThemeStatus::Published,
        'blocks' => [['type' => 'Hero', 'props' => []]],
        'settings' => Theme::defaultSettings(),
        'published_at' => now(),
    ]);

    expect($theme->usesDraftOverlay())->toBeTrue()
        ->and($theme->hasUnpublishedDraft())->toBeFalse();

    $theme->persistBuilderPayload([
        'blocks' => [['type' => 'PromoBar', 'props' => []]],
        'settings' => ['colors' => ['primary' => '#ABCDEF']],
    ]);

    $theme->refresh();

    expect($theme->hasUnpublishedDraft())->toBeTrue()
        ->and($theme->builderResolvedBlocks())->toHaveCount(1)
        ->and($theme->builderResolvedBlocks()[0]['type'])->toBe('PromoBar')
        ->and($theme->builderResolvedSettings()['colors']['primary'])->toBe('#ABCDEF')
        // Live columns untouched until promote.
        ->and($theme->blocks[0]['type'])->toBe('Hero');

    $theme->promoteDraftToPublished();
    $theme->refresh();

    expect($theme->blocks[0]['type'])->toBe('PromoBar')
        ->and($theme->draft_blocks)->toBeNull()
        ->and($theme->hasUnpublishedDraft())->toBeFalse();
});

it('persists exactly the blocks it is given (normalization happens in the controller)', function (): void {
    $theme = Theme::create([
        'name' => ['ar' => 'متجر', 'en' => 'Store'],
        'status' => ThemeStatus::Draft,
        'blocks' => [],
    ]);

    $theme->persistBuilderPayload([
        'blocks' => [
            ['type' => 'Bogus', 'props' => []],
            ['type' => 'Hero', 'props' => ['unknown' => 'dropped']],
        ],
    ]);

    $theme->refresh();

    expect($theme->blocks)->toHaveCount(2)
        ->and($theme->blocks[0]['type'])->toBe('Bogus')
        ->and($theme->blocks[1]['type'])->toBe('Hero');
});

it('activates a theme and archives the previous published theme', function (): void {
    $first = Theme::create([
        'name' => ['ar' => 'أ', 'en' => 'A'],
        'status' => ThemeStatus::Published,
        'published_at' => now(),
    ]);
    $second = Theme::create([
        'name' => ['ar' => 'ب', 'en' => 'B'],
        'status' => ThemeStatus::Draft,
    ]);

    $second->activateForCurrentStore();

    expect($second->fresh()->status)->toBe(ThemeStatus::Published)
        ->and($first->fresh()->status)->toBe(ThemeStatus::Archived);
});

it('forks a preset into a tenant-owned draft', function (): void {
    $preset = Theme::create([
        'name' => ['ar' => 'افتراضي', 'en' => 'Default'],
        'is_preset' => true,
        'preset_slug' => 'default',
        'status' => ThemeStatus::Published,
        'blocks' => [['type' => 'Hero', 'props' => []]],
    ]);

    $fork = Theme::forkPresetForStore($preset, 42);

    expect($fork->store_id)->toBe(42)
        ->and($fork->is_preset)->toBeFalse()
        ->and($fork->status)->toBe(ThemeStatus::Draft)
        ->and($fork->blocks)->toHaveCount(1);
});
