import type { FieldDef } from './fields';
import type { ColorScheme } from '../types/settings';

/**
 * Salla-style "what to show" settings, expressed as `FieldDef[]` groups bound to
 * slices of `ThemeSettings`. Each group's `key` is the property on the settings
 * object it edits (e.g. `colors`, `product_card`). Rendered schema-driven via
 * `SettingsForm`/`FieldRenderer`.
 */

export const FONT_OPTIONS = [
    { label: 'IBM Plex Sans Arabic', value: 'IBM Plex Sans Arabic' },
    { label: 'Tajawal', value: 'Tajawal' },
    { label: 'Almarai', value: 'Almarai' },
    { label: 'Cairo', value: 'Cairo' },
    { label: 'Noto Sans Arabic', value: 'Noto Sans Arabic' },
    { label: 'System UI', value: 'system-ui' },
];

/** Build a `{label, value}` list of schemes from the live settings, used by
 *  the per-section `color-scheme` format in `FieldControl`. */
export function schemeOptions(schemes: ColorScheme[] | undefined): Array<{ label: string; value: string }> {
    return [
        { label: 'Default', value: '' },
        ...(schemes ?? []).map((s) => ({ label: s.name || s.key, value: s.key })),
    ];
}

// ── Theme tab ────────────────────────────────────────────────────────────────

const toggle = (id: string, label: string): FieldDef => ({ id, label, type: 'boolean', format: 'switch' });
const group = (label: string): FieldDef => ({ id: `group-${label}`, type: 'static', format: 'title', label });

export const THEME_TAB_GROUPS: Array<{ title: string; key: string; fields: FieldDef[] }> = [
    {
        title: 'Color schemes',
        key: 'color_schemes',
        fields: [
            group('Color schemes'),
            {
                id: 'color_schemes',
                label: 'Schemes',
                type: 'collection',
                itemLabel: 'Scheme',
                minLength: 1,
                maxLength: 6,
                itemFields: [
                    { id: 'key', label: 'Key', type: 'string', format: 'text', default: '', description: 'CSS-safe slug (a-z, 0-9, hyphens). Auto-derived from name.' },
                    { id: 'name', label: 'Name', type: 'string', format: 'text', default: 'Scheme' },
                    { id: 'background', label: 'Background', type: 'string', format: 'color', default: '#FFFFFF' },
                    { id: 'surface', label: 'Surface', type: 'string', format: 'color', default: '#F9FAFB' },
                    { id: 'text', label: 'Text', type: 'string', format: 'color', default: '#1a1a1f' },
                    { id: 'muted', label: 'Muted', type: 'string', format: 'color', default: '#6B7280' },
                    { id: 'primary', label: 'Primary', type: 'string', format: 'color', default: '#458FCC' },
                    { id: 'button', label: 'Button fill', type: 'string', format: 'color', default: '#2F7AB5' },
                    { id: 'button_text', label: 'Button text', type: 'string', format: 'color', default: '#FFFFFF' },
                    { id: 'accent', label: 'Accent', type: 'string', format: 'color', default: '#8A30C7' },
                ],
            } as FieldDef,
        ],
    },
    {
        title: 'Colors',
        key: 'colors',
        fields: [
            group('Colors'),
            { id: 'primary', label: 'Primary', type: 'string', format: 'color' },
            { id: 'secondary', label: 'Secondary', type: 'string', format: 'color' },
            { id: 'accent', label: 'Accent', type: 'string', format: 'color' },
            { id: 'background', label: 'Background', type: 'string', format: 'color' },
            { id: 'surface', label: 'Surface', type: 'string', format: 'color' },
            { id: 'text', label: 'Text', type: 'string', format: 'color' },
            { id: 'muted', label: 'Muted', type: 'string', format: 'color' },
        ],
    },
    {
        title: 'Typography',
        key: 'typography',
        fields: [
            group('Typography'),
            { id: 'heading_font', label: 'Heading font', type: 'items', format: 'dropdown-list', options: FONT_OPTIONS },
            { id: 'body_font', label: 'Body font', type: 'items', format: 'dropdown-list', options: FONT_OPTIONS },
            { id: 'base_size', label: 'Base size (px)', type: 'number', format: 'text', min: 12, max: 20 },
        ],
    },
    {
        title: 'Radius',
        key: 'radius',
        fields: [
            group('Radius'),
            { id: 'card', label: 'Card', type: 'number', format: 'range', min: 0, max: 24 },
            { id: 'button', label: 'Button', type: 'number', format: 'range', min: 0, max: 24 },
        ],
    },
    {
        title: 'Effects',
        key: 'effects',
        fields: [
            group('Effects'),
            toggle('product_border', 'Product card border'),
            toggle('shine_animation', 'Shine animation on hover'),
        ],
    },
];

// ── Product card ─────────────────────────────────────────────────────────────

export const PRODUCT_CARD_FIELDS: FieldDef[] = [
    group('Card content'),
    toggle('show_image', 'Show image'),
    toggle('show_title', 'Show title'),
    toggle('show_price', 'Show price'),
    toggle('show_compare_price', 'Show compare-at price'),
    toggle('show_rating', 'Show rating'),
    toggle('show_add_to_cart', 'Show add-to-cart'),
    group('Badges'),
    toggle('show_sale_badge', 'Sale badge'),
    toggle('show_new_badge', 'New badge'),
    toggle('show_out_of_stock_badge', 'Out of stock'),
    group('Style'),
    toggle('border', 'Card border'),
    toggle('hover_zoom', 'Hover zoom'),
];

// ── Product page ─────────────────────────────────────────────────────────────

export const PRODUCT_PAGE_FIELDS: FieldDef[] = [
    group('Layout'),
    toggle('breadcrumbs', 'Breadcrumbs'),
    toggle('show_rating', 'Show rating'),
    toggle('show_related', 'Show related products'),
    toggle('show_share', 'Social share'),
    toggle('show_quantity_stepper', 'Quantity stepper'),
    toggle('buy_now_button', 'Buy now button'),
    toggle('sticky_purchase_bar', 'Sticky purchase bar (desktop)'),
];

// ── Cart ─────────────────────────────────────────────────────────────────────

export const CART_FIELDS: FieldDef[] = [
    group('Cart'),
    toggle('free_shipping_bar', 'Free-shipping progress bar'),
    { id: 'free_shipping_threshold', label: 'Free-shipping threshold (riyals)', type: 'number', format: 'text', description: 'e.g. 200 = 200 SAR.' },
    toggle('show_coupon', 'Coupon field'),
    toggle('show_cross_sell', 'You may also like'),
    toggle('show_order_summary', 'Order summary'),
];
