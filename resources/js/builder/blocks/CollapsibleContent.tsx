import React from 'react';
import { loc } from './_shared';

interface CollapsibleContentProps {
    locale: string;
    title: { ar?: string; en?: string };
    items: Array<{ heading?: { ar?: string; en?: string }; content?: { ar?: string; en?: string } }>;
}

export function CollapsibleContent(props: CollapsibleContentProps) {
    const items = props.items ?? [];
    const title = loc(props.title, props.locale);

    return (
        <div>
            {title && <h2 className="mb-6 text-center text-2xl font-bold text-neutral-900">{title}</h2>}
            {items.length > 0 && (
                <div className="mx-auto max-w-2xl space-y-2">
                    {items.map((item, i) => (
                        <details key={i} className="rounded-lg border border-neutral-200 bg-white p-4">
                            <summary className="cursor-pointer font-medium text-neutral-900">{loc(item.heading, props.locale)}</summary>
                            <div
                                className="rte-content mt-2 text-sm text-neutral-600"
                                dangerouslySetInnerHTML={{ __html: loc(item.content, props.locale) }}
                            />
                        </details>
                    ))}
                </div>
            )}
        </div>
    );
}
