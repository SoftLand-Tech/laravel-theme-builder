import React from 'react';
import { DraggablePaletteItem } from './DraggablePaletteItem';
import { useT } from '../i18n';

export interface PaletteBlock {
    type: string;
    label: string;
    category: string;
    icon: string;
}

interface PaletteProps {
    blocks: PaletteBlock[];
    onAdd: (type: string) => void;
    /** When set, the next palette click inserts at this gap index instead of appending. */
    pendingInsertAt?: number | null;
    onCancelInsertAt?: () => void;
}

/** Display order for palette categories (mirrors BlockCategory). */
const CATEGORY_ORDER = ['hero', 'products', 'banners', 'categories', 'content', 'marketing', 'layout'];

const CATEGORY_LABELS: Record<string, string> = {
    hero: 'Category: Hero',
    products: 'Category: Products',
    banners: 'Category: Banners',
    categories: 'Category: Categories',
    content: 'Category: Content',
    marketing: 'Category: Marketing',
    layout: 'Category: Layout',
};

/**
 * The "Add section" palette. Blocks (sourced from the server `blockRegistry`)
 * are grouped by category; clicking one inserts it after the selected block
 * (or appends).
 */
export function Palette({ blocks, onAdd, pendingInsertAt, onCancelInsertAt }: PaletteProps) {
    const t = useT();
    const grouped = CATEGORY_ORDER.map((category) => ({
        category,
        label: t(CATEGORY_LABELS[category] ?? category),
        items: blocks.filter((b) => b.category === category),
    })).filter((g) => g.items.length > 0);

    const leftovers = blocks.filter((b) => !CATEGORY_ORDER.includes(b.category));
    if (leftovers.length) {
        grouped.push({ category: 'other', label: t('Other'), items: leftovers });
    }

    return (
        <div className="space-y-5">
            {pendingInsertAt !== null && pendingInsertAt !== undefined ? (
                <div className="flex items-center justify-between gap-2 rounded-md border border-primary-300 bg-primary-50 px-3 py-2 text-xs text-primary-800">
                    <span>{t('Pick a section to insert at position :n', { n: pendingInsertAt + 1 })}</span>
                    {onCancelInsertAt && (
                        <button
                            type="button"
                            onClick={onCancelInsertAt}
                            className="rounded px-1.5 py-0.5 text-[11px] font-medium text-primary-700 hover:bg-primary-100"
                        >
                            {t('Cancel')}
                        </button>
                    )}
                </div>
            ) : (
                <p className="text-xs text-neutral-500">{t('Click a section to add it to the page.')}</p>
            )}
            {grouped.map((group) => (
                <div key={group.category} className="space-y-2">
                    <h4 className="text-[11px] font-semibold uppercase tracking-wide text-neutral-500">
                        {group.label}
                    </h4>
                    <div className="grid grid-cols-2 gap-2">
                        {group.items.map((b) => (
                            <DraggablePaletteItem key={b.type} block={b} onAdd={onAdd} />
                        ))}
                    </div>
                </div>
            ))}
        </div>
    );
}
