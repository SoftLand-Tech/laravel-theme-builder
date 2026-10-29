<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use SoftLand\ThemeBuilder\Enums\ThemeStatus;
use SoftLand\ThemeBuilder\Facades\ThemeBuilder;
use SoftLand\ThemeBuilder\Models\Theme;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['theme-builder.pages' => [
        'course' => ['label' => ['en' => 'Course', 'ar' => 'دورة']],
        'lesson' => ['label' => 'Lesson'],
    ]]);

    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

    $this->theme = Theme::create([
        'name' => ['ar' => 'أكاديمية', 'en' => 'Academy'],
        'status' => ThemeStatus::Published,
        'blocks' => [],
        'settings' => Theme::defaultSettings(),
        'templates' => [
            'course' => [
                'before' => [['type' => 'Hero', 'props' => ['title' => ['ar' => 'دورة', 'en' => 'A course']]]],
                'after' => [],
            ],
        ],
        'published_at' => now(),
    ]);

    config(['theme-builder.active_theme_resolver' => fn (): ?Theme => $this->theme]);
});

it('scaffolds templates for every configured page key', function (): void {
    $templates = $this->theme->resolvedTemplates();

    expect(array_keys($templates))->toBe(['course', 'lesson'])
        ->and($templates['course']['before'])->toHaveCount(1)
        ->and($templates['lesson'])->toBe(['before' => [], 'after' => []]);
});

it('normalizes arbitrary page template keys on save', function (): void {
    $response = $this->postJson(route('theme-builder.themes.save', $this->theme), [
        'blocks' => [],
        'templates' => [
            'lesson' => [
                'before' => [
                    ['type' => 'FaqAccordion', 'props' => []],
                    ['type' => 'NotABlock', 'props' => []],
                ],
                'after' => [],
            ],
        ],
    ]);

    $response->assertOk();

    $this->theme->refresh();

    // Published themes write into the draft overlay until promoted.
    $lesson = $this->theme->builderResolvedTemplates()['lesson'];

    expect($lesson['before'])->toHaveCount(1)
        ->and($lesson['before'][0]['type'])->toBe('FaqAccordion');
});

it('renders a template slot for the active theme', function (): void {
    $html = ThemeBuilder::renderTemplate('course', 'before');

    expect($html)->toContain('A course');
});

it('renders an empty string for unknown pages or slots', function (): void {
    expect(ThemeBuilder::renderTemplate('checkout'))->toBe('')
        ->and(ThemeBuilder::renderTemplate('course', 'sideways'))->toBe('')
        ->and(ThemeBuilder::renderTemplate('lesson'))->toBe('');
});

it('exposes the configured pages on the manager and facade', function (): void {
    expect(array_keys(ThemeBuilder::pages()))->toBe(['course', 'lesson']);
});
