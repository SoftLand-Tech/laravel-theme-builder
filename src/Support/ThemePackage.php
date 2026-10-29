<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * A discovered theme package (e.g. softlandtech/theme-builder-ecommerce).
 *
 * A theme package is a small composer package that ships:
 *   - `theme-builder.json`  — the manifest (site types, pages, routes, settings)
 *   - `presets/*.json`      — one or more full theme presets
 *   - optionally block-view overrides, a theme.css, and preview images
 *
 * It keeps the core package domain-agnostic: "what an LMS theme looks like"
 * lives in theme-builder-lms, not in the engine.
 */
final class ThemePackage
{
    /**
     * @param  string  $path  Absolute path to the package root.
     * @param  array<string, mixed>  $manifest  Parsed theme-builder.json.
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $path,
        public readonly array $manifest,
    ) {}

    /**
     * Load a package from its root directory (must contain theme-builder.json).
     */
    public static function load(string $path, ?Filesystem $files = null): ?self
    {
        $files ??= new Filesystem;
        $manifestPath = rtrim($path, '/').'/theme-builder.json';

        if (! $files->exists($manifestPath)) {
            return null;
        }

        $manifest = json_decode((string) $files->get($manifestPath), true);

        if (! is_array($manifest)) {
            return null;
        }

        $slug = $manifest['slug'] ?? null;

        if (! is_string($slug) || $slug === '' || ! preg_match('/^[a-z0-9][a-z0-9-]*$/', $slug)) {
            $slug = basename(rtrim($path, '/'));
        }

        return new self(
            slug: Str::slug($slug),
            path: rtrim($path, '/'),
            manifest: $manifest,
        );
    }

    /**
     * @return array{en?: string, ar?: string}|string
     */
    public function name(): array|string
    {
        $name = $this->manifest['name'] ?? null;

        if (is_array($name) || is_string($name)) {
            return $name;
        }

        return ucfirst($this->slug);
    }

    /**
     * @return array{en?: string, ar?: string}|null
     */
    public function description(): ?array
    {
        $description = $this->manifest['description'] ?? null;

        return is_array($description) ? $description : null;
    }

    /**
     * The site types this package themes: any of ecommerce|lms|cms|personal
     * (matches {@see SiteProfile}::TYPES).
     *
     * @return array<int, string>
     */
    public function siteTypes(): array
    {
        $types = $this->manifest['site_types'] ?? [$this->slug];

        if (! is_array($types)) {
            $types = [$types];
        }

        $valid = collect(SiteProfile::TYPES);

        return collect($types)
            ->map(fn ($t) => is_string($t) ? strtolower(trim($t)) : '')
            ->filter(fn ($t) => $t !== '' && $valid->contains($t))
            ->values()
            ->all();
    }

    /**
     * Page templates this package contributes to the builder's Pages tab,
     * e.g. {"product": {"label": "Product"}, "checkout": {"label": "Checkout"}}.
     *
     * @return array<string, array{label?: mixed}>
     */
    public function pages(): array
    {
        $pages = $this->manifest['pages'] ?? [];

        if (! is_array($pages)) {
            return [];
        }

        $normalized = [];

        foreach ($pages as $key => $meta) {
            if (! is_string($key) || $key === '') {
                continue;
            }
            $normalized[$key] = is_array($meta) ? $meta : ['label' => $meta];
        }

        return $normalized;
    }

    /**
     * Storefront route entries this package suggests (merged into the host's
     * `theme-builder.routes_map` by the installer / config).
     *
     * @return array<string, string>
     */
    public function routesMap(): array
    {
        $map = $this->manifest['routes_map'] ?? [];

        return is_array($map)
            ? collect($map)->filter(fn ($v, $k) => is_string($k) && is_string($v))->all()
            : [];
    }

    /**
     * Optional settings slice deep-merged into every preset seeded from this
     * package (lets an LMS theme add e.g. `course_card` defaults).
     *
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        $settings = $this->manifest['settings'] ?? [];

        return is_array($settings) ? $settings : [];
    }

    /**
     * @return array<int, string>
     */
    public function suggestedBlocks(): array
    {
        $blocks = $this->manifest['suggested_blocks'] ?? [];

        return is_array($blocks)
            ? collect($blocks)->filter(fn ($b) => is_string($b))->values()->all()
            : [];
    }

    public function presetsPath(): ?string
    {
        foreach (['presets', 'database/presets', 'database'] as $candidate) {
            $path = "{$this->path}/{$candidate}";

            if (is_dir($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * All preset files shipped by this package: filename slug => absolute path.
     *
     * @return array<string, string>
     */
    public function presets(): array
    {
        $dir = $this->presetsPath();

        if ($dir === null) {
            return [];
        }

        $presets = [];

        foreach (glob("{$dir}/*.json") ?: [] as $file) {
            $presets[basename($file, '.json')] = $file;
        }

        return $presets;
    }

    /**
     * The packaged theme stylesheet, if any (published to
     * public/themes/{slug}/theme.css by the installer).
     */
    public function cssPath(): ?string
    {
        foreach (["{$this->path}/resources/css/theme.css", "{$this->path}/dist/theme.css", "{$this->path}/public/theme.css"] as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name(),
            'site_types' => $this->siteTypes(),
            'pages' => array_keys($this->pages()),
            'presets' => array_keys($this->presets()),
        ];
    }
}
