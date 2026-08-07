<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Seeders;

use Illuminate\Filesystem\Filesystem;
use SoftLand\ThemeBuilder\Enums\ThemeStatus;
use SoftLand\ThemeBuilder\Models\Scopes\PresetOrTenantScope;
use SoftLand\ThemeBuilder\Models\Theme;

/**
 * Seeds a theme preset JSON (from the host's database/presets or the package's
 * bundled presets) into the `themes` table as a global preset row.
 */
class ThemePresetSeeder
{
    public function __construct(
        private Filesystem $files,
    ) {}

    public function seed(string $slug = 'default'): Theme
    {
        $path = $this->presetPath($slug);
        $data = json_decode((string) $this->files->get($path), true, 512, JSON_THROW_ON_ERROR);

        return Theme::withoutGlobalScope(new PresetOrTenantScope)->updateOrCreate(
            [
                'is_preset' => true,
                'preset_slug' => $slug,
                'store_id' => null,
            ],
            [
                'name' => $data['name'] ?? ['ar' => $slug, 'en' => ucfirst($slug)],
                'description' => $data['description'] ?? null,
                'preview_image' => $data['preview_image'] ?? null,
                'settings' => $data['settings'] ?? [],
                'header' => $data['header'] ?? null,
                'footer' => $data['footer'] ?? null,
                'blocks' => $data['blocks'] ?? [],
                'templates' => $data['templates'] ?? null,
                'is_preset' => true,
                'preset_slug' => $slug,
                'store_id' => null,
                'status' => ThemeStatus::Published->value,
                'published_at' => now(),
            ],
        );
    }

    /**
     * Resolve the preset JSON: prefer a host-published file, fall back to the
     * package's bundled copy.
     */
    public function presetPath(string $slug): string
    {
        $host = database_path("presets/{$slug}.json");
        $package = dirname(__DIR__, 2)."/database/presets/{$slug}.json";

        return $this->files->exists($host) ? $host : $package;
    }
}
