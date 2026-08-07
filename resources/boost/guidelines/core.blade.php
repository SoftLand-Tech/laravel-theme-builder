## softland/theme-builder

A visual theme editor (React drag-and-drop) + storefront block renderer. The host
provides catalog and tenant data through PHP interfaces — the package never assumes
the host's models, tables, or columns.

- **Activate the `theme-builder` skill** for anything block/theme/preset related: installing & wiring the package into a host, adding block types, creating/editing/validating themes, schema changes, debugging storefront/builder rendering.
- **Never hardcode catalog data or tenant columns.** Catalog/blog data flows through `SoftLand\ThemeBuilder\Contracts\StorefrontDataProvider` (normalized arrays); tenant data through `Contracts\Store` (id/name/currency/locale); media through `Contracts\MediaProvider`; the DB shape through `Contracts\ThemeSchema`. The host implements these; package code must not touch host models or assume `themes`/`store_id` names.
- **Two sync source-of-truth files per block**: `BlockRegistry::registerCoreBlocks()` (PHP) and `resources/js/builder/config/blocks.tsx`. A prop not in the PHP `defaults` is silently dropped by `sanitize()` on save — add it PHP-side first.
- **Host integration checklist** (full detail in the skill): implement `Store` on the tenant model; make the host `Theme`/`ThemeRevision` extend the package models; bind `StorefrontDataProvider`/`MediaProvider`; set `theme-builder.*` config (notably `store_resolver`, `blocks_view_prefix`, `routes.name`); mount `ThemeBuilder::routes()`; add the editor TSX to `vite.config.js` (with `resolve.preserveSymlinks` for path repos) and the `@source` line to `builder.css`.
- **Commands**: `theme-builder:make-theme {slug}`, `theme-builder:validate [slug]` (run after preset edits), `theme-builder:seed-preset {slug}`.
- Block Blade uses `tb_bi()`/`tb_route()` (config `theme-builder.routes_map`), never `route('storefront.*')`. Frontend changes need `npm run build`.
