# softlandtech/theme-builder

A general-purpose **visual theme builder** (React + `@dnd-kit` drag-and-drop editor) and **block renderer** for any Laravel site — ecommerce, LMS, CMS, or personal/portfolio. A merchant opens the builder, edits their site in a drag-and-drop editor, and **publish** makes it live.

- **No Filament. No Spatie. No domain-model coupling.** Only Laravel + the React/TS editor + Tailwind CSS.
- The host supplies catalog/content data and tenant info through **PHP interfaces** - the package never assumes your table names, column names, or models.
- **Theme packages** (`softlandtech/theme-builder-{personal,ecommerce,lms,cms}`) provide the per-site-type starter themes; the engine stays generic.
- **One-command installer** that scans your app (plain greps, no AI), asks what exists (cart? checkout?), installs the right themes, wires routes + Vite, and can hand the rest to your AI agent with a ready-made prompt.

---

## Requirements

- PHP **8.3+**, Laravel **13.x**.
- Frontend build: Vite + Tailwind v4 + `@vitejs/plugin-react`.
- The host app provides the npm deps the editor needs: `react`, `react-dom`,
  `@dnd-kit/core`, `@dnd-kit/sortable`, `@dnd-kit/utilities`. (They're not bundled.)

---

## Install — the easy way

```bash
composer require softlandtech/theme-builder
php artisan theme-builder:install
```

The installer (all greps, no AI):

1. **Scans your app** — models, migrations, routes, composer.json — and reports
   the detected site type (**ecommerce / lms / cms / personal / new empty project**)
   with the evidence it found. You can accept it or override it.
2. **Asks what already exists** — a cart page? checkout? search? accounts? blog?
   (pre-filled from the scan).
3. **Lets you pick theme packages** — personal, ecommerce, lms, cms — or several.
   Themes already on disk (vendor / beside the core package / previously
   cloned) are used as-is; anything missing is **git-cloned** from its repo
   (`github.com/SoftLand-Tech/theme-builder-{slug}`, overridable via
   `theme-builder.theme_repos`) into `theme-builder-themes/` and seeded.
   When a theme can't be installed (offline, unknown slug), the installer
   falls back to the bundled starter for your site type, so you always end
   with a working theme.
4. **Does the manual install**: publishes config/assets/lang/migrations, migrates,
   seeds the chosen presets, wires `routes/web.php` (`/` storefront +
   `ThemeBuilder::routes()`), injects the Vite entries + Tailwind `@source` line,
   checks the npm deps, and records `site_type` + `pages` in the config.
5. **Prints an AI hand-off prompt** (also saved to `theme-builder-ai-prompt.md`)
   containing the scan results, your choices, what was done, and an exact
   checklist for the host-specific remainder — paste it into Claude Code or any
   agent to finish. `--no-ai` skips this.

Every step is idempotent — re-running fills in only what's missing.

```bash
php artisan theme-builder:install
    {--type=}          # force: ecommerce|lms|cms|personal|empty (skip scanning)
    {--themes=}        # comma-separated theme slugs (skip picking)
    {--features=}      # comma-separated: cart,checkout,courses,lessons,blog,pages,portfolio,services,search,account,contact
    {--currency=}      # storefront currency code
    {--skip-migrate}   # publish migrations but don't run them
    {--skip-composer}  # copy theme-package presets instead of composer require
    {--skip-git}       # print clone commands instead of git-cloning themes
    {--no-ai}          # skip the AI hand-off prompt
```

## Install — the manual way

```bash
composer require softlandtech/theme-builder
php artisan vendor:publish --tag=theme-builder-config      # config/theme-builder.php
php artisan vendor:publish --tag=theme-builder-assets      # CSS sources → resources/css/
php artisan vendor:publish --tag=theme-builder-migrations  # (fresh installs only)
php artisan vendor:publish --tag=theme-builder-lang        # storefront translations → lang/
php artisan migrate
php artisan theme-builder:seed-preset                      # seed the bundled "default" theme
# …or the starter for your site type:
php artisan theme-builder:seed-preset personal             # personal | lms | cms
```

> If your app already has a `themes` table with the same shape, **skip** the migration
> publish - just point the package at it via the `db` config (see *Schema* below).

---

## Theme packages (personal / ecommerce / lms / cms)

The engine ships **a bundled starter per site type** (`default` for
ecommerce/empty installs, `personal`, `lms`, `cms`) so installs work offline
with zero extra packages; richer site-type personality comes from theme
packages. Each is a small composer package containing:

```
theme-builder.json         manifest: slug, site_types, pages, routes_map, settings
presets/{slug}.json        the starter theme (blocks, settings, header, footer, templates)
resources/css/theme.css    packaged CSS overrides, scoped under .theme-{slug}
src/{Name}ThemeProvider.php
```

| Package | Site type | Pages it adds |
|---|---|---|
| `softlandtech/theme-builder-personal` | personal / portfolio | about, services, contact |
| `softlandtech/theme-builder-ecommerce` | ecommerce | product, collection, cart, checkout |
| `softlandtech/theme-builder-lms` | LMS / courses | course, lesson |
| `softlandtech/theme-builder-cms` | CMS / blog | post, page |

Each lives in its own public repo — `github.com/SoftLand-Tech/theme-builder-{slug}` —
and installs three ways:

```bash
# 1) The installer lists every theme and clones the ones you pick:
php artisan theme-builder:install

# 2) Plain clone — the core discovers anything under theme-builder-themes/:
git clone https://github.com/SoftLand-Tech/theme-builder-lms theme-builder-themes/theme-builder-lms
php artisan theme-builder:seed-preset lms

# 3) Composer:
composer require softlandtech/theme-builder-lms
php artisan theme-builder:seed-preset lms
```

Theme packages are discovered automatically — from `vendor/*/*/theme-builder.json`,
from folders beside the core package (`theme-builder-*` — the dev layout), and
from the host's themes directory (`theme-builder.themes_path`, default
`theme-builder-themes/` — update a cloned theme with `git pull`, then re-seed).
Their pages appear in the editor's **Pages** tab, their presets in the theme
picker, and their suggested `routes_map` entries are offered to the installer.

**Build your own**: copy one, edit `theme-builder.json` + the preset, and
`php artisan theme-builder:validate {slug}` it. `theme-builder:make-theme
{slug} --from={slug}` scaffolds a customizable copy into `database/presets/`.
Point `theme-builder.theme_repos.{slug}` at your fork to make it
clone-installable too.

---

## Wire the frontend build (manual installs)

The editor's React source is built by **your** Vite, not the package's.

**1. Add the editor entry to `vite.config.js`:**

```js
laravel({
    input: [
        // ...your existing entries...
        'resources/css/storefront.css',
        'resources/css/builder.css',
        'vendor/softlandtech/theme-builder/resources/js/builder/main.tsx', // ← editor
    ],
    // ...
}),
```

**2. (Only for a symlinked path-repository install)** tell Vite
to preserve the symlink so the editor's `react`/`@dnd-kit` imports resolve from
your `node_modules`:

```js
resolve: { preserveSymlinks: true },
```

**3. (Only if you render the package's bundled block views)** add a Tailwind `@source`
line to `resources/css/storefront.css` so its utility classes compile:

```css
@source '../../vendor/softlandtech/theme-builder/resources/views/**/*.blade.php';
```

> Skip step 3 if you point the package at **your own** block views via
> `theme-builder.blocks_view_prefix` (see *Block views*).

Then `npm install && npm run build`.

---

## Register routes (manual installs)

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

### Pages & templates

Every theme stores per-**page** block lists (`before` / `after` the page's own
content). Which pages exist is **not hardcoded**: it comes from
`theme-builder.pages` config → installed theme packages → the starter pages
for your `site_type` (course/lesson for LMS, post/page for CMS,
about/services/contact for personal, product/cart/collection for ecommerce).
The editor's Pages tab follows automatically.

- Wrap your own page content with the merchant's sections:

```blade
{!! ThemeBuilder::renderTemplate('product', 'before') !!}
    ...your product content...
{!! ThemeBuilder::renderTemplate('product', 'after') !!}
```

- Or render a full marketing page (landing, services, …) from its template:

```php
Route::get('/services', fn () => app(\SoftLand\ThemeBuilder\Http\Controllers\StorefrontController::class)->page('services'));
```

---

## Commands

```bash
# Interactive installer: scan → ask → install → wire → (optional) AI hand-off.
php artisan theme-builder:install [options]

# Scaffold a new theme preset, optionally based on any existing one
# (including theme packages), with optional per-theme override directories.
php artisan theme-builder:make-theme {slug} [--name="Display Name"] [--from=default] [--overrides] [--force]

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

## Testing

```bash
composer test      # run the Pest suite (Testbench + in-memory SQLite)
composer lint      # Laravel Pint in test mode (no changes)
composer format    # fix code style with Laravel Pint
```

The suite covers the block registry (every registered block resolves to a real
Blade component), the Theme model (defaults, draft overlay, promotion,
activation, preset forking), the builder HTTP endpoints (save/publish/promote/
restore, tenant isolation, validation — including arbitrary page-template keys),
storefront rendering, the site detector, theme-package discovery, the
`theme-builder:install` command (non-interactive, with host-file restore), and
the `theme-builder:validate` command.

---

## AI assistant integration (Laravel Boost)

The package ships a **Boost guideline** (`resources/boost/guidelines/core.blade.php`)
and an **agent skill** (`resources/boost/skills/theme-builder/SKILL.md`) using Boost's
[first-party third-party-package hook](https://laravel.com/docs/13.x/boost#third-party-package-ai-guidelines).
Boost **auto-discovers** them - no manual publish needed:

```bash
composer require laravel/boost --dev
php artisan boost:install            # installs the MCP server + guidelines + skills (incl. this package's)
# later, after composer updates:
php artisan boost:update --discover  # re-scans for newly installed packages
```

> **Not using Boost?** Plain Claude Code can still use the skill:
> ```bash
> php artisan vendor:publish --tag=theme-builder-skills   # → .claude/skills/theme-builder/
> ```

The installer's generated prompt (`theme-builder-ai-prompt.md`) pairs with the
skill: the prompt carries your app's scan results and remaining checklist, the
skill carries the package integration knowledge.

---

## Provide tenant data - the `Store` interface

The package reads tenant data **only** through an interface - never by assuming your
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
- Single-tenant: leave `store_resolver` null - the bundled `NullStore` is used and
  the storefront renders the default preset.

> The package resolves the store **fresh on every access**, so route-model binding
> (which runs before your tenant middleware) safely sees "no tenant" and the
> controllers see the real tenant afterward.

---

## Provide content data - the `StorefrontDataProvider` interface

Block types that show products/categories/blog (or courses mapped onto the same
shapes) pull data through a contract, so the package ships no domain models.
Implement and bind it:

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
keep your own block views - see *Block views*).

**LMS hosts**: map courses → `products()` (name/slug/url/price/rating), lessons →
your own pages, and the same grids render your catalog — see the
`theme-builder-lms` README.

Without a provider, static blocks (Hero, Banners, FAQ, …) render normally and
product/category/blog blocks render empty - the bundled `NullStorefrontDataProvider`
never throws.

### Media (optional)

Implement `SoftLand\ThemeBuilder\Contracts\MediaProvider` and bind it to power the
editor's media picker (`list`, `store`, `url`). Default is a null provider.

---

## Schema - tables & columns are configurable

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
  views - no file moves.

Override a single block by publishing a view to
`resources/views/vendor/theme-builder/components/storefront/blocks/<kebab>.blade.php`.

Per-theme component/header/footer overrides follow the convention
`resources/views/components/themes/{slug}/…` (file existence is the signal - no
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
| `site_type` | What kind of app this is (`ecommerce`/`lms`/`cms`/`personal`). Filled by the installer. |
| `pages` | Page templates the builder edits (key => label). Null = theme packages → legacy trio. |
| `discover_siblings` | Auto-discover `theme-builder-*` packages beside the core package. |
| `themes_path` | Where cloned theme repos live (default `theme-builder-themes/`). |
| `theme_repos` | slug => clone URL map for `theme-builder:install`. Point entries at forks. |
| `offer_remote_themes` | Offer to git-clone catalog themes during install. |
| `db` | `themes_table`, `revisions_table`, `tenant_column`, `columns` (remap). |
| `acting_user_resolver` | Closure returning the revision author id (or null). |
| `blocks_view_prefix` | Blade namespace/prefix for block components. |
| `storefront_data_provider` | Default provider class (`NullStorefrontDataProvider`). |
| `media_provider` | Default media provider class (`NullMediaProvider`). |
| `routes` | `enabled`, `path`, `name`, `middleware`. |
| `routes_map` | Maps `tb_route()` names (e.g. `product`, `cart`, `course`, `page`) to URLs/closures. |

### Helpers

`tb_bi($value)` (bilingual `{ar,en}` → locale), `tb_money($cents, $currency)`,
`tb_route($name, $params=[])` (resolves via `routes_map`, strips a `storefront.`
prefix, falls back to `#`).

Facade: `ThemeBuilder::routes()`, `ThemeBuilder::pages()`,
`ThemeBuilder::packages()`, `ThemeBuilder::renderTemplate($page, $slot)`.

---

## Quickstart (single-tenant)

```bash
composer require softlandtech/theme-builder
php artisan theme-builder:install --type=personal --themes=personal
npm install && npm run build
```

Open `/`, then `/builder/themes/{id}/edit`. Implement `StorefrontDataProvider`
when you want real product/category data — or paste the generated
`theme-builder-ai-prompt.md` into your AI agent and let it finish.

---

## License

MIT.
