import React from 'react';
import { useDroppable } from '@dnd-kit/core';
import { useT } from '../i18n';

interface DropIndicatorProps {
    /** Position in the block list this gap represents (0..blocks.length). */
    index: number;
    /** When true, renders a tall empty-state drop zone instead of a thin line. */
    variant?: 'line' | 'empty';
    /** Highlight from palette drag (pointer over a block maps to a gap index). */
    highlight?: boolean;
}

/**
 * A drop target rendered between blocks (and at the ends). On hover it shows an
 * insertion line, so merchants get clear feedback for where a dragged block
 * (palette or existing) will land.
 */
export function DropIndicator({ index, variant = 'line', highlight = false }: DropIndicatorProps) {
    const t = useT();
    const { setNodeRef, isOver } = useDroppable({
        id: `gap-${index}`,
        data: { source: 'gap', index },
    });

    const active = isOver || highlight;

    if (variant === 'empty') {
        return (
            <div
                ref={setNodeRef}
                className={`flex flex-col items-center justify-center rounded-xl border-2 border-dashed py-16 text-center transition ${
                    active ? 'border-primary-500 bg-primary-500/5' : 'border-neutral-300'
                }`}
            >
                <p className={`text-sm ${active ? 'text-primary-600' : 'text-neutral-500'}`}>
                    {active ? t('Drop to add section') : t('Drag a section here, or pick one from the sidebar')}
                </p>
            </div>
        );
    }

    return (
        <div
            ref={setNodeRef}
            className="relative z-20 -my-1 flex h-5 w-full items-center px-0"
            aria-hidden={!active}
        >
            <div
                className={`h-0.5 w-full rounded-full transition-opacity ${
                    active ? 'bg-primary-500 opacity-100 shadow-[0_0_0_1px_rgba(47,122,181,0.35)]' : 'bg-transparent opacity-0'
                }`}
            />
        </div>
    );
}
