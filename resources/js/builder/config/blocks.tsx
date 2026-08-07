import type { ComponentType } from 'react';
import type { FieldDef } from './fields';
import { sectionStyleFields, SECTION_STYLE_DEFAULTS } from './sectionStyle';
import { Hero } from '../blocks/Hero';
import { ProductsGrid } from '../blocks/ProductsGrid';
import { ProductsTabs } from '../blocks/ProductsTabs';
import { BannersSingle } from '../blocks/BannersSingle';
import { BannersDouble } from '../blocks/BannersDouble';
import { CategoryGrid } from '../blocks/CategoryGrid';
import { PromoBar } from '../blocks/PromoBar';
import { RichText } from '../blocks/RichText';
import { Testimonials } from '../blocks/Testimonials';
import { FaqAccordion } from '../blocks/FaqAccordion';
import { TrustBadges } from '../blocks/TrustBadges';
import { Countdown } from '../blocks/Countdown';
import { ProductsFeatured } from '../blocks/ProductsFeatured';
import { Slideshow } from '../blocks/Slideshow';
import { ProductsCarousel } from '../blocks/ProductsCarousel';
import { ImageWithText } from '../blocks/ImageWithText';
import { Multicolumn } from '../blocks/Multicolumn';
import { Video } from '../blocks/Video';
import { Brands } from '../blocks/Brands';
import { CategoryShortcuts } from '../blocks/CategoryShortcuts';
import { Collage } from '../blocks/Collage';
import { CollapsibleContent } from '../blocks/CollapsibleContent';
import { ParallaxBanner } from '../blocks/ParallaxBanner';
import { ContactForm } from '../blocks/ContactForm';
import { CustomHtml } from '../blocks/CustomHtml';
import { BlogArticle } from '../blocks/BlogArticle';
import { BlogCategoriesGrid } from '../blocks/BlogCategoriesGrid';
import { BlogPostsGrid } from '../blocks/BlogPostsGrid';
import {
    STARTER_BRAND_ITEMS,
    STARTER_COLLAGE_ITEMS,
    STARTER_COLLAPSIBLE_ROWS,
    STARTER_MULTICOLUMN_ITEMS,
    STARTER_SLIDESHOW_SLIDES,
    STARTER_TRUST_BADGES,
} from './blockStarterDefaults';

/**
 * Per-block schema: the React preview component, its palette category, the
 * ordered settings `fields`, and the `defaults` used when inserting a new block.
 *
 * `defaults` mirrors the PHP `BlockRegistry::registerCoreBlocks()` (the
 * server-side source of truth) so an inserted block matches what the server
 * would normalize. Bilingual values are `{ ar, en }`; repeaters are arrays.
 */
export interface BlockSchema {
    Component: ComponentType<Record<string, unknown>>;
    category: string;
    fields: FieldDef[];
    defaults: Record<string, unknown>;
}

// ─────────────────────────────────────────────────────────────────────────────
// Field builders — keep the per-block declarations terse and regular.
// ─────────────────────────────────────────────────────────────────────────────

const t = (id: string, label: string): FieldDef => ({
    id, label, type: 'string', format: 'text', multilanguage: true,
});

const ta = (id: string, label: string): FieldDef => ({
    id, label, type: 'string', format: 'textarea', multilanguage: true,
});

const rt = (id: string, label: string): FieldDef => ({
    id, label, type: 'string', format: 'richtext', multilanguage: true,
});

const plain = (id: string, label: string): FieldDef => ({
    id, label, type: 'string', format: 'text',
});

const url = (id: string, label: string): FieldDef => ({
    id, label, type: 'string', format: 'link',
});

const num = (id: string, label: string, min = 0, max = 100): FieldDef => ({
    id, label, type: 'number', format: 'text', min: min, max: max,
});

const sel = (id: string, label: string, options: { label: string; value: string }[]): FieldDef => ({
    id, label, type: 'items', format: 'dropdown-list', options,
});

const toggle = (id: string, label: string): FieldDef => ({
    id, label, type: 'boolean', format: 'switch',
});

const image = (id: string, label: string, width: number, height: number): FieldDef => ({
    id, label, type: 'string', format: 'image', dimensions: { width, height },
});

const product = (id: string, label: string): FieldDef => ({
    id, label, type: 'string', format: 'product',
});

const collection = (
    id: string,
    label: string,
    itemLabel: string,
    itemFields: FieldDef[],
    minLength = 0,
    maxLength = 50,
): FieldDef => ({
    id, label, type: 'collection', itemLabel, itemFields, minLength, maxLength,
});

const group = (label: string): FieldDef => ({ id: `group-${label}`, type: 'static', format: 'title', label });

const visibility = (): FieldDef[] => [
    { id: 'visibleOnDesktop', label: 'Show on desktop', type: 'boolean', format: 'switch' },
    { id: 'visibleOnMobile', label: 'Show on mobile', type: 'boolean', format: 'switch' },
];

// Reusable option sets.
const sourceOptions = [
    { label: 'Latest', value: 'latest' },
    { label: 'Bestsellers', value: 'bestseller' },
    { label: 'Category', value: 'category' },
    { label: 'Hand-pick', value: 'handpick' },
];

const heightOptions = [
    { label: 'Small', value: 'small' },
    { label: 'Medium', value: 'medium' },
    { label: 'Large', value: 'large' },
];

const alignOptions = [
    { label: 'Start', value: 'start' },
    { label: 'Center', value: 'center' },
    { label: 'End', value: 'end' },
];

const overlayField = (id = 'overlayOpacity', label = 'Overlay opacity'): FieldDef => ({
    id, label, type: 'number', format: 'range', min: 0, max: 80,
});

const heightField = (id = 'height', label = 'Height'): FieldDef => ({
    id, label, type: 'items', format: 'dropdown-list', options: heightOptions,
});

/** Category picker as a `category` format field (single). */
const categoryField = (id: string, label: string, required = false): FieldDef => ({
    id, label, type: 'number', format: 'category',
    conditions: required ? [] : [{ id: 'source', operation: '=', value: 'category' }],
});

/** Hand-pick products; shown only when source === 'handpick'. */
const handpickField = (id = 'productIds', label = 'Products'): FieldDef => ({
    id, label, type: 'string', format: 'product-list',
    conditions: [{ id: 'source', operation: '=', value: 'handpick' }],
});

const blogSourceOptions = [
    { label: 'Latest', value: 'latest' },
    { label: 'Featured', value: 'featured' },
    { label: 'Hand-pick', value: 'handpick' },
];

/** Single blog-post picker (BlogArticle). */
const blogPostField = (id: string, label: string): FieldDef => ({
    id, label, type: 'number', format: 'blog-post',
});

/** Hand-pick blog posts (BlogPostsGrid); shown only when source === 'handpick'. */
const blogPostListField = (id = 'postIds', label = 'Posts'): FieldDef => ({
    id, label, type: 'string', format: 'blog-post-list',
    conditions: [{ id: 'source', operation: '=', value: 'handpick' }],
});

// ─────────────────────────────────────────────────────────────────────────────
// Block schemas
// ─────────────────────────────────────────────────────────────────────────────

export const BLOCK_SCHEMAS: Record<string, BlockSchema> = {
    Hero: {
        Component: Hero as ComponentType<Record<string, unknown>>,
        category: 'hero',
        fields: [
            group('Content'),
            ta('title', 'Title'),
            ta('subtitle', 'Subtitle'),
            image('backgroundImage', 'Background image', 1600, 600),
            overlayField(),
            heightField(),
            group('Call to action'),
            t('ctaText', 'Button text'),
            url('ctaUrl', 'Button link'),
            sel('layout', 'Layout', [
                { label: 'Centered', value: 'center' },
                { label: 'Start', value: 'start' },
                { label: 'End', value: 'end' },
            ]),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: 'متجرنا', en: 'Our Store' },
            subtitle: { ar: 'تسوق أحدث المنتجات', en: 'Shop the latest' },
            backgroundImage: null,
            overlayOpacity: 30,
            height: 'medium',
            ctaText: { ar: 'تسوق الآن', en: 'Shop now' },
            ctaUrl: '/collections/all',
            layout: 'center',
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    ProductsGrid: {
        Component: ProductsGrid as ComponentType<Record<string, unknown>>,
        category: 'products',
        fields: [
            group('Content'),
            t('title', 'Title'),
            sel('source', 'Product source', sourceOptions),
            categoryField('categoryId', 'Category'),
            handpickField(),
            num('columns', 'Columns', 1, 6),
            num('mobileColumns', 'Mobile columns', 1, 3),
            num('limit', 'Number of products', 1, 24),
            toggle('showAddToCart', 'Show add-to-cart'),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: 'منتجاتنا', en: 'Our Products' },
            source: 'latest',
            categoryId: null,
            productIds: [],
            columns: 4,
            mobileColumns: 2,
            limit: 8,
            showAddToCart: true,
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    BlogArticle: {
        Component: BlogArticle as ComponentType<Record<string, unknown>>,
        category: 'content',
        fields: [
            group('Content'),
            t('title', 'Title'),
            blogPostField('postId', 'Post'),
            toggle('showExcerpt', 'Show excerpt'),
            t('ctaText', 'Button text'),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: 'من المدوّنة', en: 'From the blog' },
            postId: null,
            showExcerpt: true,
            ctaText: { ar: 'اقرأ المقال', en: 'Read article' },
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    BlogCategoriesGrid: {
        Component: BlogCategoriesGrid as ComponentType<Record<string, unknown>>,
        category: 'content',
        fields: [
            group('Content'),
            t('title', 'Title'),
            num('columns', 'Columns', 1, 6),
            num('limit', 'Number of categories', 1, 12),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: 'تصفّح المواضيع', en: 'Browse topics' },
            columns: 4,
            limit: 8,
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    BlogPostsGrid: {
        Component: BlogPostsGrid as ComponentType<Record<string, unknown>>,
        category: 'content',
        fields: [
            group('Content'),
            t('title', 'Title'),
            sel('source', 'Post source', blogSourceOptions),
            blogPostListField(),
            num('columns', 'Columns', 1, 6),
            num('mobileColumns', 'Mobile columns', 1, 3),
            num('limit', 'Number of posts', 1, 12),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: 'أحدث المقالات', en: 'Latest articles' },
            source: 'latest',
            postIds: [],
            columns: 3,
            mobileColumns: 1,
            limit: 3,
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    ProductsTabs: {
        Component: ProductsTabs as ComponentType<Record<string, unknown>>,
        category: 'products',
        fields: [
            group('Tabs'),
            collection(
                'tabs', 'Tabs', 'Tab',
                [
                    { id: 'label', label: 'Label', type: 'string', format: 'text', multilanguage: true, default: { ar: '', en: '' } },
                    { id: 'source', label: 'Source', type: 'items', format: 'dropdown-list', options: sourceOptions, default: 'latest' },
                    {
                        id: 'categoryId', label: 'Category', type: 'number', format: 'category', default: null,
                        conditions: [{ id: 'source', operation: '=', value: 'category' }],
                    },
                    {
                        id: 'productIds', label: 'Products', type: 'string', format: 'product-list', default: [],
                        conditions: [{ id: 'source', operation: '=', value: 'handpick' }],
                    },
                ],
                1, 8,
            ),
            num('columns', 'Columns', 1, 6),
            num('mobileColumns', 'Mobile columns', 1, 3),
            num('limit', 'Products per tab', 1, 24),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            tabs: [
                { label: { ar: 'وصل حديثًا', en: 'New' }, source: 'latest', categoryId: null, productIds: [] },
                { label: { ar: 'الأكثر مبيعًا', en: 'Bestsellers' }, source: 'bestseller', categoryId: null, productIds: [] },
            ],
            columns: 4,
            mobileColumns: 2,
            limit: 8,
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    BannersSingle: {
        Component: BannersSingle as ComponentType<Record<string, unknown>>,
        category: 'banners',
        fields: [
            group('Content'),
            image('image', 'Image', 1200, 500),
            overlayField(),
            heightField(),
            t('title', 'Title'),
            t('subtitle', 'Subtitle'),
            group('Call to action'),
            t('ctaText', 'Button text'),
            url('ctaUrl', 'Button link'),
            sel('align', 'Alignment', alignOptions),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            image: null,
            overlayOpacity: 40,
            height: 'medium',
            title: { ar: '', en: '' },
            subtitle: { ar: '', en: '' },
            ctaText: { ar: '', en: '' },
            ctaUrl: '',
            align: 'center',
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    BannersDouble: {
        Component: BannersDouble as ComponentType<Record<string, unknown>>,
        category: 'banners',
        fields: [
            group('Left banner'),
            image('leftImage', 'Image', 800, 600),
            t('leftTitle', 'Title'),
            url('leftUrl', 'Link'),
            group('Right banner'),
            image('rightImage', 'Image', 800, 600),
            t('rightTitle', 'Title'),
            url('rightUrl', 'Link'),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            leftImage: null,
            leftTitle: { ar: '', en: '' },
            leftUrl: '',
            rightImage: null,
            rightTitle: { ar: '', en: '' },
            rightUrl: '',
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    CategoryGrid: {
        Component: CategoryGrid as ComponentType<Record<string, unknown>>,
        category: 'categories',
        fields: [
            group('Content'),
            t('title', 'Title'),
            num('columns', 'Columns', 1, 8),
            num('limit', 'Number of categories', 1, 24),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: 'تسوق حسب الفئة', en: 'Shop by category' },
            columns: 4,
            limit: 8,
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    PromoBar: {
        Component: PromoBar as ComponentType<Record<string, unknown>>,
        category: 'layout',
        fields: [
            group('Content'),
            t('text', 'Text'),
            url('url', 'Link'),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            text: { ar: 'شحن مجاني للطلبات فوق 200 ريال', en: 'Free shipping over 200 SAR' },
            url: '',
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    RichText: {
        Component: RichText as ComponentType<Record<string, unknown>>,
        category: 'content',
        fields: [
            group('Content'),
            t('title', 'Title'),
            rt('content', 'Body'),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: '', en: '' },
            content: { ar: '', en: '' },
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    Testimonials: {
        Component: Testimonials as ComponentType<Record<string, unknown>>,
        category: 'content',
        fields: [
            group('Content'),
            t('title', 'Title'),
            collection(
                'items', 'Testimonials', 'Testimonial',
                [
                    { id: 'quote', label: 'Quote', type: 'string', format: 'textarea', multilanguage: true, default: { ar: '', en: '' } },
                    { id: 'author', label: 'Author', type: 'string', format: 'text', multilanguage: true, default: { ar: '', en: '' } },
                ],
                0, 20,
            ),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: 'آراء عملائنا', en: 'What our customers say' },
            items: [],
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    FaqAccordion: {
        Component: FaqAccordion as ComponentType<Record<string, unknown>>,
        category: 'content',
        fields: [
            group('Content'),
            t('title', 'Title'),
            collection(
                'items', 'Questions', 'Question',
                [
                    { id: 'question', label: 'Question', type: 'string', format: 'text', multilanguage: true, default: { ar: '', en: '' } },
                    { id: 'answer', label: 'Answer', type: 'string', format: 'textarea', multilanguage: true, default: { ar: '', en: '' } },
                ],
                0, 50,
            ),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: 'الأسئلة الشائعة', en: 'Frequently asked questions' },
            items: [],
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    TrustBadges: {
        Component: TrustBadges as ComponentType<Record<string, unknown>>,
        category: 'layout',
        fields: [
            collection(
                'items', 'Badges', 'Badge',
                [
                    { id: 'label', label: 'Label', type: 'string', format: 'text', multilanguage: true, default: { ar: '', en: '' } },
                    { id: 'icon', label: 'Icon', type: 'string', format: 'icon', default: 'check' },
                ],
                1, 8,
            ),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            items: [...STARTER_TRUST_BADGES],
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    Countdown: {
        Component: Countdown as ComponentType<Record<string, unknown>>,
        category: 'marketing',
        fields: [
            group('Content'),
            t('title', 'Title'),
            {
                id: 'endsAt', label: 'End date & time', type: 'string', format: 'datetime',
                description: 'When the countdown ends.',
            } as FieldDef,
            group('Call to action'),
            t('ctaText', 'Button text'),
            url('ctaUrl', 'Button link'),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: 'ينتهي العرض قريبًا', en: 'Offer ends soon' },
            endsAt: null,
            ctaText: { ar: 'تسوق العرض', en: 'Shop the offer' },
            ctaUrl: '',
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    ProductsFeatured: {
        Component: ProductsFeatured as ComponentType<Record<string, unknown>>,
        category: 'products',
        fields: [
            group('Content'),
            t('title', 'Title'),
            product('productId', 'Featured product'),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: 'منتج مميز', en: 'Featured product' },
            productId: null,
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    Slideshow: {
        Component: Slideshow as ComponentType<Record<string, unknown>>,
        category: 'hero',
        fields: [
            group('Slides'),
            collection(
                'slides', 'Slides', 'Slide',
                [
                    { id: 'image', label: 'Image', type: 'string', format: 'image', default: null, dimensions: { width: 1600, height: 600 } },
                    { id: 'overlayOpacity', label: 'Overlay opacity', type: 'number', format: 'range', min: 0, max: 80, default: 30 },
                    { id: 'title', label: 'Title', type: 'string', format: 'text', multilanguage: true, default: { ar: '', en: '' } },
                    { id: 'subtitle', label: 'Subtitle', type: 'string', format: 'textarea', multilanguage: true, default: { ar: '', en: '' } },
                    { id: 'ctaText', label: 'Button text', type: 'string', format: 'text', multilanguage: true, default: { ar: '', en: '' } },
                    { id: 'ctaUrl', label: 'Button link', type: 'string', format: 'link', default: '' },
                ],
                1, 10,
            ),
            group('Behavior'),
            toggle('autoplay', 'Auto-rotate slides'),
            num('interval', 'Seconds per slide', 3, 15),
            heightField(),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            slides: [...STARTER_SLIDESHOW_SLIDES],
            autoplay: true,
            interval: 5,
            height: 'medium',
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    ProductsCarousel: {
        Component: ProductsCarousel as ComponentType<Record<string, unknown>>,
        category: 'products',
        fields: [
            group('Content'),
            t('title', 'Title'),
            sel('source', 'Product source', sourceOptions),
            categoryField('categoryId', 'Category'),
            handpickField(),
            num('limit', 'Number of products', 1, 20),
            toggle('autoplay', 'Auto-scroll'),
            {
                id: 'interval',
                label: 'Auto-scroll interval (seconds)',
                type: 'number',
                format: 'text',
                min: 3,
                max: 15,
                conditions: [{ id: 'autoplay', operation: '=', value: true }],
            } as FieldDef,
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: 'منتجات مختارة', en: 'Selected products' },
            source: 'latest',
            categoryId: null,
            productIds: [],
            limit: 10,
            autoplay: false,
            interval: 5,
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    ImageWithText: {
        Component: ImageWithText as ComponentType<Record<string, unknown>>,
        category: 'content',
        fields: [
            group('Image'),
            image('image', 'Image', 800, 600),
            sel('imagePosition', 'Image position', [
                { label: 'Start', value: 'start' },
                { label: 'End', value: 'end' },
            ]),
            group('Text'),
            t('title', 'Title'),
            rt('content', 'Body'),
            t('ctaText', 'Button text'),
            url('ctaUrl', 'Button link'),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            image: null,
            imagePosition: 'start',
            title: { ar: '', en: '' },
            content: { ar: '', en: '' },
            ctaText: { ar: '', en: '' },
            ctaUrl: '',
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    Multicolumn: {
        Component: Multicolumn as ComponentType<Record<string, unknown>>,
        category: 'content',
        fields: [
            group('Content'),
            t('title', 'Title'),
            num('columns', 'Columns', 1, 5),
            collection(
                'items', 'Columns', 'Column',
                [
                    { id: 'image', label: 'Image (optional)', type: 'string', format: 'image', default: null, dimensions: { width: 200, height: 200 } },
                    { id: 'icon', label: 'Icon (if no image)', type: 'string', format: 'icon', default: 'check' },
                    { id: 'label', label: 'Title', type: 'string', format: 'text', multilanguage: true, default: { ar: '', en: '' } },
                    { id: 'text', label: 'Text', type: 'string', format: 'textarea', multilanguage: true, default: { ar: '', en: '' } },
                    { id: 'ctaText', label: 'Button text', type: 'string', format: 'text', multilanguage: true, default: { ar: '', en: '' } },
                    { id: 'url', label: 'Link', type: 'string', format: 'link', default: '' },
                ],
                1, 8,
            ),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: '', en: '' },
            columns: 3,
            items: [...STARTER_MULTICOLUMN_ITEMS],
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    Video: {
        Component: Video as ComponentType<Record<string, unknown>>,
        category: 'content',
        fields: [
            group('Content'),
            t('title', 'Title'),
            { id: 'videoUrl', label: 'Video URL', type: 'string', format: 'video' } as FieldDef,
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: '', en: '' },
            videoUrl: '',
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    Brands: {
        Component: Brands as ComponentType<Record<string, unknown>>,
        category: 'content',
        fields: [
            group('Content'),
            t('title', 'Title'),
            collection(
                'items', 'Brands', 'Brand',
                [
                    { id: 'image', label: 'Logo', type: 'string', format: 'image', default: null, dimensions: { width: 200, height: 80 } },
                    { id: 'label', label: 'Name', type: 'string', format: 'text', multilanguage: true, default: { ar: '', en: '' } },
                    { id: 'url', label: 'Link', type: 'string', format: 'link', default: '' },
                ],
                1, 12,
            ),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: '', en: '' },
            items: [...STARTER_BRAND_ITEMS],
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    CategoryShortcuts: {
        Component: CategoryShortcuts as ComponentType<Record<string, unknown>>,
        category: 'categories',
        fields: [
            group('Content'),
            t('title', 'Title'),
            collection(
                'items', 'Shortcuts', 'Shortcut',
                [
                    { id: 'categoryId', label: 'Category', type: 'number', format: 'category', default: null },
                    { id: 'label', label: 'Custom label', type: 'string', format: 'text', multilanguage: true, default: { ar: '', en: '' } },
                    { id: 'icon', label: 'Icon', type: 'string', format: 'icon', default: 'box' },
                ],
                1, 8,
            ),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: 'تسوق حسب الفئة', en: 'Shop by category' },
            items: [],
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    Collage: {
        Component: Collage as ComponentType<Record<string, unknown>>,
        category: 'banners',
        fields: [
            collection(
                'items', 'Images', 'Image',
                [
                    { id: 'image', label: 'Image', type: 'string', format: 'image', default: null, dimensions: { width: 800, height: 800 } },
                    { id: 'url', label: 'Link', type: 'string', format: 'link', default: '' },
                ],
                2, 5,
            ),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            items: [...STARTER_COLLAGE_ITEMS],
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    CollapsibleContent: {
        Component: CollapsibleContent as ComponentType<Record<string, unknown>>,
        category: 'content',
        fields: [
            group('Content'),
            t('title', 'Title'),
            collection(
                'items', 'Rows', 'Row',
                [
                    { id: 'heading', label: 'Heading', type: 'string', format: 'text', multilanguage: true, default: { ar: '', en: '' } },
                    { id: 'content', label: 'Content', type: 'string', format: 'richtext', multilanguage: true, default: { ar: '', en: '' } },
                ],
                1, 20,
            ),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: '', en: '' },
            items: [...STARTER_COLLAPSIBLE_ROWS],
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    ParallaxBanner: {
        Component: ParallaxBanner as ComponentType<Record<string, unknown>>,
        category: 'banners',
        fields: [
            group('Content'),
            image('image', 'Background image', 1600, 800),
            overlayField(),
            t('title', 'Title'),
            ta('subtitle', 'Subtitle'),
            t('ctaText', 'Button text'),
            url('ctaUrl', 'Button link'),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            image: null,
            overlayOpacity: 40,
            title: { ar: '', en: '' },
            subtitle: { ar: '', en: '' },
            ctaText: { ar: '', en: '' },
            ctaUrl: '',
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    ContactForm: {
        Component: ContactForm as ComponentType<Record<string, unknown>>,
        category: 'content',
        fields: [
            group('Content'),
            t('title', 'Title'),
            ta('subtitle', 'Subtitle'),
            toggle('showPhone', 'Show phone field'),
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            title: { ar: 'تواصل معنا', en: 'Contact us' },
            subtitle: { ar: '', en: '' },
            showPhone: true,
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },

    CustomHtml: {
        Component: CustomHtml as ComponentType<Record<string, unknown>>,
        category: 'layout',
        fields: [
            group('Safe markup'),
            {
                id: 'html',
                label: 'Custom HTML',
                type: 'string',
                format: 'html',
                description: 'Safe markup only — script tags, inline event handlers, and javascript: URLs are stripped on save.',
            } as FieldDef,
            ...sectionStyleFields(),
            group('Visibility'),
            ...visibility(),
        ],
        defaults: {
            ...SECTION_STYLE_DEFAULTS,
            html: '',
            visibleOnDesktop: true,
            visibleOnMobile: true,
        },
    },
};

/** Default props for a new block of `type` (deep clone so edits never mutate the schema). */
export function defaultPropsFor(type: string): Record<string, unknown> {
    const schema = BLOCK_SCHEMAS[type];
    if (!schema) return {};
    return structuredClone(schema.defaults);
}

/** A blank item for a collection field, built from its itemFields' defaults. */
export function defaultCollectionItem(field: FieldDef): Record<string, unknown> {
    const item: Record<string, unknown> = {};
    for (const f of field.itemFields ?? []) {
        item[f.id] = f.default !== undefined ? structuredClone(f.default) : defaultScalarFor(f);
    }
    return item;
}

function defaultScalarFor(field: FieldDef): unknown {
    if (field.multilanguage) return { ar: '', en: '' };
    switch (field.type) {
        case 'boolean': return false;
        case 'number': return 0;
        case 'string':
        case 'items':
        default:
            return '';
    }
}
