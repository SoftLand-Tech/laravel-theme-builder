<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Builder;

/**
 * Registry of all block types available in the builder.
 *
 * Each block type maps to:
 * - A Blade component (storefront render) under the `theme-builder::` namespace
 * - A React component name (editor preview)
 * - A JSON schema for validation (mirrored in resources/js/builder/config/blocks.tsx)
 * - A default props structure
 */
final class BlockRegistry
{
    /** @var array<string, BlockDefinition> */
    private array $blocks = [];

    public function __construct()
    {
        $this->registerCoreBlocks();
    }

    public function register(BlockDefinition $definition): void
    {
        $this->blocks[$definition->type] = $definition;
    }

    public function has(string $type): bool
    {
        return isset($this->blocks[$type]);
    }

    public function get(string $type): ?BlockDefinition
    {
        return $this->blocks[$type] ?? null;
    }

    /**
     * @return array<string, BlockDefinition>
     */
    public function all(): array
    {
        return $this->blocks;
    }

    /**
     * @return array<int, array{type: string, label: string, category: string, icon: string}>
     */
    public function forEditor(): array
    {
        return collect($this->blocks)
            ->map(fn (BlockDefinition $b) => [
                'type' => $b->type,
                'label' => $b->label,
                'category' => $b->category->value,
                'icon' => $b->icon,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    public function defaultsFor(string $type): array
    {
        return $this->blocks[$type]?->defaults ?? [];
    }

    /**
     * Validate and normalize a block's props against the registry.
     * Unknown types are dropped; missing keys get defaults merged.
     *
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>|null
     */
    public function normalize(array $block): ?array
    {
        $type = $block['type'] ?? null;

        if (! is_string($type) || ! $this->has($type)) {
            return null;
        }

        $definition = $this->blocks[$type];
        $props = $block['props'] ?? [];
        $props = array_merge($definition->defaults, $props);
        $props = $definition->sanitize($props);

        return [
            'type' => $type,
            'props' => $props,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public function normalizeBlockTree(array $blocks): array
    {
        return collect($blocks)
            ->map(fn (array $block) => $this->normalize($block))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * The shared per-section style defaults, merged into every block so the
     * merchant can re-style any section. Mirrors
     * `resources/js/builder/config/sectionStyle.ts`.
     *
     * @return array<string, mixed>
     */
    public static function sectionDefaults(): array
    {
        return [
            'sectionPaddingTop' => 'medium',
            'sectionPaddingBottom' => 'medium',
            'sectionBackground' => '', // '' = inherit; otherwise the scheme key (e.g. '1')
            'sectionWidth' => 'contained',
            'sectionTextAlign' => '',
            'hidden' => false, // hide a section on the live storefront without deleting it
        ];
    }

    /**
     * The Blade view prefix every block is registered under (and the renderer
     * falls back to). Configurable so a host can point at its own namespace.
     */
    public static function blocksPrefix(): string
    {
        return (string) config('theme-builder.blocks_view_prefix', 'theme-builder::storefront.blocks');
    }

    private function registerCoreBlocks(): void
    {
        $section = self::sectionDefaults();
        $prefix = self::blocksPrefix();

        $blocks = [
            BlockDefinition::make('Hero', 'Hero banner', BlockCategory::Hero)
                ->blade("{$prefix}.hero")
                ->icon('heroicon-o-sparkles')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => 'متجرنا', 'en' => 'Our Store'],
                    'subtitle' => ['ar' => 'تسوق أحدث المنتجات', 'en' => 'Shop the latest'],
                    'backgroundImage' => null,
                    'overlayOpacity' => 30,
                    'height' => 'medium',
                    'ctaText' => ['ar' => 'تسوق الآن', 'en' => 'Shop now'],
                    'ctaUrl' => '/collections/all',
                    'layout' => 'center',
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('ProductsGrid', 'Product grid', BlockCategory::Products)
                ->blade("{$prefix}.products-grid")
                ->icon('heroicon-o-shopping-bag')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => 'منتجاتنا', 'en' => 'Our Products'],
                    'source' => 'latest',
                    'categoryId' => null,
                    'productIds' => [],
                    'columns' => 4,
                    'mobileColumns' => 2,
                    'limit' => 8,
                    'showAddToCart' => true,
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('ProductsTabs', 'Product tabs', BlockCategory::Products)
                ->blade("{$prefix}.products-tabs")
                ->icon('heroicon-o-squares-2x2')
                ->defaults([
                    ...$section,
                    'tabs' => [
                        ['label' => ['ar' => 'وصل حديثًا', 'en' => 'New'], 'source' => 'latest', 'categoryId' => null, 'productIds' => []],
                        ['label' => ['ar' => 'الأكثر مبيعًا', 'en' => 'Bestsellers'], 'source' => 'bestseller', 'categoryId' => null, 'productIds' => []],
                    ],
                    'columns' => 4,
                    'mobileColumns' => 2,
                    'limit' => 8,
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('ProductsCarousel', 'Products carousel', BlockCategory::Products)
                ->blade("{$prefix}.products-carousel")
                ->icon('heroicon-o-arrows-right-left')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => 'منتجات مختارة', 'en' => 'Selected products'],
                    'source' => 'latest',
                    'categoryId' => null,
                    'productIds' => [],
                    'limit' => 10,
                    'autoplay' => false,
                    'interval' => 5,
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('BannersSingle', 'Single banner', BlockCategory::Banners)
                ->blade("{$prefix}.banner-single")
                ->icon('heroicon-o-photo')
                ->defaults([
                    ...$section,
                    'image' => null,
                    'overlayOpacity' => 40,
                    'height' => 'medium',
                    'title' => ['ar' => '', 'en' => ''],
                    'subtitle' => ['ar' => '', 'en' => ''],
                    'ctaText' => ['ar' => '', 'en' => ''],
                    'ctaUrl' => '',
                    'align' => 'center',
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('BannersDouble', 'Double banner', BlockCategory::Banners)
                ->blade("{$prefix}.banner-double")
                ->icon('heroicon-o-rectangle-group')
                ->defaults([
                    ...$section,
                    'leftImage' => null,
                    'leftTitle' => ['ar' => '', 'en' => ''],
                    'leftUrl' => '',
                    'rightImage' => null,
                    'rightTitle' => ['ar' => '', 'en' => ''],
                    'rightUrl' => '',
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('CategoryGrid', 'Category grid', BlockCategory::Categories)
                ->blade("{$prefix}.category-grid")
                ->icon('heroicon-o-squares-2x2')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => 'تسوق حسب الفئة', 'en' => 'Shop by category'],
                    'columns' => 4,
                    'limit' => 8,
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('CategoryShortcuts', 'Category shortcuts', BlockCategory::Categories)
                ->blade("{$prefix}.category-shortcuts")
                ->icon('heroicon-o-rectangle-stack')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => 'تسوق حسب الفئة', 'en' => 'Shop by category'],
                    'items' => [],
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('BlogArticle', 'Blog article', BlockCategory::Content)
                ->blade("{$prefix}.blog-article")
                ->icon('heroicon-o-document-text')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => 'من المدوّنة', 'en' => 'From the blog'],
                    'postId' => null,
                    'showExcerpt' => true,
                    'ctaText' => ['ar' => 'اقرأ المقال', 'en' => 'Read article'],
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('BlogCategoriesGrid', 'Blog categories', BlockCategory::Content)
                ->blade("{$prefix}.blog-categories-grid")
                ->icon('heroicon-o-squares-2x2')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => 'تصفّح المواضيع', 'en' => 'Browse topics'],
                    'columns' => 4,
                    'limit' => 8,
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('BlogPostsGrid', 'Blog posts', BlockCategory::Content)
                ->blade("{$prefix}.blog-posts-grid")
                ->icon('heroicon-o-rectangle-stack')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => 'أحدث المقالات', 'en' => 'Latest articles'],
                    'source' => 'latest',
                    'postIds' => [],
                    'columns' => 3,
                    'mobileColumns' => 1,
                    'limit' => 3,
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('PromoBar', 'Promo bar', BlockCategory::Layout)
                ->blade("{$prefix}.promo-bar")
                ->icon('heroicon-o-megaphone')
                ->defaults([
                    ...$section,
                    'text' => ['ar' => 'شحن مجاني للطلبات فوق 200 ريال', 'en' => 'Free shipping over 200 SAR'],
                    'url' => '',
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('RichText', 'Rich text', BlockCategory::Content)
                ->blade("{$prefix}.rich-text")
                ->icon('heroicon-o-document-text')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => '', 'en' => ''],
                    'content' => ['ar' => '', 'en' => ''],
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('ImageWithText', 'Image with text', BlockCategory::Content)
                ->blade("{$prefix}.image-with-text")
                ->icon('heroicon-o-photo')
                ->defaults([
                    ...$section,
                    'image' => null,
                    'imagePosition' => 'start',
                    'title' => ['ar' => '', 'en' => ''],
                    'content' => ['ar' => '', 'en' => ''],
                    'ctaText' => ['ar' => '', 'en' => ''],
                    'ctaUrl' => '',
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('Multicolumn', 'Feature columns', BlockCategory::Content)
                ->blade("{$prefix}.multicolumn")
                ->icon('heroicon-o-squares-2x2')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => '', 'en' => ''],
                    'columns' => 3,
                    'items' => BlockStarterDefaults::multicolumnItems(),
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('Testimonials', 'Testimonials', BlockCategory::Content)
                ->blade("{$prefix}.testimonials")
                ->icon('heroicon-o-chat-bubble-left-right')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => 'آراء عملائنا', 'en' => 'What our customers say'],
                    'items' => [],
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('FaqAccordion', 'FAQ accordion', BlockCategory::Content)
                ->blade("{$prefix}.faq")
                ->icon('heroicon-o-question-mark-circle')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => 'الأسئلة الشائعة', 'en' => 'Frequently asked questions'],
                    'items' => [],
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('CollapsibleContent', 'Collapsible rows', BlockCategory::Content)
                ->blade("{$prefix}.collapsible-content")
                ->icon('heroicon-o-bars-arrow-down')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => '', 'en' => ''],
                    'items' => BlockStarterDefaults::collapsibleRows(),
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('TrustBadges', 'Trust badges', BlockCategory::Layout)
                ->blade("{$prefix}.trust-badges")
                ->icon('heroicon-o-shield-check')
                ->defaults([
                    ...$section,
                    'items' => BlockStarterDefaults::trustBadges(),
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('Countdown', 'Countdown timer', BlockCategory::Marketing)
                ->blade("{$prefix}.countdown")
                ->icon('heroicon-o-clock')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => 'ينتهي العرض قريبًا', 'en' => 'Offer ends soon'],
                    'endsAt' => null,
                    'ctaText' => ['ar' => 'تسوق العرض', 'en' => 'Shop the offer'],
                    'ctaUrl' => '',
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('ProductsFeatured', 'Featured product', BlockCategory::Products)
                ->blade("{$prefix}.product-featured")
                ->icon('heroicon-o-star')
                ->defaults([
                    ...$section,
                    'productId' => null,
                    'title' => ['ar' => 'منتج مميز', 'en' => 'Featured product'],
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('Slideshow', 'Image slideshow', BlockCategory::Hero)
                ->blade("{$prefix}.slideshow")
                ->icon('heroicon-o-film')
                ->defaults([
                    ...$section,
                    'slides' => BlockStarterDefaults::slideshowSlides(),
                    'autoplay' => true,
                    'interval' => 5,
                    'height' => 'medium',
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('Video', 'Video', BlockCategory::Content)
                ->blade("{$prefix}.video")
                ->icon('heroicon-o-play-circle')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => '', 'en' => ''],
                    'videoUrl' => '',
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('Brands', 'Brands / logos', BlockCategory::Content)
                ->blade("{$prefix}.brands")
                ->icon('heroicon-o-rectangle-group')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => '', 'en' => ''],
                    'items' => BlockStarterDefaults::brandItems(),
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('Collage', 'Media collage', BlockCategory::Banners)
                ->blade("{$prefix}.collage")
                ->icon('heroicon-o-rectangle-stack')
                ->defaults([
                    ...$section,
                    'items' => BlockStarterDefaults::collageItems(),
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('ParallaxBanner', 'Parallax banner', BlockCategory::Banners)
                ->blade("{$prefix}.parallax-banner")
                ->icon('heroicon-o-photo')
                ->defaults([
                    ...$section,
                    'image' => null,
                    'title' => ['ar' => '', 'en' => ''],
                    'subtitle' => ['ar' => '', 'en' => ''],
                    'ctaText' => ['ar' => '', 'en' => ''],
                    'ctaUrl' => '',
                    'overlayOpacity' => 40,
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('ContactForm', 'Contact form', BlockCategory::Content)
                ->blade("{$prefix}.contact-form")
                ->icon('heroicon-o-envelope')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => 'تواصل معنا', 'en' => 'Contact us'],
                    'subtitle' => ['ar' => '', 'en' => ''],
                    'showPhone' => true,
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('CustomHtml', 'Custom HTML (safe markup)', BlockCategory::Layout)
                ->blade("{$prefix}.custom-html")
                ->icon('heroicon-o-code-bracket')
                ->defaults([
                    ...$section,
                    'html' => '',
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('Newsletter', 'Newsletter signup', BlockCategory::Marketing)
                ->blade("{$prefix}.newsletter")
                ->icon('heroicon-o-envelope')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => 'اشترك في نشرتنا البريدية', 'en' => 'Subscribe to our newsletter'],
                    'subtitle' => ['ar' => 'انضم لقائمتنا واحصل على عروض حصرية وأحدث المنتجات.', 'en' => 'Join our list for exclusive offers and the latest gear.'],
                    'placeholder' => ['ar' => 'بريدك الإلكتروني', 'en' => 'Your email'],
                    'ctaText' => ['ar' => 'اشترك', 'en' => 'Subscribe'],
                    'layout' => 'center',
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('BentoHero', 'Bento hero', BlockCategory::Hero)
                ->blade("{$prefix}.bento-hero")
                ->icon('heroicon-o-rectangle-group')
                ->defaults([
                    ...$section,
                    'eyebrow' => ['ar' => 'مميّز', 'en' => 'Featured'],
                    'ctaText' => ['ar' => 'تسوق الآن', 'en' => 'Shop now'],
                    'source' => 'latest',
                    'categoryId' => null,
                    'productIds' => [],
                    'limit' => 6,
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('DiscountBanners', 'Discount banners', BlockCategory::Banners)
                ->blade("{$prefix}.discount-banners")
                ->icon('heroicon-o-tag')
                ->defaults([
                    ...$section,
                    'leftMeta' => ['ar' => 'هذا الأسبوع فقط', 'en' => 'THIS WEEK ONLY'],
                    'leftTitle' => ['ar' => 'خصومات ضخمة', 'en' => 'Mega Discounts'],
                    'leftPct' => ['ar' => 'خصم 50%', 'en' => '50% Off'],
                    'leftCta' => ['ar' => 'تسوق الآن', 'en' => 'Shop now'],
                    'leftImage' => null,
                    'leftUrl' => '',
                    'rightMeta' => ['ar' => 'إصدار محدود', 'en' => 'LIMITED EDITION'],
                    'rightTitle' => ['ar' => 'سماعات استوديو برو', 'en' => 'Studio Buds Pro'],
                    'rightPct' => ['ar' => 'خصم 30%', 'en' => '30% Off'],
                    'rightCta' => ['ar' => 'تسوق الآن', 'en' => 'Shop now'],
                    'rightImage' => null,
                    'rightUrl' => '',
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),

            BlockDefinition::make('CompactRow', 'Compact product row', BlockCategory::Products)
                ->blade("{$prefix}.compact-row")
                ->icon('heroicon-o-rectangle-stack')
                ->defaults([
                    ...$section,
                    'title' => ['ar' => 'مختارات لك', 'en' => 'Just for you'],
                    'ctaText' => ['ar' => 'اطلب الآن', 'en' => 'Order now'],
                    'source' => 'latest',
                    'categoryId' => null,
                    'productIds' => [],
                    'limit' => 4,
                    'visibleOnDesktop' => true,
                    'visibleOnMobile' => true,
                ]),
        ];

        foreach ($blocks as $block) {
            $this->register($block);
        }
    }
}
