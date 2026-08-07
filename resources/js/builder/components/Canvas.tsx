import React, { useState } from 'react';
import { SortableContext, verticalListSortingStrategy } from '@dnd-kit/sortable';
import type { ThemeSettings, ColorScheme } from '../types/settings';
import type { Block, BlockProps } from '../types/blocks';
import { CanvasBlock } from './CanvasBlock';
import { CanvasDropArea } from './CanvasDropArea';
import { DropIndicator } from './DropIndicator';
import { PreviewFrame } from './PreviewFrame';
import { CardSettingsProvider } from '../blocks/ProductCardPreview';

interface CanvasProps {
    blocks: Block[];
    selectedId: string | null;
    locale: string;
    viewport: 'desktop' | 'mobile';
    settings: ThemeSettings;
    colorSchemes: ColorScheme[];
    themeSlug?: string;
    themeCssUrl?: string;
    onSelect: (id: string | null) => void;
    onDuplicate: (id: string) => void;
    onDelete: (id: string) => void;
    onMove: (id: string, dir: -1 | 1) => void;
    onToggleHide: (id: string) => void;
    onPatch: (id: string, partial: BlockProps) => void;
    onInsertAt: (index: number) => void;
    /** Gap index highlighted while dragging from the palette (0..blocks.length). */
    paletteHoverIndex?: number | null;
}

/**
 * Inline "+" button shown between two blocks (and at the ends). Hovering a gap
 * reveals it; clicking opens the section picker (Shopify-style insertion).
 */
function InsertBetween({ onInsert }: { onInsert: () => void }) {
    const [hovered, setHovered] = useState(false);
    return (
        <div
            className="relative -my-2 flex h-4 items-center justify-center"
            onMouseEnter={() => setHovered(true)}
            onMouseLeave={() => setHovered(false)}
        >
            <button
                type="button"
                onClick={(e) => {
                    e.stopPropagation();
                    onInsert();
                }}
                className={`flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-medium text-neutral-600 transition ${
                    hovered ? 'border-primary-500 bg-white text-primary-700 opacity-100' : 'border-neutral-300 bg-white opacity-0'
                }`}
                title="Add a section here"
            >
                + <span className="hidden sm:inline">Add section</span>
            </button>
        </div>
    );
}

/**
 * The home-page WYSIWYG canvas (presentational — the `<DndContext>` lives in
 * `Builder`). Maps the block array onto the React preview components and feeds
 * the active product-card settings in via context.
 */
export function Canvas({
    blocks,
    selectedId,
    locale,
    viewport,
    settings,
    colorSchemes,
    onSelect,
    onDuplicate,
    onDelete,
    onMove,
    onToggleHide,
    onPatch,
    onInsertAt,
    paletteHoverIndex = null,
    themeSlug,
    themeCssUrl,
}: CanvasProps) {
    const highlightAt = (index: number) => paletteHoverIndex === index;

    return (
        <PreviewFrame settings={settings} locale={locale} viewport={viewport} themeCssUrl={themeCssUrl} themeScopeClass={themeSlug ? `theme-${themeSlug}` : ''} onBackgroundClick={() => onSelect(null)}>
            <CardSettingsProvider value={settings.product_card}>
                <CanvasDropArea>
                {blocks.length === 0 ? (
                    <div className="p-6">
                        <DropIndicator index={0} variant="empty" highlight={highlightAt(0)} />
                    </div>
                ) : (
                    <SortableContext items={blocks.map((b) => b.id)} strategy={verticalListSortingStrategy}>
                        <div className="flex flex-col">
                            <DropIndicator index={0} highlight={highlightAt(0)} />
                            <InsertBetween onInsert={() => onInsertAt(0)} />
                            {blocks.map((block, i) => (
                                <React.Fragment key={block.id}>
                                    <CanvasBlock
                                        block={block}
                                        selected={block.id === selectedId}
                                        locale={locale}
                                        viewport={viewport}
                                        colorSchemes={colorSchemes}
                                        onSelect={(e) => {
                                            e.stopPropagation();
                                            onSelect(block.id);
                                        }}
                                        onDuplicate={() => onDuplicate(block.id)}
                                        onDelete={() => onDelete(block.id)}
                                        onMove={(dir) => onMove(block.id, dir)}
                                        onToggleHide={() => onToggleHide(block.id)}
                                        onPatch={(partial) => onPatch(block.id, partial)}
                                        themeSlug={themeSlug}
                                    />
                                    <InsertBetween onInsert={() => onInsertAt(i + 1)} />
                                    <DropIndicator index={i + 1} highlight={highlightAt(i + 1)} />
                                </React.Fragment>
                            ))}
                        </div>
                    </SortableContext>
                )}
                </CanvasDropArea>
            </CardSettingsProvider>
        </PreviewFrame>
    );
}
