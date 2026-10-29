<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use SoftLand\ThemeBuilder\Models\Scopes\PresetOrTenantScope;
use SoftLand\ThemeBuilder\Models\Theme;
use SoftLand\ThemeBuilder\Seeders\ThemePresetSeeder;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
});

it('validates the bundled default preset', function (): void {
    $this->artisan('theme-builder:validate', ['slug' => 'default'])
        ->assertExitCode(0);
});

it('validates every bundled starter preset', function (string $slug): void {
    $this->artisan('theme-builder:validate', ['slug' => $slug])
        ->assertExitCode(0);
})->with(['default', 'personal', 'lms', 'cms']);

it('fails validation for an unknown preset slug', function (): void {
    $this->artisan('theme-builder:validate', ['slug' => 'nope'])
        ->assertExitCode(1);
});

it('validates a stored theme by id', function (): void {
    app(ThemePresetSeeder::class)->seed('default');

    $theme = Theme::withoutGlobalScope(PresetOrTenantScope::class)
        ->where('preset_slug', 'default')
        ->firstOrFail();

    $this->artisan('theme-builder:validate', ['--theme' => (string) $theme->id])
        ->assertExitCode(0);
});
