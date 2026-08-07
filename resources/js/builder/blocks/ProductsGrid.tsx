import React from 'react';
import { loc } from './_shared';
import { ProductCardPreview, SAMPLE_PRODUCTS } from './ProductCardPreview';
import { useViewport } from '../viewportContext';

interface ProductsGridProps {
    locale: string;
    title: { ar?: string; en?: string };
    source: string;
    columns: number;
    mobileColumns: number;
    limit: number;
    showAddToCart?: boolean;
}

const COL_CLASS: Record<number, string> = {
    1: 'grid-cols-1',
    2: 'grid-cols-2',
    3: 'grid-cols-3',
    4: 'grid-cols-4',
    5: 'grid-cols-5',
    6: 'grid-cols-6',
};

export function ProductsGrid(props: ProductsGridProps) {
    const viewport = useViewport();
    const title = loc(props.title, props.locale);
    const count = Math.min(props.limit || 8, SAMPLE_PRODUCTS.length);
    const desktop = Math.max(1, Math.min(6, props.columns ?? 4));
    const mobileCols = Math.max(1, Math.min(3, props.mobileColumns ?? 2));
    const cols = viewport === 'mobile' ? mobileCols : desktop;

    return (
        <div>
            {title && <h2 className="mb-6 text-xl font-bold text-neutral-900 sm:text-2xl">{title}</h2>}
            <div className={`grid ${COL_CLASS[cols] ?? 'grid-cols-4'} gap-4`}>
                {SAMPLE_PRODUCTS.slice(0, count).map((p, i) => (
                    <ProductCardPreview key={i} product={p} locale={props.locale} />
                ))}
            </div>
        </div>
    );
}
