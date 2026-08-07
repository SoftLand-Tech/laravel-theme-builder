<?php

declare(strict_types=1);

/** @var Router $router */

use Illuminate\Routing\Router;
use SoftLand\ThemeBuilder\Http\Controllers\BuilderController;
use SoftLand\ThemeBuilder\Http\Controllers\MediaController;
use SoftLand\ThemeBuilder\Http\Controllers\PickerController;
use SoftLand\ThemeBuilder\Http\Controllers\RevisionController;
use SoftLand\ThemeBuilder\Http\Controllers\ThemePickerController;

$router = app('router');

$router->post('/themes/{preset}/adopt', [ThemePickerController::class, 'adopt'])
    ->name('themes.adopt');

$router->post('/themes/{theme}/activate', [ThemePickerController::class, 'activate'])
    ->name('themes.activate');

$router->group(['prefix' => '/themes/{theme}'], function () use ($router): void {
    $router->get('/edit', [BuilderController::class, 'edit'])->name('themes.edit');

    $router->post('/save', [BuilderController::class, 'save'])->name('themes.save');
    $router->post('/publish', [BuilderController::class, 'publish'])->name('themes.publish');
    $router->post('/promote', [BuilderController::class, 'promote'])->name('themes.promote');

    $router->post('/revisions/{revision}/restore', [BuilderController::class, 'restoreRevision'])
        ->name('themes.restore-revision');
    $router->get('/revisions', [RevisionController::class, 'index'])->name('themes.revisions');

    $router->get('/exit', [BuilderController::class, 'exit'])->name('themes.exit');

    $router->get('/media', [MediaController::class, 'index'])->name('themes.media.index');
    $router->post('/media', [MediaController::class, 'store'])->name('themes.media.store');
});

$router->get('/api/products', [PickerController::class, 'products'])->name('api.products');
$router->get('/api/categories', [PickerController::class, 'categories'])->name('api.categories');
$router->get('/api/blog-posts', [PickerController::class, 'blogPosts'])->name('api.blog-posts');
