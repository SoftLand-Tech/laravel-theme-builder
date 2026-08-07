import React from 'react';
import type { ColorScheme, ThemeSettings } from '../types/settings';
import { PreviewFrame } from './PreviewFrame';
import { TemplateSections } from './TemplateSections';
import { CardSettingsProvider, SAMPLE_PRODUCTS, useCardSettings } from '../blocks/ProductCardPreview';
import { loc } from '../blocks/_shared';
import { useT } from '../i18n';
import type { TemplatesShape } from './PagesPanel';

interface ProductPagePreviewProps {
    settings: ThemeSettings;
    locale: string;
    viewport: 'desktop' | 'mobile';
    templates: TemplatesShape;
    colorSchemes: ColorScheme[];
    themeSlug?: string;
}

/**
 * Representative product-detail preview for the editor's Product page. Reflects
 * the `product_page` toggles (breadcrumbs, rating, share, quantity stepper, buy
 * now, sticky bar) and the product-card settings, with the per-page template
 * blocks (before/after) rendered around the main content — same as the
 * storefront, so a Countdown or banner added there is visible and live.
 */
export function ProductPagePreview({ settings, locale, viewport, templates, colorSchemes, themeSlug }: ProductPagePreviewProps) {
    const t = useT();
    const p = settings.product_page;
    const product = SAMPLE_PRODUCTS[0];
    const productTemplates = templates.product ?? {};

    return (
        <PreviewFrame settings={settings} locale={locale} viewport={viewport}>
            <CardSettingsProvider value={settings.product_card}>
                <TemplateSections blocks={productTemplates.before} colorSchemes={colorSchemes} themeSlug={themeSlug} locale={locale} />
                <div className="mx-auto max-w-5xl px-6 py-10">
                    {p.breadcrumbs && (
                        <nav className="mb-6 text-xs text-[var(--color-muted)]">
                            {locale === 'ar' ? 'الرئيسية / القسم / المنتج' : 'Home / Category / Product'}
                        </nav>
                    )}
                    <div className="grid gap-10 md:grid-cols-2">
                        <div className="flex aspect-square items-center justify-center overflow-hidden rounded-[var(--radius-card)] bg-gradient-to-br from-neutral-200 to-neutral-300 font-display text-7xl text-neutral-400">
                            {(loc(product.name, locale) || 'P')[0]}
                        </div>
                        <div className="pdp-actions flex flex-col gap-4">
                            {p.show_rating && product.rating ? (
                                <div className="text-sm text-amber-500">{'★'.repeat(product.rating)}<span className="text-neutral-300">{'★'.repeat(5 - product.rating)}</span></div>
                            ) : null}
                            <h1 className="text-3xl font-bold text-[var(--color-text)]">{loc(product.name, locale)}</h1>
                            <div className="flex items-center gap-2">
                                <span className="text-2xl font-semibold text-[var(--color-text)]">{product.price}</span>
                                {product.comparePrice && (
                                    <span className="text-sm text-[var(--color-muted)] line-through">{product.comparePrice}</span>
                                )}
                            </div>
                            <p className="text-sm text-[var(--color-muted)]">
                                {locale === 'ar' ? 'وصف المنتج يظهر هنا في صفحة المنتج.' : 'The product description appears here on the product page.'}
                            </p>
                            {p.show_quantity_stepper && (
                                <div className="flex w-fit items-center gap-3 rounded-[var(--radius-button)] border border-neutral-200 px-3 py-1.5">
                                    <span className="px-2 text-[var(--color-muted)]">−</span>
                                    <span className="min-w-6 text-center font-medium text-[var(--color-text)]">1</span>
                                    <span className="px-2 text-[var(--color-muted)]">+</span>
                                </div>
                            )}
                            <div className="flex flex-wrap gap-2">
                                <button type="button" className="rounded-[var(--radius-button)] bg-[var(--color-primary)] px-6 py-2.5 text-sm font-semibold text-white">
                                    {t('Add to cart')}
                                </button>
                                {p.buy_now_button && (
                                    <button type="button" className="rounded-[var(--radius-button)] border border-[var(--color-primary)] px-6 py-2.5 text-sm font-semibold text-[var(--color-primary)]">
                                        {t('Buy now')}
                                    </button>
                                )}
                            </div>
                            {p.show_share && (
                                <div className="flex gap-3 pt-2 text-xs text-[var(--color-muted)]">
                                    <span>{t('Share')}:</span>
                                    <span>×</span><span>f</span><span>in</span>
                                </div>
                            )}
                        </div>
                    </div>

                    {p.show_related && (
                        <div className="mt-14">
                            <h2 className="mb-5 text-xl font-bold text-[var(--color-text)]">{t('You may also like')}</h2>
                            <RelatedRow locale={locale} />
                        </div>
                    )}
                </div>
                <TemplateSections blocks={productTemplates.after} colorSchemes={colorSchemes} themeSlug={themeSlug} locale={locale} />
            </CardSettingsProvider>
        </PreviewFrame>
    );
}

function RelatedRow({ locale }: { locale: string }) {
    const card = useCardSettings();
    return (
        <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
            {SAMPLE_PRODUCTS.slice(0, 4).map((product, i) => (
                <RelatedMini key={i} name={loc(product.name, locale)} price={product.price} showPrice={card.show_price} />
            ))}
        </div>
    );
}

function RelatedMini({ name, price, showPrice }: { name: string; price: string; showPrice: boolean }) {
    return (
        <div className="flex flex-col gap-2 rounded-[var(--radius-card)] bg-[var(--color-surface)] p-3 shadow-sm">
            <div className="aspect-square rounded bg-gradient-to-br from-neutral-200 to-neutral-300" />
            <div className="line-clamp-1 text-xs font-medium text-[var(--color-text)]">{name}</div>
            {showPrice && <div className="text-xs font-semibold text-[var(--color-text)]">{price}</div>}
        </div>
    );
}
