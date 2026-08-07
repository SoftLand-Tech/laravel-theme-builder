import type { Active, Over } from '@dnd-kit/core';

export const CANVAS_DROP_AREA_ID = 'canvas-drop-area';

export function isPointInRect(
    point: { x: number; y: number },
    rect: { left: number; top: number; right: number; bottom: number },
): boolean {
    return point.x >= rect.left && point.x <= rect.right && point.y >= rect.top && point.y <= rect.bottom;
}

/**
 * Map a palette drag "over" target to a list index (0..blocks.length).
 * Gaps use their index; blocks use pointer position in the block's vertical half.
 */
export function resolvePaletteInsertIndex(
    over: Over,
    overData: { source?: string; index?: number } | undefined,
    blocks: Array<{ id: string }>,
    active: Active,
): number | null {
    if (overData?.source === 'gap' && typeof overData.index === 'number') {
        return overData.index;
    }

    if (overData?.source === 'block') {
        const idx = blocks.findIndex((b) => b.id === String(over.id));
        if (idx < 0) {
            return null;
        }

        const translated = active.rect.current.translated;
        if (!translated) {
            return idx;
        }

        const pointerMidY = translated.top + translated.height / 2;
        const { top, height } = over.rect;

        return pointerMidY > top + height / 2 ? idx + 1 : idx;
    }

    return null;
}
