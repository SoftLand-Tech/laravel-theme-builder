<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Support;

use SoftLand\ThemeBuilder\Contracts\Store;

/**
 * Default store when no `store_resolver` is configured (single-tenant installs,
 * or before the host's tenant middleware has run). Yields a null tenant key so
 * scopes no-op and authorization skips per-tenant isolation.
 */
final class NullStore implements Store
{
    public function getThemeBuilderTenantKey(): int|string|null
    {
        return null;
    }

    public function getThemeBuilderStoreName(): ?string
    {
        return null;
    }

    public function getThemeBuilderCurrencyCode(): string
    {
        return (string) config('theme-builder.currency', 'USD');
    }

    public function getThemeBuilderLocale(): string
    {
        return app()->getLocale();
    }
}
