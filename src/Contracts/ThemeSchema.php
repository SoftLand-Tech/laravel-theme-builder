<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Contracts;

use SoftLand\ThemeBuilder\Support\DefaultThemeSchema;

/**
 * Describes the package's database shape so a host is never forced onto the
 * canonical table/column names. The package binds {@see DefaultThemeSchema}
 * by default (which itself reads the publishable `theme-builder.db` config);
 * a host overrides the binding — or extends the default — in its own service
 * provider to remap anything.
 */
interface ThemeSchema
{
    /** The themes table name. */
    public function themesTable(): string;

    /** The theme revisions table name. */
    public function revisionsTable(): string;

    /** The themes-table column holding the owning tenant key (default 'store_id'). */
    public function tenantColumn(): string;

    /**
     * Map a package logical attribute to its physical column. Defaults to the
     * identity map; override to remap content columns. Used by the Theme model
     * for query qualification.
     */
    public function column(string $logical): string;
}
