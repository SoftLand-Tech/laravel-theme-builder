# Changelog

All notable changes to `softlandtech/theme-builder` will be documented in this
file. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- **Bundled starters per site type**: `personal`, `lms`, and `cms` presets
  ship with the core next to `default`, so `theme-builder:install` produces a
  working theme for any Laravel project — even offline with no theme packages.
- **Type-aware page fallback**: without configured or packaged pages, the
  builder offers course/lesson (LMS), post/page (CMS),
  about/services/contact (personal/empty), and product/cart/collection
  (ecommerce/unknown) instead of the ecommerce trio for everyone. The
  installer records these in `theme-builder.pages` when no package pages exist.
- The installer seeds **every preset a chosen theme package ships** (not just
  a same-named file) and falls back to the site type's bundled starter when a
  chosen theme can't be installed — installs never end theme-less.
- The installer replaces the stock Laravel **welcome route** with the
  storefront home route (custom `/` routes are still left untouched).
- Type-native installer questions: an LMS is asked about courses/lessons, a
  CMS about blog/pages, a personal site about portfolio/services; the AI
  hand-off checklist now gives per-type page/routes_map guidance.
- **General-purpose theme builder**: the package is no longer ecommerce-only.
  `theme-builder:install` detects the site type (ecommerce / lms / cms /
  personal / new empty project) via grep-based scanning (`SiteDetector` — no
  AI), asks which storefront features already exist (cart, checkout, search,
  account, blog, contact), installs the chosen theme packages, and wires
  routes/web.php, vite.config, the Tailwind `@source` line, npm deps, and the
  config — then optionally prints a complete AI hand-off prompt (saved to
  `theme-builder-ai-prompt.md`).
- **Theme packages**: `softlandtech/theme-builder-{personal,ecommerce,lms,cms}`
  — small composer packages shipping a `theme-builder.json` manifest (site
  types, pages, routes_map, settings), a starter preset, and packaged CSS.
  Each is published as its own public repo
  (`github.com/SoftLand-Tech/theme-builder-{slug}`) and installs by composer,
  by plain `git clone` into the host's themes directory (`themes_path`
  config, default `theme-builder-themes/`), or straight from the installer —
  `theme-builder:install` now lists every catalog theme and git-clones the
  ones you pick that aren't on disk (`theme_repos` config points entries at
  forks; `--skip-git` prints the clone commands instead; `offer_remote_themes`
  gates the offers). Discovered from vendor, from folders beside the core
  package, and from the themes directory (`ThemePackageManager`).
- **Dynamic page templates**: builder pages are no longer hardcoded to
  product/cart/collection. Keys come from `theme-builder.pages` config →
  theme packages → legacy trio; the editor Pages tab and payload
  validation/normalization follow, and arbitrary page keys are sanitized on
  save (previously only the trio were validated).
- `StorefrontController::page()` renders a full page from a template
  (landing/services/…); `BlockRenderer::renderTemplate()` and
  `ThemeBuilder::renderTemplate($page, $slot)` embed merchant sections around
  host-owned content.
- `ThemeBuilder::pages()` / `ThemeBuilder::packages()` facade methods;
  `make-theme --from={slug}` can base a new preset on any available preset
  (including theme packages); generic `routes_map` entries (`page`, `post`,
  `service`, `course`, `lesson`).
- Tests: SiteDetector, ThemePackageManager, dynamic page templates, and the
  install command (non-interactive, with host-file snapshot/restore).

### Fixed

- `theme-builder:install --type=bogus` crashed with a `TypeError` instead of
  exiting non-zero with an error message.
- The preset seeder and all four theme-ownership checks (`Builder`,
  `ThemePicker`, `Media`, `Revision` controllers) hardcoded the `store_id`
  column, ignoring `theme-builder.db.tenant_column`; the owner column is now
  resolved through `ThemeSchema` everywhere and is always mass-assignable.
- The Tailwind `@source` step skipped whenever *any* `@source` existed (the
  published CSS always has some, so the vendor line was never added), wrote a
  machine-specific absolute path, and broke Tailwind v3 hosts. It now checks
  for the theme-builder reference specifically, writes a portable relative
  path, and tells v3 hosts to use `tailwind.config` content instead. The
  README's `@source` path is fixed too (`../../vendor/…`).
- Composer path repositories were written with string keys (composer expects
  a list) and absolute URLs; they are now a deduped list with portable
  relative paths.
- The installer no longer throws when no database is configured — it
  publishes the migration and defers `migrate` to the remaining-steps list.
- Editor shell Vite entry pointed at `vendor/softland/...` instead of
  `vendor/softlandtech/...` (the editor's script 404'd on composer installs).

## [0.1.0] - 2026-08-17

First release.

### Added

- Visual theme builder (React + `@dnd-kit`) and storefront block renderer for
  Laravel, with 32 block types.
- Tenant-agnostic contracts: `Store`, `StorefrontDataProvider`, `MediaProvider`,
  `ThemeSchema` - no domain-model coupling.
- Configurable schema (tables/columns), routes, view namespace, and block view
  prefix; publishable config, migrations, assets, presets, lang, and skills.
- One generic ecommerce preset (`database/presets/default.json`), seedable via
  `theme-builder:seed-preset`.
- Draft/publish workflow with revision history and semantic versioning
  (`ThemeRevision::nextVersion`).
- Commands: `theme-builder:seed-preset`, `theme-builder:make-theme`,
  `theme-builder:validate`.
- Laravel Boost guideline + agent skill for AI-assisted host integration.
- Test suite: Pest + Testbench covering registry, model, HTTP endpoints,
  storefront rendering, and console commands.

### Fixed

- `BlockRenderer::render()` now resolves block views through the `components.`
  path convention (component names like `theme-builder::storefront.blocks.hero`
  map to `theme-builder::components.storefront.blocks.hero`).
- Builder payload validation accepts empty `props` objects (a block whose every
  field is at its default) for both `blocks` and template slots.
- Stale block-count assertion in tests (28 -> 32).

[Unreleased]: https://github.com/SoftLand-Tech/laravel-theme-builder/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/SoftLand-Tech/laravel-theme-builder/releases/tag/v0.1.0
