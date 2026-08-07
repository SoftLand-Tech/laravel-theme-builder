import React, { createContext, useContext } from 'react';
import { loc } from './_shared';
import { useT } from '../i18n';
import { CURRENCY } from '../config/api';

export interface ProductCardSettings {
    show_image: boolean;
    show_title: boolean;
    show_price: boolean;
    show_compare_price: boolean;
    show_rating: boolean;
    show_add_to_cart: boolean;
    show_sale_badge: boolean;
    show_new_badge: boolean;
    show_out_of_stock_badge: boolean;
    border: boolean;
    hover_zoom: boolean;
}

const DEFAULT_CARD: ProductCardSettings = {
    show_image: true,
    show_title: true,
    show_price: true,
    show_compare_price: true,
    show_rating: true,
    show_add_to_cart: true,
    show_sale_badge: true,
    show_new_badge: false,
    show_out_of_stock_badge: true,
    border: false,
    hover_zoom: true,
};

const CardSettingsContext = createContext<ProductCardSettings>(DEFAULT_CARD);

export function useCardSettings(): ProductCardSettings {
    return useContext(CardSettingsContext);
}

/** Provider used by the canvas to feed the active `product_card` settings in. */
export function CardSettingsProvider({
    value,
    children,
}: {
    value: ProductCardSettings;
    children: React.ReactNode;
}) {
    return <CardSettingsContext.Provider value={value}>{children}</CardSettingsContext.Provider>;
}

export interface SampleProduct {
    name: { ar: string; en: string };
    price: string;
    comparePrice?: string;
    rating?: number;
    image?: string;
    isNew?: boolean;
    outOfStock?: boolean;
}

/** Realistic sample products so the editor preview looks like a real storefront. */
export const SAMPLE_PRODUCTS: SampleProduct[] = [
    { name: { ar: 'قميص قطني كلاسيك', en: 'Classic Cotton Shirt' }, price: `149 ${CURRENCY}`, comparePrice: `199 ${CURRENCY}`, rating: 5, isNew: false },
    { name: { ar: 'حقيبة جلد طبيعي', en: 'Leather Handbag' }, price: `320 ${CURRENCY}`, rating: 4 },
    { name: { ar: 'سماعات لاسلكية', en: 'Wireless Headphones' }, price: `89 ${CURRENCY}`, comparePrice: `120 ${CURRENCY}`, rating: 5, isNew: true },
    { name: { ar: 'ساعة يد أنيقة', en: 'Elegant Wristwatch' }, price: `210 ${CURRENCY}`, rating: 4 },
    { name: { ar: 'نظارة شمسية', en: 'Sunglasses' }, price: `75 ${CURRENCY}`, rating: 5, outOfStock: true },
    { name: { ar: 'حذاء رياضي', en: 'Running Shoes' }, price: `185 ${CURRENCY}`, comparePrice: `240 ${CURRENCY}`, rating: 5 },
    { name: { ar: 'عطر فاخر', en: 'Luxury Perfume' }, price: `260 ${CURRENCY}`, rating: 4, isNew: true },
    { name: { ar: 'محفظة جلد', en: 'Leather Wallet' }, price: `95 ${CURRENCY}`, rating: 5 },
];

/**
 * A product card preview that mirrors resources/views/components/product/card.blade.php
 * — same tokens (paper-deep / clay / ink / stone), same 3/4 aspect, same eyebrow
 * badges, same hover treatment — so the editor matches the live storefront.
 */
export function ProductCardPreview({ product, locale }: { product: SampleProduct; locale: string }) {
    const c = useContext(CardSettingsContext);
    const t = useT();
    const onSale = Boolean(product.comparePrice);
    const name = loc(product.name, locale);
    const initial = (name || 'P')[0];

    return (
        <div className="product-card group relative flex flex-col">
            <a href="#" onClick={(e) => e.preventDefault()} className="block">
                {c.show_image && (
                    <div className="relative aspect-[3/4] overflow-hidden rounded-(--radius-cadi) bg-paper-deep">
                        {product.image ? (
                            <img
                                src={product.image}
                                alt=""
                                className={`absolute inset-0 h-full w-full object-cover transition-transform duration-300 ${c.hover_zoom ? 'group-hover:scale-[1.03]' : ''}`}
                            />
                        ) : (
                            <div className="absolute inset-0 flex items-center justify-center">
                                <span className="font-display text-5xl text-stone/40">{initial}</span>
                            </div>
                        )}

                        <div className="absolute left-3 top-3 flex flex-col gap-1.5">
                            {c.show_sale_badge && onSale && (
                                <span className="eyebrow w-fit rounded-full bg-clay-600 px-2.5 py-1 text-paper">{t('Sale')}</span>
                            )}
                            {c.show_new_badge && product.isNew && (
                                <span className="eyebrow w-fit rounded-full bg-clay-700 px-2.5 py-1 text-paper">{t('New')}</span>
                            )}
                            {c.show_out_of_stock_badge && product.outOfStock && (
                                <span className="eyebrow w-fit rounded-full bg-ink/80 px-2.5 py-1 text-paper">{t('Out of stock')}</span>
                            )}
                        </div>

                        <div className="absolute inset-0 bg-ink/0 transition-colors duration-300 group-hover:bg-ink/[0.03]" />
                    </div>
                )}

                <div className="mt-4 space-y-1">
                    {c.show_title && (
                        <h3 className="text-sm font-medium text-ink transition-colors group-hover:text-clay-700">{name}</h3>
                    )}
                    <div className="flex items-baseline gap-2">
                        {c.show_price && (
                            <span className={`text-sm ${onSale ? 'text-clay-700' : 'text-ink-soft'}`}>{product.price}</span>
                        )}
                        {c.show_compare_price && onSale && (
                            <span className="text-xs text-stone line-through">{product.comparePrice}</span>
                        )}
                    </div>
                </div>
            </a>

            {c.show_add_to_cart && (
                <a
                    href="#"
                    onClick={(e) => e.preventDefault()}
                    className="mt-3 inline-flex w-fit items-center justify-center rounded-(--radius-button) bg-clay-600 px-4 py-2 text-xs font-medium text-paper transition hover:bg-clay-700"
                >
                    {t('Add to cart')}
                </a>
            )}
        </div>
    );
}
