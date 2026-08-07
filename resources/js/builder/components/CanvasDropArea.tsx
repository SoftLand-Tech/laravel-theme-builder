import React from 'react';
import { useDroppable } from '@dnd-kit/core';
import { CANVAS_DROP_AREA_ID } from '../paletteDropIndex';

interface CanvasDropAreaProps {
    children: React.ReactNode;
}

/** Full-width droppable bounds used to ignore sidebar drops while allowing canvas-wide collision fallback. */
export function CanvasDropArea({ children }: CanvasDropAreaProps) {
    const { setNodeRef } = useDroppable({
        id: CANVAS_DROP_AREA_ID,
        data: { source: 'canvas-area' },
    });

    return (
        <div ref={setNodeRef} className="relative w-full min-h-[12rem]">
            {children}
        </div>
    );
}
