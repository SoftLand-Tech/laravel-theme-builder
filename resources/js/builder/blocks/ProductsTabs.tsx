import React, { useState } from 'react';
import { loc } from './_shared';
import { ProductCardPreview, SAMPLE_PRODUCTS } from './ProductCardPreview';
import { useViewport } from '../viewportContext';

interface Tab {
    label?: { ar?: string; en?: string };
    source?: string;
}

interface ProductsTabsProps {
    locale: string;
    tabs: Tab[];
    columns: number;
    mobileColumns?: number;
    limit: number;
}

const COL_CLASS: Record<number, string> = {
    1: 'grid-cols-1', 2: 'grid-cols-2', 3: 'grid-cols-3',
    4: 'grid-cols-4', 5: 'grid-cols-5', 6: 'grid-cols-6',
};

export function ProductsTabs(props: ProductsTabsProps) {
    const viewport = useViewport();
    const tabs = props.tabs ?? [];
    const [active, setActive] = useState(0);
    const count = Math.min(props.limit || 8, SAMPLE_PRODUCTS.length);
    const desktop = Math.max(1, Math.min(6, props.columns ?? 4));
    const mobileCols = Math.max(1, Math.min(3, props.mobileColumns ?? 2));
    const cols = viewport === 'mobile' ? mobileCols : desktop;

    return (
        <section>
            <div className="mb-6 flex gap-1 overflow-x-auto border-b border-neutral-200 sm:gap-2">
                {tabs.map((tab, i) => (
                    <button
                        key={i}
                        type="button"
                        onClick={() => setActive(i)}
                        className={`whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium transition sm:px-4 ${
                            active === i ? 'border-primary-500 text-primary-700' : 'border-transparent text-neutral-500'
                        }`}
                    >
                        {loc(tab.label, props.locale)}
                    </button>
                ))}
            </div>

            {tabs.map((_, i) => (
                <div key={i} className={`grid ${COL_CLASS[cols] ?? 'grid-cols-4'} gap-4 ${active === i ? '' : 'hidden'}`}>
                    {SAMPLE_PRODUCTS.slice(0, count).map((p, j) => (
                        <ProductCardPreview key={j} product={p} locale={props.locale} />
                    ))}
                </div>
            ))}
        </section>
    );
}
