<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use SoftLand\ThemeBuilder\Contracts\Store;
use SoftLand\ThemeBuilder\Enums\ThemeStatus;
use SoftLand\ThemeBuilder\Http\Controllers\StorefrontController;
use SoftLand\ThemeBuilder\Models\Theme;
use SoftLand\ThemeBuilder\Seeders\ThemePresetSeeder;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

    // The host normally registers this route in routes/web.php.
    Route::get('/', [StorefrontController::class, 'home'])->middleware('web');
});

it('renders the storefront home with a seeded preset theme', function (): void {
    app(ThemePresetSeeder::class)->seed('default');

    $this->get('/')->assertOk()->assertSee('Welcome to our store', false);
});

it('renders the storefront home with no theme at all', function (): void {
    $this->get('/')->assertOk();
});

it('renders the storefront home with a published tenant theme', function (): void {
    $theme = Theme::create([
        'name' => ['ar' => 'متجري', 'en' => 'My Store'],
        'status' => ThemeStatus::Published,
        'blocks' => [
            ['type' => 'Hero', 'props' => ['title' => ['ar' => 'أهلا', 'en' => 'Hello']]],
        ],
        'settings' => Theme::defaultSettings(),
        'published_at' => now(),
        'store_id' => 1,
    ]);

    app('config')->set('theme-builder.store_resolver', fn () => new class implements Store
    {
        public function getThemeBuilderTenantKey(): int|string|null
        {
            return 1;
        }

        public function getThemeBuilderStoreName(): ?string
        {
            return 'My Store';
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

    $this->get('/')->assertOk()->assertSee('Hello', false);
});
