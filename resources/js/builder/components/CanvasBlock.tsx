import React from 'react';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { useT } from '../i18n';
import type { Block, BlockProps } from '../types/blocks';
import type { ColorScheme } from '../types/settings';
import { Section } from './Section';
import { SectionToolbar } from './SectionToolbar';
import { ThemePreview } from '../themes/ThemePreview';

interface CanvasBlockProps {
    block: Block;
    selected: boolean;
    locale: string;
    viewport: 'desktop' | 'mobile';
    colorSchemes: ColorScheme[];
    themeSlug?: string;
    onSelect: (e: React.MouseEvent) => void;
    onDuplicate: () => void;
    onDelete: () => void;
    onMove: (dir: -1 | 1) => void;
    onToggleHide: () => void;
    onPatch: (partial: BlockProps) => void;
}

/**
 * A single block on the canvas: when selected, a Shopify-style inline toolbar
 * (`SectionToolbar`) exposes section-style quick controls, visibility toggles,
 * hide/duplicate/delete/move. Otherwise a hover-only minimal bar with drag
 * handle + block name.
 */
export function CanvasBlock({
    block,
    selected,
    locale,
    viewport,
    colorSchemes,
    onSelect,
    onDuplicate,
    onDelete,
    onMove,
    onToggleHide,
    onPatch,
    themeSlug,
}: CanvasBlockProps) {
    const t = useT();
    const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
        id: block.id,
        data: { source: 'block' },
    });

    const isHidden = (block.props.hidden ?? false) === true;
    const desktopVisible = (block.props.visibleOnDesktop ?? true) === true;
    const mobileVisible = (block.props.visibleOnMobile ?? true) === true;
    const hiddenHere =
        (viewport === 'mobile' && !mobileVisible) ||
        (viewport === 'desktop' && !desktopVisible);

    return (
        <div
            ref={setNodeRef}
            style={{ transform: CSS.Transform.toString(transform), transition, opacity: isDragging ? 0.4 : 1 }}
            onClick={(e) => onSelect(e)}
            className={`group relative cursor-pointer rounded-lg border transition ${
                selected
                    ? 'border-primary-500 ring-2 ring-primary-500/40'
                    : 'border-transparent hover:border-neutral-300'
            } ${isHidden ? 'opacity-50' : ''}`}
        >
            {selected ? (
                <SectionToolbar
                    name={block.type}
                    hidden={isHidden}
                    hiddenHere={hiddenHere}
                    viewport={viewport}
                    sectionPaddingTop={String(block.props.sectionPaddingTop ?? 'medium')}
                    sectionPaddingBottom={String(block.props.sectionPaddingBottom ?? 'medium')}
                    sectionWidth={String(block.props.sectionWidth ?? 'contained')}
                    sectionBackground={String(block.props.sectionBackground ?? '')}
                    sectionTextAlign={String(block.props.sectionTextAlign ?? '')}
                    visibleOnDesktop={desktopVisible}
                    visibleOnMobile={mobileVisible}
                    colorSchemes={colorSchemes}
                    dragHandleProps={{ ...attributes, ...listeners }}
                    onPatch={onPatch}
                    onMove={onMove}
                    onDuplicate={onDuplicate}
                    onDelete={onDelete}
                    onToggleHide={onToggleHide}
                />
            ) : (
                <div
                    className={`absolute inset-x-0 top-0 z-10 flex items-center justify-between bg-white/80 px-2 py-1 backdrop-blur transition ${
                        selected ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'
                    }`}
                    onClick={(e) => e.stopPropagation()}
                >
                    <div className="flex items-center gap-1">
                        <button
                            type="button"
                            className="canvas-drag-handle cursor-grab text-neutral-400 hover:text-neutral-900"
                            title={t('Drag to reorder')}
                            {...attributes}
                            {...listeners}
                        >
                            ⠿
                        </button>
                        <span className="text-[11px] font-medium text-neutral-600">{t(block.type)}</span>
                        {hiddenHere && (
                            <span className="rounded bg-neutral-700 px-1.5 py-0.5 text-[10px] text-neutral-300">
                                {t('hidden on')} {viewport}
                            </span>
                        )}
                        {isHidden && (
                            <span className="rounded bg-warning-100 px-1.5 py-0.5 text-[10px] font-semibold text-warning-700">
                                {t('hidden')}
                            </span>
                        )}
                    </div>
                    <div className="flex items-center gap-0.5">
                        <HoverBtn title={t('Move up')} onClick={() => onMove(-1)}>↑</HoverBtn>
                        <HoverBtn title={t('Move down')} onClick={() => onMove(1)}>↓</HoverBtn>
                    </div>
                </div>
            )}

            {/* Live preview, wrapped in the same section frame the storefront uses */}
            <Section
                sectionPaddingTop={String(block.props.sectionPaddingTop ?? 'medium')}
                sectionPaddingBottom={String(block.props.sectionPaddingBottom ?? 'medium')}
                sectionWidth={String(block.props.sectionWidth ?? 'contained')}
                sectionBackground={String(block.props.sectionBackground ?? '')}
                sectionTextAlign={String(block.props.sectionTextAlign ?? '')}
                colorSchemes={colorSchemes}
            >
                <ThemePreview
                    type={block.type}
                    themeSlug={themeSlug}
                    locale={locale}
                    {...block.props}
                />
            </Section>
        </div>
    );
}

function HoverBtn({
    children,
    onClick,
    title,
}: {
    children: React.ReactNode;
    onClick: () => void;
    title: string;
}) {
    return (
        <button
            type="button"
            title={title}
            onClick={(e) => {
                e.stopPropagation();
                onClick();
            }}
            className="flex h-6 w-6 items-center justify-center rounded text-xs transition text-neutral-400 hover:bg-neutral-200 hover:text-neutral-900"
        >
            {children}
        </button>
    );
}
