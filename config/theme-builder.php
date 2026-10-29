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
    | Site type & pages
    |----------------------------------------------------------------------
    |
    | `site_type` records what kind of app the builder is installed on
    | (ecommerce | lms | cms | personal). Purely informational for hosts,
    | theme packages, and the generated AI hand-off prompt. Null = unset
    | (the installer fills it after scanning the app).
    |
    | `pages` lists the page templates editable in the builder's Pages tab
    | (keys) and their labels. Null = auto: the union of the registered
    | theme packages' pages, falling back to the starter pages for
    | `site_type` (course/lesson for LMS, post/page for CMS,
    | about/services/contact for personal, product/cart/collection for
    | ecommerce) when no theme package is installed.
    */

    'site_type' => null, // 'ecommerce' | 'lms' | 'cms' | 'personal'

    'pages' => null, // e.g. ['product' => ['label' => 'Product'], 'checkout' => ['label' => 'Checkout']]

    /*
    | Auto-discover theme packages beside this package (the dev-monorepo
    | layout: Sites/theme-builder-ecommerce next to Sites/laravel-theme-builder,
    | which mirrors vendor/<owner>/theme-builder-*). Installed theme packages
    | in vendor are always discovered; this only gates the sibling glob.
    */
    'discover_siblings' => true,

    /*
    | Where `theme-builder:install` clones theme repos when a chosen theme
    | isn't installed locally — and where the package manager discovers them
    | on every boot afterwards. Clone one by hand into this directory and it
    | works the same way. Null = the host's base_path('theme-builder-themes').
    */
    'themes_path' => null,

    /*
    | Git repos themes can be cloned from during `theme-builder:install`,
    | slug => clone URL. The four official packages live under
    | github.com/SoftLand-Tech. Point an entry at a fork to install yours.
    */
    'theme_repos' => [
        'personal' => 'https://github.com/SoftLand-Tech/theme-builder-personal',
        'ecommerce' => 'https://github.com/SoftLand-Tech/theme-builder-ecommerce',
        'lms' => 'https://github.com/SoftLand-Tech/theme-builder-lms',
        'cms' => 'https://github.com/SoftLand-Tech/theme-builder-cms',
    ],

    /*
    | Whether `theme-builder:install` should offer catalog themes that are
    | not installed yet (it would git-clone them from `theme_repos`). Turn
    | off for offline / hermetic environments; explicit `--themes=` slugs
    | are still honored.
    */
    'offer_remote_themes' => true,

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
        // Catalog (ecommerce-style sites).
        'product' => fn (array $p) => '/products/'.($p['slug'] ?? ''),
        'category' => fn (array $p) => '/collections/'.($p['slug'] ?? ''),
        'cart' => '/cart',
        'checkout' => '/checkout',
        'search' => '/search',
        // Content (CMS / personal sites).
        'page' => fn (array $p) => '/pages/'.($p['slug'] ?? ''),
        'post' => fn (array $p) => '/blog/'.($p['slug'] ?? ''),
        'service' => fn (array $p) => '/services/'.($p['slug'] ?? ''),
        // Courses (LMS-style sites).
        'course' => fn (array $p) => '/courses/'.($p['slug'] ?? ''),
        'lesson' => fn (array $p) => '/courses/'.($p['course'] ?? '').'/'.($p['slug'] ?? ''),
        // Shared.
        'blog.index' => '/blog',
        'contact.store' => '/contact',
    ],
];
