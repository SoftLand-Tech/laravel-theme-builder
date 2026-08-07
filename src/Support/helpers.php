<?php

declare(strict_types=1);

use Illuminate\Support\Str;

if (! function_exists('tb_bi')) {
    /**
     * Resolve a bilingual value to the active locale, with Arabic fallback.
     * Mirrors the host app's `bi()` helper. Value may be a string (returned
     * as-is) or an array with 'ar' / 'en' keys.
     *
     * @param  array{ar?: string, en?: string}|string|null  $value
     */
    function tb_bi(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return '';
        }

        $locale = app()->getLocale();

        return $value[$locale] ?? ($value['ar'] ?? ($value['en'] ?? ''));
    }
}

if (! function_exists('tb_money')) {
    /**
     * Format integer cents into a localized currency string. Generic default;
     * hosts typically rebind this (or bind a richer StorefrontDataProvider/
     * card override) for full i18n.
     */
    function tb_money(int $cents, ?string $currency = null): string
    {
        $currency ??= (string) config('theme-builder.currency', 'USD');

        return number_format($cents / 100, 2, '.', '').' '.$currency;
    }
}

if (! function_exists('tb_route')) {
    /**
     * Resolve a storefront-style route name to a URL via the configured route
     * map, so generic package blocks/chrome never assume host routes exist.
     *
     * Names may be passed as 'product' or 'storefront.product' (the
     * 'storefront.' prefix is stripped). Each config entry under
     * `theme-builder.routes_map` is a static URL string or a closure accepting
     * the params array. Missing entries resolve to '#'.
     *
     * @param  array<string, mixed>  $params
     */
    function tb_route(string $name, array $params = []): string
    {
        $key = Str::replaceFirst('storefront.', '', $name);
        $entry = config("theme-builder.routes_map.{$key}");

        if ($entry === null) {
            return '#';
        }

        if (is_string($entry)) {
            return $entry;
        }

        if (is_callable($entry)) {
            return (string) $entry($params);
        }

        return '#';
    }
}
