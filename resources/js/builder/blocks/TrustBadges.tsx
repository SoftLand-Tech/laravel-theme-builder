import React from 'react';
import { loc } from './_shared';
import { iconSvgPath } from '../config/fields';
import { useViewport } from '../viewportContext';

interface TrustBadgesProps {
    locale: string;
    items?: Array<{ label?: { ar?: string; en?: string }; icon?: string }>;
}

export function TrustBadges(props: TrustBadgesProps) {
    const viewport = useViewport();
    const items = props.items ?? [];
    const colClass = viewport === 'mobile' ? 'grid-cols-2' : 'grid-cols-2 md:grid-cols-4';

    return (
        <div className={`grid ${colClass} gap-3`}>
            {items.map((item, i) => (
                <div key={i} className="flex items-center justify-center gap-2 rounded-lg border border-neutral-200 bg-white px-4 py-3">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        className="h-4 w-4 text-primary-600"
                        aria-hidden="true"
                    >
                        <path d={iconSvgPath(item.icon ?? 'check')} />
                    </svg>
                    <span className="text-sm text-neutral-700">{loc(item.label, props.locale)}</span>
                </div>
            ))}
        </div>
    );
}
