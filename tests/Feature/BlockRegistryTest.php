<?php

declare(strict_types=1);

use SoftLand\ThemeBuilder\Builder\BlockRegistry;
use SoftLand\ThemeBuilder\Builder\BlockRenderer;

it('points every registered block at an existing blade component', function (): void {
    $registry = app(BlockRegistry::class);

    foreach ($registry->all() as $definition) {
        expect($definition->bladeComponent)->not->toBeNull()
            ->and($definition->bladeComponent)->toStartWith('theme-builder::');
    }
});

it('resolves every registered block to a renderable view', function (): void {
    $registry = app(BlockRegistry::class);

    foreach ($registry->all() as $definition) {
        // The component name maps to a plain view via the renderer's
        // components. path convention (anonymous components live under
        // resources/views/components/…).
        $viewName = BlockRenderer::viewNameFor($definition->bladeComponent);

        expect($viewName)->not->toBeNull();

        $path = view($viewName)->getPath();

        expect($path)->toBeFile("{$definition->type} should resolve to a view");
    }
});

it('keeps block count and names in sync with the editor registry', function (): void {
    $registry = app(BlockRegistry::class);

    expect($registry->all())->toHaveCount(32)
        ->and($registry->forEditor())->toHaveCount(32);

    foreach ($registry->forEditor() as $entry) {
        expect($entry)->toHaveKeys(['type', 'label', 'category', 'icon'])
            ->and($registry->has($entry['type']))->toBeTrue();
    }
});

it('provides section style defaults to every block', function (): void {
    $registry = app(BlockRegistry::class);

    foreach ($registry->all() as $definition) {
        expect($definition->defaults)->toHaveKeys([
            'sectionPaddingTop',
            'sectionPaddingBottom',
            'sectionBackground',
            'sectionWidth',
            'sectionTextAlign',
            'hidden',
        ]);
    }
});
