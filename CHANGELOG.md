# Changelog

All notable changes to `softlandtech/theme-builder` will be documented in this
file. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
