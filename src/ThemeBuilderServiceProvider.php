<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder;

use Illuminate\Support\ServiceProvider;
use Override;
use SoftLand\ThemeBuilder\Builder\BlockRegistry;
use SoftLand\ThemeBuilder\Builder\BlockRenderer;
use SoftLand\ThemeBuilder\Console\MakeThemeCommand;
use SoftLand\ThemeBuilder\Console\SeedPresetCommand;
use SoftLand\ThemeBuilder\Console\ValidateThemeCommand;
use SoftLand\ThemeBuilder\Contracts\MediaProvider;
use SoftLand\ThemeBuilder\Contracts\Store;
use SoftLand\ThemeBuilder\Contracts\StorefrontDataProvider;
use SoftLand\ThemeBuilder\Contracts\ThemeSchema;
use SoftLand\ThemeBuilder\Models\Theme;
use SoftLand\ThemeBuilder\Support\DefaultThemeSchema;
use SoftLand\ThemeBuilder\Support\NullMediaProvider;
use SoftLand\ThemeBuilder\Support\NullStore;
use SoftLand\ThemeBuilder\Support\NullStorefrontDataProvider;
use SoftLand\ThemeBuilder\View\StorefrontViewComposers;

class ThemeBuilderServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/theme-builder.php', 'theme-builder');

        // Current Store (tenant), resolved FRESH on every access — mirrors a
        // host's Tenancy::get(): route-model binding runs before the host's
        // tenant middleware (so this is the NullStore there), while controllers
        // read it after middleware. Hosts set `theme-builder.store_resolver`.
        $this->app->bind(Store::class, function ($app): Store {
            $resolver = $app['config']->get('theme-builder.store_resolver');

            if ($resolver !== null) {
                $store = $app->call($resolver);

                if ($store instanceof Store) {
                    return $store;
                }
            }

            return $app->make(NullStore::class);
        });
        $this->app->singleton(NullStore::class);

        // Convenience: the bare tenant key, derived from the current Store.
        // Resolved fresh (bound, not scoped/singleton) for the same reason as Store.
        $this->app->bind('theme-builder.tenant_id', fn ($app) => $app->make(Store::class)->getThemeBuilderTenantKey());

        // Database shape — overridable by binding a custom ThemeSchema.
        $this->app->singleton(ThemeSchema::class, DefaultThemeSchema::class);

        $this->app->singleton('theme-builder', ThemeBuilderManager::class);

        $this->app->singleton(BlockRegistry::class);
        $this->app->singleton(BlockRenderer::class);

        $this->app->bind(StorefrontDataProvider::class, config('theme-builder.storefront_data_provider', NullStorefrontDataProvider::class));
        $this->app->bind(MediaProvider::class, config('theme-builder.media_provider', NullMediaProvider::class));
    }

    public function boot(): void
    {
        $namespace = (string) config('theme-builder.view_namespace', 'theme-builder');

        $this->loadViewsFrom([
            resource_path('views/vendor/theme-builder'),
            __DIR__.'/../resources/views',
        ], $namespace);

        $this->registerPublishing();

        $this->app->make(StorefrontViewComposers::class)->register();

        if ($this->app->runningInConsole()) {
            $this->commands([
                SeedPresetCommand::class,
                MakeThemeCommand::class,
                ValidateThemeCommand::class,
            ]);
        }

        if ((bool) config('theme-builder.routes.enabled', true)) {
            $this->app['theme-builder']->routes();
        }
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/theme-builder.php' => config_path('theme-builder.php'),
        ], 'theme-builder-config');

        $this->publishes([
            __DIR__.'/../resources/css' => resource_path('css'),
        ], 'theme-builder-assets');

        $this->publishes([
            __DIR__.'/../database/presets' => database_path('presets'),
        ], 'theme-builder-presets');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'theme-builder-migrations');

        $this->publishes([
            __DIR__.'/../lang' => lang_path(),
        ], 'theme-builder-lang');

        // AI integration. Laravel Boost auto-discovers the package's
        // resources/boost/{guidelines,skills} on `boost:install` / `boost:update --discover`,
        // so no publish is needed there. For hosts NOT using Boost (plain Claude Code),
        // this tag copies the agent skill into .claude/skills/.
        $this->publishes([
            __DIR__.'/../resources/boost/skills/theme-builder' => base_path('.claude/skills/theme-builder'),
        ], 'theme-builder-skills');
    }
}
