import type { FieldDef } from './fields';

/**
 * Header & footer structured-editing field schemas. The values live on
 * `Theme.header` and `Theme.footer` (separate from the block tree and the
 * theme settings), and are persisted by `BuilderController@save/publish`.
 *
 * Non-technical: every link is a dropdown + optional external URL, and every
 * category/payment/social reference is a name-based dropdown (no raw ids).
 */

const group = (label: string): FieldDef => ({ id: `group-${label}`, type: 'static', format: 'title', label });

export const HEADER_FIELDS: FieldDef[] = [
    group('Logo'),
    { id: 'logo_image', label: 'Logo image', type: 'string', format: 'image', dimensions: { width: 200, height: 60 } },
    { id: 'logo_text', label: 'Logo text (if no image)', type: 'string', format: 'text' },
    group('Colors'),
    { id: 'background_color', label: 'Background', type: 'string', format: 'color' },
    { id: 'text_color', label: 'Text', type: 'string', format: 'color' },
    { id: 'accent_color', label: 'Accent', type: 'string', format: 'color' },
    group('Menu'),
    {
        id: 'menu',
        label: 'Custom menu links',
        type: 'collection',
        itemLabel: 'Link',
        minLength: 0,
        maxLength: 12,
        itemFields: [
            { id: 'label', label: 'Label', type: 'string', format: 'text', multilanguage: true, default: { ar: '', en: '' } },
            { id: 'url', label: 'Link', type: 'string', format: 'link', default: '' },
        ],
    },
    { id: 'category_count', label: 'Category links (auto)', type: 'number', format: 'text', min: 0, max: 12 },
    group('Options'),
    { id: 'sticky', label: 'Sticky', type: 'boolean', format: 'switch' },
    { id: 'show_search', label: 'Search box', type: 'boolean', format: 'switch' },
    { id: 'show_account', label: 'Account icon', type: 'boolean', format: 'switch' },
    { id: 'show_cart', label: 'Cart icon', type: 'boolean', format: 'switch' },
];

export const FOOTER_FIELDS: FieldDef[] = [
    group('Brand'),
    { id: 'show_logo', label: 'Show store name', type: 'boolean', format: 'switch' },
    { id: 'tagline', label: 'Tagline', type: 'string', format: 'textarea', multilanguage: true },
    { id: 'copyright_text', label: 'Copyright text', type: 'string', format: 'text' },
    group('Colors'),
    { id: 'background_color', label: 'Background', type: 'string', format: 'color' },
    { id: 'text_color', label: 'Text', type: 'string', format: 'color' },
    { id: 'muted_color', label: 'Muted', type: 'string', format: 'color' },
    group('Link columns'),
    {
        id: 'columns',
        label: 'Columns',
        type: 'collection',
        itemLabel: 'Column',
        minLength: 0,
        maxLength: 4,
        itemFields: [
            { id: 'heading', label: 'Heading', type: 'string', format: 'text', multilanguage: true, default: { ar: '', en: '' } },
            {
                id: 'links',
                label: 'Links',
                type: 'collection',
                itemLabel: 'Link',
                minLength: 0,
                maxLength: 10,
                itemFields: [
                    { id: 'label', label: 'Label', type: 'string', format: 'text', multilanguage: true, default: { ar: '', en: '' } },
                    { id: 'url', label: 'Link', type: 'string', format: 'link', default: '' },
                ],
            },
        ],
    },
    group('Social'),
    {
        id: 'social',
        label: 'Social links',
        type: 'collection',
        itemLabel: 'Network',
        minLength: 0,
        maxLength: 6,
        itemFields: [
            {
                id: 'platform',
                label: 'Platform',
                type: 'items',
                format: 'dropdown-list',
                options: [
                    { label: 'Instagram', value: 'instagram' },
                    { label: 'Twitter / X', value: 'twitter' },
                    { label: 'Snapchat', value: 'snapchat' },
                    { label: 'TikTok', value: 'tiktok' },
                    { label: 'WhatsApp', value: 'whatsapp' },
                    { label: 'YouTube', value: 'youtube' },
                    { label: 'Facebook', value: 'facebook' },
                ],
                default: 'instagram',
            },
            { id: 'url', label: 'URL', type: 'string', format: 'link', default: '' },
        ],
    },
    group('Payment icons'),
    {
        id: 'payment_icons',
        label: 'Payment methods',
        type: 'string',
        format: 'multi-select',
        options: [
            { label: 'mada', value: 'mada' },
            { label: 'Visa', value: 'visa' },
            { label: 'Mastercard', value: 'mastercard' },
            { label: 'Apple Pay', value: 'applepay' },
            { label: 'STC Pay', value: 'stcpay' },
            { label: 'Cash on delivery', value: 'cod' },
        ],
    },
];
