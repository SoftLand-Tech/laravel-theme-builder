<?php

declare(strict_types=1);

use SoftLand\ThemeBuilder\Support\NullMediaProvider;
use SoftLand\ThemeBuilder\Support\NullStorefrontDataProvider;

return [

    /*
    |----------------------------------------------------------------------
    | Store (tenant) resolution
    |----------------------------------------------------------------------
    |
    | A single closure returning the current Store instance (the host's tenant
    | model, implementing SoftLand\ThemeBuilder\Contracts\Store) or null. The
    | package reads the tenant key, store name, currency, and locale ONLY
    | through that interface — never by assuming columns. Null = single-tenant.
    */

    'store_resolver' => null, // fn (): ?\SoftLand\ThemeBuilder\Contracts\Store

    /*
    | Resolve the active Theme instance directly (bypasses the tenant query).
    | Closure returning ?SoftLand\ThemeBuilder\Models\Theme.
    */

    'active_theme_resolver' => null,

    // Where the editor's "Exit" button redirects.
    'exit_url' => '/',

    // Currency fallback for the bundled NullStore / tb_money() helper.
    'currency' => 'SAR',

    // Default preset slug (shipped preset + fallback theme).
    'default_preset' => 'default',

    /*
    |----------------------------------------------------------------------
    | Database shape (publishable; overridable via ThemeSchema binding)
    |----------------------------------------------------------------------
    |
    | Sensible canonical defaults. A host that needs different names can either
    | publish this config and edit them, or bind a custom ThemeSchema
    | implementation in its service provider for full control (see
    | SoftLand\ThemeBuilder\Contracts\ThemeSchema).
    */

    'db' => [
        'themes_table' => 'themes',
        'revisions_table' => 'theme_revisions',
        'tenant_column' => 'store_id',
        // Optional logical => physical content-column remap (identity by default).
        'columns' => [],
    ],

    /*
    | Where per-theme block/header/footer override Blade files live inside the
    | HOST app. Null = package default (resources/views/components/themes/{slug}).
    */

    'theme_override_path' => null,

    // Closure returning the acting user id for revision authorship (or null).
    'acting_user_resolver' => fn () => null,

    // Blade view namespace registered by loadViewsFrom().
    'view_namespace' => 'theme-builder',

    /*
    | The Blade view prefix the registry registers every block under, and the
    | renderer falls back to. Defaults to the package namespace. A host with its
    | own pre-existing block components (e.g. an app being migrated onto the
    | package) can point this at its own namespace (e.g. 'storefront.blocks') so
    | the package's renderer resolves the host's views with no file moves.
    */
    'blocks_view_prefix' => 'theme-builder::storefront.blocks',

    // Default catalog-data + media providers. Hosts bind real implementations.
    'storefront_data_provider' => NullStorefrontDataProvider::class,
    'media_provider' => NullMediaProvider::class,

    /*
    | Editor routes. The host usually mounts ThemeBuilder::routes() inside its
    | own middleware group (auth/tenant). `middleware` here is the minimum.
    */

    'routes' => [
        'enabled' => true,
        'path' => 'builder',
        'name' => 'theme-builder',
        'middleware' => ['web'],
    ],

    'storefront' => [
        'home_view' => 'theme-builder::storefront.home',
    ],

    /*
    | Storefront route map used by the tb_route() helper so generic blocks/chrome
    | never assume host routes exist. Each entry is a static URL string OR a
    | closure accepting the params array. Hosts override to point at real routes.
    */

    'routes_map' => [
        'home' => '/',
        'product' => fn (array $p) => '/products/'.($p['slug'] ?? ''),
        'category' => fn (array $p) => '/collections/'.($p['slug'] ?? ''),
        'cart' => '/cart',
        'checkout' => '/checkout',
        'search' => '/search',
        'blog.index' => '/blog',
        'contact.store' => '/contact',
    ],
];
