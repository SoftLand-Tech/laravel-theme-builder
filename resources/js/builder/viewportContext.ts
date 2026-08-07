import { createContext, useContext } from 'react';

export type Viewport = 'desktop' | 'mobile';

/**
 * The active editor viewport (desktop/mobile), provided by `PreviewFrame` so
 * block previews can render responsively (e.g. product grids collapse to one
 * column on mobile) — mirroring the storefront's real responsive CSS.
 */
export const ViewportContext = createContext<Viewport>('desktop');

export function useViewport(): Viewport {
    return useContext(ViewportContext);
}

/** Resolve the effective column count for a grid preview: merchant's choice on
 *  desktop, a smaller count on mobile. */
export function useResponsiveColumns(desktop: number, mobile = 1): number {
    const viewport = useContext(ViewportContext);
    const count = Math.max(1, desktop || 4);
    return viewport === 'mobile' ? Math.min(count, mobile) : count;
}
