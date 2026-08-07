import React from 'react';
import { loc } from './_shared';
import { useViewport } from '../viewportContext';

const SAMPLE: Array<{ ar: string; en: string; count?: number }> = [
    { ar: 'أدلة الشراء', en: 'Buying guides', count: 6 },
    { ar: 'إلهام', en: 'Inspiration', count: 9 },
    { ar: 'قصص عملائنا', en: 'Customer stories', count: 4 },
    { ar: 'نصائح وحيل', en: 'Tips & tricks', count: 12 },
    { ar: 'العروض', en: 'Offers', count: 3 },
    { ar: 'من وراء العلامة', en: 'Behind the brand', count: 5 },
    { ar: 'أخبار', en: 'News', count: 7 },
    { ar: 'دليل الإهداءات', en: 'Gift guides', count: 8 },
];

interface BlogCategoriesGridProps {
    locale: string;
    title: { ar?: string; en?: string };
    columns: number;
    limit: number;
}

const COL_CLASS: Record<number, string> = {
    2: 'grid-cols-2', 3: 'grid-cols-3', 4: 'grid-cols-4',
    5: 'grid-cols-5', 6: 'grid-cols-6', 7: 'grid-cols-7', 8: 'grid-cols-8',
};

export function BlogCategoriesGrid(props: BlogCategoriesGridProps) {
    const viewport = useViewport();
    const title = loc(props.title, props.locale);
    const count = Math.min(props.limit || 8, SAMPLE.length);
    const desktopCols = Math.max(2, Math.min(8, props.columns ?? 4));
    const cols = viewport === 'mobile' ? 2 : desktopCols;

    return (
        <div>
            {title && <h2 className="mb-6 text-xl font-bold text-neutral-900 sm:text-2xl">{title}</h2>}
            <div className={`grid ${COL_CLASS[cols] ?? 'grid-cols-4'} gap-4`}>
                {SAMPLE.slice(0, count).map((cat, i) => {
                    const name = props.locale === 'ar' ? cat.ar : cat.en;
                    return (
                        <a
                            key={i}
                            href="#"
                            onClick={(e) => e.preventDefault()}
                            className="group flex flex-col items-center gap-3 rounded-lg border border-neutral-200 bg-white p-6 transition hover:border-primary-400 hover:shadow-sm"
                        >
                            <div className="flex h-20 w-20 items-center justify-center rounded-full bg-neutral-100 text-2xl font-bold text-neutral-400">
                                {(name || 'B')[0].toUpperCase()}
                            </div>
                            <span className="text-sm font-medium text-neutral-900">{name}</span>
                            {cat.count && <span className="text-xs text-neutral-500">{cat.count}</span>}
                        </a>
                    );
                })}
            </div>
        </div>
    );
}
