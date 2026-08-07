import React from 'react';
import type { ColorScheme, ThemeSettings } from '../types/settings';
import { PreviewFrame } from './PreviewFrame';
import { TemplateSections } from './TemplateSections';
import type { TemplatesShape } from './PagesPanel';

interface CartPagePreviewProps {
    settings: ThemeSettings;
    locale: string;
    viewport: 'desktop' | 'mobile';
    templates: TemplatesShape;
    colorSchemes: ColorScheme[];
    themeSlug?: string;
}

/**
 * Representative cart preview for the editor's Cart page. Reflects the `cart`
 * toggles (free-shipping progress bar, coupon, order summary, cross-sell), with
 * the per-page template blocks (before/after) rendered around the main content
 * — same as the storefront.
 */
export function CartPagePreview({ settings, locale, viewport, templates, colorSchemes, themeSlug }: CartPagePreviewProps) {
    const c = settings.cart;
    const ar = locale === 'ar';
    const cartTemplates = templates.cart ?? {};

    const items = [
        { name: ar ? 'قميص قطني كلاسيك' : 'Classic Cotton Shirt', qty: 1, price: '149 SAR' },
        { name: ar ? 'سماعات لاسلكية' : 'Wireless Headphones', qty: 2, price: '178 SAR' },
    ];
    const subtotal = 32700; // halalas
    const threshold = c.free_shipping_threshold * 100;
    const remaining = Math.max(0, threshold - subtotal);
    const pct = Math.min(100, Math.round((subtotal / threshold) * 100));

    return (
        <PreviewFrame settings={settings} locale={locale} viewport={viewport}>
            <TemplateSections blocks={cartTemplates.before} colorSchemes={colorSchemes} themeSlug={themeSlug} locale={locale} />
            <div className="mx-auto max-w-3xl px-6 py-10">
                <h1 className="mb-1 text-2xl font-bold text-[var(--color-text)]">{ar ? 'سلة التسوق' : 'Your cart'}</h1>
                <p className="mb-6 text-xs uppercase tracking-widest text-[var(--color-muted)]">{ar ? 'الحقيبة' : 'Bag'}</p>

                {c.free_shipping_bar && (
                    <div className="free-shipping-bar mb-6 rounded-[var(--radius-card)] bg-[var(--color-surface)] p-4">
                        <p className="mb-2 text-xs text-[var(--color-text)]">
                            {remaining > 0
                                ? (ar ? `أضف ${(remaining / 100).toFixed(0)} ريال للحصول على شحن مجاني` : `Add ${(remaining / 100).toFixed(0)} SAR for free shipping`)
                                : (ar ? 'تأهلت للشحن المجاني! 🎉' : 'You qualify for free shipping! 🎉')}
                        </p>
                        <div className="h-2 overflow-hidden rounded-full bg-neutral-200">
                            <div className="h-full rounded-full bg-[var(--color-primary)]" style={{ width: `${pct}%` }} />
                        </div>
                    </div>
                )}

                <div className="grid gap-8 md:grid-cols-[1fr_300px]">
                    <ul className="divide-y divide-neutral-200 border-y border-neutral-200">
                        {items.map((item, i) => (
                            <li key={i} className="flex gap-4 py-4">
                                <div className="h-20 w-16 shrink-0 rounded-[var(--radius-card)] bg-gradient-to-br from-neutral-200 to-neutral-300" />
                                <div className="flex flex-1 flex-col">
                                    <span className="text-sm font-medium text-[var(--color-text)]">{item.name}</span>
                                    <span className="text-xs text-[var(--color-muted)]">{ar ? 'المقاس: M' : 'Size: M'}</span>
                                    <div className="mt-auto flex w-fit items-center gap-3 rounded-[var(--radius-button)] border border-neutral-200 px-2 py-1 text-xs">
                                        <span>−</span><span>{item.qty}</span><span>+</span>
                                    </div>
                                </div>
                                <span className="text-sm font-semibold text-[var(--color-text)]">{item.price}</span>
                            </li>
                        ))}
                    </ul>

                    <aside className="flex flex-col gap-4">
                        {c.show_coupon && (
                            <div className="rounded-[var(--radius-card)] bg-[var(--color-surface)] p-3">
                                <label className="mb-1 block text-xs text-[var(--color-muted)]">{ar ? 'كود الخصم' : 'Coupon code'}</label>
                                <div className="flex gap-2">
                                    <input className="w-full rounded border border-neutral-200 bg-white px-2 py-1.5 text-sm text-[var(--color-text)]" placeholder={ar ? 'SAR10' : 'SAR10'} />
                                    <button className="rounded bg-[var(--color-primary)] px-3 py-1.5 text-xs font-medium text-white">{ar ? 'تطبيق' : 'Apply'}</button>
                                </div>
                            </div>
                        )}

                        {c.show_order_summary && (
                            <div className="rounded-[var(--radius-card)] bg-[var(--color-surface)] p-4 text-sm">
                                <h3 className="mb-3 font-semibold text-[var(--color-text)]">{ar ? 'ملخص الطلب' : 'Order summary'}</h3>
                                <Row label={ar ? 'المجموع الفرعي' : 'Subtotal'} value="327 SAR" />
                                <Row label={ar ? 'الشحن' : 'Shipping'} value={ar ? 'يُحسب عند الدفع' : 'Calculated at checkout'} />
                                <div className="my-2 border-t border-neutral-200" />
                                <Row label={ar ? 'الإجمالي' : 'Total'} value="327 SAR" bold />
                            </div>
                        )}

                        <button className="rounded-[var(--radius-button)] bg-[var(--color-primary)] px-6 py-3 text-sm font-semibold text-white">
                            {ar ? 'إتمام الشراء' : 'Proceed to checkout'}
                        </button>
                    </aside>
                </div>

                {c.show_cross_sell && (
                    <div className="mt-12">
                        <h2 className="mb-4 text-lg font-bold text-[var(--color-text)]">{ar ? 'قد يعجبك أيضًا' : 'You may also like'}</h2>
                        <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                            {Array.from({ length: 4 }).map((_, i) => (
                                <div key={i} className="aspect-[3/4] rounded-[var(--radius-card)] bg-gradient-to-br from-neutral-200 to-neutral-300" />
                            ))}
                        </div>
                    </div>
                )}
            </div>
            <TemplateSections blocks={cartTemplates.after} colorSchemes={colorSchemes} themeSlug={themeSlug} locale={locale} />
        </PreviewFrame>
    );
}

function Row({ label, value, bold }: { label: string; value: string; bold?: boolean }) {
    return (
        <div className="flex items-center justify-between py-1">
            <span className={bold ? 'font-semibold text-[var(--color-text)]' : 'text-[var(--color-muted)]'}>{label}</span>
            <span className={bold ? 'font-semibold text-[var(--color-text)]' : 'text-[var(--color-text)]'}>{value}</span>
        </div>
    );
}
