import React from 'react';
import { loc } from './_shared';
import { useT } from '../i18n';
import { useViewport } from '../viewportContext';

const SAMPLE_CATEGORIES: Array<{ ar: string; en: string; count?: number }> = [
    { ar: 'جديدنا', en: 'New Arrivals', count: 24 },
    { ar: 'الأكثر مبيعًا', en: 'Bestsellers', count: 18 },
    { ar: 'العروض', en: 'Offers', count: 12 },
    { ar: 'ملابس', en: 'Apparel', count: 56 },
    { ar: 'إكسسوارات', en: 'Accessories', count: 31 },
    { ar: 'إلكترونيات', en: 'Electronics', count: 9 },
    { ar: 'المنزل', en: 'Home', count: 22 },
    { ar: 'تجميل', en: 'Beauty', count: 40 },
];

interface CategoryGridProps {
    locale: string;
    title: { ar?: string; en?: string };
    columns: number;
    limit: number;
}

const COL_CLASS: Record<number, string> = {
    2: 'grid-cols-2', 3: 'grid-cols-3', 4: 'grid-cols-4',
    5: 'grid-cols-5', 6: 'grid-cols-6', 7: 'grid-cols-7', 8: 'grid-cols-8',
};

export function CategoryGrid(props: CategoryGridProps) {
    const t = useT();
    const viewport = useViewport();
    const title = loc(props.title, props.locale);
    const count = Math.min(props.limit || 8, SAMPLE_CATEGORIES.length);
    const desktopCols = Math.max(2, Math.min(8, props.columns ?? 4));
    const cols = viewport === 'mobile' ? 2 : desktopCols;

    return (
        <div>
            {title && <h2 className="mb-6 text-xl font-bold text-neutral-900 sm:text-2xl">{title}</h2>}
            <div className={`grid ${COL_CLASS[cols] ?? 'grid-cols-4'} gap-4`}>
                {SAMPLE_CATEGORIES.slice(0, count).map((cat, i) => {
                    const name = props.locale === 'ar' ? cat.ar : cat.en;
                    return (
                        <a
                            key={i}
                            href="#"
                            onClick={(e) => e.preventDefault()}
                            className="group flex flex-col items-center gap-3 rounded-lg border border-neutral-200 bg-white p-6 transition hover:border-primary-400 hover:shadow-sm"
                        >
                            <div className="flex h-20 w-20 items-center justify-center rounded-full bg-neutral-100 text-2xl font-bold text-neutral-400">
                                {(name || 'C')[0].toUpperCase()}
                            </div>
                            <span className="text-sm font-medium text-neutral-900">{name}</span>
                            {cat.count && <span className="text-xs text-neutral-500">{cat.count} {t('products')}</span>}
                        </a>
                    );
                })}
            </div>
        </div>
    );
}
