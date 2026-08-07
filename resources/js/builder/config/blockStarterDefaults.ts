/** Starter repeater rows — keep in sync with `App\Support\Builder\BlockStarterDefaults`. */

export const STARTER_TRUST_BADGES = [
    { label: { ar: 'توصيل سريع', en: 'Fast delivery' }, icon: 'truck' },
    { label: { ar: 'دفع آمن', en: 'Secure payment' }, icon: 'shield' },
    { label: { ar: 'إرجاع سهل', en: 'Easy returns' }, icon: 'refresh' },
    { label: { ar: 'ضمان الجودة', en: 'Quality guarantee' }, icon: 'badge' },
] as const;

export const STARTER_SLIDESHOW_SLIDES = [
    {
        image: null,
        overlayOpacity: 30,
        title: { ar: 'عنوان الشريحة', en: 'Slide headline' },
        subtitle: { ar: 'نص تعريفي قصير', en: 'Short supporting text' },
        ctaText: { ar: 'تسوق الآن', en: 'Shop now' },
        ctaUrl: '/collections/all',
    },
] as const;

export const STARTER_MULTICOLUMN_ITEMS = [
    {
        image: null,
        icon: 'truck',
        label: { ar: 'توصيل سريع', en: 'Fast delivery' },
        text: { ar: 'نوصّل طلبك بسرعة إلى باب منزلك.', en: 'We deliver quickly to your door.' },
        ctaText: { ar: '', en: '' },
        url: '',
    },
    {
        image: null,
        icon: 'shield',
        label: { ar: 'دفع آمن', en: 'Secure payment' },
        text: { ar: 'مدى، Apple Pay، والدفع عند الاستلام.', en: 'mada, Apple Pay, and cash on delivery.' },
        ctaText: { ar: '', en: '' },
        url: '',
    },
    {
        image: null,
        icon: 'refresh',
        label: { ar: 'إرجاع سهل', en: 'Easy returns' },
        text: { ar: 'سياسة إرجاع واضحة وسهلة.', en: 'Clear, hassle-free returns.' },
        ctaText: { ar: '', en: '' },
        url: '',
    },
] as const;

export const STARTER_COLLAGE_ITEMS = [
    { image: null, url: '' },
    { image: null, url: '' },
] as const;

export const STARTER_BRAND_ITEMS = [
    { image: null, label: { ar: 'علامة ١', en: 'Brand 1' }, url: '' },
    { image: null, label: { ar: 'علامة ٢', en: 'Brand 2' }, url: '' },
    { image: null, label: { ar: 'علامة ٣', en: 'Brand 3' }, url: '' },
] as const;

export const STARTER_COLLAPSIBLE_ROWS = [
    {
        heading: { ar: 'تفاصيل الشحن', en: 'Shipping details' },
        content: {
            ar: '<p>نوصّل داخل المملكة خلال ٢–٥ أيام عمل.</p>',
            en: '<p>We ship across Saudi Arabia in 2–5 business days.</p>',
        },
    },
] as const;
