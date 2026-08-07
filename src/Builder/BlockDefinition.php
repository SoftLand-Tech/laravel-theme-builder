<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Builder;

/**
 * Represents a single block type in the registry.
 *
 * @param  array<string, mixed>  $defaults
 */
final class BlockDefinition
{
    public function __construct(
        public string $type,
        public string $label,
        public BlockCategory $category,
        public ?string $bladeComponent = null,
        public ?string $reactComponent = null,
        public string $icon = 'heroicon-o-cube',
        public array $defaults = [],
    ) {}

    public static function make(string $type, string $label, BlockCategory $category): self
    {
        return new self($type, $label, $category);
    }

    public function blade(string $component): self
    {
        $this->bladeComponent = $component;

        return $this;
    }

    public function react(string $name): self
    {
        $this->reactComponent = $name;

        return $this;
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $defaults
     */
    public function defaults(array $defaults): self
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Strip disallowed keys and sanitize string values for safe storage.
     *
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    public function sanitize(array $props): array
    {
        $allowed = array_keys($this->defaults);
        $clean = [];

        foreach ($allowed as $key) {
            $clean[$key] = array_key_exists($key, $props) ? $this->sanitizeValue($props[$key]) : $this->defaults[$key];
        }

        return $clean;
    }

    private function sanitizeValue(mixed $value): mixed
    {
        if (is_string($value)) {
            return $this->sanitizeString($value);
        }

        if (is_array($value)) {
            return array_map($this->sanitizeValue(...), $value);
        }

        return $value;
    }

    /**
     * Allow only safe HTML in content fields. Script tags, inline event
     * handlers, and javascript: URLs are always stripped — CustomHtml is
     * safe markup (iframes, styled HTML), not arbitrary script execution.
     */
    private function sanitizeString(string $value): string
    {
        // Remove script tags and event handlers entirely.
        $value = preg_replace('#<script[^>]*>.*?</script>#is', '', $value) ?? $value;
        $value = preg_replace('#\son\w+\s*=\s*["\'][^"\']*["\']#i', '', $value) ?? $value;
        $value = preg_replace('#javascript:#i', '', $value) ?? $value;

        return trim($value);
    }
}
