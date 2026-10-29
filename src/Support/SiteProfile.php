<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Support;

/**
 * The result of scanning a host Laravel app: its probable site type, the
 * concrete evidence found on disk, and which storefront features already
 * exist (so the installer never asks about a cart the host obviously has).
 *
 * Produced by {@see SiteDetector} — plain file greps, no AI involved.
 */
final class SiteProfile
{
    public const string TYPE_ECOMMERCE = 'ecommerce';

    public const string TYPE_LMS = 'lms';

    public const string TYPE_CMS = 'cms';

    public const string TYPE_PERSONAL = 'personal';

    public const string TYPE_EMPTY = 'empty';

    public const string TYPE_UNKNOWN = 'unknown';

    public const array TYPES = [
        self::TYPE_ECOMMERCE,
        self::TYPE_LMS,
        self::TYPE_CMS,
        self::TYPE_PERSONAL,
    ];

    /**
     * @param  string  $type  One of the TYPE_* constants.
     * @param  array<string, int>  $scores  Per-type signal scores (ecommerce/lms/cms/personal).
     * @param  array<int, string>  $evidence  Human-readable signals found on disk.
     * @param  array<string, bool>  $features  has_cart, has_checkout, has_products, has_courses, …
     */
    public function __construct(
        public readonly string $type,
        public readonly array $scores = [],
        public readonly array $evidence = [],
        public readonly array $features = [],
    ) {}

    public function hasFeature(string $feature): bool
    {
        return ! empty($this->features[$feature]);
    }

    /**
     * Confidence in the winning type: 0–1, based on how dominant its score is.
     */
    public function confidence(): float
    {
        $total = array_sum($this->scores);

        if ($total <= 0) {
            return 0.0;
        }

        return round($this->scores[$this->type] / $total, 2);
    }

    /**
     * The theme-package slugs that fit this profile, best first.
     *
     * @return array<int, string>
     */
    public function suggestedThemeSlugs(): array
    {
        return match ($this->type) {
            self::TYPE_ECOMMERCE => ['ecommerce'],
            self::TYPE_LMS => ['lms'],
            self::TYPE_CMS => ['cms'],
            self::TYPE_PERSONAL => ['personal'],
            self::TYPE_EMPTY => ['personal'],
            default => ['personal'],
        };
    }

    /**
     * The page templates a site type gets when no theme package is installed
     * and the host hasn't configured `theme-builder.pages` explicitly.
     *
     * @return array<string, array{label: string}>
     */
    public static function pagesForType(string $type): array
    {
        return match ($type) {
            self::TYPE_LMS => [
                'course' => ['label' => 'Course'],
                'lesson' => ['label' => 'Lesson'],
            ],
            self::TYPE_CMS => [
                'post' => ['label' => 'Post'],
                'page' => ['label' => 'Page'],
            ],
            self::TYPE_PERSONAL, self::TYPE_EMPTY => [
                'about' => ['label' => 'About'],
                'services' => ['label' => 'Services'],
                'contact' => ['label' => 'Contact'],
            ],
            // Ecommerce keeps the historical trio; unknown types fall back to
            // it too so older installs behave exactly as before.
            default => [
                'product' => ['label' => 'Product'],
                'cart' => ['label' => 'Cart'],
                'collection' => ['label' => 'Collection'],
            ],
        };
    }

    /**
     * The bundled starter preset for a site type. The core always ships these,
     * so `theme-builder:install` produces a working theme even when no theme
     * package is installed and the network is unavailable.
     */
    public static function starterPreset(string $type): string
    {
        return match ($type) {
            self::TYPE_LMS => 'lms',
            self::TYPE_CMS => 'cms',
            self::TYPE_PERSONAL, self::TYPE_EMPTY => 'personal',
            default => 'default',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'confidence' => $this->confidence(),
            'scores' => $this->scores,
            'evidence' => $this->evidence,
            'features' => array_keys(array_filter($this->features)),
        ];
    }
}
