import React from 'react';
import type { ColorScheme, ThemeSettings } from '../types/settings';
import { PreviewFrame } from './PreviewFrame';
import { TemplateSections } from './TemplateSections';
import { CardSettingsProvider, ProductCardPreview, SAMPLE_PRODUCTS } from '../blocks/ProductCardPreview';
import type { TemplatesShape } from './PagesPanel';

interface CollectionPagePreviewProps {
    settings: ThemeSettings;
    locale: string;
    viewport: 'desktop' | 'mobile';
    templates: TemplatesShape;
    colorSchemes: ColorScheme[];
    themeSlug?: string;
}

/**
 * Representative category/collection preview for the editor's Categories page.
 * Mirrors storefront/category.blade.php: eyebrow + title, product grid, and the
 * per-page template blocks (before/after) around the main content.
 */
export function CollectionPagePreview({
    settings,
    locale,
    viewport,
    templates,
    colorSchemes,
    themeSlug,
}: CollectionPagePreviewProps) {
    const ar = locale === 'ar';
    const collectionTemplates = templates.collection ?? {};
    const categoryName = ar ? 'الأزياء الرجالية' : 'Men\'s Fashion';

    return (
        <PreviewFrame settings={settings} locale={locale} viewport={viewport}>
            <CardSettingsProvider value={settings.product_card}>
                <TemplateSections
                    blocks={collectionTemplates.before}
                    colorSchemes={colorSchemes}
                    themeSlug={themeSlug}
                    locale={locale}
                />
                <main className="mx-auto max-w-6xl px-5 py-16 sm:px-8">
                    <div className="mb-12 text-center">
                        <p className="mb-2 text-xs uppercase tracking-widest text-[var(--color-muted)]">
                            {ar ? 'مجموعة' : 'Collection'}
                        </p>
                        <h1 className="text-4xl font-bold text-[var(--color-text)] sm:text-5xl">
                            {categoryName}
                        </h1>
                    </div>

                    <div className="grid grid-cols-2 gap-x-5 gap-y-10 sm:grid-cols-3 xl:grid-cols-4">
                        {SAMPLE_PRODUCTS.slice(0, 8).map((product, i) => (
                            <ProductCardPreview key={i} product={product} locale={locale} />
                        ))}
                    </div>
                </main>
                <TemplateSections
                    blocks={collectionTemplates.after}
                    colorSchemes={colorSchemes}
                    themeSlug={themeSlug}
                    locale={locale}
                />
            </CardSettingsProvider>
        </PreviewFrame>
    );
}
