<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use SoftLand\ThemeBuilder\Models\Theme;

/**
 * Preset adopt / theme activate. Hosts with their own theme gallery may skip
 * these endpoints and drive `Theme::forkPresetForStore()` / `activateForCurrentStore()`
 * directly; these are provided for a turnkey install.
 */
class ThemePickerController extends Controller
{
    /**
     * Fork a preset into a new tenant-owned theme and make it the live theme.
     */
    public function adopt(Request $request, Theme $preset): RedirectResponse
    {
        abort_unless($preset->is_preset, 403, 'Only preset themes can be adopted.');

        $theme = Theme::forkPresetForStore($preset, $this->tenantId());
        $theme->activateForCurrentStore();

        $name = (string) config('theme-builder.routes.name', 'theme-builder');

        return redirect()->route("{$name}.themes.edit", $theme);
    }

    /**
     * Set a tenant-owned theme as the active published theme.
     */
    public function activate(Request $request, Theme $theme): RedirectResponse
    {
        $this->authorizeTheme($theme);

        $theme->activateForCurrentStore();

        return redirect(config('theme-builder.exit_url', '/'));
    }

    private function tenantId(): int|string|null
    {
        return app()->bound('theme-builder.tenant_id') ? app('theme-builder.tenant_id') : null;
    }

    private function authorizeTheme(Theme $theme): void
    {
        $tenantId = $this->tenantId();

        if ($tenantId === null) {
            return;
        }

        abort_unless($theme->store_id === $tenantId, 403);
    }
}
