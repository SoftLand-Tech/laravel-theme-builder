<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use SoftLand\ThemeBuilder\Builder\BlockRegistry;
use SoftLand\ThemeBuilder\Models\Theme;

class ValidateThemeCommand extends Command
{
    protected $signature = 'theme-builder:validate
        {slug? : Preset slug to validate (omitting validates every preset found)}
        {--theme= : Validate a stored theme by id instead of a preset}';

    protected $description = 'Validate a theme preset (or stored theme) against the package contracts: required keys, settings slices, color schemes, and every block.';

    /** @var array<int, string> */
    private array $errors = [];

    /** @var array<int, string> */
    private array $warnings = [];

    public function handle(BlockRegistry $registry, Filesystem $files): int
    {
        if ($themeId = $this->option('theme')) {
            return $this->validateStoredTheme($registry, (int) $themeId);
        }

        $slugs = $this->argument('slug') !== null
            ? [(string) $this->argument('slug')]
            : $this->discoverPresets($files);

        if ($slugs === []) {
            $this->components->error('No presets found. Run `php artisan theme-builder:make-theme <slug>` first.');

            return self::FAILURE;
        }

        $totalErrors = 0;

        foreach ($slugs as $slug) {
            $this->errors = [];
            $this->warnings = [];

            $data = $this->loadPreset($files, $slug);

            if ($data === null) {
                $this->components->error("Preset [{$slug}] not found.");

                $totalErrors++;

                continue;
            }

            $this->validatePayload($registry, $slug, $data);
            $this->report($slug);
            $totalErrors += count($this->errors);
        }

        return $totalErrors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function validateStoredTheme(BlockRegistry $registry, int $themeId): int
    {
        /** @var Theme|null $theme */
        $theme = Theme::query()->find($themeId);

        if ($theme === null) {
            $this->components->error("Theme #{$themeId} not found.");

            return self::FAILURE;
        }

        $presetSlug = $theme->preset_slug ?? '-';

        $this->validatePayload($registry, "theme #{$themeId} ({$presetSlug})", [
            'name' => $theme->nameTranslations(),
            'settings' => $theme->settings ?? [],
            'header' => $theme->header ?? [],
            'footer' => $theme->footer ?? [],
            'blocks' => $theme->blocks ?? [],
            'templates' => $theme->templates ?? [],
        ]);
        $this->report("theme #{$themeId}");

        return $this->errors === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validatePayload(BlockRegistry $registry, string $label, array $data): void
    {
        $this->checkRequiredKeys($data);
        $this->checkName($data['name'] ?? null);
        $this->checkSettings($data['settings'] ?? []);
        $this->checkDefaults($data['header'] ?? [], Theme::headerDefaults(), 'header');
        $this->checkDefaults($data['footer'] ?? [], Theme::footerDefaults(), 'footer');
        $this->checkBlocks($registry, $data['blocks'] ?? [], 'blocks');
        $this->checkTemplates($registry, $data['templates'] ?? []);
    }

    /** @param  array<string, mixed>  $data */
    private function checkRequiredKeys(array $data): void
    {
        foreach (['name', 'settings', 'header', 'footer', 'blocks', 'templates'] as $key) {
            if (! array_key_exists($key, $data)) {
                $this->errors[] = "missing top-level key `{$key}`.";
            }
        }
    }

    private function checkName(mixed $name): void
    {
        if (is_array($name)) {
            foreach (['ar', 'en'] as $locale) {
                if (! isset($name[$locale]) || $name[$locale] === '') {
                    $this->warnings[] = "name.{$locale} is empty.";
                }
            }
        } elseif (! is_string($name) || $name === '') {
            $this->errors[] = 'name must be a string or {ar,en} object.';
        }
    }

    /** @param  array<string, mixed>  $settings */
    private function checkSettings(array $settings): void
    {
        if ($settings === []) {
            $this->errors[] = 'settings is empty.';

            return;
        }

        foreach (array_keys(Theme::defaultSettings()) as $slice) {
            if (! array_key_exists($slice, $settings)) {
                $this->warnings[] = "settings.{$slice} missing (will fall back to defaults).";
            }
        }

        // Hard-required color fields.
        foreach (['primary', 'background', 'surface', 'text', 'muted'] as $color) {
            if (! isset($settings['colors'][$color])) {
                $this->errors[] = "settings.colors.{$color} is required.";
            }
        }

        // Typography + radius.
        foreach (['heading_font', 'body_font'] as $font) {
            if (empty($settings['typography'][$font])) {
                $this->errors[] = "settings.typography.{$font} is required.";
            }
        }
        foreach (['card', 'button'] as $radius) {
            if (! array_key_exists($radius, $settings['radius'] ?? [])) {
                $this->warnings[] = "settings.radius.{$radius} missing.";
            }
        }

        $this->checkColorSchemes($settings['color_schemes'] ?? []);
    }

    /** @param  array<int, array<string, mixed>>  $schemes */
    private function checkColorSchemes(array $schemes): void
    {
        if ($schemes === []) {
            $this->errors[] = 'settings.color_schemes must contain at least one scheme.';

            return;
        }

        $required = ['key', 'background', 'surface', 'text', 'muted', 'primary', 'button', 'button_text', 'accent'];
        $keys = [];

        foreach ($schemes as $i => $scheme) {
            foreach ($required as $field) {
                if (! array_key_exists($field, $scheme)) {
                    $this->errors[] = "color_schemes[{$i}] missing `{$field}`.";
                }
            }
            if (isset($scheme['key'])) {
                if (isset($keys[$scheme['key']])) {
                    $this->errors[] = "duplicate color_scheme key `{$scheme['key']}`.";
                }
                $keys[$scheme['key']] = true;
            }
        }
    }

    /** @param  array<string, mixed>  $defaults */
    private function checkDefaults(array $given, array $defaults, string $label): void
    {
        foreach (array_keys($defaults) as $key) {
            if (! array_key_exists($key, $given)) {
                $this->warnings[] = "{$label}.{$key} missing (will fall back to defaults).";
            }
        }
    }

    /** @param  array<int, array<string, mixed>>  $blocks */
    private function checkBlocks(BlockRegistry $registry, array $blocks, string $label): void
    {
        if (! is_array($blocks)) {
            $this->errors[] = "{$label} must be an array of {type, props}.";

            return;
        }

        foreach ($blocks as $i => $block) {
            $this->checkBlock($registry, $block, "{$label}[{$i}]");
        }
    }

    /** @param  array<string, mixed>  $block */
    private function checkBlock(BlockRegistry $registry, array $block, string $path): void
    {
        $type = $block['type'] ?? null;

        if (! is_string($type)) {
            $this->errors[] = "{$path} missing a string `type`.";

            return;
        }

        if (! $registry->has($type)) {
            $this->errors[] = "{$path} references unknown block type `{$type}`.";

            return;
        }

        $props = $block['props'] ?? [];

        if (! is_array($props)) {
            $this->errors[] = "{$path} `props` must be an object.";

            return;
        }

        // Round-trip: anything not in the definition's defaults is silently
        // dropped by sanitize(). Flag those so authors fix the schema.
        $allowed = array_keys($registry->get($type)->defaults);
        $unknown = array_diff(array_keys($props), $allowed);

        if ($unknown !== []) {
            $this->warnings[] = "{$path} ({$type}) props will be dropped: ".implode(', ', $unknown).'.';
        }

        // Missing section-style contract fields hint the block won't behave.
        foreach (['visibleOnDesktop', 'visibleOnMobile'] as $visibility) {
            if (! array_key_exists($visibility, $props)) {
                $this->warnings[] = "{$path} ({$type}) missing `{$visibility}`.";
            }
        }
    }

    /** @param  array<string, mixed>  $templates */
    private function checkTemplates(BlockRegistry $registry, array $templates): void
    {
        foreach (['product', 'cart', 'collection'] as $page) {
            if (! array_key_exists($page, $templates)) {
                $this->warnings[] = "templates.{$page} missing.";

                continue;
            }

            foreach (['before', 'after'] as $slot) {
                $blocks = $templates[$page][$slot] ?? [];

                if (! is_array($blocks)) {
                    $this->errors[] = "templates.{$page}.{$slot} must be an array.";

                    continue;
                }

                foreach ($blocks as $i => $block) {
                    $this->checkBlock($registry, $block, "templates.{$page}.{$slot}[{$i}]");
                }
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function discoverPresets(Filesystem $files): array
    {
        $slugs = [];

        foreach ([database_path('presets'), dirname(__DIR__, 2).'/database/presets'] as $dir) {
            if (! $files->exists($dir)) {
                continue;
            }

            foreach ($files->files($dir) as $file) {
                if ($file->getExtension() === 'json') {
                    $slug = $file->getBasename('.json');
                    $slugs[$slug] = $slug;
                }
            }
        }

        return array_values($slugs);
    }

    /** @return array<string, mixed>|null */
    private function loadPreset(Filesystem $files, string $slug): ?array
    {
        $host = database_path("presets/{$slug}.json");
        $path = $files->exists($host) ? $host : dirname(__DIR__, 2)."/database/presets/{$slug}.json";

        if (! $files->exists($path)) {
            return null;
        }

        $data = json_decode((string) $files->get($path), true);

        return is_array($data) ? $data : null;
    }

    private function report(string $slug): void
    {
        $this->newLine();
        $ok = $this->errors === [];
        $this->components->{$ok ? 'info' : 'error'}($ok ? "✓ {$slug} — valid" : "✗ {$slug} — invalid");

        foreach ($this->errors as $message) {
            $this->line("  <error>✗</error> {$message}");
        }
        foreach ($this->warnings as $message) {
            $this->line("  <comment>!</comment> {$message}");
        }
    }
}
