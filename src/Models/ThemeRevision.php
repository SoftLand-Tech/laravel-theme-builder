<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;
use SoftLand\ThemeBuilder\Contracts\ThemeSchema;

/**
 * @property array $blocks
 * @property ?array $settings
 * @property ?array $header
 * @property ?array $footer
 * @property ?array $templates
 * @property Theme $theme
 */
#[Fillable(['theme_id', 'store_user_id', 'revision_number', 'version', 'change_summary', 'blocks', 'settings', 'header', 'footer', 'templates', 'published_at'])]
class ThemeRevision extends Model
{
    /**
     * The physical table name, resolved from the bound ThemeSchema so a host is
     * never forced onto the canonical `theme_revisions` name.
     */
    #[Override]
    public function getTable(): string
    {
        if (app()->bound(ThemeSchema::class)) {
            return app(ThemeSchema::class)->revisionsTable();
        }

        return parent::getTable();
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'settings' => 'array',
            'header' => 'array',
            'footer' => 'array',
            'templates' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Theme, $this>
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public static function latestForTheme(Theme $theme): ?self
    {
        return $theme->revisions()->latest('revision_number')->first();
    }

    /**
     * Compute the next semantic version for a theme's revision history.
     *
     * Minor bumps increment the patch segment (`1.2` → `1.3`); major bumps
     * reset it and raise the major segment (`1.x` → `2.0`). The first
     * revision on a theme starts at `1.0`. Malformed prior versions fall
     * back to a safe increment so a corrupt label never blocks publishing.
     */
    public static function nextVersion(Theme $theme, bool $major = false): string
    {
        $latest = $theme->revisions()->latest('revision_number')->first()?->version;

        if (! $latest || ! preg_match('/^(\d+)(?:\.(\d+))?$/', $latest, $m)) {
            return '1.0';
        }

        $majorPart = (int) $m[1];
        $minorPart = isset($m[2]) ? (int) $m[2] : 0;

        if ($major) {
            return ($majorPart + 1).'.0';
        }

        return $majorPart.'.'.($minorPart + 1);
    }
}
