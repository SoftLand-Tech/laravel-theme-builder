<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Builder;

/**
 * Sensible starter rows for repeater-driven blocks so new sections preview
 * immediately in the builder and on the storefront after normalization.
 */
final class BlockStarterDefaults
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function trustBadges(): array
    {
        return [
            ['label' => ['ar' => 'توصيل سريع', 'en' => 'Fast delivery'], 'icon' => 'truck'],
            ['label' => ['ar' => 'دفع آمن', 'en' => 'Secure payment'], 'icon' => 'shield'],
            ['label' => ['ar' => 'إرجاع سهل', 'en' => 'Easy returns'], 'icon' => 'refresh'],
            ['label' => ['ar' => 'ضمان الجودة', 'en' => 'Quality guarantee'], 'icon' => 'badge'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function slideshowSlides(): array
    {
        return [[
            'image' => null,
            'overlayOpacity' => 30,
            'title' => ['ar' => 'عنوان الشريحة', 'en' => 'Slide headline'],
            'subtitle' => ['ar' => 'نص تعريفي قصير', 'en' => 'Short supporting text'],
            'ctaText' => ['ar' => 'تسوق الآن', 'en' => 'Shop now'],
            'ctaUrl' => '/collections/all',
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function multicolumnItems(): array
    {
        return [
            [
                'image' => null,
                'icon' => 'truck',
                'label' => ['ar' => 'توصيل سريع', 'en' => 'Fast delivery'],
                'text' => ['ar' => 'نوصّل طلبك بسرعة إلى باب منزلك.', 'en' => 'We deliver quickly to your door.'],
                'ctaText' => ['ar' => '', 'en' => ''],
                'url' => '',
            ],
            [
                'image' => null,
                'icon' => 'shield',
                'label' => ['ar' => 'دفع آمن', 'en' => 'Secure payment'],
                'text' => ['ar' => 'مدى، Apple Pay، والدفع عند الاستلام.', 'en' => 'mada, Apple Pay, and cash on delivery.'],
                'ctaText' => ['ar' => '', 'en' => ''],
                'url' => '',
            ],
            [
                'image' => null,
                'icon' => 'refresh',
                'label' => ['ar' => 'إرجاع سهل', 'en' => 'Easy returns'],
                'text' => ['ar' => 'سياسة إرجاع واضحة وسهلة.', 'en' => 'Clear, hassle-free returns.'],
                'ctaText' => ['ar' => '', 'en' => ''],
                'url' => '',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function collageItems(): array
    {
        return [
            ['image' => null, 'url' => ''],
            ['image' => null, 'url' => ''],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function brandItems(): array
    {
        return [
            ['image' => null, 'label' => ['ar' => 'علامة ١', 'en' => 'Brand 1'], 'url' => ''],
            ['image' => null, 'label' => ['ar' => 'علامة ٢', 'en' => 'Brand 2'], 'url' => ''],
            ['image' => null, 'label' => ['ar' => 'علامة ٣', 'en' => 'Brand 3'], 'url' => ''],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function collapsibleRows(): array
    {
        return [[
            'heading' => ['ar' => 'تفاصيل الشحن', 'en' => 'Shipping details'],
            'content' => ['ar' => '<p>نوصّل داخل المملكة خلال ٢–٥ أيام عمل.</p>', 'en' => '<p>We ship across Saudi Arabia in 2–5 business days.</p>'],
        ]];
    }
}
