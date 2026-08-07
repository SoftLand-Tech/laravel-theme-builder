import React from 'react';
import { loc } from './_shared';
import { useT } from '../i18n';

interface FaqAccordionProps {
    locale: string;
    title: { ar?: string; en?: string };
    items: Array<{ question?: { ar?: string; en?: string }; answer?: { ar?: string; en?: string } }>;
}

export function FaqAccordion(props: FaqAccordionProps) {
    const t = useT();
    const items = props.items ?? [];
    const title = loc(props.title, props.locale);

    return (
        <div>
            {title && <h2 className="mb-6 text-center text-2xl font-bold text-neutral-900">{title}</h2>}
            {items.length > 0 ? (
                <div className="mx-auto max-w-2xl space-y-2">
                    {items.map((item, i) => (
                        <details key={i} className="rounded-lg border border-neutral-200 bg-white p-4">
                            <summary className="cursor-pointer font-medium text-neutral-900">{loc(item.question, props.locale)}</summary>
                            <p className="mt-2 whitespace-pre-line text-sm text-neutral-600">{loc(item.answer, props.locale)}</p>
                        </details>
                    ))}
                </div>
            ) : (
                <p className="text-center text-sm text-neutral-500">{props.locale === 'ar' ? 'لا توجد أسئلة بعد.' : 'No FAQs yet.'}</p>
            )}
        </div>
    );
}
