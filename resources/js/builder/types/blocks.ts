/**
 * Block tree types shared across the editor.
 *
 * The wire format persisted to the server is `{ type, props }[]` (no id). The
 * client attaches a transient `id` for React keys and drag-and-drop only; it is
 * stripped by `toWire` before saving.
 */

export type Bilingual = { ar: string; en: string };

export type BlockProps = Record<string, unknown>;

export interface WireBlock {
    type: string;
    props: BlockProps;
}

export interface Block extends WireBlock {
    id: string;
}

/** Strip the client-only `id` so we persist the canonical wire shape. */
export function toWire(blocks: Block[]): WireBlock[] {
    return blocks.map(({ type, props }) => ({ type, props }));
}

/** Generate a stable-enough unique id for a block (React keys + DnD). */
export function makeBlockId(type: string): string {
    const rand = typeof crypto !== 'undefined' && 'randomUUID' in crypto
        ? crypto.randomUUID()
        : Math.random().toString(36).slice(2);
    return `${type}-${rand}`;
}

/** Normalize an incoming wire block list into client blocks with ids. */
export function hydrateBlocks(wire: WireBlock[]): Block[] {
    return wire.map((b) => ({ ...b, id: makeBlockId(b.type) }));
}
