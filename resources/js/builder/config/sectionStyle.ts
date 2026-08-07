import type { FieldDef } from './fields';

/**
 * Shared per-section style contract. Added to every block's field list so any
 * section can be re-styled by the merchant (padding, background scheme, width,
 * text alignment) without touching the block's own content fields.
 *
 * The matching defaults live on every block in both the TS schema and the PHP
 * `BlockRegistry` (server source of truth). Stored as top-level props so
 * `BlockDefinition::sanitize()`'s allowlist picks them up automatically.
 */

export const SECTION_STYLE_DEFAULTS = {
    sectionPaddingTop: 'medium' as 'none' | 'small' | 'medium' | 'large',
    sectionPaddingBottom: 'medium' as 'none' | 'small' | 'medium' | 'large',
    sectionBackground: '' as string, // '' = use the section's default background
    sectionWidth: 'contained' as 'contained' | 'full',
    sectionTextAlign: '' as string, // '' = inherit from block layout
    hidden: false as boolean, // hide a section on the live storefront without deleting it
};

export function sectionStyleFields(): FieldDef[] {
    return [
        { id: 'group-section', type: 'static', format: 'title', label: 'Section style' },
        {
            id: 'sectionPaddingTop',
            label: 'Top padding',
            type: 'items',
            format: 'dropdown-list',
            options: [
                { label: 'None', value: 'none' },
                { label: 'Small', value: 'small' },
                { label: 'Medium', value: 'medium' },
                { label: 'Large', value: 'large' },
            ],
        },
        {
            id: 'sectionPaddingBottom',
            label: 'Bottom padding',
            type: 'items',
            format: 'dropdown-list',
            options: [
                { label: 'None', value: 'none' },
                { label: 'Small', value: 'small' },
                { label: 'Medium', value: 'medium' },
                { label: 'Large', value: 'large' },
            ],
        },
        {
            id: 'sectionBackground',
            label: 'Background',
            type: 'items',
            format: 'color-scheme',
            options: [{ label: 'Default', value: '' }],
        },
        {
            id: 'sectionWidth',
            label: 'Width',
            type: 'items',
            format: 'dropdown-list',
            options: [
                { label: 'Contained', value: 'contained' },
                { label: 'Full width', value: 'full' },
            ],
        },
        {
            id: 'sectionTextAlign',
            label: 'Text alignment',
            type: 'items',
            format: 'text-align',
        },
    ];
}

/** Padding scale → Tailwind classes, shared with the Blade wrapper. */
export const PADDING_CLASS: Record<string, string> = {
    none: '',
    small: 'pt-6',
    medium: 'pt-12',
    large: 'pt-20',
};

export const PADDING_BOTTOM_CLASS: Record<string, string> = {
    none: '',
    small: 'pb-6',
    medium: 'pb-12',
    large: 'pb-20',
};
