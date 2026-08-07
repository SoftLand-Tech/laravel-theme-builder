import React, { useState } from 'react';
import { BLOCK_SCHEMAS, defaultPropsFor } from '../config/blocks';
import { FieldControl } from './FieldControl';
import { FieldShell } from './FormControls';
import { useT } from '../i18n';

/**
 * Templates are per-page marketing block lists (before / after the pinned main
 * content). Only blocks that don't depend on a product/cart context are usable
 * here (e.g. Hero, Slideshow, Testimonials — not ProductsFeatured).
 */

const ALLOWED_TEMPLATE_BLOCKS = [
    'Hero', 'Slideshow', 'BannersSingle', 'BannersDouble', 'PromoBar',
    'RichText', 'ImageWithText', 'Multicolumn', 'Testimonials', 'FaqAccordion',
    'CollapsibleContent', 'TrustBadges', 'Countdown', 'Brands', 'CategoryGrid',
    'CategoryShortcuts', 'Collage', 'ParallaxBanner', 'ContactForm', 'CustomHtml',
    'ProductsGrid', 'ProductsCarousel', 'ProductsTabs',
];

type TemplateKey = 'product' | 'cart' | 'collection';
type Slot = 'before' | 'after';
type WireBlock = { type: string; props: Record<string, unknown> };

export interface TemplatesShape {
    product?: { before?: WireBlock[]; after?: WireBlock[] };
    cart?: { before?: WireBlock[]; after?: WireBlock[] };
    collection?: { before?: WireBlock[]; after?: WireBlock[] };
}

export type PreviewPage = 'home' | 'product' | 'cart' | 'collection';

interface PagesPanelProps {
    previewPage: PreviewPage;
    onPreviewPageChange: (page: PreviewPage) => void;
    templates: TemplatesShape;
    onChange: (next: TemplatesShape) => void;
}

export function PagesPanel({ previewPage, onPreviewPageChange, templates, onChange }: PagesPanelProps) {
    const t = useT();

    const templateKey: TemplateKey | null = previewPage === 'home' ? null : previewPage;

    const ensure = (key: TemplateKey): TemplatesShape => ({
        ...templates,
        [key]: { before: templates[key]?.before ?? [], after: templates[key]?.after ?? [] },
    });

    const addBlock = (slot: Slot, type: string) => {
        if (!templateKey) {
            return;
        }
        const next = ensure(templateKey);
        const list = [...(next[templateKey][slot] ?? [])];
        list.push({ type, props: defaultPropsFor(type) });
        next[templateKey][slot] = list;
        onChange(next);
    };

    const removeBlock = (slot: Slot, index: number) => {
        if (!templateKey) {
            return;
        }
        const next = ensure(templateKey);
        next[templateKey][slot] = (next[templateKey][slot] ?? []).filter((_, i) => i !== index);
        onChange(next);
    };

    const updateProps = (slot: Slot, index: number, partial: Record<string, unknown>) => {
        if (!templateKey) {
            return;
        }
        const next = ensure(templateKey);
        const list = [...(next[templateKey][slot] ?? [])];
        list[index] = { ...list[index], props: { ...list[index].props, ...partial } };
        next[templateKey][slot] = list;
        onChange(next);
    };

    const current = templateKey ? (templates[templateKey] ?? { before: [], after: [] }) : { before: [], after: [] };

    return (
        <div className="space-y-4">
            <h2 className="text-sm font-semibold text-neutral-900">{t('Pages')}</h2>
            <div className="flex gap-0.5 rounded-lg border border-neutral-200 bg-neutral-100 p-0.5">
                {(['home', 'product', 'cart', 'collection'] as const).map((p) => (
                    <button
                        key={p}
                        type="button"
                        onClick={() => onPreviewPageChange(p)}
                        className={`min-w-0 flex-1 truncate rounded-md px-1.5 py-1.5 text-[11px] font-medium transition ${
                            previewPage === p ? 'bg-primary-600 text-white' : 'text-neutral-500 hover:text-neutral-900'
                        }`}
                    >
                        {t(p === 'home' ? 'Home' : p === 'product' ? 'Product' : p === 'cart' ? 'Cart' : 'Categories')}
                    </button>
                ))}
            </div>

            {previewPage === 'home' && (
                <p className="text-[11px] text-neutral-500">
                    {t('Edit home page sections from the Components tab.')}
                </p>
            )}

            {templateKey && (
                <>
                    <p className="text-[11px] text-neutral-500">
                        {t('Add marketing sections before or after the main content on this page.')}
                    </p>

                    {(['before', 'after'] as const).map((slot) => (
                        <SlotEditor
                            key={slot}
                            slot={slot}
                            blocks={current[slot] ?? []}
                            onAdd={(type) => addBlock(slot, type)}
                            onRemove={(i) => removeBlock(slot, i)}
                            onUpdate={(i, partial) => updateProps(slot, i, partial)}
                        />
                    ))}
                </>
            )}
        </div>
    );
}

function SlotEditor({
    slot,
    blocks,
    onAdd,
    onRemove,
    onUpdate,
}: {
    slot: Slot;
    blocks: WireBlock[];
    onAdd: (type: string) => void;
    onRemove: (index: number) => void;
    onUpdate: (index: number, partial: Record<string, unknown>) => void;
}) {
    const t = useT();
    const [adding, setAdding] = useState(false);
    const [openIdx, setOpenIdx] = useState<number | null>(null);

    return (
        <div className="space-y-2">
            <div className="flex items-center justify-between">
                <h3 className="text-xs font-semibold uppercase tracking-wide text-neutral-500">
                    {t(slot === 'before' ? 'Before content' : 'After content')}
                </h3>
                <button
                    type="button"
                    onClick={() => setAdding((v) => !v)}
                    className="text-[11px] text-primary-600 hover:underline"
                >
                    {t('Add section')}
                </button>
            </div>

            {adding && (
                <div className="grid grid-cols-2 gap-1 rounded-lg border border-neutral-200 bg-neutral-50 p-2">
                    {ALLOWED_TEMPLATE_BLOCKS.map((type) => (
                        <button
                            key={type}
                            type="button"
                            onClick={() => {
                                onAdd(type);
                                setAdding(false);
                            }}
                            className="rounded px-2 py-1.5 text-left text-xs text-neutral-700 hover:bg-neutral-200"
                        >
                            {t(type)}
                        </button>
                    ))}
                </div>
            )}

            {blocks.map((block, index) => {
                const schema = BLOCK_SCHEMAS[block.type];
                const open = openIdx === index;
                return (
                    <div key={index} className="rounded-lg border border-neutral-200 bg-white">
                        <div className="flex items-center gap-3 px-3 py-2">
                            <button
                                type="button"
                                onClick={() => setOpenIdx(open ? null : index)}
                                className="min-w-0 flex-1 truncate text-start text-xs font-medium text-neutral-800"
                            >
                                {t(block.type)}
                            </button>
                            <button
                                type="button"
                                onClick={() => onRemove(index)}
                                className="shrink-0 text-[11px] text-red-500 hover:underline"
                            >
                                {t('Remove')}
                            </button>
                        </div>
                        {open && schema && (
                            <div className="space-y-3 border-t border-neutral-200 px-3 py-3">
                                {schema.fields
                                    .filter((f) => f.id !== 'visibleOnDesktop' && f.id !== 'visibleOnMobile' && !f.id.startsWith('group-'))
                                    .map((field) => {
                                        if (field.type === 'static') return null;
                                        return (
                                            <FieldShell key={field.id} label={field.label} description={field.description}>
                                                <FieldControl
                                                    field={field}
                                                    value={block.props[field.id]}
                                                    onChange={(v) => onUpdate(index, { [field.id]: v })}
                                                />
                                            </FieldShell>
                                        );
                                    })}
                            </div>
                        )}
                    </div>
                );
            })}
            {blocks.length === 0 && (
                <p className="rounded border border-dashed border-neutral-300 px-3 py-3 text-center text-[11px] text-neutral-400">
                    {t('No sections here yet.')}
                </p>
            )}
        </div>
    );
}
