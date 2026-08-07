import React from 'react';
import { loc } from './_shared';
import { iconSvgPath } from '../config/fields';
import { useViewport } from '../viewportContext';

interface MulticolumnProps {
    locale: string;
    title: { ar?: string; en?: string };
    columns: number;
    items: Array<{
        image?: string;
        icon?: string;
        label?: { ar?: string; en?: string };
        text?: { ar?: string; en?: string };
        ctaText?: { ar?: string; en?: string };
        url?: string;
    }>;
}

const COL_CLASS: Record<number, string> = {
    1: 'grid-cols-1', 2: 'grid-cols-2', 3: 'grid-cols-3',
    4: 'grid-cols-4', 5: 'grid-cols-5',
};

export function Multicolumn(props: MulticolumnProps) {
    const viewport = useViewport();
    const items = props.items ?? [];
    const desktop = Math.max(1, Math.min(5, props.columns ?? 3));
    const cols = viewport === 'mobile' ? 1 : desktop;

    return (
        <div>
            {loc(props.title, props.locale) && (
                <h2 className="mb-6 text-center text-2xl font-bold text-neutral-900">{loc(props.title, props.locale)}</h2>
            )}
            {items.length > 0 && (
                <div className={`grid ${COL_CLASS[cols] ?? 'grid-cols-3'} gap-6`}>
                    {items.map((item, i) => (
                        <div key={i} className="flex flex-col items-center rounded-lg border border-neutral-200 bg-white p-6 text-center">
                            {item.image ? (
                                <img src={item.image} alt={loc(item.label, props.locale)} className="mb-4 h-16 w-16 object-contain" />
                            ) : (
                                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-primary-50 text-primary-600">
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="2"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        className="h-6 w-6"
                                        aria-hidden="true"
                                    >
                                        <path d={iconSvgPath(item.icon ?? 'check')} />
                                    </svg>
                                </div>
                            )}
                            {loc(item.label, props.locale) && <h3 className="mb-1 text-sm font-semibold text-neutral-900">{loc(item.label, props.locale)}</h3>}
                            {loc(item.text, props.locale) && <p className="text-xs text-neutral-500">{loc(item.text, props.locale)}</p>}
                            {loc(item.ctaText, props.locale) && (
                                <a
                                    href={item.url || '#'}
                                    onClick={(e) => e.preventDefault()}
                                    className="mt-3 text-xs font-semibold text-primary-600 hover:underline"
                                >
                                    {loc(item.ctaText, props.locale)}
                                </a>
                            )}
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
