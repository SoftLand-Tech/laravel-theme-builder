import React from 'react';
import { loc } from './_shared';
import { useViewport } from '../viewportContext';

interface BrandsProps {
    locale: string;
    title: { ar?: string; en?: string };
    items: Array<{ image?: string; label?: { ar?: string; en?: string }; url?: string }>;
}

export function Brands(props: BrandsProps) {
    const viewport = useViewport();
    const items = props.items ?? [];
    const title = loc(props.title, props.locale);
    const colClass = viewport === 'mobile' ? 'grid-cols-2' : 'grid-cols-3 sm:grid-cols-4 lg:grid-cols-6';

    return (
        <div>
            {title && <h2 className="mb-6 text-center text-2xl font-bold text-neutral-900">{title}</h2>}
            {items.length > 0 && (
                <div className={`grid ${colClass} items-center gap-6`}>
                    {items.map((item, i) => {
                        const label = loc(item.label, props.locale);
                        const inner = (
                            <>
                                {item.image && <img src={item.image} alt={label} className="h-16 max-w-full object-contain" />}
                                {label && <span className="text-xs text-neutral-600">{label}</span>}
                            </>
                        );
                        return item.url ? (
                            <a key={i} href={item.url} onClick={(e) => e.preventDefault()} className="flex flex-col items-center gap-2 opacity-70 transition hover:opacity-100">{inner}</a>
                        ) : (
                            <div key={i} className="flex flex-col items-center gap-2 opacity-70">{inner}</div>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
