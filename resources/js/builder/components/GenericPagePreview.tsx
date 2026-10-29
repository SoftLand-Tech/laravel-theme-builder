import React from 'react';
import type { ColorScheme, ThemeSettings } from '../types/settings';
import { PreviewFrame } from './PreviewFrame';
import { TemplateSections } from './TemplateSections';
import type { TemplatesShape } from './PagesPanel';

interface GenericPagePreviewProps {
    page: string;
    label?: string;
    settings: ThemeSettings;
    locale: string;
    viewport: 'desktop' | 'mobile';
    templates: TemplatesShape;
    colorSchemes: ColorScheme[];
    themeSlug?: string;
}

/**
 * Preview for pages without a bespoke editor mock (course, lesson, service,
 * checkout, landing… — anything a theme package declares): the per-page
 * template sections (before/after) around a labeled stand-in for the host's
 * own page content.
 */
export function GenericPagePreview({
    page,
    label,
    settings,
    locale,
    viewport,
    templates,
    colorSchemes,
    themeSlug,
}: GenericPagePreviewProps) {
    const ar = locale === 'ar';
    const pageTemplates = templates[page] ?? {};
    const heading = label || page.charAt(0).toUpperCase() + page.slice(1);

    return (
        <PreviewFrame settings={settings} locale={locale} viewport={viewport}>
            <TemplateSections
                blocks={pageTemplates.before}
                colorSchemes={colorSchemes}
                themeSlug={themeSlug}
                locale={locale}
            />
            <main className="mx-auto max-w-6xl px-5 py-16 sm:px-8">
                <div className="mb-12 text-center">
                    <p className="mb-2 text-xs uppercase tracking-widest text-[var(--color-muted)]">
                        {ar ? 'صفحة' : 'Page'}
                    </p>
                    <h1 className="text-4xl font-bold text-[var(--color-text)] sm:text-5xl">{heading}</h1>
                </div>

                <div className="rounded-xl border border-dashed border-[var(--color-line)] px-6 py-16 text-center">
                    <p className="text-sm text-[var(--color-muted)]">
                        {ar
                            ? 'محتوى هذه الصفحة يقدّمه تطبيقك — أضف أقسامًا تسويقية قبلها وبعدها.'
                            : 'This page’s main content comes from your app — add marketing sections around it.'}
                    </p>
                </div>
            </main>
            <TemplateSections
                blocks={pageTemplates.after}
                colorSchemes={colorSchemes}
                themeSlug={themeSlug}
                locale={locale}
            />
        </PreviewFrame>
    );
}
