# softland/theme-builder

A standalone **visual theme builder** (React + `@dnd-kit` drag-and-drop editor) and
**storefront block renderer** for Laravel. A merchant opens the builder, edits their
storefront in a drag-and-drop editor, and **publish** makes it live.

- **No Filament. No Spatie. No domain-model coupling.** Only Laravel + the React/TS
  editor + Tailwind CSS.
- The host supplies catalog data and tenant info through **PHP interfaces** — the
  package never assumes your table names, column names, or Store model.
- Schema (tables/columns), tenant data, and routes are all **configurable + overridable**
  via interfaces, service providers, and a publishable config.
- Ships **one generic ecommerce theme** preset (publishable).

---

## Requirements

- PHP **8.3+**, Laravel **13.x**.
- Frontend build: Vite + Tailwind v4 + `@vitejs/plugin-react`.
- The host app provides the npm deps the editor needs: `react`, `react-dom`,
  `@dnd-kit/core`, `@dnd-kit/sortable`, `@dnd-kit/utilities`. (They're not bundled.)

---

## Install

```bash
composer require softland/theme-builder
```

The service provider (`SoftLand\ThemeBuilder\ThemeBuilderServiceProvider`) and the
`ThemeBuilder` facade are **auto-discovered**.

> **Local development via a path repository** (e.g. while building/iterating on the
> package): add to your app's `composer.json`:
> ```json
> "repositories": [{ "type": "path", "url": "path/to/laravel-theme-builder", "options": { "symlink": true } }],
> "require":     { "softland/theme-builder": "dev-main" }
> ```
> and set `"minimum-stability": "dev"` (with `"prefer-stable": true`).

### Publish what you need

```bash
php artisan vendor:publish --tag=theme-builder-config      # config/theme-builder.php
php artisan vendor:publish --tag=theme-builder-assets      # CSS sources → resources/css/
php artisan vendor:publish --tag=theme-builder-migrations  # (fresh installs only)
php artisan vendor:publish --tag=theme-builder-presets     # generic preset → database/presets/
php artisan vendor:publish --tag=theme-builder-lang        # storefront translations → lang/
php artisan migrate
php artisan theme-builder:seed-preset                      # seed the generic "default" theme
```

> If your app already has a `themes` table with the same shape, **skip** the migration
> publish — just point the package at it via the `db` config (see *Schema* below).

---

## Wire the frontend build

The editor's React source is built by **your** Vite, not the package's.

**1. Add the editor entry to `vite.config.js`:**

```js
laravel({
    input: [
        // ...your existing entries...
        'resources/css/storefront.css',
        'resources/css/builder.css',
        'vendor/softland/theme-builder/resources/js/builder/main.tsx', // ← editor
    ],
    // ...
}),
```

**2. If you install via a symlinked path repository**, tell Vite to preserve the
symlink so the editor's `react`/`@dnd-kit` imports resolve from your `node_modules`:

```js
resolve: { preserveSymlinks: true },
```

**3. (Only if you render the package's bundled block views)** add a Tailwind `@source`
line to `resources/css/storefront.css` so its utility classes compile:

```css
@source '../vendor/softland/theme-builder/resources/views/**/*.blade.php';
```

> Skip step 3 if you point the package at **your own** block views via
> `theme-builder.blocks_view_prefix` (see *Block views*).

Then `npm install && npm run build`.

---

## Register routes

In `routes/web.php`:

```php
use SoftLand\ThemeBuilder\Facades\ThemeBuilder;

// 1) Storefront: render the active theme's home blocks.
Route::get('/', [\SoftLand\ThemeBuilder\Http\Controllers\StorefrontController::class, 'home']);

// 2) Editor + autosave/publish/picker endpoints. Wrap in YOUR auth/tenant middleware.
Route::middleware(['web'])->group(function () {
    ThemeBuilder::routes();
});
```

`ThemeBuilder::routes()` accepts overrides: `['prefix' => 'builder', 'name' => 'builder', 'middleware' => [...], 'domain' => null]`
(defaults come from `config('theme-builder.routes')`).

The editor shell lives at `GET /builder/themes/{theme}/edit` (by default) and the
JSON endpoints the editor calls (save/publish/promote/media/revisions/api) are
registered alongside.

---

## Commands

```bash
# Scaffold a new theme preset (copies the bundled default), optionally with
# per-theme override directories.
php artisan theme-builder:make-theme {slug} [--name="Display Name"] [--overrides] [--force]

# Validate a preset (or a stored theme) against the package contracts:
# required keys, settings/color-scheme/typography slices, header/footer
# defaults, and every block (round-tripped through the registry).
php artisan theme-builder:validate [slug]          # one preset, or all if omitted
php artisan theme-builder:validate --theme=6       # a stored theme by id

# Seed a preset into the themes table (is_preset = 1) so it's pickable.
php artisan theme-builder:seed-preset [slug]
```

`validate` exits non-zero on errors (suitable for CI / a pre-commit hook) and
prints `!` warnings for anything that will fall back to defaults.

---

## AI assistant integration (Laravel Boost)

The package ships a **Boost guideline** (`resources/boost/guidelines/core.blade.php`)
and an **agent skill** (`resources/boost/skills/theme-builder/SKILL.md`) using Boost's
[first-party third-party-package hook](https://laravel.com/docs/13.x/boost#third-party-package-ai-guidelines).
Boost **auto-discovers** them — no manual publish needed:

```bash
composer require laravel/boost --dev
php artisan boost:install            # installs the MCP server + guidelines + skills (incl. this package's)
# later, after composer updates:
php artisan boost:update --discover  # re-scans for newly installed packages
```

After `boost:install`:
- the **guideline** is loaded upfront into your agent context (CLAUDE.md / AGENTS.md),
- the **skill** activates on-demand for theme/block/preset work and contains the **full
  host-integration checklist** (Store interface, extending the models, binding providers,
  Vite + Tailwind gotchas, routes, config) plus the add-a-block checklist and pitfalls,
- Boost's `search-docs` MCP tool continues to serve Laravel-ecosystem docs alongside it.

> **Not using Boost?** Plain Claude Code can still use the skill:
> ```bash
> php artisan vendor:publish --tag=theme-builder-skills   # → .claude/skills/theme-builder/
> ```

So you can install + configure the package **either manually** (following the sections
above) **or by asking your AI agent** — the skill knows every step.

---

## Provide tenant data — the `Store` interface

The package reads tenant data **only** through an interface — never by assuming your
`Store` model's columns. Implement it on your tenant model:

```php
namespace App\Models;

use SoftLand\ThemeBuilder\Contracts\Store as ThemeBuilderStore;

class Store extends Model implements ThemeBuilderStore
{
    public function getThemeBuilderTenantKey(): int|string|null { return $this->getKey(); }
    public function getThemeBuilderStoreName(): ?string        { return $this->name; }
    public function getThemeBuilderCurrencyCode(): string      { return $this->currency ?? 'SAR'; }
    public function getThemeBuilderLocale(): string            { return $this->default_locale ?? 'ar'; }
}
```

Then tell the package how to resolve the current store, in a service provider's
`register()`:

```php
config([
    'theme-builder.store_resolver' => fn () => \App\Support\Tenancy::get(), // ?Store
]);
```

- Multi-tenant: return the current `Store` (or null). The package derives the tenant
  key, store name, currency, and locale from it.
- Single-tenant: leave `store_resolver` null — the bundled `NullStore` is used and
  the storefront renders the default preset.

> The package resolves the store **fresh on every access**, so route-model binding
> (which runs before your tenant middleware) safely sees "no tenant" and the
> controllers see the real tenant afterward.

---

## Provide catalog data — the `StorefrontDataProvider` interface

Block types that show products/categories/blog pull data through a contract, so the
package ships no Product models. Implement and bind it:

```php
namespace App\ThemeBuilder;

use SoftLand\ThemeBuilder\Contracts\StorefrontDataProvider;

class MyStorefrontDataProvider implements StorefrontDataProvider
{
    public function products(array $props): array
    {
        // Return normalized arrays (ProductView shape), e.g.:
        // [['id' => 1, 'name' => 'Tee', 'slug' => 'tee', 'url' => '/products/tee',
        //   'price' => 9900, 'image' => '...', 'subtitle' => '...'], ...]
    }
    // productsForTabs, categories, categoryLinks, featuredProduct,
    // blogPost, blogCategories, blogPosts,
    // searchProducts, searchCategories, searchBlogPosts (for editor pickers)
}
```

```php
// AppServiceProvider::register()
$this->app->bind(
    \SoftLand\ThemeBuilder\Contracts\StorefrontDataProvider::class,
    \App\ThemeBuilder\MyStorefrontDataProvider::class,
);
```

Value shapes are documented in the interface docblock. `featuredProduct()` and
`blogPost()` return `mixed` (a normalized array by default, or your own model if you
keep your own block views — see *Block views*).

Without a provider, static blocks (Hero, Banners, FAQ, …) render normally and
product/category/blog blocks render empty — the bundled `NullStorefrontDataProvider`
never throws.

### Media (optional)

Implement `SoftLand\ThemeBuilder\Contracts\MediaProvider` and bind it to power the
editor's media picker (`list`, `store`, `url`). Default is a null provider.

---

## Schema — tables & columns are configurable

The package's `themes` / `theme_revisions` tables and the owning **tenant column**
are not hardcoded. Three ways to adapt, in order of simplicity:

**a) Publish the config** and edit `db`:

```php
// config/theme-builder.php
'db' => [
    'themes_table'    => 'themes',
    'revisions_table' => 'theme_revisions',
    'tenant_column'   => 'store_id', // your owner column: 'tenant_id', 'shop_id', …
    'columns'         => [],         // optional logical => physical content-column remap
],
```

**b) Bind a custom `ThemeSchema`** for full control (table names, tenant column,
per-column remap) in a service provider:

```php
$this->app->singleton(
    \SoftLand\ThemeBuilder\Contracts\ThemeSchema::class,
    \App\ThemeBuilder\MyThemeSchema::class,
);
```

**c) Extend the (non-`final`) models** (`SoftLand\ThemeBuilder\Models\Theme`,
`ThemeRevision`) and override methods.

The default is `SoftLand\ThemeBuilder\Support\DefaultThemeSchema`, which reads the
`db` config above.

---

## Block views

The renderer registers each block under `theme-builder.blocks_view_prefix`
(default `theme-builder::storefront.blocks` → the package's bundled Blade components).

- **Use the bundled blocks**: keep the default. Add the `@source` line above.
- **Use your own blocks** (e.g. an app being migrated onto the package): set
  `'blocks_view_prefix' => 'storefront.blocks'` and the renderer resolves *your*
  views — no file moves.

Override a single block by publishing a view to
`resources/views/vendor/theme-builder/components/storefront/blocks/<kebab>.blade.php`.

Per-theme component/header/footer overrides follow the convention
`resources/views/components/themes/{slug}/…` (file existence is the signal — no
registration). A packaged theme stylesheet goes at `public/themes/{slug}/theme.css`.

---

## Configuration reference

Published at `config/theme-builder.php`:

| Key | Purpose |
|---|---|
| `store_resolver` | Closure returning the current `Store` (tenant) or null. |
| `active_theme_resolver` | Optional closure returning the active `Theme` directly. |
| `exit_url` | Where the editor's "Exit" button redirects. |
| `currency` | Fallback currency for the `NullStore` / `tb_money()`. |
| `default_preset` | Slug of the shipped/fallback preset. |
| `db` | `themes_table`, `revisions_table`, `tenant_column`, `columns` (remap). |
| `acting_user_resolver` | Closure returning the revision author id (or null). |
| `blocks_view_prefix` | Blade namespace/prefix for block components. |
| `storefront_data_provider` | Default provider class (`NullStorefrontDataProvider`). |
| `media_provider` | Default media provider class (`NullMediaProvider`). |
| `routes` | `enabled`, `path`, `name`, `middleware`. |
| `routes_map` | Maps `tb_route()` names (e.g. `product`, `cart`) to URLs/closures. |

### Helpers

`tb_bi($value)` (bilingual `{ar,en}` → locale), `tb_money($cents, $currency)`,
`tb_route($name, $params=[])` (resolves via `routes_map`, strips a `storefront.`
prefix, falls back to `#`).

---

## Quickstart (single-tenant)

```bash
composer require softland/theme-builder
php artisan vendor:publish --tag=theme-builder-config
php artisan vendor:publish --tag=theme-builder-assets
php artisan vendor:publish --tag=theme-builder-migrations
php artisan vendor:publish --tag=theme-builder-lang
php artisan migrate
php artisan theme-builder:seed-preset
# add the editor entry to vite.config.js, the @source line, then:
npm install && npm run build
```

```php
// routes/web.php
Route::get('/', [\SoftLand\ThemeBuilder\Http\Controllers\StorefrontController::class, 'home']);
Route::middleware(['web'])->group(fn () => \SoftLand\ThemeBuilder\Facades\ThemeBuilder::routes());
```

Open `/`, then `/builder/themes/{id}/edit`. Implement `StorefrontDataProvider` when
you want real product/category data.

---

## License

MIT.
