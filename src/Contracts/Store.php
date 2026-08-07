<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Contracts;

/**
 * The host's tenant/store model implements this so the package can read tenant
 * data through methods instead of assuming column names. A single
 * `theme-builder.store_resolver` config returns the current Store instance
 * (or null), and the package derives the tenant key, store name, currency, and
 * locale from it.
 *
 * This replaces the previous scattered `tenant_id_resolver` / `store_name_resolver`
 * / `currency` closures — the host owns its own shape and exposes it here.
 */
interface Store
{
    /** The owning tenant key stored on `themes.{tenant_column}` (e.g. the store id). */
    public function getThemeBuilderTenantKey(): int|string|null;

    /** Display name shown in the editor shell, or null. */
    public function getThemeBuilderStoreName(): ?string;

    /** ISO 4217 currency code for price rendering (e.g. 'SAR'). */
    public function getThemeBuilderCurrencyCode(): string;

    /** Active storefront locale, e.g. 'ar' or 'en'. */
    public function getThemeBuilderLocale(): string;
}
