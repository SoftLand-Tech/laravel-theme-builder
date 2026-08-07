import React from 'react';
import { loc } from './_shared';
import { useViewport } from '../viewportContext';

interface TestimonialsProps {
    locale: string;
    title?: { ar?: string; en?: string };
    items?: Array<{ quote?: { ar?: string; en?: string }; author?: { ar?: string; en?: string } }>;
}

export function Testimonials(props: TestimonialsProps) {
    const viewport = useViewport();
    const title = loc(props.title, props.locale);
    const items = props.items ?? [];
    const colClass = viewport === 'mobile' ? 'grid-cols-1' : 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3';

    return (
        <div>
            {title && <h2 className="mb-6 text-center text-2xl font-bold text-neutral-900">{title}</h2>}

            {items.length > 0 ? (
                <div className={`grid ${colClass} gap-4`}>
                    {items.map((item, i) => (
                        <figure key={i} className="rounded-lg border border-neutral-200 bg-white p-6 text-center">
                            <blockquote className="text-sm text-neutral-700">{loc(item.quote, props.locale)}</blockquote>
                            {loc(item.author, props.locale) && (
                                <figcaption className="mt-3 text-xs text-neutral-500">{loc(item.author, props.locale)}</figcaption>
                            )}
                        </figure>
                    ))}
                </div>
            ) : (
                <p className="text-center text-sm text-neutral-500">{props.locale === 'ar' ? 'لا توجد آراء بعد.' : 'No testimonials yet.'}</p>
            )}
        </div>
    );
}
