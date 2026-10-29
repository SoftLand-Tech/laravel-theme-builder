<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use SoftLand\ThemeBuilder\Contracts\ThemeSchema;
use SoftLand\ThemeBuilder\Seeders\ThemePresetSeeder;
use SoftLand\ThemeBuilder\Support\SiteDetector;
use SoftLand\ThemeBuilder\Support\SiteProfile;
use SoftLand\ThemeBuilder\Support\ThemePackage;
use SoftLand\ThemeBuilder\Support\ThemePackageManager;

/**
 * The one-command installer.
 *
 * 1. Scans the host app with plain greps (SiteDetector — no AI) and reports
 *    what kind of site it found (ecommerce / lms / cms / personal / empty).
 * 2. Asks what already exists (cart page? checkout? search?).
 * 3. Lets the merchant pick one or more theme packages
 *    (personal / ecommerce / lms / cms).
 * 4. Does the full manual install: publishes config/assets/lang/migrations,
 *    migrates, installs + seeds the chosen theme packages, wires
 *    routes/web.php, vite.config, and the Tailwind @source line, checks the
 *    npm deps, and records site type + pages in the config.
 * 5. Finally offers to print a complete hand-off prompt for an AI agent to
 *    finish the host-specific bits (providers, middleware, page routes).
 *
 * Every step is idempotent — re-running only fills in what's missing.
 */
class InstallCommand extends Command
{
    protected $signature = 'theme-builder:install
        {--type= : Force the site type (ecommerce|lms|cms|personal|empty) instead of scanning}
        {--themes= : Comma-separated theme package slugs to install (default: suggested for the type)}
        {--features= : Comma-separated features the site has (cart,checkout,courses,lessons,blog,pages,portfolio,services,search,account,contact)}
        {--currency= : Storefront currency code (default: keep current config)}
        {--skip-migrate : Do not run `php artisan migrate`}
        {--skip-composer : Do not run composer for theme packages (copy their presets instead)}
        {--skip-git : Do not git-clone theme repos (print the clone commands instead)}
        {--no-ai : Skip the final AI hand-off prompt}
        {--force : Overwrite files without asking}';

    protected $description = 'Scan the app, choose theme packages, and install + wire softlandtech/theme-builder end to end';

    private const string CORE_VENDOR_PATH = 'vendor/softlandtech/theme-builder';

    /** @var array<int, string> */
    private array $done = [];

    /** @var array<int, string> */
    private array $todo = [];

    /** @var array<int, string> */
    private array $chosenThemeSlugs = [];

    /** @var array<string, string> slug => repo URL for themes cloned this run. */
    private array $clonedRepos = [];

    private ?SiteProfile $profile = null;

    private string $siteType = SiteProfile::TYPE_UNKNOWN;

    /** @var array<string, bool> */
    private array $features = [];

    public function handle(
        Filesystem $files,
        SiteDetector $detector,
        ThemePackageManager $themePackages,
        ThemePresetSeeder $seeder,
    ): int {
        $this->components->twoColumnDetail('<fg=white;options=bold>softlandtech/theme-builder</>', 'interactive installer');

        // ── 1. Scan the app (greps only — no AI) ─────────────────────────
        $resolvedType = $this->resolveSiteType($detector);

        if ($resolvedType === null) {
            return self::FAILURE;
        }

        $this->siteType = $resolvedType;

        $this->components->info("Site type: <fg=white;options=bold>{$this->siteTypeLabel($this->siteType)}</>");

        // ── 2. What already exists? ──────────────────────────────────────
        $this->features = $this->resolveFeatures();

        // ── 3. Choose theme packages ─────────────────────────────────────
        $this->chosenThemeSlugs = $this->resolveThemes($themePackages);

        // ── 4. Install ───────────────────────────────────────────────────
        $this->publishTags();
        $this->runMigrations();
        $this->installThemePackages($files, $themePackages);
        $this->seedPresets($seeder, $themePackages);
        $this->wireRoutes($files);
        $this->wireVite($files);
        $this->wireTailwindSource($files);
        $this->checkNpmDeps($files);
        $this->writeConfig($files, $themePackages);
        $this->setCurrency();

        // ── 5. Summary ───────────────────────────────────────────────────
        $this->newLine();
        $this->components->twoColumnDetail('<fg=green;options=bold>Install finished.</>', 'Remaining steps are listed below.');

        foreach ($this->done as $line) {
            $this->components->twoColumnDetail('✓', $line);
        }

        foreach ($this->todo as $line) {
            $this->components->twoColumnDetail('…', $line);
        }

        // ── 6. AI hand-off ───────────────────────────────────────────────
        if (! $this->option('no-ai')) {
            $this->offerAiPrompt($files);
        }

        return self::SUCCESS;
    }

    // ─── Step 1: detection ────────────────────────────────────────────────

    private function resolveSiteType(SiteDetector $detector): ?string
    {
        $forced = (string) ($this->option('type') ?? '');

        if ($forced !== '') {
            $normalized = $this->normalizeType($forced);

            if ($normalized === null) {
                $this->components->error("Unknown type [{$forced}]. Use one of: ecommerce, lms, cms, personal, empty.");
            }

            return $normalized;
        }

        $this->profile = $detector->detect();
        $label = $this->siteTypeLabel($this->profile->type);

        foreach (array_slice($this->profile->evidence, 0, 6) as $line) {
            $this->line("  <fg=gray>· {$line}</>");
        }

        if ($this->input->isInteractive()) {
            $use = $this->confirm("Detected <fg=white;options=bold>{$label}</> (confidence ".(int) ($this->profile->confidence() * 100).'%). Continue with this?', true);

            if (! $use) {
                $picked = $this->choice(
                    'Which type is this site?',
                    array_merge(SiteProfile::TYPES, [SiteProfile::TYPE_EMPTY]),
                );

                return $this->normalizeType((string) $picked);
            }
        }

        return $this->profile->type;
    }

    private function normalizeType(string $type): ?string
    {
        $type = strtolower(trim($type));

        return in_array($type, array_merge(SiteProfile::TYPES, [SiteProfile::TYPE_EMPTY]), true) ? $type : null;
    }

    private function siteTypeLabel(string $type): string
    {
        return match ($type) {
            SiteProfile::TYPE_ECOMMERCE => 'Ecommerce store',
            SiteProfile::TYPE_LMS => 'LMS / courses',
            SiteProfile::TYPE_CMS => 'CMS / blog',
            SiteProfile::TYPE_PERSONAL => 'Personal / portfolio',
            SiteProfile::TYPE_EMPTY => 'New / empty project',
            default => 'Unknown',
        };
    }

    // ─── Step 2: features ─────────────────────────────────────────────────

    /**
     * @return array<string, bool>
     */
    private function resolveFeatures(): array
    {
        $option = (string) ($this->option('features') ?? '');

        if ($option !== '') {
            $features = [];

            foreach (explode(',', $option) as $feature) {
                $key = strtolower(trim($feature));
                if ($key !== '') {
                    $features[$key] = true;
                }
            }

            return $features;
        }

        // Type-native questions first (an LMS is asked about courses, not
        // carts), then the features every site type can have.
        $byType = [
            SiteProfile::TYPE_ECOMMERCE => [
                'cart' => 'a cart page',
                'checkout' => 'a checkout page',
            ],
            SiteProfile::TYPE_LMS => [
                'courses' => 'course pages',
                'lessons' => 'lesson pages',
            ],
            SiteProfile::TYPE_CMS => [
                'blog' => 'a blog / news section',
                'pages' => 'CMS pages',
            ],
            SiteProfile::TYPE_PERSONAL => [
                'portfolio' => 'a portfolio / projects section',
                'services' => 'a services section',
            ],
        ];

        $candidates = array_merge(
            $byType[$this->siteType] ?? [],
            [
                'search' => 'site search',
                'account' => 'login / accounts',
                'blog' => 'a blog / news section',
                'contact' => 'a contact page',
            ],
        );

        $features = [];

        foreach ($candidates as $key => $label) {
            $detected = $this->profile?->hasFeature('has_'.$key) ?? false;
            // Ecommerce checkouts almost always exist; pre-check them when the
            // merchant can still say no. Non-interactive runs trust the scan.
            $default = $detected || ($this->input->isInteractive()
                && $this->siteType === SiteProfile::TYPE_ECOMMERCE
                && in_array($key, ['cart', 'checkout', 'search'], true));

            if ($this->input->isInteractive()) {
                $features[$key] = $this->confirm("Does the site have {$label}?", $default);
            } else {
                $features[$key] = $default;
            }
        }

        return array_filter($features);
    }

    // ─── Step 3: themes ───────────────────────────────────────────────────

    /**
     * Theme choices: packages already on disk (vendor / siblings / themes
     * directory) PLUS every catalog theme (the official
     * SoftLand-Tech/theme-builder-{slug} repos), offered for cloning.
     *
     * @return array<int, string>
     */
    private function resolveThemes(ThemePackageManager $themePackages): array
    {
        $option = (string) ($this->option('themes') ?? '');

        if ($option !== '') {
            return array_values(array_filter(array_map(
                fn ($slug) => strtolower(trim((string) $slug)),
                explode(',', $option),
            )));
        }

        $suggested = $this->profile?->suggestedThemeSlugs()
            ?? [$this->siteType !== SiteProfile::TYPE_UNKNOWN ? $this->siteType : 'personal'];

        /** @var array<string, array{slug: string, note: string}> $choices */
        $choices = [];

        foreach ($themePackages->packages() as $package) {
            $types = implode(', ', $package->siteTypes());

            $choices[$package->slug] = [
                'slug' => $package->slug,
                'note' => $types !== '' ? $types : 'on disk',
            ];
        }

        foreach (config('theme-builder.offer_remote_themes', true) ? $themePackages->catalog() : [] as $slug => $repo) {
            if (! isset($choices[$slug])) {
                $choices[$slug] = [
                    'slug' => $slug,
                    'note' => 'clone from '.preg_replace('#^https?://#', '', $repo),
                ];
            }
        }

        if ($choices === []) {
            $this->components->warn('No theme packages found — installing the bundled "default" preset. Theme packages (personal/ecommerce/lms/cms) can be added later.');

            return [];
        }

        $chosen = [];

        foreach ($choices as $choice) {
            $default = in_array($choice['slug'], $suggested, true);

            if ($this->input->isInteractive()) {
                $pick = $this->confirm("Install theme package <fg=white;options=bold>{$choice['slug']}</> ({$choice['note']})?", $default);
            } else {
                $pick = $default;
            }

            if ($pick) {
                $chosen[] = $choice['slug'];
            }
        }

        return $chosen;
    }

    // ─── Step 4a: publish ─────────────────────────────────────────────────

    private function publishTags(): void
    {
        $this->call('vendor:publish', ['--tag' => 'theme-builder-config', '--force' => (bool) $this->option('force')]);
        $this->call('vendor:publish', ['--tag' => 'theme-builder-assets', '--force' => (bool) $this->option('force')]);
        $this->call('vendor:publish', ['--tag' => 'theme-builder-lang', '--force' => (bool) $this->option('force')]);

        $this->done[] = 'Published config, CSS assets, and translations.';
    }

    // ─── Step 4b: migrate ─────────────────────────────────────────────────

    private function runMigrations(): void
    {
        $themesTable = app(ThemeSchema::class)->themesTable();

        try {
            $tableExists = Schema::hasTable($themesTable);
        } catch (\Throwable) {
            // No database connection yet (fresh project without .env database
            // config): publish the migration and let the merchant run it later.
            $this->call('vendor:publish', ['--tag' => 'theme-builder-migrations']);
            $this->todo[] = 'Configure your database, then run `php artisan migrate` (migration published, not executed).';

            return;
        }

        if ($tableExists) {
            $this->done[] = "Table `{$themesTable}` already exists — migrations untouched.";

            return;
        }

        $this->call('vendor:publish', ['--tag' => 'theme-builder-migrations']);

        if ($this->option('skip-migrate')) {
            $this->todo[] = 'Run `php artisan migrate` (migration published, not executed).';

            return;
        }

        $this->call('migrate');
        $this->done[] = 'Published + ran the theme-builder migrations.';
    }

    // ─── Step 4c: theme packages ──────────────────────────────────────────

    private function installThemePackages(Filesystem $files, ThemePackageManager $themePackages): void
    {
        if ($this->chosenThemeSlugs === []) {
            return;
        }

        foreach ($this->chosenThemeSlugs as $slug) {
            $package = $themePackages->package($slug);

            // Not on disk → clone it from its repo (official
            // github.com/SoftLand-Tech/theme-builder-{slug} or a configured fork).
            if ($package === null) {
                $package = $this->cloneThemePackage($slug, $themePackages, $files);

                if ($package === null) {
                    continue;
                }
            }

            // Already composer-installed inside vendor → nothing to do.
            if (str_starts_with($package->path, base_path('vendor').DIRECTORY_SEPARATOR)) {
                $this->done[] = "Theme package `{$slug}` already installed ({$package->path}).";

                continue;
            }

            // On disk but not in vendor (dev sibling or a fresh clone) →
            // composer path-repository, or a composer-free preset copy.
            $this->addPathRepository($slug, $package)
                ? $this->done[] = "Added path repository + composer-required `{$slug}`."
                : $this->copyPackageAssets($files, $package);
        }
    }

    /**
     * git-clone a theme package into the host's themes directory and register
     * it with the manager so seeding works immediately. Themes stay plain git
     * checkouts — `git pull` updates them.
     */
    private function cloneThemePackage(string $slug, ThemePackageManager $themePackages, Filesystem $files): ?ThemePackage
    {
        $repo = $themePackages->repoFor($slug);

        if ($repo === null) {
            $this->components->warn("Theme [{$slug}] is neither on disk nor in `theme-builder.theme_repos` — skipping.");

            return null;
        }

        $dest = $themePackages->themesPath().'/theme-builder-'.$slug;

        if (($existing = $themePackages->register($dest)) !== null) {
            $this->done[] = "Theme `{$slug}` already cloned ({$dest}).";

            return $existing;
        }

        if ($this->option('skip-git')) {
            $this->todo[] = "Clone manually: git clone {$repo} {$dest}";

            return null;
        }

        $files->ensureDirectoryExists(dirname($dest));
        $this->components->info("Cloning {$repo} …");

        $result = Process::timeout(600)->run(['git', 'clone', '--depth', '1', $repo, $dest]);

        if (! $result->successful()) {
            $this->components->warn("Could not clone [{$slug}] from {$repo} — check the URL/network, or pass --skip-git.");
            $this->line((string) $result->errorOutput());

            return null;
        }

        $package = $themePackages->register($dest);
        $this->clonedRepos[$slug] = $repo;
        $this->done[] = "Cloned `theme-builder-{$slug}` → {$dest} (git pull there to update).";

        return $package;
    }

    /**
     * Register the package as a composer path repository and require it.
     */
    private function addPathRepository(string $slug, ThemePackage $package): bool
    {
        if ($this->option('skip-composer')) {
            return false;
        }

        $composerJson = base_path('composer.json');

        if (! is_file($composerJson)) {
            return false;
        }

        $data = json_decode((string) file_get_contents($composerJson), true);

        if (! is_array($data)) {
            return false;
        }

        $name = "softlandtech/theme-builder-{$slug}";

        // Composer expects `repositories` to be a LIST — normalize any
        // string-keyed entries, dedupe by URL, and store the path relative
        // to the project so composer.json stays portable across machines.
        $url = $this->relativePath(base_path(), $package->path);
        $repos = [];

        foreach ((array) ($data['repositories'] ?? []) as $repo) {
            if (is_array($repo)) {
                $repos[] = $repo;
            }
        }

        $alreadyListed = collect($repos)->contains(
            fn ($repo) => ($repo['type'] ?? null) === 'path' && ($repo['url'] ?? null) === $url
        );

        if (! $alreadyListed) {
            $repos[] = ['type' => 'path', 'url' => $url, 'options' => ['symlink' => true]];
        }

        $data['repositories'] = $repos;

        file_put_contents($composerJson, json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        )."\n");

        $result = Process::timeout(300)->run([
            'composer', 'require', $name, '--no-interaction', '--update-with-dependencies',
        ]);

        if (! $result->successful()) {
            $this->components->warn("composer require {$name} failed — falling back to copying its preset.");
            $this->line((string) $result->errorOutput());

            return false;
        }

        return true;
    }

    /**
     * Composer-free fallback: copy the package's preset (and theme.css) into
     * the host so seeding + customization still work.
     */
    private function copyPackageAssets(Filesystem $files, ThemePackage $package): void
    {
        foreach ($package->presets() as $presetSlug => $path) {
            $files->ensureDirectoryExists(database_path('presets'));
            $files->copy($path, database_path("presets/{$presetSlug}.json"));
            $this->done[] = "Copied preset `{$presetSlug}.json` into database/presets (composer-free install).";
        }

        if (($css = $package->cssPath()) !== null) {
            $files->ensureDirectoryExists(public_path("themes/{$package->slug}"));
            $files->copy($css, public_path("themes/{$package->slug}/theme.css"));
            $this->done[] = "Copied theme.css → public/themes/{$package->slug}/theme.css.";
        }

        $this->todo[] = "Require `softlandtech/theme-builder-{$package->slug}` via composer when convenient (currently preset-copy only).";
    }

    // ─── Step 4d: seed ────────────────────────────────────────────────────

    private function seedPresets(ThemePresetSeeder $seeder, ThemePackageManager $themePackages): void
    {
        $slugs = $this->chosenThemeSlugs;

        if ($slugs === []) {
            $slugs = [SiteProfile::starterPreset($this->siteType)];
        }

        $presets = $themePackages->presets();
        $seeded = [];

        foreach ($slugs as $slug) {
            // A chosen theme package may ship one or more presets under their
            // own filenames — seed all of them, not just a same-named file.
            $package = $themePackages->package($slug);
            $toSeed = $package !== null ? array_keys($package->presets()) : [];

            if ($toSeed === [] && isset($presets[$slug])) {
                $toSeed = [$slug];
            }

            if ($toSeed === []) {
                // Not on disk (clone failed / offline / unknown slug): fall
                // back to this site type's bundled starter so the install
                // still ends with a working, pickable theme.
                $fallback = SiteProfile::starterPreset($this->siteType);

                if (! isset($seeded[$fallback]) && isset($presets[$fallback])) {
                    $this->components->warn("Preset [{$slug}] not found — seeding bundled starter `{$fallback}` instead.");
                    $toSeed = [$fallback];
                } else {
                    $this->components->warn("Preset [{$slug}] not found — skipping seed.");

                    continue;
                }
            }

            foreach ($toSeed as $preset) {
                if (isset($seeded[$preset]) || ! isset($presets[$preset])) {
                    continue;
                }

                $seeder->seed($preset);
                $seeded[$preset] = true;
                $this->done[] = "Seeded preset `{$preset}` (pickable in the theme picker).";
            }
        }
    }

    // ─── Step 4e: routes ──────────────────────────────────────────────────

    private function wireRoutes(Filesystem $files): void
    {
        $path = base_path('routes/web.php');

        if (! $files->exists($path)) {
            $this->todo[] = 'routes/web.php not found — register the storefront + builder routes manually.';

            return;
        }

        $content = (string) $files->get($path);
        $block = '';

        // Only add the home route when no `/` route exists at all.
        $hasRootRoute = (bool) preg_match("#Route::(?:get|match|any)\(\s*['\"]/['\"]#s", $content)
            || str_contains($content, 'StorefrontController');

        if (! $hasRootRoute) {
            $block .= "\n// theme-builder: render the active theme's home blocks.\n"
                ."Route::get('/', [\\SoftLand\\ThemeBuilder\\Http\\Controllers\\StorefrontController::class, 'home']);\n";
            $this->done[] = 'Added the storefront home route (`GET /`) to routes/web.php.';
        } elseif ($this->replaceWelcomeStub($files, $path, $content)) {
            $content = (string) $files->get($path);
            $this->done[] = 'Replaced the default Laravel welcome route (`GET /`) with the storefront home route.';
        } else {
            $this->done[] = 'routes/web.php already defines `GET /` — left untouched.';
        }

        if (! str_contains($content, 'ThemeBuilder::routes()')) {
            $block .= "\n// theme-builder: visual editor + save/publish endpoints.\n"
                ."// Wrap in YOUR auth/tenant middleware before going live.\n"
                ."\\SoftLand\\ThemeBuilder\\Facades\\ThemeBuilder::routes();\n";
            $this->done[] = 'Registered the builder routes (ThemeBuilder::routes()) in routes/web.php.';
        }

        if ($block !== '') {
            $files->append($path, "\n".$block);
        }
    }

    /**
     * Replace the stock Laravel welcome route (`Route::get('/', fn () =>
     * view('welcome'))` or the closure equivalent) with the storefront home
     * route. Anything else on `/` is real host content and is left alone.
     */
    private function replaceWelcomeStub(Filesystem $files, string $path, string $content): bool
    {
        if (! str_contains($content, "view('welcome')") && ! str_contains($content, 'view("welcome")')) {
            return false;
        }

        $replacement = "// theme-builder: render the active theme's home blocks.\n"
            ."Route::get('/', [\\SoftLand\\ThemeBuilder\\Http\\Controllers\\StorefrontController::class, 'home']);";

        $updated = preg_replace(
            "#Route::get\(\s*['\"]/['\"]\s*,\s*(?:fn\s*\([^)]*\)\s*=>\s*view\(\s*['\"]welcome['\"]\s*\)|function\s*\([^)]*\)\s*\{[^}]*?view\(\s*['\"]welcome['\"]\s*\)[^}]*?\})\s*;?#s",
            $replacement,
            $content,
            1,
            $count,
        );

        if (! is_string($updated) || $count === 0) {
            return false;
        }

        $files->put($path, $updated);

        return true;
    }

    // ─── Step 4f: vite ────────────────────────────────────────────────────

    private function wireVite(Filesystem $files): void
    {
        $path = null;

        foreach (['vite.config.js', 'vite.config.ts', 'vite.config.mjs'] as $candidate) {
            if ($files->exists(base_path($candidate))) {
                $path = base_path($candidate);
                break;
            }
        }

        if ($path === null) {
            $this->todo[] = 'No vite.config found — add the builder entries to your bundler manually (see README → Wire the frontend build).';

            return;
        }

        $content = (string) $files->get($path);

        $entries = [
            "'resources/css/storefront.css'",
            "'resources/css/builder.css'",
            "'".self::CORE_VENDOR_PATH."/resources/js/builder/main.tsx'",
        ];

        $missing = array_filter($entries, fn ($entry) => ! str_contains($content, trim($entry, "'")));

        if ($missing === []) {
            $this->done[] = 'vite.config already wires storefront.css / builder.css / the editor entry.';

            return;
        }

        // Insert missing entries right after `input: [`.
        if (preg_match('/input:\s*\[/', $content, $m, PREG_OFFSET_CAPTURE)) {
            $at = (int) $m[0][1] + strlen((string) $m[0][0]);
            $inject = "\n".implode('', array_map(
                fn ($entry) => str_repeat(' ', 8).$entry.',',
                $missing
            ));
            $content = substr_replace($content, $inject, $at, 0);
            $files->put($path, $content);
            $this->done[] = 'Added '.implode(', ', $missing).' to vite.config inputs.';

            return;
        }

        $this->todo[] = 'vite.config has no `input: [...]` array — add these entries manually: '.implode(', ', $entries).'.';
    }

    // ─── Step 4g: tailwind source ─────────────────────────────────────────

    private function wireTailwindSource(Filesystem $files): void
    {
        $css = base_path('resources/css/storefront.css');

        if (! $files->exists($css)) {
            $this->todo[] = 'resources/css/storefront.css not found — add the Tailwind @source line after publishing assets.';

            return;
        }

        $content = (string) $files->get($css);

        // The published file already carries @source lines for its own views;
        // only skip when the package views are already referenced.
        if (str_contains($content, 'theme-builder')) {
            $this->done[] = 'storefront.css already references the theme-builder views.';

            return;
        }

        // `@source` is Tailwind v4 syntax — v3 hosts use tailwind.config content.
        if (str_contains($content, '@tailwind') || $this->isTailwindV3($files)) {
            $this->todo[] = 'Tailwind v3 detected — add `vendor/softlandtech/theme-builder/resources/views/**` to your tailwind.config `content` array.';

            return;
        }

        $views = realpath(dirname(__DIR__, 2).'/resources/views');

        if ($views === false) {
            $this->todo[] = 'Could not resolve the package views path for the Tailwind @source line.';

            return;
        }

        // A path relative to the CSS file stays valid when the project moves
        // machines; an absolute realpath would not.
        $relative = $this->relativePath(dirname((string) realpath($css)), $views);

        $files->append($css, "\n@source '{$relative}/**/*.blade.php';\n");
        $this->done[] = 'Appended the Tailwind @source line to resources/css/storefront.css.';
    }

    private function isTailwindV3(Filesystem $files): bool
    {
        $path = base_path('package.json');

        if (! $files->exists($path)) {
            return false;
        }

        $data = json_decode((string) $files->get($path), true);
        $deps = array_merge((array) ($data['dependencies'] ?? []), (array) ($data['devDependencies'] ?? []));
        $version = (string) ($deps['tailwindcss'] ?? '');

        return (bool) preg_match('/\b3(\.|$)/', $version);
    }

    /**
     * Relative path from $from (directory) to $to (file or directory),
     * using forward slashes for CSS/composer contexts.
     */
    private function relativePath(string $from, string $to): string
    {
        $from = str_replace('\\', '/', $from);
        $to = str_replace('\\', '/', $to);

        $fromParts = array_values(array_filter(explode('/', rtrim($from, '/'))));
        $toParts = array_values(array_filter(explode('/', rtrim($to, '/'))));

        while ($fromParts !== [] && $toParts !== [] && $fromParts[0] === $toParts[0]) {
            array_shift($fromParts);
            array_shift($toParts);
        }

        $relative = str_repeat('../', count($fromParts)).implode('/', $toParts);

        return $relative === '' ? '.' : $relative;
    }

    // ─── Step 4h: npm deps ────────────────────────────────────────────────

    private function checkNpmDeps(Filesystem $files): void
    {
        $path = base_path('package.json');

        if (! $files->exists($path)) {
            $this->todo[] = 'No package.json — install the editor deps manually: react, react-dom, @dnd-kit/core, @dnd-kit/sortable, @dnd-kit/utilities.';

            return;
        }

        $data = json_decode((string) $files->get($path), true);
        $deps = array_merge((array) ($data['dependencies'] ?? []), (array) ($data['devDependencies'] ?? []));

        $required = ['react', 'react-dom', '@dnd-kit/core', '@dnd-kit/sortable', '@dnd-kit/utilities'];
        $missing = array_values(array_filter($required, fn ($dep) => ! isset($deps[$dep])));

        if ($missing !== []) {
            $this->todo[] = 'Install editor npm deps: npm install '.implode(' ', $missing);
        } else {
            $this->done[] = 'Editor npm deps present.';
        }

        if (! isset($deps['@vitejs/plugin-react'])) {
            $this->todo[] = 'Optional: npm install -D @vitejs/plugin-react (the editor entry is TSX).';
        }

        $this->todo[] = 'Run `npm install && npm run build` (or `npm run dev`).';
    }

    // ─── Step 4i: config ──────────────────────────────────────────────────

    private function writeConfig(Filesystem $files, ThemePackageManager $themePackages): void
    {
        $path = config_path('theme-builder.php');

        if (! $files->exists($path)) {
            $this->todo[] = 'config/theme-builder.php not published — set site_type + pages manually.';

            return;
        }

        $content = (string) $files->get($path);

        // site_type (single-line value; any trailing comment is consumed).
        $content = preg_replace(
            "/('site_type'\s*=>\s*).*$/m",
            "\$1'{$this->siteType}',",
            $content,
            1,
        ) ?? $content;

        // pages: union of chosen packages' pages (single-line literal),
        // falling back to this site type's starter pages so an LMS never
        // ends up with the ecommerce trio just because no package installed.
        $pages = [];

        foreach ($this->chosenThemeSlugs as $slug) {
            $package = $themePackages->package($slug);
            $pages = array_merge($pages, $package?->pages() ?? []);
        }

        if ($pages === []) {
            $pages = SiteProfile::pagesForType($this->siteType);
        }

        if ($pages !== []) {
            $literal = str_replace('\\', '\\\\', $this->phpValue($pages));
            $content = preg_replace(
                "/('pages'\s*=>\s*).*$/m",
                '$1'.$literal.',',
                $content,
                1,
            ) ?? $content;
        }

        $files->put($path, $content);
        $this->done[] = "Recorded site_type=`{$this->siteType}`".($pages !== [] ? ' + pages ('.implode(', ', array_keys($pages)).')' : '').' in config/theme-builder.php.';
    }

    private function setCurrency(): void
    {
        $currency = strtoupper(trim((string) ($this->option('currency') ?? '')));

        if ($currency === '') {
            return;
        }

        $path = config_path('theme-builder.php');

        if (! is_file($path)) {
            return;
        }

        $content = (string) file_get_contents($path);
        $content = preg_replace(
            "/('currency'\s*=>\s*)([^,]+),/",
            "\$1'{$currency}',",
            $content,
            1,
        ) ?? $content;

        file_put_contents($path, $content);
        $this->done[] = "Currency set to {$currency}.";
    }

    // ─── Step 6: AI hand-off ──────────────────────────────────────────────

    private function offerAiPrompt(Filesystem $files): void
    {
        if ($this->input->isInteractive()) {
            $want = $this->confirm('Finish the remaining host-specific integration using an AI agent? (prints a ready-to-paste prompt)', true);
        } else {
            $want = true;
        }

        if (! $want) {
            return;
        }

        $prompt = $this->buildAiPrompt();
        $file = base_path('theme-builder-ai-prompt.md');
        $files->put($file, $prompt);

        $this->newLine();
        $this->components->info('AI completion prompt (also saved to '.$file.'):');
        $this->newLine();
        $this->line($prompt);
    }

    private function buildAiPrompt(): string
    {
        $profile = $this->profile?->toArray() ?? ['type' => $this->siteType, 'confidence' => null, 'evidence' => [], 'features' => []];
        $themes = $this->chosenThemeSlugs !== [] ? implode(', ', $this->chosenThemeSlugs) : 'default (bundled)';
        $repos = $this->clonedRepos !== []
            ? implode('; ', array_map(fn ($slug, $repo) => "{$slug} ← {$repo}", array_keys($this->clonedRepos), $this->clonedRepos))
            : 'none cloned (all themes were already on disk)';
        $done = $this->done !== [] ? implode("\n", array_map(fn ($l) => '- [x] '.$l, $this->done)) : '- (nothing)';
        $todo = $this->todo !== [] ? implode("\n", array_map(fn ($l) => '- [ ] '.$l, $this->todo)) : '- (none)';
        $features = implode(', ', array_keys($this->features)) ?: 'none';
        $typeLabel = $this->siteTypeLabel($this->siteType);
        $pageGuidance = $this->pageTemplateGuidance();

        return <<<MD
        # Task: finish integrating softlandtech/theme-builder into this Laravel app

        You are an expert Laravel engineer. The visual theme builder package
        `softlandtech/theme-builder` has just been installed into this app by
        `php artisan theme-builder:install`. Everything the installer could do
        automatically is done. Your job is the host-specific remainder. Work
        through the checklist at the end IN ORDER, verifying each step before
        moving on. Do not reinstall what already works.

        ## What the package is

        A drag-and-drop React theme editor (`/builder/themes/{id}/edit`) plus a
        server-side Blade block renderer for the storefront. Themes are JSON
        block trees stored in the `themes` table (with draft overlay + publish
        flow). The package is domain-agnostic: it never assumes models or
        tables — the host feeds it data through interfaces.

        ## Scanned site profile (grep-based detection, no AI ran)

        - Site type: **{$this->siteType}** ({$typeLabel})
        - Detection evidence: {$this->evidenceLine()}
        - Existing features the merchant confirmed: {$features}

        ## Installer choices

        - Theme packages installed/seeded: **{$themes}**
        - Theme clone sources: {$repos}
        - Site type recorded in config: `theme-builder.site_type`
        - Pages editable in the builder: see `theme-builder.pages` in config/theme-builder.php

        ## Already done by the installer

        {$done}

        ## Still to do

        {$todo}

        ## Your checklist

        1. **Frontend build** — ensure `npm install` passes and `npm run build`
           compiles the three entries (`resources/css/storefront.css`,
           `resources/css/builder.css`,
           `vendor/softlandtech/theme-builder/resources/js/builder/main.tsx`).
           If Vite cannot resolve the vendor entry, symlink path-repos need
           `resolve: { preserveSymlinks: true }` in vite.config.

        2. **Protect the editor** — `ThemeBuilder::routes()` is currently
           registered in `routes/web.php` with only `web` middleware. Wrap it
           in the app's auth/admin middleware (e.g.
           `Route::middleware(['web', 'auth'])->group(fn () => ThemeBuilder::routes());`).
           Single-tenant apps can keep it simple; multi-tenant apps must also
           resolve the tenant.

        3. **Feed the blocks data** — implement
           `SoftLand\ThemeBuilder\Contracts\StorefrontDataProvider` and bind it
           (`\$this->app->bind(StorefrontDataProvider::class, MyProvider::class)`).
           Methods: products(array \$props): array, productsForTabs, categories,
           categoryLinks, featuredProduct, blogPost, blogCategories, blogPosts,
           searchProducts, searchCategories, searchBlogPosts. ALL return
           normalized arrays (ProductView: id, name, slug, url, price(cents),
           comparePrice, currency, image, subtitle, rating, badge, onSale,
           availability — never Eloquent models). For an LMS map courses →
           products; for a CMS map posts → products where grids are used.
           Without a provider, static blocks render and data blocks stay empty.

        4. **Media picker (optional)** — implement
           `SoftLand\ThemeBuilder\Contracts\MediaProvider` (list/store/url) and
           bind it to power image uploads in the editor.

        5. **Multi-tenant only** — implement
           `SoftLand\ThemeBuilder\Contracts\Store` on the tenant model
           (getThemeBuilderTenantKey/StoreName/CurrencyCode/Locale) and set
           `theme-builder.store_resolver` to a closure returning the current
           tenant. Leave null for single-tenant apps.

        6. **Page templates** — the merchant confirmed these site features:
           {$features}. {$pageGuidance}

        7. **Seed a first draft for the chosen theme packages (if empty)** —
           presets are already seeded by the installer; skip unless the themes
           table lacks them. Useful commands:
           `php artisan theme-builder:seed-preset {slug}`,
           `php artisan theme-builder:make-theme {slug} --from={slug}` (copy + customize),
           `php artisan theme-builder:validate {slug}`.

        8. **Verify end to end** — `GET /` renders the storefront with the
           seeded theme; `GET /builder/themes/{id}/edit` opens the editor;
           drag a block, wait for "Saved", publish, and confirm it appears on
           `/`. Fix anything that breaks.

        ## Package quick facts

        - Config: `config/theme-builder.php` (store_resolver, active_theme_resolver,
          db.{themes_table,revisions_table,tenant_column}, blocks_view_prefix,
          routes.{enabled,path,name,middleware}, routes_map, site_type, pages).
        - Models: `Theme` (blocks/settings/header/footer/templates JSON columns,
          draft overlay, publish flow), `ThemeRevision` (versions).
        - Commands: install, seed-preset, make-theme, validate.
        - Helpers: `tb_bi()` ({ar,en} → locale), `tb_money(cents, currency)`,
          `tb_route(name, params)` (routes_map).
        - Block registry: PHP `BlockRegistry` + `resources/js/builder/config/blocks.tsx`
          are the two lockstep sources of truth — a prop missing from the PHP
          defaults is silently dropped on save.

        Report each completed checklist item and any deviations from this plan.
        MD;
    }

    private function evidenceLine(): string
    {
        $evidence = $this->profile?->evidence ?? [];

        return $evidence !== [] ? implode('; ', array_slice($evidence, 0, 5)) : 'not scanned (type forced)';
    }

    /**
     * Per-site-type guidance for wiring page templates and routes_map.
     */
    private function pageTemplateGuidance(): string
    {
        $example = match ($this->siteType) {
            SiteProfile::TYPE_LMS => 'course → /courses/{slug}, lesson → /courses/{course}/{slug}',
            SiteProfile::TYPE_CMS => 'post → /blog/{slug}, page → /pages/{slug}',
            SiteProfile::TYPE_PERSONAL, SiteProfile::TYPE_EMPTY => 'about → /about, services → /services, contact → /contact',
            default => 'product → /products/{slug}, cart → /cart, checkout → /checkout',
        };

        $slot = match ($this->siteType) {
            SiteProfile::TYPE_LMS => 'course',
            SiteProfile::TYPE_CMS => 'post',
            SiteProfile::TYPE_PERSONAL, SiteProfile::TYPE_EMPTY => 'services',
            default => 'cart',
        };

        return "For each feature with a real page in this app ({$example}), make sure "
            .'`theme-builder.routes_map` in config/theme-builder.php points at the real URLs, '
            ."and render the merchant's before/after sections around the app's own content with "
            ."`{!! \\SoftLand\\ThemeBuilder\\Facades\\ThemeBuilder::renderTemplate('{$slot}', 'before') !!}` "
            ."(and `'after'`). Full marketing pages (landing, services, about) can be rendered "
            ."wholesale via `StorefrontController::page('services')`.";
    }

    // ─── helpers ──────────────────────────────────────────────────────────

    /**
     * Compact single-line PHP array-literal dumper for config writes.
     */
    private function phpValue(mixed $value): string
    {
        if (is_array($value)) {
            $parts = [];

            foreach ($value as $key => $item) {
                $parts[] = (is_string($key) ? "'{$key}' => " : '').$this->phpValue($item);
            }

            return '['.implode(', ', $parts).']';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return "'".str_replace("'", "\\'", (string) $value)."'";
    }
}
