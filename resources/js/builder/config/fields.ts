/**
 * Field schema for block settings — modeled on Salla Twilight's `twilight.json`.
 *
 * Data `type` is decoupled from UI `format` (Salla's cleanest idea): a single
 * `boolean` type renders as a switch; a single `string` type renders as a text
 * input, a textarea, or an image picker depending on its `format`. Bilingual
 * values are enabled with `multilanguage: true` (stores `{ ar, en }`, English
 * fallback). Repeatable sub-fields use the `collection` type. Field visibility
 * can adapt to other field values via `conditions`.
 */

export type FieldType = 'boolean' | 'string' | 'number' | 'items' | 'collection' | 'static';

export type FieldFormat =
    | 'switch'
    | 'text'
    | 'textarea'
    | 'image'
    | 'product'
    | 'product-list'
    | 'blog-post'
    | 'blog-post-list'
    | 'collection'
    | 'collection-list'
    | 'dropdown-list'
    | 'multi-select'
    | 'color'
    | 'range'
    | 'datetime'
    | 'richtext'
    | 'link'
    | 'category'
    | 'video'
    | 'text-align'
    | 'icon'
    | 'color-scheme'
    | 'html'
    | 'title'
    | 'line'
    | 'description';

export interface FieldOption {
    label: string;
    value: string;
}

export interface FieldCondition {
    /** id of another field on the same block whose value controls visibility. */
    id: string;
    operation: '=';
    value: unknown;
}

export interface FieldDef {
    /** Storage key in the block's props (e.g. "title", "ctaUrl", "slides"). */
    id: string;
    type: FieldType;
    format?: FieldFormat;
    label?: string;
    description?: string;
    placeholder?: string;
    /** Render AR/EN inputs; store `{ ar, en }` with English fallback. */
    multilanguage?: boolean;
    default?: unknown;
    /** `items` / `dropdown-list` options. */
    options?: FieldOption[];
    /** Recommended image dimensions surfaced as helper text. */
    dimensions?: { width: number; height: number };
    /** `collection` item schema. */
    itemFields?: FieldDef[];
    itemLabel?: string;
    /** Length bounds for strings and collections. */
    minLength?: number;
    maxLength?: number;
    /** Numeric bounds for `number` inputs. */
    min?: number;
    max?: number;
    /** Show this field only when every condition is met. */
    conditions?: FieldCondition[];
}

/** Resolve the effective format, defaulting from the type when omitted. */
export function resolveFormat(field: FieldDef): FieldFormat {
    if (field.format) return field.format;
    switch (field.type) {
        case 'boolean':
            return 'switch';
        case 'number':
            return 'text';
        case 'items':
            return 'dropdown-list';
        case 'collection':
            return 'text'; // unused; collection renders via RepeaterField
        case 'static':
            return 'description';
        case 'string':
        default:
            return 'text';
    }
}

/** Curated icon names exposed to non-technical users via the icon picker. */
export const ICON_OPTIONS: Array<{ label: string; value: string }> = [
    { label: 'Check', value: 'check' },
    { label: 'Truck (shipping)', value: 'truck' },
    { label: 'Shield (warranty)', value: 'shield' },
    { label: 'Phone', value: 'phone' },
    { label: 'Star', value: 'star' },
    { label: 'Heart', value: 'heart' },
    { label: 'Tag', value: 'tag' },
    { label: 'Gift', value: 'gift' },
    { label: 'Credit card', value: 'card' },
    { label: 'Lock (secure)', value: 'lock' },
    { label: 'Return / refresh', value: 'refresh' },
    { label: 'Headset (support)', value: 'headset' },
    { label: 'Clock', value: 'clock' },
    { label: 'Map pin', value: 'map' },
    { label: 'Mail', value: 'mail' },
    { label: 'Box', value: 'box' },
];

/** Map an icon name to the inline SVG path data used in Blade previews. */
export function iconSvgPath(icon: string): string {
    switch (icon) {
        case 'truck': return 'M3 7h11v8H3zM14 10h4l3 3v2h-7';
        case 'shield': return 'M12 3l8 3v6c0 4.5-3 7.5-8 9-5-1.5-8-4.5-8-9V6z';
        case 'phone': return 'M5 4h3l2 5-2 1a11 11 0 005 5l1-2 5 2v3a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2';
        case 'star': return 'M12 3l2.6 5.3 5.8.8-4.2 4.1 1 5.8L12 16.3 6.8 19l1-5.8L3.6 9.1l5.8-.8z';
        case 'heart': return 'M12 21s-7-4.5-9.5-9A5 5 0 0112 6a5 5 0 019.5 6c-2.5 4.5-9.5 9-9.5 9z';
        case 'tag': return 'M3 12l9-9 9 9-9 9zM7.5 7.5h.01';
        case 'gift': return 'M20 12v8H4v-8M2 7h20v5H2zM12 22V7M12 7S11 3 8.5 3 6 5 6 5s2 2 6 2zM12 7s1-4 3.5-4S18 5 18 5s-2 2-6 2z';
        case 'card': return 'M2 6h20v12H2zM2 10h20';
        case 'lock': return 'M5 11h14v9H5zM8 11V7a4 4 0 018 0v4';
        case 'refresh': return 'M21 12a9 9 0 11-3-6.7M21 4v5h-5';
        case 'headset': return 'M4 13a8 8 0 0116 0M4 13v4a2 2 0 002 2h1v-6H6a2 2 0 00-2 0zM20 13v4a2 2 0 01-2 2h-1v-6h1a2 2 0 012 0zM18 19a3 3 0 01-3 2h-3';
        case 'clock': return 'M12 21a9 9 0 100-18 9 9 0 000 18zM12 7v5l3 2';
        case 'map': return 'M12 22s8-7 8-13a8 8 0 10-16 0c0 6 8 13 8 13zM12 11a2 2 0 100-4 2 2 0 000 4z';
        case 'mail': return 'M3 5h18v14H3zM3 7l9 6 9-6';
        case 'box': return 'M3 7l9-4 9 4v10l-9 4-9-4zM3 7l9 4 9-4M12 11v10';
        case 'check':
        default: return 'M5 13l4 4L19 7';
    }
}

/** Whether a field should be shown given the current block props. Fails open. */
export function meetsConditions(field: FieldDef, props: Record<string, unknown>): boolean {
    if (!field.conditions || field.conditions.length === 0) return true;
    return field.conditions.every((c) => props[c.id] === c.value);
}
