<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Support;

use Illuminate\Filesystem\Filesystem;

/**
 * Greps a host Laravel app and guesses what kind of site it is — ecommerce,
 * LMS, CMS/blog, personal/portfolio, or an empty new project. No AI: just
 * composer.json packages, model filenames, migration names/content, route
 * definitions, and view folders.
 *
 * Usage: `(new SiteDetector)->detect(base_path())` (or omit the path).
 */
class SiteDetector
{
    /** Model basenames that signal each site type (basename => [type => points]). */
    private const array MODEL_SIGNALS = [
        // Ecommerce.
        'Product' => ['ecommerce' => 4],
        'Order' => ['ecommerce' => 3],
        'Cart' => ['ecommerce' => 3],
        'CartItem' => ['ecommerce' => 2],
        'CartItems' => ['ecommerce' => 2],
        'Variant' => ['ecommerce' => 2],
        'ProductVariant' => ['ecommerce' => 3],
        'Collection' => ['ecommerce' => 1],
        'Coupon' => ['ecommerce' => 2],
        'Discount' => ['ecommerce' => 1],
        'Brand' => ['ecommerce' => 1],
        'Review' => ['ecommerce' => 1],
        'Shipment' => ['ecommerce' => 3],
        'Address' => ['ecommerce' => 1],
        'Category' => ['ecommerce' => 1, 'cms' => 1, 'lms' => 1],

        // LMS.
        'Course' => ['lms' => 4],
        'Lesson' => ['lms' => 4],
        'Enrollment' => ['lms' => 4],
        'Enrolment' => ['lms' => 4],
        'Section' => ['lms' => 2],
        'Quiz' => ['lms' => 3],
        'Question' => ['lms' => 1],
        'Assignment' => ['lms' => 2],
        'Instructor' => ['lms' => 2],
        'Teacher' => ['lms' => 2],
        'Student' => ['lms' => 2],
        'Curriculum' => ['lms' => 2],
        'Certificate' => ['lms' => 1],

        // CMS / blog.
        'Post' => ['cms' => 3],
        'Article' => ['cms' => 3],
        'Page' => ['cms' => 2],
        'Tag' => ['cms' => 1],
        'Comment' => ['cms' => 1],
        'Menu' => ['cms' => 1],
        'MenuItem' => ['cms' => 1],

        // Personal / portfolio / brochure.
        'Portfolio' => ['personal' => 4],
        'Project' => ['personal' => 3],
        'Resume' => ['personal' => 3],
        'Skill' => ['personal' => 2],
        'Experience' => ['personal' => 2],
        'Service' => ['personal' => 2],
        'TeamMember' => ['personal' => 2],
        'Testimonial' => ['personal' => 1],
    ];

    /** composer package names that signal a site type. */
    private const array PACKAGE_SIGNALS = [
        'lunarphp/lunar' => ['ecommerce' => 6],
        'lunarphp/core' => ['ecommerce' => 6],
        'bagisto/bagisto' => ['ecommerce' => 6],
        'vanilo/framework' => ['ecommerce' => 5],
        'darryldecode/cart' => ['ecommerce' => 4],
        'bumbummen99/shoppingcart' => ['ecommerce' => 4],
        'hardevine/shoppingcart' => ['ecommerce' => 4],
        'ecommerce/common' => ['ecommerce' => 3],
        'doublethreedigital/dummy-commerce' => ['ecommerce' => 4],
        'mollie/laravel-mollie' => ['ecommerce' => 2],
        'srmklive/paypal' => ['ecommerce' => 2],
        'lyranetwork/payzone-laravel' => ['ecommerce' => 2],
        'maatwebsite/excel' => ['cms' => 1],
        'spatie/laravel-sitemap' => ['cms' => 1],
        'spatie/laravel-responsecache' => ['cms' => 1],
        'qa-soft/el_mohtaraf' => ['lms' => 2],
        // Payments shared by ecommerce + LMS (both sell things).
        'stripe/stripe-php' => ['ecommerce' => 1, 'lms' => 1],
        'paypal/rest-api-sdk-php' => ['ecommerce' => 1, 'lms' => 1],
    ];

    /** Route/view keywords: needle => features + type points. */
    private const array ROUTE_SIGNALS = [
        'checkout' => ['features' => ['has_checkout'], 'types' => ['ecommerce' => 4]],
        'cart' => ['features' => ['has_cart'], 'types' => ['ecommerce' => 3]],
        'products' => ['features' => ['has_products'], 'types' => ['ecommerce' => 2]],
        'shop' => ['features' => ['has_products'], 'types' => ['ecommerce' => 2]],
        'orders' => ['features' => ['has_orders'], 'types' => ['ecommerce' => 2]],
        'wishlist' => ['features' => ['has_wishlist'], 'types' => ['ecommerce' => 2]],
        'coupons' => ['types' => ['ecommerce' => 1]],
        'courses' => ['features' => ['has_courses'], 'types' => ['lms' => 4]],
        'lessons' => ['features' => ['has_lessons'], 'types' => ['lms' => 3]],
        'enroll' => ['features' => ['has_enrollments'], 'types' => ['lms' => 3]],
        'quiz' => ['types' => ['lms' => 2]],
        'instructor' => ['types' => ['lms' => 1]],
        'curriculum' => ['types' => ['lms' => 1]],
        'blog' => ['features' => ['has_blog'], 'types' => ['cms' => 3]],
        'posts' => ['features' => ['has_blog'], 'types' => ['cms' => 2]],
        'articles' => ['features' => ['has_blog'], 'types' => ['cms' => 2]],
        'pages' => ['features' => ['has_pages'], 'types' => ['cms' => 1]],
        'portfolio' => ['features' => ['has_portfolio'], 'types' => ['personal' => 4]],
        'projects' => ['features' => ['has_projects'], 'types' => ['personal' => 3]],
        'services' => ['features' => ['has_services'], 'types' => ['personal' => 2]],
        'about' => ['types' => ['personal' => 1, 'cms' => 1]],
        'resume' => ['types' => ['personal' => 3]],
        'contact' => ['features' => ['has_contact'], 'types' => ['personal' => 1]],
        'search' => ['features' => ['has_search']],
        'login' => ['features' => ['has_account']],
        'register' => ['features' => ['has_account']],
        'dashboard' => ['features' => ['has_account']],
    ];

    /** Default Laravel skeleton migrations (anything beyond these = real domain work). */
    private const array SKELETON_MIGRATION_PATTERNS = [
        '/0001_01_01_000000_create_users_table/',
        '/0001_01_01_000001_create_cache_table/',
        '/0001_01_01_000002_create_jobs_table/',
    ];

    public function __construct(
        private readonly Filesystem $files = new Filesystem,
    ) {}

    /**
     * Scan a Laravel app root and classify it.
     */
    public function detect(?string $basePath = null): SiteProfile
    {
        $basePath ??= base_path();
        $basePath = rtrim($basePath, '/');

        $scores = [
            SiteProfile::TYPE_ECOMMERCE => 0,
            SiteProfile::TYPE_LMS => 0,
            SiteProfile::TYPE_CMS => 0,
            SiteProfile::TYPE_PERSONAL => 0,
        ];
        $features = [];
        $evidence = [];
        $domainSignals = 0;

        $addClassification = function (array $typePoints, string $because) use (&$scores, &$evidence, &$domainSignals): void {
            $touched = false;

            foreach ($typePoints as $type => $points) {
                if ($points <= 0 || ! isset($scores[$type])) {
                    continue;
                }
                $scores[$type] += $points;
                $touched = true;
            }

            if ($touched) {
                $evidence[] = $because;
                $domainSignals++;
            }
        };

        $addFeature = function (array $featureKeys, string $because) use (&$features, &$evidence): void {
            $added = false;

            foreach ($featureKeys as $feature) {
                if (empty($features[$feature])) {
                    $features[$feature] = true;
                    $added = true;
                }
            }

            if ($added) {
                $evidence[] = $because;
            }
        };

        // 1. Composer packages.
        $packages = $this->composerPackages($basePath);

        foreach ($packages as $package => $version) {
            if (isset(self::PACKAGE_SIGNALS[$package])) {
                $addClassification(self::PACKAGE_SIGNALS[$package], "composer package `{$package}`");
            }
        }

        // 2. Model basenames (app/Models + a shallow app/ look for non-standard apps).
        $models = $this->modelBasenames($basePath);

        foreach ($models as $model) {
            if (isset(self::MODEL_SIGNALS[$model])) {
                $addClassification(self::MODEL_SIGNALS[$model], "app model `{$model}`");

                $featureMap = [
                    'Product' => 'has_products',
                    'Cart' => 'has_cart',
                    'Order' => 'has_orders',
                    'Course' => 'has_courses',
                    'Lesson' => 'has_lessons',
                    'Enrollment' => 'has_enrollments',
                    'Post' => 'has_blog',
                    'Article' => 'has_blog',
                    'Page' => 'has_pages',
                    'Portfolio' => 'has_portfolio',
                    'Project' => 'has_projects',
                    'Service' => 'has_services',
                    'Category' => 'has_categories',
                ];
                $feature = $featureMap[$model] ?? null;

                if ($feature !== null) {
                    $features[$feature] = true;
                }
            }
        }

        // 3. Migration names + content (works even when models live in a package).
        [$migrationTypes, $migrationFeatures, $migrationEvidence] = $this->scanMigrations($basePath);

        foreach ($migrationTypes as $because => $typePoints) {
            $addClassification($typePoints, $because);
        }

        foreach ($migrationFeatures as $feature => $because) {
            $features[$feature] = true;
            $evidence[] = $because;
        }

        // 4. Route definitions + view folders.
        [$routeFeatures, $routeTypes] = $this->scanRoutesAndViews($basePath);

        foreach ($routeTypes as $because => $typePoints) {
            $addClassification($typePoints, $because);
        }

        foreach ($routeFeatures as $feature => $because) {
            $addFeature([$feature], $because);
        }

        // 5. Decide the type.
        arsort($scores);
        $bestType = array_key_first($scores);
        $bestScore = $scores[$bestType];

        $type = SiteProfile::TYPE_UNKNOWN;

        if ($bestScore >= 4) {
            $type = $bestType;
        } elseif ($this->looksLikeFreshProject($basePath)) {
            $type = SiteProfile::TYPE_EMPTY;
        } elseif ($bestScore > 0) {
            $type = $bestType;
        }

        if ($type === SiteProfile::TYPE_UNKNOWN && $this->looksLikeFreshProject($basePath)) {
            $type = SiteProfile::TYPE_EMPTY;
        }

        return new SiteProfile(
            type: $type,
            scores: $scores,
            evidence: array_values(array_unique($evidence)),
            features: $features,
        );
    }

    /**
     * Fresh Laravel skeleton: only User (+ maybe Admin) models, no migrations
     * beyond the shipped ones, and a routes/web.php that still returns the
     * welcome view (or nothing else).
     */
    private function looksLikeFreshProject(string $basePath): bool
    {
        $models = $this->modelBasenames($basePath);

        if ($models !== [] && array_diff($models, ['User', 'Admin']) !== []) {
            return false;
        }

        $migrations = $this->migrationFiles($basePath);
        $domainMigrations = array_filter(
            $migrations,
            fn (string $name) => collect(self::SKELETON_MIGRATION_PATTERNS)->every(
                fn (string $pattern) => ! preg_match($pattern, $name)
            ),
        );

        return $domainMigrations === [];
    }

    /**
     * @return array<string, string>
     */
    private function composerPackages(string $basePath): array
    {
        $path = "{$basePath}/composer.json";

        if (! $this->files->exists($path)) {
            return [];
        }

        $data = json_decode((string) $this->files->get($path), true);

        if (! is_array($data)) {
            return [];
        }

        return array_merge(
            (array) ($data['require'] ?? []),
            (array) ($data['require-dev'] ?? []),
        );
    }

    /**
     * @return array<int, string>
     */
    private function modelBasenames(string $basePath): array
    {
        $names = [];

        // app/Models is the conventional home; a shallow app/ scan catches
        // non-standard apps (both scanned flat, without recursion).
        foreach (["{$basePath}/app/Models", "{$basePath}/app"] as $dir) {
            if (! $this->files->isDirectory($dir)) {
                continue;
            }

            foreach ($this->files->files($dir) as $file) {
                if ($file->getExtension() === 'php') {
                    $names[] = $file->getBasename('.php');
                }
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @return array<int, string>
     */
    private function migrationFiles(string $basePath): array
    {
        $dir = "{$basePath}/database/migrations";

        if (! $this->files->isDirectory($dir)) {
            return [];
        }

        return collect($this->files->files($dir))
            ->map(fn ($file) => $file->getBasename('.php'))
            ->values()
            ->all();
    }

    /**
     * Migration filenames are extremely reliable ("create_products_table").
     *
     * @return array{0: array<string, array<string, int>>, 1: array<string, string>, 2: array<int, string>}
     */
    private function scanMigrations(string $basePath): array
    {
        $typePoints = [];
        $features = [];
        $tableMap = [
            'products' => [['ecommerce' => 4], ['has_products']],
            'product_variants' => [['ecommerce' => 2], []],
            'carts' => [['ecommerce' => 3], ['has_cart']],
            'cart_items' => [['ecommerce' => 2], []],
            'orders' => [['ecommerce' => 3], ['has_orders']],
            'order_items' => [['ecommerce' => 2], []],
            'coupons' => [['ecommerce' => 2], []],
            'discounts' => [['ecommerce' => 1], []],
            'brands' => [['ecommerce' => 1], []],
            'collections' => [['ecommerce' => 1], []],
            'courses' => [['lms' => 4], ['has_courses']],
            'lessons' => [['lms' => 4], ['has_lessons']],
            'enrollments' => [['lms' => 4], ['has_enrollments']],
            'quizzes' => [['lms' => 3], []],
            'quiz_questions' => [['lms' => 2], []],
            'assignments' => [['lms' => 2], []],
            'certificates' => [['lms' => 1], []],
            'instructors' => [['lms' => 1], []],
            'posts' => [['cms' => 3], ['has_blog']],
            'articles' => [['cms' => 3], ['has_blog']],
            'tags' => [['cms' => 1], []],
            'comments' => [['cms' => 1], []],
            'portfolios' => [['personal' => 4], ['has_portfolio']],
            'projects' => [['personal' => 3], ['has_projects']],
            'services' => [['personal' => 2], ['has_services']],
            'resumes' => [['personal' => 3], []],
            'skills' => [['personal' => 2], []],
            'team_members' => [['personal' => 2], []],
            'testimonials' => [['personal' => 1], []],
        ];

        foreach ($this->migrationFiles($basePath) as $migration) {
            $snake = str_replace('-', '_', $migration);

            foreach ($tableMap as $table => [$points, $featureKeys]) {
                if (str_contains($snake, "_{$table}_table") || str_contains($snake, "create_{$table}")) {
                    $typePoints["migration `{$migration}`"] = $points;

                    foreach ($featureKeys as $feature) {
                        $features[$feature] = "migration `{$migration}`";
                    }

                    break;
                }
            }
        }

        return [$typePoints, $features, []];
    }

    /**
     * @return array{0: array<string, string>, 1: array<string, array<string, int>>}
     */
    private function scanRoutesAndViews(string $basePath): array
    {
        $haystacks = [];

        foreach (["{$basePath}/routes"] as $dir) {
            if (! $this->files->isDirectory($dir)) {
                continue;
            }

            foreach ($this->files->allFiles($dir) as $file) {
                if (in_array($file->getExtension(), ['php', 'js', 'ts'], true)) {
                    $haystacks[$file->getPathname()] = strtolower((string) $this->files->get($file->getPathname()));
                }
            }
        }

        // View folders are a second signal (resources/views/{cart,checkout,shop,...}).
        $viewDirs = [];
        $viewsDir = "{$basePath}/resources/views";

        if ($this->files->isDirectory($viewsDir)) {
            foreach ($this->files->directories($viewsDir) as $dir) {
                $viewDirs[basename($dir)] = true;
            }
        }

        $features = [];
        $typePoints = [];

        foreach (self::ROUTE_SIGNALS as $needle => $signal) {
            $foundIn = null;

            foreach ($haystacks as $path => $content) {
                if (str_contains($content, $needle)) {
                    $foundIn = 'routes/'.basename($path);
                    break;
                }
            }

            if ($foundIn === null && isset($viewDirs[$needle])) {
                $foundIn = "views/{$needle}/";
            }

            if ($foundIn === null) {
                continue;
            }

            foreach ($signal['features'] ?? [] as $feature) {
                $features[$feature] = "`{$needle}` found in {$foundIn}";
            }

            foreach ($signal['types'] ?? [] as $type => $points) {
                $typePoints["`{$needle}` in {$foundIn}"][$type] = $points;
            }
        }

        return [$features, $typePoints];
    }
}
