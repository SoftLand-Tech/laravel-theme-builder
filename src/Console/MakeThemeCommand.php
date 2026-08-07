<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use SoftLand\ThemeBuilder\Models\Theme;

class MakeThemeCommand extends Command
{
    protected $signature = 'theme-builder:make-theme
        {slug : The theme slug (kebab-case; used as preset_slug and the JSON filename)}
        {--name= : Display name (default: the slug, Title-Cased)}
        {--overrides : Also scaffold per-theme override directories}
        {--force : Overwrite an existing preset}';

    protected $description = 'Scaffold a new theme preset (JSON) and optional override directories.';

    public function handle(Filesystem $files): int
    {
        $slug = (string) $this->argument('slug');

        if (! preg_match('/^[a-z0-9][a-z0-9-]*$/', $slug)) {
            $this->components->error("Invalid slug [{$slug}]. Use lowercase kebab-case (letters, digits, hyphens).");

            return self::FAILURE;
        }

        $dest = database_path("presets/{$slug}.json");

        if ($files->exists($dest) && ! $this->option('force')) {
            $this->components->error("A preset already exists at [{$dest}]. Use --force to overwrite.");

            return self::FAILURE;
        }

        $data = $this->starterPreset($files, $slug);

        $files->ensureDirectoryExists(dirname($dest));
        $files->put(
            $dest,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
        );

        $this->components->info("Created preset [{$slug}] at {$dest}.");

        if ($this->option('overrides')) {
            $this->scaffoldOverrides($files, $slug);
        }

        $this->newLine();
        $this->components->info('Next steps:');
        $this->line("  • Edit the preset: {$dest}");
        $this->line("  • Validate it:      php artisan theme-builder:validate {$slug}");
        $this->line('  • Seed it:          php artisan theme-builder:seed-preset '.$slug);

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function starterPreset(Filesystem $files, string $slug): array
    {
        $stubPath = dirname(__DIR__, 2).'/database/presets/default.json';
        $data = $slug === 'default' && $files->exists($stubPath)
            ? json_decode((string) $files->get($stubPath), true)
            : null;

        if (! is_array($data)) {
            // Minimal valid starter if the bundled default is unavailable.
            $data = [
                'settings' => Theme::defaultSettings(),
                'header' => Theme::headerDefaults(),
                'footer' => Theme::footerDefaults(),
                'blocks' => [],
                'templates' => [
                    'product' => ['before' => [], 'after' => []],
                    'cart' => ['before' => [], 'after' => []],
                    'collection' => ['before' => [], 'after' => []],
                ],
            ];
        }

        $name = (string) ($this->option('name') ?: ucfirst(str_replace('-', ' ', $slug)));
        $data['name'] = ['ar' => $name, 'en' => $name];
        $data['description'] = ['ar' => '', 'en' => ''];

        return $data;
    }

    private function scaffoldOverrides(Filesystem $files, string $slug): void
    {
        $viewDir = resource_path("views/components/themes/{$slug}/blocks");
        $publicDir = public_path("themes/{$slug}");

        $files->ensureDirectoryExists($viewDir);
        $files->ensureDirectoryExists($publicDir);
        $files->put($publicDir.'/theme.css', "/* {$slug} theme overrides — scoped under .theme-{$slug} */\n");

        $this->components->info('Scaffolded overrides:');
        $this->line("  • {$viewDir}  (per-block Blade overrides)");
        $this->line("  • {$publicDir} (theme.css + assets)");
    }
}
