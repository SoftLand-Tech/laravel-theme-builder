import React from 'react';
import { loc } from './_shared';
import { iconSvgPath } from '../config/fields';
import { useViewport } from '../viewportContext';

interface CategoryShortcutsProps {
    locale: string;
    title: { ar?: string; en?: string };
    items: Array<{ categoryId?: number | null; label?: { ar?: string; en?: string }; icon?: string }>;
}

export function CategoryShortcuts(props: CategoryShortcutsProps) {
    const viewport = useViewport();
    const items = props.items ?? [];
    const title = loc(props.title, props.locale);
    const colClass = viewport === 'mobile' ? 'grid-cols-2' : 'grid-cols-2 sm:grid-cols-3 md:grid-cols-6';

    return (
        <div>
            {title && <h2 className="mb-6 text-center text-2xl font-bold text-neutral-900">{title}</h2>}
            {items.length > 0 && (
                <div className={`grid ${colClass} gap-3`}>
                    {items.map((item, i) => (
                        <a
                            key={i}
                            href="#"
                            onClick={(e) => e.preventDefault()}
                            className="flex flex-col items-center gap-2 text-center"
                        >
                            <span className="flex h-16 w-16 items-center justify-center rounded-full bg-primary-50 text-primary-600">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="2"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    className="h-7 w-7"
                                    aria-hidden="true"
                                >
                                    <path d={iconSvgPath(item.icon ?? 'box')} />
                                </svg>
                            </span>
                            <span className="text-xs font-medium text-neutral-700">{loc(item.label, props.locale) || 'Category'}</span>
                        </a>
                    ))}
                </div>
            )}
        </div>
    );
}
