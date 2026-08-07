import React from 'react';
import { useDraggable } from '@dnd-kit/core';
import { CSS } from '@dnd-kit/utilities';
import { useT } from '../i18n';
import type { PaletteBlock } from './Palette';
import { BlockWireframe } from './BlockWireframe';

interface DraggablePaletteItemProps {
    block: PaletteBlock;
    onAdd: (type: string) => void;
}

/**
 * A palette entry that is BOTH click-to-add (the button) AND draggable onto the
 * canvas. The drag carries `{ source: 'palette', type }` so the canvas can
 * insert a new block on drop. PointerSensor's distance constraint keeps clicks
 * firing as clicks.
 */
export function DraggablePaletteItem({ block, onAdd }: DraggablePaletteItemProps) {
    const t = useT();
    const { attributes, listeners, setNodeRef, transform, isDragging } = useDraggable({
        id: `palette-${block.type}`,
        data: { source: 'palette', type: block.type, label: t(block.type) },
    });

    const style: React.CSSProperties = {
        transform: CSS.Translate.toString(transform),
        opacity: isDragging ? 0.4 : 1,
    };

    return (
        <button
            ref={setNodeRef}
            type="button"
            style={style}
            onClick={() => onAdd(block.type)}
            className="flex w-full cursor-grab flex-col items-stretch gap-1.5 rounded-lg border border-neutral-200 bg-white p-2 text-left transition hover:border-primary-500/50 hover:bg-neutral-50 active:cursor-grabbing"
            {...attributes}
            {...listeners}
        >
            <div className="overflow-hidden rounded-md border border-neutral-100 bg-neutral-50 px-2 py-1.5">
                <BlockWireframe type={block.type} />
            </div>
            <span className="text-[11px] font-medium leading-tight text-neutral-800">{t(block.type)}</span>
        </button>
    );
}
