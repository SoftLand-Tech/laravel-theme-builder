<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Support;

use SoftLand\ThemeBuilder\Contracts\ThemeSchema;

/**
 * Default schema: canonical table/column names, optionally overridden via the
 * publishable `theme-builder.db` config. Hosts needing finer control should
 * bind a custom ThemeSchema implementation in their service provider.
 */
final class DefaultThemeSchema implements ThemeSchema
{
    public function themesTable(): string
    {
        return (string) config('theme-builder.db.themes_table', 'themes');
    }

    public function revisionsTable(): string
    {
        return (string) config('theme-builder.db.revisions_table', 'theme_revisions');
    }

    public function tenantColumn(): string
    {
        return (string) config('theme-builder.db.tenant_column', 'store_id');
    }

    public function column(string $logical): string
    {
        /** @var array<string, string> $map */
        $map = (array) config('theme-builder.db.columns', []);

        return $map[$logical] ?? $logical;
    }
}
