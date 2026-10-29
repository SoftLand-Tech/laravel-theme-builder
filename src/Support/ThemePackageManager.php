<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Support;

use Illuminate\Filesystem\Filesystem;

/**
 * Discovers and aggregates theme packages (softlandtech/theme-builder-*, or
 * any composer package shipping a theme-builder.json manifest).
 *
 * Sources, in order:
 *   1. Explicit register() calls — a theme package's service provider calls
 *      this from boot() with its package root.
 *   2. A one-time filesystem scan (composer vendor dir + this package's own
 *      siblings) so theme packages work even without a provider.
 *
 * The manager feeds the seeder (package presets), the builder's Pages tab
 * (package pages), and the installer (which themes fit which site type).
 */
class ThemePackageManager
{
    /** Official theme packages, each published as its own SoftLand-Tech repo. */
    public const array OFFICIAL_SLUGS = ['personal', 'ecommerce', 'lms', 'cms'];

    /** Default clone URL pattern when `theme-builder.theme_repos` doesn't list one. */
    public const string REPO_PATTERN = 'https://github.com/SoftLand-Tech/theme-builder-';

    /** @var array<string, ThemePackage> */
    private array $packages = [];

    private bool $scanned = false;

    public function __construct(
        private readonly Filesystem $files = new Filesystem,
    ) {}

    /**
     * Register a theme package by root path (idempotent, slug-keyed).
     */
    public function register(string $path): ?ThemePackage
    {
        $package = ThemePackage::load($path, $this->files);

        if ($package === null) {
            return null;
        }

        return $this->packages[$package->slug] = $package;
    }

    /**
     * @return array<string, ThemePackage>
     */
    public function packages(): array
    {
        $this->scanVendorOnce();

        return $this->packages;
    }

    public function package(string $slug): ?ThemePackage
    {
        return $this->packages()[$slug] ?? null;
    }

    /**
     * Theme packages that declare one of the given {@see SiteProfile} types.
     *
     * @param  array<int, string>  $siteTypes
     * @return array<string, ThemePackage>
     */
    public function forSiteTypes(array $siteTypes): array
    {
        $siteTypes = array_map(fn ($t) => strtolower(trim((string) $t)), $siteTypes);

        return collect($this->packages())
            ->filter(fn (ThemePackage $p) => collect($p->siteTypes())->intersect($siteTypes)->isNotEmpty())
            ->all();
    }

    /**
     * Every available preset, host-first: database/presets (host) → theme
     * packages → the core's bundled presets.
     *
     * @return array<string, string> preset slug => absolute JSON path
     */
    public function presets(): array
    {
        $presets = [];

        $hostDir = database_path('presets');

        if ($this->files->isDirectory($hostDir)) {
            foreach ($this->files->files($hostDir) as $file) {
                if ($file->getExtension() === 'json') {
                    $presets[$file->getBasename('.json')] = $file->getPathname();
                }
            }
        }

        foreach ($this->packages() as $package) {
            foreach ($package->presets() as $slug => $path) {
                $presets[$slug] ??= $path;
            }
        }

        $coreDir = dirname(__DIR__, 2).'/database/presets';

        foreach ($this->files->files($coreDir) as $file) {
            if ($file->getExtension() === 'json') {
                $presets[$file->getBasename('.json')] ??= $file->getPathname();
            }
        }

        return $presets;
    }

    /**
     * The page templates the builder's Pages tab should offer:
     *   1. `theme-builder.pages` config when the host set it (installer does),
     *   2. else the union of all registered theme packages' pages,
     *   3. else the starter pages for `theme-builder.site_type` (an LMS gets
     *      course/lesson, not the ecommerce trio).
     *
     * @return array<string, array{label?: mixed}>
     */
    public function pages(): array
    {
        $configured = config('theme-builder.pages');

        if (is_array($configured) && $configured !== []) {
            return $this->normalizePages($configured);
        }

        $pages = [];

        foreach ($this->packages() as $package) {
            foreach ($package->pages() as $key => $meta) {
                $pages[$key] ??= $meta;
            }
        }

        if ($pages !== []) {
            return $pages;
        }

        return SiteProfile::pagesForType((string) config('theme-builder.site_type', SiteProfile::TYPE_UNKNOWN));
    }

    /**
     * Route-map entries suggested by all registered packages (host config
     * always wins at runtime — this feeds the installer's config writer).
     *
     * @return array<string, string>
     */
    public function routesMap(): array
    {
        $map = [];

        foreach ($this->packages() as $package) {
            foreach ($package->routesMap() as $name => $url) {
                $map[$name] ??= $url;
            }
        }

        return $map;
    }

    /**
     * Deep-merge every registered package's settings slice into the given
     * settings array (used when seeding package presets).
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    public function mergeSettings(array $settings, ?string $forPresetSlug = null): array
    {
        foreach ($this->packages() as $package) {
            $applies = $forPresetSlug === null
                || $forPresetSlug === $package->slug
                || str_starts_with($forPresetSlug, $package->slug.'-')
                || array_key_exists($forPresetSlug, $package->presets());

            if (! $applies) {
                continue;
            }

            $settings = array_replace_recursive($settings, $package->settings());
        }

        return $settings;
    }

    /**
     * Discover theme packages: one glob over vendor, plus a glob over the
     * siblings of this package's own root. The sibling glob covers both the
     * composer layout (vendor/softlandtech/theme-builder-*) and the dev
     * layout (a path-repo checkout with theme packages checked out beside
     * the core package).
     */
    private function scanVendorOnce(): void
    {
        if ($this->scanned) {
            return;
        }

        $this->scanned = true;

        $manifests = [];

        // Composer layout: vendor/<owner>/<name>/theme-builder.json.
        $vendor = base_path('vendor');

        if (is_dir($vendor)) {
            $manifests = array_merge($manifests, glob("{$vendor}/*/*/theme-builder.json") ?: []);
        }

        // Sibling layout: theme-builder-{slug} packages checked out beside
        // the core package (the dev monorepo layout — Sites/theme-builder-* —
        // which mirrors vendor/<owner>/theme-builder-*). Disable via config
        // for hermetic tests.
        if (config('theme-builder.discover_siblings', true)) {
            $coreParent = dirname(dirname(__DIR__, 2));
            $manifests = array_merge($manifests, glob("{$coreParent}/theme-builder-*/theme-builder.json") ?: []);
        }

        // Cloned layout: packages git-cloned by `theme-builder:install` (or by
        // hand — see the theme repos' READMEs) into the host's themes
        // directory, e.g. theme-builder-themes/theme-builder-ecommerce.
        $themesDir = $this->themesPath();

        if (is_dir($themesDir)) {
            $manifests = array_merge($manifests, glob("{$themesDir}/theme-builder-*/theme-builder.json") ?: []);
        }

        foreach ($manifests as $manifest) {
            $this->register(dirname($manifest));
        }
    }

    /**
     * @param  array<string, mixed>  $pages
     * @return array<string, array{label?: mixed}>
     */
    private function normalizePages(array $pages): array
    {
        $normalized = [];

        foreach ($pages as $key => $meta) {
            if (is_int($key) && is_string($meta)) {
                $normalized[$meta] = ['label' => ucfirst($meta)];

                continue;
            }

            if (is_string($key) && $key !== '') {
                $normalized[$key] = is_array($meta) ? $meta : ['label' => $meta];
            }
        }

        return $normalized;
    }

    /**
     * The clone URL for a theme package slug: `theme-builder.theme_repos`
     * config first, then the official SoftLand-Tech pattern, else null.
     */
    public function repoFor(string $slug): ?string
    {
        $repos = config('theme-builder.theme_repos');
        $repo = is_array($repos) ? ($repos[$slug] ?? null) : null;

        if (is_string($repo) && $repo !== '') {
            return $repo;
        }

        return in_array($slug, self::OFFICIAL_SLUGS, true)
            ? self::REPO_PATTERN.$slug
            : null;
    }

    /**
     * Every theme the installer can offer to clone: slug => repo URL (the
     * official packages plus anything declared in `theme-builder.theme_repos`).
     *
     * @return array<string, string>
     */
    public function catalog(): array
    {
        $catalog = [];

        foreach (self::OFFICIAL_SLUGS as $slug) {
            $catalog[$slug] = self::REPO_PATTERN.$slug;
        }

        $repos = config('theme-builder.theme_repos');

        if (is_array($repos)) {
            foreach ($repos as $slug => $repo) {
                if (is_string($slug) && $slug !== '' && is_string($repo) && $repo !== '') {
                    $catalog[$slug] = $repo;
                }
            }
        }

        return $catalog;
    }

    /**
     * Where `theme-builder:install` clones theme repos into (and where this
     * manager discovers them on later boots). Default: the host's
     * `theme-builder-themes/` directory, overridable via config.
     */
    public function themesPath(): string
    {
        $path = config('theme-builder.themes_path');

        return rtrim((string) ($path ?: base_path('theme-builder-themes')), '/');
    }
}
