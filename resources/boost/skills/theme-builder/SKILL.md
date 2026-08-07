---
name: theme-builder
description: >
  Install, configure, and extend the softland/theme-builder Laravel package: a React
  drag-and-drop visual theme editor + a server-side storefront block renderer. Covers
  the full host integration — wiring the Store interface, making Theme/ThemeRevision
  models extend the package, binding catalog/media providers, the Vite asset build,
  the Tailwind scan gotcha, routes, and config. Also: adding block types, creating
  and validating theme presets, and changing the DB schema. Trigger when the user
  asks to install/configure the package, integrate a storefront onto it, add/change
  a block, create/edit/validate a theme or preset, wire catalog or tenant data,
  change the theme database shape, or debug storefront/builder rendering. Keywords:
  theme builder, storefront block, preset, StorefrontDataProvider, MediaProvider,
  ThemeSchema, Store interface, blocks_view_prefix, theme-builder:make-theme,
  theme-builder:validate.
license: MIT
metadata:
  author: SoftLand
---

# theme-builder (softland/theme-builder)

A Laravel package: a **React `@dnd-kit` drag-and-drop theme editor** + a **storefront
block renderer**. The editor produces a JSON block tree; the renderer turns it into
enriched Blade on the live storefront. **No Filament, no Spatie, no domain-model
coupling** — the host supplies catalog + tenant data through interfaces.

## Mental model

```
editor (React) ─POST {blocks,settings,header,footer,templates}─▶ BuilderController
storefront req ─▶ StorefrontController ─▶ BlockRenderer::resolve() ─enrich via StorefrontDataProvider─▶
                                                    ▼
                          <x-dynamic-component :component="…::storefront.blocks.{kebab}">
```

Each block type is defined in **two lockstep places**: `BlockRegistry::registerCoreBlocks()`
(PHP truth) and `resources/js/builder/config/blocks.tsx` (`BLOCK_SCHEMAS`). A prop not
in the PHP `defaults` is silently dropped by `sanitize()` on save.

## Commands

```bash
php artisan theme-builder:make-theme {slug} [--name=] [--overrides]   # scaffold a preset (+ override dirs)
php artisan theme-builder:validate [slug] [--theme=#id]                # lint a preset/theme against the contracts
php artisan theme-builder:seed-preset [slug]                           # seed a preset into themes (is_preset=1)
```

`validate` round-trips every block through the registry and checks settings/color-schemes/
header/footer/templates. Run it after touching a preset.

## Contracts the host implements

| Contract | Host responsibility |
|---|---|
| `Contracts\Store` | Tenant key / name / currency / locale. Implement on the tenant model; bind `theme-builder.store_resolver`. |
| `Contracts\StorefrontDataProvider` | products/categories/blog as **normalized arrays** + editor picker search. |
| `Contracts\MediaProvider` | Media picker list/store/url (optional). |
| `Contracts\ThemeSchema` | themes/revisions table names + tenant column + column map. Override the binding to adapt to an existing schema. |

Helpers: `tb_bi()` (bilingual), `tb_money()`, `tb_route()` (config `routes_map`).

---

## Integrating into a host app (full checklist)

The package ships all the engine code. A host owns: **presets, config, contract
implementations, and (optionally) its own storefront views.** Steps:

### 1. Composer + publish

```bash
composer require softland/theme-builder
php artisan vendor:publish --tag=theme-builder-config
php artisan vendor:publish --tag=theme-builder-assets      # CSS → resources/css/
php artisan vendor:publish --tag=theme-builder-migrations  # fresh installs only
php artisan vendor:publish --tag=theme-builder-lang        # storefront translations
php artisan migrate
php artisan theme-builder:seed-preset
```

### 2. Implement the `Store` interface on the tenant model

```php
use SoftLand\ThemeBuilder\Contracts\Store as ThemeBuilderStore;
class Store extends Model implements ThemeBuilderStore {
    public function getThemeBuilderTenantKey(): int|string|null { return $this->getKey(); }
    public function getThemeBuilderStoreName(): ?string        { return $this->name; }
    public function getThemeBuilderCurrencyCode(): string      { return $this->currency ?? 'SAR'; }
    public function getThemeBuilderLocale(): string            { return $this->default_locale ?? 'ar'; }
}
```

### 3. Make the host's `Theme`/`ThemeRevision` extend the package models

If the host already has a `themes` table (same shape), its models should extend the
package's and re-add host-only concerns (e.g. Spatie Translatable/Media) at the host
level, overriding `casts()` (drop the package's `name`/`description` array casts so
Spatie manages them), `revisions()`, and `nameTranslations()`:

```php
class Theme extends \SoftLand\ThemeBuilder\Models\Theme implements \Spatie\MediaLibrary\HasMedia {
    use \Spatie\Translatable\HasTranslations;
    use \Spatie\MediaLibrary\InteractsWithMedia;
    protected function casts(): array { /* parent casts minus name/description */ }
    public function nameTranslations(): array { return $this->getTranslations('name'); }
}
```

### 4. Bind catalog + media providers + config (in a service provider's `register()`)

```php
$this->app->bind(\SoftLand\ThemeBuilder\Contracts\StorefrontDataProvider::class, \App\ThemeBuilder\MyProvider::class);
$this->app->bind(\SoftLand\ThemeBuilder\Contracts\MediaProvider::class, \App\ThemeBuilder\MyMediaProvider::class);
config([
    'theme-builder.store_resolver'      => fn () => \App\Support\Tenancy::get(), // ?Store
    'theme-builder.blocks_view_prefix'  => 'storefront.blocks', // use the HOST's block views, or omit for the package's
    'theme-builder.exit_url'            => '/admin/themes',
    'theme-builder.acting_user_resolver'=> fn () => auth('admin')->id(),
    'theme-builder.routes.enabled'      => false,                // host mounts routes itself (next step)
    'theme-builder.routes.path'         => 'builder',
    'theme-builder.routes.name'         => 'builder',            // MUST match the name passed to ThemeBuilder::routes()
    'theme-builder.routes_map'          => [ /* 'product' => fn(array $p) => route('store.product', $p), ... */ ],
]);
```

> **Critical:** `routes.name` must equal the `name` option passed to `ThemeBuilder::routes()`
> (the editor's save/publish URLs are generated from it). Mismatch → `RouteNotFoundException`.

### 5. Register routes

```php
// Storefront (host-owned route → package controller):
Route::get('/', [\SoftLand\ThemeBuilder\Http\Controllers\StorefrontController::class, 'home']);

// Editor (wrap in YOUR auth/tenant middleware):
Route::middleware(['web'])->group(fn () =>
    \SoftLand\ThemeBuilder\Facades\ThemeBuilder::routes(['middleware' => [MyEditorAccess::class]])
);
```

`ThemeBuilder::routes(['prefix'=>'builder','name'=>'builder','middleware'=>[...],'domain'=>null])`.

### 6. Frontend build — two gotchas

In `vite.config.js`, point the editor entry at the package's vendor TSX, and **if the
package is a symlinked path repository** preserve symlinks so `react`/`@dnd-kit` resolve
from the host's `node_modules`:

```js
laravel({ input: [/*…*/, 'vendor/softland/theme-builder/resources/js/builder/main.tsx'] }),
// …
resolve: { preserveSymlinks: true },  // only needed for symlinked path repos
```

Then in `resources/css/builder.css` (the CSS the editor loads), **scan the package's JS**
so its Tailwind classes compile — the source lives under gitignored `vendor/`, which
Tailwind v4 auto-detection skips. Without this the drop indicator (and other builder-only
classes) are invisible:

```css
@source '../../vendor/softland/theme-builder/resources/js/**/*.{ts,tsx}';
```

(If the host renders the package's bundled block *views* instead of its own, also add
`@source '../vendor/softland/theme-builder/resources/views/**/*.blade.php';` to its
`storefront.css`.) Then `npm install && npm run build`.

---

## Adding a block type (checklist)

1. **PHP** — `BlockRegistry::registerCoreBlocks()`: `BlockDefinition::make('MyBlock', …)->blade('theme-builder::storefront.blocks.my-block')->defaults([...sectionDefaults(), …])`.
2. **TS** — `config/blocks.tsx` `BLOCK_SCHEMAS`: same `defaults` + fields + React preview import.
3. **React preview** — `blocks/MyBlock.tsx`.
4. **Blade** — `resources/views/components/storefront/blocks/my-block.blade.php` (`@props(['props'=>[]])`, no outer padding). Use `tb_bi()`/`tb_route()`, never `route('storefront.*')`.
5. **Data block?** add a case to `BlockRenderer::dataFor()` + document the shape on `StorefrontDataProvider`.
6. Rebuild + `theme-builder:validate`.

## Creating / editing a theme

`make-theme my-theme --overrides` → `database/presets/my-theme.json` (+ override dirs).
`validate my-theme` → `seed-preset my-theme`. Per-theme overrides are convention-based
(file existence = enabled): `resources/views/components/themes/{slug}/blocks/{kebab}.blade.php`
+ `public/themes/{slug}/theme.css`.

## Schema flexibility (tables/columns vary)

Don't assume `themes`/`store_id`. Publish config and edit `theme-builder.db`
(`themes_table`, `revisions_table`, `tenant_column`, `columns`), or bind a custom
`ThemeSchema`, or extend the non-`final` models. Read tenant data **only** via the
`Store` interface.

## Common pitfalls

- **404 on `/builder/themes/{id}/edit`**: the global scope intentionally no-ops when no tenant (route-model binding runs before tenant middleware); ownership is enforced in the controller, not the scope. Keep that behavior — don't make the scope filter on a null tenant.
- **`Undefined variable: $categories`** in host chrome: the package composer binds chrome data to both `theme-builder::*` and `components.storefront.{header,footer,bottom-nav}`. Host chrome must use one of those names, or add its own composer.
- **`Route [theme-builder.themes.save] not defined`**: `theme-builder.routes.name` ≠ the `name` passed to `ThemeBuilder::routes()`. Make them match.
- **Drop indicator / builder styles vanish after moving to vendor/**: the `@source` line in `builder.css` is missing (see step 6).
- **New prop vanishes on save**: not in the registry `defaults` → `sanitize()` drops it. Add it PHP-side first.
- **`react` not resolvable in build**: symlinked path repo without `resolve.preserveSymlinks: true` (see step 6).
