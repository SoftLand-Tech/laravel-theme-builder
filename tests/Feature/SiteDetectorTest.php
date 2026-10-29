<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use SoftLand\ThemeBuilder\Support\SiteDetector;
use SoftLand\ThemeBuilder\Support\SiteProfile;

beforeEach(function (): void {
    $this->files = new Filesystem;
    $this->root = sys_get_temp_dir().'/theme-builder-detector-'.uniqid();
    $this->files->ensureDirectoryExists("{$this->root}/app/Models");
    $this->files->ensureDirectoryExists("{$this->root}/database/migrations");
    $this->files->ensureDirectoryExists("{$this->root}/routes");

    // Laravel skeleton baseline.
    $this->files->put("{$this->root}/composer.json", json_encode(['require' => ['php' => '^8.3', 'laravel/framework' => '^13.0']]));
    $this->files->put("{$this->root}/app/Models/User.php", '<?php class User {}');
    $this->files->put("{$this->root}/routes/web.php", "<?php Route::get('/', fn () => view('welcome'));");
    foreach (['0001_01_01_000000_create_users_table', '0001_01_01_000001_create_cache_table', '0001_01_01_000002_create_jobs_table'] as $migration) {
        $this->files->put("{$this->root}/database/migrations/{$migration}.php", '<?php return new class {};');
    }
});

afterEach(function (): void {
    (new Filesystem)->deleteDirectory($this->root);
});

it('classifies a fresh Laravel skeleton as empty', function (): void {
    $profile = (new SiteDetector)->detect($this->root);

    expect($profile->type)->toBe(SiteProfile::TYPE_EMPTY)
        ->and($profile->suggestedThemeSlugs())->toBe(['personal']);
});

it('detects an ecommerce app from models and routes', function (): void {
    $this->files->put("{$this->root}/app/Models/Product.php", '<?php class Product {}');
    $this->files->put("{$this->root}/app/Models/Order.php", '<?php class Order {}');
    $this->files->put("{$this->root}/routes/web.php", "<?php Route::get('/cart', ...); Route::get('/checkout', ...); Route::get('/products/{slug}', ...);");

    $profile = (new SiteDetector)->detect($this->root);

    expect($profile->type)->toBe(SiteProfile::TYPE_ECOMMERCE)
        ->and($profile->hasFeature('has_cart'))->toBeTrue()
        ->and($profile->hasFeature('has_checkout'))->toBeTrue()
        ->and($profile->hasFeature('has_products'))->toBeTrue()
        ->and($profile->suggestedThemeSlugs())->toBe(['ecommerce']);
});

it('detects an LMS app from course and lesson migrations', function (): void {
    foreach (['create_courses_table', 'create_lessons_table'] as $table) {
        $this->files->put("{$this->root}/database/migrations/2024_01_01_000000_{$table}.php", '<?php return new class {};');
    }

    $profile = (new SiteDetector)->detect($this->root);

    expect($profile->type)->toBe(SiteProfile::TYPE_LMS)
        ->and($profile->hasFeature('has_courses'))->toBeTrue()
        ->and($profile->hasFeature('has_lessons'))->toBeTrue();
});

it('detects a CMS/blog app from a Post model and blog routes', function (): void {
    $this->files->put("{$this->root}/app/Models/Post.php", '<?php class Post {}');
    $this->files->put("{$this->root}/routes/web.php", "<?php Route::get('/blog', ...);");

    $profile = (new SiteDetector)->detect($this->root);

    expect($profile->type)->toBe(SiteProfile::TYPE_CMS)
        ->and($profile->hasFeature('has_blog'))->toBeTrue();
});

it('detects a personal/portfolio app', function (): void {
    $this->files->put("{$this->root}/app/Models/Portfolio.php", '<?php class Portfolio {}');
    $this->files->put("{$this->root}/app/Models/Project.php", '<?php class Project {}');

    $profile = (new SiteDetector)->detect($this->root);

    expect($profile->type)->toBe(SiteProfile::TYPE_PERSONAL)
        ->and($profile->hasFeature('has_portfolio'))->toBeTrue();
});

it('weighs composer ecommerce packages strongly', function (): void {
    $this->files->put("{$this->root}/composer.json", json_encode(['require' => ['lunarphp/lunar' => '^1.0']]));

    $profile = (new SiteDetector)->detect($this->root);

    expect($profile->type)->toBe(SiteProfile::TYPE_ECOMMERCE);
});
