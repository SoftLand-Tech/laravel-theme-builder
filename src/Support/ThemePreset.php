<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Support;

use Illuminate\Support\Collection;
use SoftLand\ThemeBuilder\Models\Theme;

/**
 * An immutable view-model for presenting a theme preset to the picker UI.
 */
final readonly class ThemePreset
{
    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, mixed>  $settings
     */
    public function __construct(
        public int $id,
        public string|array $name,
        public string $slug,
        public ?string $description,
        public ?string $previewImage,
        public array $blocks,
        public array $settings,
    ) {}

    public static function fromModel(Theme $theme): self
    {
        $name = $theme->nameTranslations();

        return new self(
            id: $theme->id,
            name: $name,
            slug: $theme->preset_slug ?? ($name['en'] ?? 'theme'),
            description: $theme->description,
            previewImage: $theme->preview_image,
            blocks: $theme->blocks ?? [],
            settings: $theme->settings ?? [],
        );
    }

    /**
     * @param  Collection<int, Theme>  $themes
     * @return Collection<int, self>
     */
    public static function collection(Collection $themes): Collection
    {
        return $themes->map(fn (Theme $t) => self::fromModel($t));
    }
}
