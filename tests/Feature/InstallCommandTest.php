<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use SoftLand\ThemeBuilder\Models\Scopes\PresetOrTenantScope;
use SoftLand\ThemeBuilder\Models\Theme;
use SoftLand\ThemeBuilder\Support\ThemePackageManager;

/**
 * Runs the installer non-interactively against a fixture theme package and
 * restores every host (testbench skeleton) file it touches afterwards.
 */
beforeEach(function (): void {
    $this->files = new Filesystem;

    // ── A fixture theme package (manifest + preset + css) ──────────────
    $this->fixtureRoot = sys_get_temp_dir().'/theme-builder-install-'.uniqid();
    $this->files->ensureDirectoryExists("{$this->fixtureRoot}/presets");
    $this->files->ensureDirectoryExists("{$this->fixtureRoot}/resources/css");

    $this->files->put("{$this->fixtureRoot}/theme-builder.json", json_encode([
        'slug' => 'fixture',
        'name' => ['en' => 'Fixture'],
        'site_types' => ['cms'],
        'pages' => ['post' => ['label' => ['en' => 'Article', 'ar' => 'مقال']]],
        'routes_map' => ['post' => '/posts'],
    ]));

    $this->files->put("{$this->fixtureRoot}/presets/fixture.json", json_encode([
        'name' => ['en' => 'Fixture'],
        'settings' => [
            'colors' => ['primary' => '#000000', 'background' => '#FFFFFF', 'surface' => '#F0F0F0', 'text' => '#111111', 'muted' => '#666666'],
            'color_schemes' => [[
                'key' => '1', 'name' => 'Default', 'background' => '#FFFFFF', 'surface' => '#F0F0F0',
                'text' => '#111111', 'muted' => '#666666', 'primary' => '#000000',
                'button' => '#000000', 'button_text' => '#FFFFFF', 'accent' => '#FF0000',
            ]],
            'typography' => ['heading_font' => 'Inter', 'body_font' => 'Inter'],
            'radius' => ['card' => 8, 'button' => 8],
        ],
        'header' => [],
        'footer' => [],
        'blocks' => [],
        'templates' => ['post' => ['before' => [], 'after' => []]],
    ]));

    $this->files->put("{$this->fixtureRoot}/resources/css/theme.css", '/* fixture */');

    // NOTE: tests that need the fixture discoverable locally register it
    // themselves (`app(ThemePackageManager::class)->register(...)`) — the
    // clone tests rely on it NOT being on disk.

    // ── Snapshot every host file the installer may touch ──────────────
    // (Taken BEFORE the scaffolding below so files we create ourselves are
    // recorded as absent and removed again on teardown.)
    $this->snapshot = [];

    foreach (installerTouchedPaths() as $path) {
        $this->snapshot[$path] = is_file($path) ? (string) file_get_contents($path) : null;
    }

    // ── Ensure a routes/web.php + vite.config exist to be wired ────────
    // (Some testbench skeletons ship without them; the snapshot cleanup
    // below removes them again when we created them.)
    if (! is_file(base_path('routes/web.php'))) {
        $this->files->ensureDirectoryExists(base_path('routes'));
        $this->files->put(base_path('routes/web.php'), "<?php\n\nuse Illuminate\Support\Facades\Route;\n\nRoute::get('/', fn () => view('welcome'));\n");
    }

    if (! is_file(base_path('vite.config.js'))) {
        $this->files->put(base_path('vite.config.js'), "import { defineConfig } from 'vite';\n\nexport default defineConfig({\n    input: ['resources/css/app.css'],\n});\n");
    }
});

afterEach(function (): void {
    // Test-local "remote" + themes directory.
    foreach (['sourceRepo', 'themesDir'] as $prop) {
        if (isset($this->{$prop})) {
            (new Filesystem)->deleteDirectory($this->{$prop});
        }
    }

    foreach ($this->snapshot as $path => $content) {
        if ($content === null) {
            if (is_file($path)) {
                unlink($path);
            }
        } else {
            file_put_contents($path, $content);
        }
    }

    // Dirs the installer may have created (only remove marker files + dirs
    // that did not exist before — conservative cleanup).
    foreach ([database_path('presets'), public_path('themes/fixture'), public_path('themes'), lang_path('ar')] as $dir) {
        if (is_dir($dir) && (new Filesystem)->isEmptyDirectory($dir)) {
            rmdir($dir);
        }
    }

    $this->files->deleteDirectory($this->fixtureRoot);
});

it('installs the package end to end, non-interactive', function (): void {
    app(ThemePackageManager::class)->register($this->fixtureRoot);

    $this->artisan('theme-builder:install', [
        '--type' => 'cms',
        '--themes' => 'fixture',
        '--features' => 'cart,checkout',
        '--skip-composer' => true,
        '--no-interaction' => true,
    ])->assertExitCode(0);

    // Migrations ran.
    expect(Schema::hasTable('themes'))->toBeTrue();

    // The fixture preset was seeded as a global preset.
    $preset = Theme::withoutGlobalScope(PresetOrTenantScope::class)
        ->where('preset_slug', 'fixture')
        ->where('is_preset', true)
        ->first();

    expect($preset)->not->toBeNull();

    // Routes were wired.
    $routes = (string) file_get_contents(base_path('routes/web.php'));
    expect($routes)->toContain('ThemeBuilder::routes()');

    // Vite inputs were wired.
    $vite = (string) file_get_contents(base_path('vite.config.js'));
    expect($vite)->toContain('resources/css/storefront.css')
        ->and($vite)->toContain('vendor/softlandtech/theme-builder/resources/js/builder/main.tsx');

    // Config was published + patched.
    $config = (string) file_get_contents(config_path('theme-builder.php'));
    expect($config)->toContain("'site_type' => 'cms'")
        ->and($config)->toContain("'post' => ['label' => ['en' => 'Article', 'ar' => 'مقال']]");

    // Composer-free fallback copied the preset + css.
    expect(is_file(database_path('presets/fixture.json')))->toBeTrue()
        ->and(is_file(public_path('themes/fixture/theme.css')))->toBeTrue();

    // The AI hand-off prompt was written.
    $prompt = (string) file_get_contents(base_path('theme-builder-ai-prompt.md'));
    expect($prompt)->toContain('softlandtech/theme-builder')
        ->and($prompt)->toContain('cms')
        ->and($prompt)->toContain('StorefrontDataProvider');
});

it('respects --no-ai and skips the prompt file', function (): void {
    $this->artisan('theme-builder:install', [
        '--type' => 'personal',
        '--themes' => '',
        '--features' => 'contact',
        '--skip-composer' => true,
        '--no-ai' => true,
        '--no-interaction' => true,
    ])->assertExitCode(0);

    expect(is_file(base_path('theme-builder-ai-prompt.md')))->toBeFalse();
});

it('rejects an unknown --type without crashing', function (): void {
    $this->artisan('theme-builder:install', [
        '--type' => 'bogus',
        '--no-interaction' => true,
    ])->assertExitCode(1);
});

it('seeds the bundled starter when the chosen theme is unavailable', function (): void {
    config(['theme-builder.theme_repos' => []]);

    $this->artisan('theme-builder:install', [
        '--type' => 'lms',
        '--themes' => 'doesnotexist',
        '--skip-composer' => true,
        '--skip-git' => true,
        '--no-ai' => true,
        '--no-interaction' => true,
    ])->assertExitCode(0);

    // The LMS starter was seeded as a fallback…
    expect(
        Theme::withoutGlobalScope(PresetOrTenantScope::class)
            ->where('preset_slug', 'lms')
            ->where('is_preset', true)
            ->exists()
    )->toBeTrue();

    // …type-appropriate pages were recorded…
    $config = (string) file_get_contents(config_path('theme-builder.php'));
    expect($config)->toContain("'site_type' => 'lms'")
        ->and($config)->toContain("'course' => ['label' => 'Course']");

    // …and the stock welcome route was replaced by the storefront route.
    $routes = (string) file_get_contents(base_path('routes/web.php'));
    expect($routes)->toContain('StorefrontController')
        ->and($routes)->not->toContain("view('welcome')");
});

it('clones a chosen theme from its repo when it is not on disk', function (): void {
    // A "remote": a real local git repo containing the fixture package.
    $this->sourceRepo = sys_get_temp_dir().'/theme-builder-remote-'.uniqid();
    $this->files->copyDirectory($this->fixtureRoot, $this->sourceRepo);

    Process::path($this->sourceRepo)->run(['git', 'init', '-q'])->throw();
    Process::path($this->sourceRepo)->run(['git', 'add', '-A'])->throw();
    Process::path($this->sourceRepo)->run([
        'git', '-c', 'user.email=test@example.com', '-c', 'user.name=Test', 'commit', '-qm', 'init',
    ])->throw();

    $this->themesDir = sys_get_temp_dir().'/theme-builder-themes-'.uniqid();
    $this->files->ensureDirectoryExists($this->themesDir);

    config([
        'theme-builder.theme_repos' => ['fixture' => $this->sourceRepo],
        'theme-builder.themes_path' => $this->themesDir,
    ]);

    $this->artisan('theme-builder:install', [
        '--type' => 'cms',
        '--themes' => 'fixture',
        '--skip-composer' => true,
        '--no-ai' => true,
        '--no-interaction' => true,
    ])->assertExitCode(0);

    // The repo was cloned into the themes directory…
    expect(is_file("{$this->themesDir}/theme-builder-fixture/theme-builder.json"))->toBeTrue();

    // …its preset was seeded…
    expect(
        Theme::withoutGlobalScope(PresetOrTenantScope::class)
            ->where('preset_slug', 'fixture')
            ->where('is_preset', true)
            ->exists()
    )->toBeTrue();

    // …and its stylesheet was copied for serving.
    expect(is_file(public_path('themes/fixture/theme.css')))->toBeTrue();
});

it('lists the manual clone command instead of cloning with --skip-git', function (): void {
    config([
        'theme-builder.theme_repos' => ['fixture' => '/nonexistent/repo.git'],
        'theme-builder.themes_path' => $this->themesDir = sys_get_temp_dir().'/theme-builder-themes-'.uniqid(),
    ]);

    $this->artisan('theme-builder:install', [
        '--type' => 'cms',
        '--themes' => 'fixture',
        '--skip-composer' => true,
        '--skip-git' => true,
        '--no-ai' => true,
        '--no-interaction' => true,
    ])->assertExitCode(0);

    expect(is_dir("{$this->themesDir}/theme-builder-fixture"))->toBeFalse()
        ->and(
            Theme::withoutGlobalScope(PresetOrTenantScope::class)->where('preset_slug', 'fixture')->exists()
        )->toBeFalse();
});

/**
 * @return array<int, string>
 */
function installerTouchedPaths(): array
{
    $paths = [
        base_path('routes/web.php'),
        base_path('vite.config.js'),
        config_path('theme-builder.php'),
        base_path('theme-builder-ai-prompt.md'),
        database_path('presets/fixture.json'),
        public_path('themes/fixture/theme.css'),
        base_path('resources/css/storefront.css'),
        base_path('resources/css/builder.css'),
        base_path('resources/css/_storefront-theme.css'),
        base_path('resources/css/_saudi-riyal-font.css'),
        lang_path('storefront.php'),
        lang_path('ar/storefront.php'),
    ];

    foreach (glob(database_path('migrations/*_create_theme_builder_tables.php')) ?: [] as $migration) {
        $paths[] = $migration;
    }

    return $paths;
}
