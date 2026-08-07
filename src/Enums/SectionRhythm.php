<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Enums;

/**
 * Theme-level section rhythm. The multiplier scales the whole
 * `--space-section-*` ramp, so per-section small/medium/large keep their
 * relative distinctions instead of being flattened onto one step.
 */
enum SectionRhythm: string
{
    case Compact = 'compact';
    case Medium = 'medium';
    case Spacious = 'spacious';

    public function label(): string
    {
        return match ($this) {
            self::Compact => 'Compact',
            self::Medium => 'Medium',
            self::Spacious => 'Spacious',
        };
    }

    /**
     * The `--space-section-scale` value, as the exact string emitted into CSS.
     */
    public function multiplier(): string
    {
        return match ($this) {
            self::Compact => '0.75',
            self::Medium => '1',
            self::Spacious => '1.4',
        };
    }

    /**
     * Resolve a stored settings value, falling back to Medium for anything
     * unrecognised. Merchant settings are free-form JSON, so this never throws.
     */
    public static function fromSetting(mixed $value): self
    {
        return (is_string($value) ? self::tryFrom($value) : null) ?? self::Medium;
    }
}
