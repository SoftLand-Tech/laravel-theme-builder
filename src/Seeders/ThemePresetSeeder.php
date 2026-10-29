<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Seeders;

use Illuminate\Filesystem\Filesystem;
use SoftLand\ThemeBuilder\Enums\ThemeStatus;
use SoftLand\ThemeBuilder\Models\Scopes\PresetOrTenantScope;
use SoftLand\ThemeBuilder\Models\Theme;
use SoftLand\ThemeBuilder\Support\ThemePackageManager;

/**
 * Seeds a theme preset JSON into the `themes` table as a global preset row.
 * Presets resolve host-first (database/presets), then theme packages
 * (vendor/…/theme-builder.json), then the core's bundled presets.
 */
class ThemePresetSeeder
{
    public function __construct(
        private Filesystem $files,
        private ThemePackageManager $themePackages,
    ) {}

    public function seed(string $slug = 'default'): Theme
    {
        $path = $this->presetPath($slug);
        $data = json_decode((string) $this->files->get($path), true, 512, JSON_THROW_ON_ERROR);

        // Theme packages may declare extra settings slices that deep-merge
        // into every preset they ship (e.g. course_card defaults for LMS).
        $settings = $this->themePackages->mergeSettings((array) ($data['settings'] ?? []), $slug);

        $tenantColumn = Theme::ownerColumn();

        return Theme::withoutGlobalScope(new PresetOrTenantScope)->updateOrCreate(
            [
                'is_preset' => true,
                'preset_slug' => $slug,
                $tenantColumn => null,
            ],
            [
                'name' => $data['name'] ?? ['ar' => $slug, 'en' => ucfirst($slug)],
                'description' => $data['description'] ?? null,
                'preview_image' => $data['preview_image'] ?? null,
                'settings' => $settings,
                'header' => $data['header'] ?? null,
                'footer' => $data['footer'] ?? null,
                'blocks' => $data['blocks'] ?? [],
                'templates' => $data['templates'] ?? null,
                'is_preset' => true,
                'preset_slug' => $slug,
                $tenantColumn => null,
                'status' => ThemeStatus::Published->value,
                'published_at' => now(),
            ],
        );
    }

    /**
     * Resolve the preset JSON: host-published file → theme package → the
     * package's bundled copy (via ThemePackageManager::presets()).
     */
    public function presetPath(string $slug): string
    {
        $host = database_path("presets/{$slug}.json");

        if ($this->files->exists($host)) {
            return $host;
        }

        $managed = $this->themePackages->presets()[$slug] ?? null;

        if ($managed !== null) {
            return $managed;
        }

        return dirname(__DIR__, 2)."/database/presets/{$slug}.json";
    }
}
