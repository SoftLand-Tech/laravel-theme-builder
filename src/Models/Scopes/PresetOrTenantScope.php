<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Override;
use SoftLand\ThemeBuilder\Contracts\ThemeSchema;

/**
 * Includes preset (global) rows that have no store_id, plus the current
 * tenant's rows. Tenant id is resolved through the package's configured
 * `theme-builder.tenant_id`.
 *
 * When no tenant is resolved (single-tenant installs, console, or — crucially —
 * route-model binding, which runs BEFORE the host's tenant-setting middleware),
 * the scope is a no-op. This mirrors the host app's original scope and keeps
 * implicit key binding working; per-route ownership is still enforced by the
 * controllers' authorizeTheme().
 */
class PresetOrTenantScope implements Scope
{
    #[Override]
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = $this->tenantId();

        if ($tenantId === null) {
            return;
        }

        $tenantColumn = app(ThemeSchema::class)->tenantColumn();

        $builder->where(function (Builder $q) use ($model, $tenantId, $tenantColumn): void {
            $q->where($model->qualifyColumn($tenantColumn), $tenantId)
                ->orWhere($model->qualifyColumn('is_preset'), true);
        });
    }

    private function tenantId(): int|string|null
    {
        return app()->bound('theme-builder.tenant_id')
            ? app('theme-builder.tenant_id')
            : null;
    }
}
