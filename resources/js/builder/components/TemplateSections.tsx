import React from 'react';
import type { ColorScheme } from '../types/settings';
import { Section } from './Section';
import { ThemePreview } from '../themes/ThemePreview';

type WireBlock = { type: string; props: Record<string, unknown> };

interface TemplateSectionsProps {
    blocks?: WireBlock[];
    colorSchemes: ColorScheme[];
    themeSlug?: string;
    locale: string;
}

/**
 * Renders the per-page template blocks (the before/after marketing sections
 * edited in PagesPanel) so the Product and Cart page previews reflect them —
 * the storefront injects the very same blocks around its main content. Each
 * block reuses the home canvas's Section + ThemePreview frame, so spacing,
 * color schemes, and compiled-theme overrides all match the live site.
 */
export function TemplateSections({ blocks, colorSchemes, themeSlug, locale }: TemplateSectionsProps) {
    if (!blocks || blocks.length === 0) return null;

    return (
        <>
            {blocks.map((block, index) => (
                <Section
                    key={`${block.type}-${index}`}
                    sectionPaddingTop={String(block.props.sectionPaddingTop ?? 'medium')}
                    sectionPaddingBottom={String(block.props.sectionPaddingBottom ?? 'medium')}
                    sectionWidth={String(block.props.sectionWidth ?? 'contained')}
                    sectionBackground={String(block.props.sectionBackground ?? '')}
                    sectionTextAlign={String(block.props.sectionTextAlign ?? '')}
                    colorSchemes={colorSchemes}
                >
                    <ThemePreview type={block.type} themeSlug={themeSlug} locale={locale} {...block.props} />
                </Section>
            ))}
        </>
    );
}
