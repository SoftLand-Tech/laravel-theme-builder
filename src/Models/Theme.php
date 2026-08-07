<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Override;
use SoftLand\ThemeBuilder\Builder\BlockRegistry;
use SoftLand\ThemeBuilder\Contracts\ThemeSchema;
use SoftLand\ThemeBuilder\Enums\SectionRhythm;
use SoftLand\ThemeBuilder\Enums\ThemeStatus;
use SoftLand\ThemeBuilder\Models\Scopes\PresetOrTenantScope;
use SoftLand\ThemeBuilder\Support\ThemePreset;

/**
 * @property int|null $store_id
 * @property bool $is_preset
 * @property ?string $preset_slug
 * @property ?string $preview_image
 * @property ?string $description
 * @property ?array $settings
 * @property ?array $blocks
 * @property ?array $header
 * @property ?array $footer
 * @property ?array $templates
 * @property ?array $draft_blocks
 * @property ?array $draft_settings
 * @property ?array $draft_header
 * @property ?array $draft_footer
 * @property ?array $draft_templates
 * @property ThemeStatus $status
 */
#[Fillable(['store_id', 'name', 'status', 'blocks', 'settings', 'templates', 'header', 'footer', 'draft_blocks', 'draft_settings', 'draft_templates', 'draft_header', 'draft_footer', 'is_preset', 'preset_slug', 'preview_image', 'description'])]
class Theme extends Model
{
    /**
     * The physical table name, resolved from the bound ThemeSchema so a host is
     * never forced onto the canonical `themes` name.
     */
    #[Override]
    public function getTable(): string
    {
        if (app()->bound(ThemeSchema::class)) {
            return app(ThemeSchema::class)->themesTable();
        }

        return parent::getTable();
    }

    /**
     * The physical column holding the owning tenant key (default 'store_id').
     */
    public static function ownerColumn(): string
    {
        return app()->bound(ThemeSchema::class)
            ? app(ThemeSchema::class)->tenantColumn()
            : 'store_id';
    }

    /** @var array<string, string|null> */
    private array $componentForCache = [];

    #[Override]
    protected function casts(): array
    {
        return [
            // Bilingual {ar,en} values stored as JSON. Hosts that re-introduce
            // Spatie Translatable override casts() to drop these two.
            'name' => 'array',
            'description' => 'array',
            'header' => 'array',
            'footer' => 'array',
            'blocks' => 'array',
            'settings' => 'array',
            'templates' => 'array',
            'draft_blocks' => 'array',
            'draft_settings' => 'array',
            'draft_header' => 'array',
            'draft_footer' => 'array',
            'draft_templates' => 'array',
            'status' => ThemeStatus::class,
            'is_preset' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    #[Override]
    protected static function booted(): void
    {
        static::addGlobalScope(new PresetOrTenantScope);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ThemeRevision::class);
    }

    /**
     * The bilingual {ar,en} name, regardless of how the host stores it (array
     * column in the package; Spatie Translatable in some hosts). Hosts using
     * Spatie Translatable override this to `return $this->getTranslations('name')`.
     *
     * @return array<string, string>
     */
    public function nameTranslations(): array
    {
        return is_array($this->name)
            ? $this->name
            : ['ar' => (string) $this->name, 'en' => (string) $this->name];
    }

    /**
     * @return array<string, string>|null
     */
    public function descriptionTranslations(): ?array
    {
        $value = $this->description;

        return is_array($value) ? $value : (is_string($value) && $value !== '' ? ['ar' => $value, 'en' => $value] : null);
    }

    protected function scopePresets(Builder $query): Builder
    {
        return $query->where('is_preset', true)->whereNull($query->getModel()->qualifyColumn(self::ownerColumn()));
    }

    protected function scopeForStore(Builder $query, int|string|null $storeId = null): Builder
    {
        $storeId ??= $this->tenantId();

        return $query->where(self::ownerColumn(), $storeId)->where('is_preset', false);
    }

    public function isPreset(): bool
    {
        return $this->is_preset;
    }

    public function isCustom(): bool
    {
        return ! $this->is_preset && $this->{self::ownerColumn()} !== null;
    }

    /**
     * Clone a preset into a tenant-owned draft.
     */
    public static function forkPresetForStore(self $preset, int|string|null $storeId): self
    {
        return static::withoutGlobalScope(new PresetOrTenantScope)->create([
            self::ownerColumn() => $storeId,
            'name' => $preset->nameTranslations(),
            'is_preset' => false,
            'preset_slug' => $preset->preset_slug,
            'preview_image' => $preset->preview_image,
            'description' => $preset->description,
            'blocks' => $preset->blocks,
            'settings' => $preset->settings,
            'header' => $preset->header,
            'footer' => $preset->footer,
            'templates' => $preset->templates,
            'status' => ThemeStatus::Draft,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultSettings(): array
    {
        return [
            'colors' => [
                'primary' => '#458FCC',
                'secondary' => '#3A29AB',
                'accent' => '#8A30C7',
                'background' => '#FFFFFF',
                'surface' => '#F9FAFB',
                'text' => '#1a1a1f',
                'muted' => '#6B7280',
            ],
            'color_schemes' => [
                [
                    'key' => '1',
                    'name' => 'Default',
                    'background' => '#FFFFFF',
                    'surface' => '#F9FAFB',
                    'text' => '#1a1a1f',
                    'muted' => '#6B7280',
                    'primary' => '#458FCC',
                    'button' => '#2F7AB5',
                    'button_text' => '#FFFFFF',
                    'accent' => '#8A30C7',
                ],
                [
                    'key' => '2',
                    'name' => 'Dark',
                    'background' => '#1a1a1f',
                    'surface' => '#26262e',
                    'text' => '#FFFFFF',
                    'muted' => '#a3a3ad',
                    'primary' => '#73FAFD',
                    'button' => '#2F7AB5',
                    'button_text' => '#FFFFFF',
                    'accent' => '#EB3DF7',
                ],
            ],
            'typography' => [
                'heading_font' => 'IBM Plex Sans Arabic',
                'body_font' => 'IBM Plex Sans Arabic',
                'base_size' => 16,
            ],
            'radius' => [
                'card' => 12,
                'button' => 8,
            ],
            // `price` and `outofstock` default to null so they derive from the
            // merchant's text/muted colors (mirroring the token file's
            // `--color-price: var(--color-ink)`). A literal here would ship
            // near-black price text on the built-in Dark scheme.
            'semantic' => [
                'price' => null,
                'sale' => '#C0392B',
                'instock' => '#1E8E5A',
                'lowstock' => '#D08700',
                'outofstock' => null,
            ],
            'layout' => [
                'content_width' => 1152,
                'section_rhythm' => SectionRhythm::Medium->value,
            ],
            'effects' => [
                'product_border' => false,
                'shine_animation' => false,
            ],
            'product_card' => [
                'show_image' => true,
                'show_title' => true,
                'show_price' => true,
                'show_compare_price' => true,
                'show_rating' => true,
                'show_add_to_cart' => true,
                'show_sale_badge' => true,
                'show_new_badge' => false,
                'show_out_of_stock_badge' => true,
                'border' => false,
                'hover_zoom' => true,
            ],
            'product_page' => [
                'breadcrumbs' => true,
                'show_rating' => true,
                'show_related' => true,
                'sticky_purchase_bar' => false,
                'show_share' => true,
                'show_quantity_stepper' => true,
                'buy_now_button' => false,
            ],
            'cart' => [
                'free_shipping_bar' => true,
                'free_shipping_threshold' => 200,
                'show_coupon' => true,
                'show_cross_sell' => false,
                'show_order_summary' => true,
            ],
        ];
    }

    /**
     * Resolve settings with defaults merged (deep).
     *
     * @return array<string, mixed>
     */
    public function resolvedSettings(): array
    {
        return array_replace_recursive(self::defaultSettings(), $this->settings ?? []);
    }

    /**
     * Map resolved settings into the storefront's real CSS custom properties
     * (the names `storefront.css` actually consumes), so theme colors/fonts/
     * radius take effect on the live site. Legacy `--color-primary` aliases are
     * also emitted for forward-compat with editor-side utilities.
     *
     * @param  array<string, mixed>  $settings
     */
    public static function cssVarsFromSettings(array $settings): string
    {
        $colors = $settings['colors'] ?? [];
        $typo = $settings['typography'] ?? [];
        $radius = $settings['radius'] ?? [];
        $semantic = $settings['semantic'] ?? [];
        $layout = $settings['layout'] ?? [];

        $defaults = self::defaultSettings();
        $colorDefaults = $defaults['colors'];
        $semanticDefaults = $defaults['semantic'];

        $primary = self::normalizeHex($colors['primary'] ?? $colorDefaults['primary']);
        $text = self::normalizeHex($colors['text'] ?? $colorDefaults['text']);
        $muted = self::normalizeHex($colors['muted'] ?? $colorDefaults['muted']);
        $background = self::normalizeHex($colors['background'] ?? $colorDefaults['background']);
        $surface = self::normalizeHex($colors['surface'] ?? $colorDefaults['surface']);

        [$background, $surface, $text, $muted] = self::harmonizeStorefrontNeutrals(
            $background,
            $surface,
            $text,
            $muted,
        );

        $ramp = self::deriveRamp($primary);

        $vars = [
            '--color-clay-50' => $ramp[50],
            '--color-clay-100' => $ramp[100],
            '--color-clay-200' => $ramp[200],
            '--color-clay-300' => $ramp[300],
            '--color-clay-400' => $ramp[400],
            '--color-clay-500' => $ramp[500],
            '--color-clay-600' => $ramp[600],
            '--color-clay-700' => $ramp[700],
            '--color-clay-800' => $ramp[800],
            '--color-clay-900' => $ramp[900],

            '--color-paper' => $background,
            '--color-paper-deep' => self::mix($background, $text, 0.05),
            '--color-surface' => $surface,
            '--color-bg' => $background,
            '--color-ink' => $text,
            '--color-text' => $text,
            '--color-ink-soft' => $muted,
            '--color-muted' => $muted,
            '--color-stone' => $muted,
            '--color-line' => self::mix($background, $text, 0.14),
            '--color-line-strong' => self::mix($background, $text, 0.22),

            '--color-primary' => $primary,
            '--color-secondary' => self::normalizeHex($colors['secondary'] ?? $colorDefaults['secondary']),
            '--color-accent' => self::normalizeHex($colors['accent'] ?? $colorDefaults['accent']),

            '--color-price' => self::normalizeHex($semantic['price'] ?? $text),
            '--color-outofstock' => self::normalizeHex($semantic['outofstock'] ?? $muted),
            '--color-success-600' => self::normalizeHex($semantic['instock'] ?? $semanticDefaults['instock']),
            '--color-warning-600' => self::normalizeHex($semantic['lowstock'] ?? $semanticDefaults['lowstock']),
            '--color-danger-600' => self::normalizeHex($semantic['sale'] ?? $semanticDefaults['sale']),
        ];

        if (! empty($typo['body_font'])) {
            $vars['--font-sans'] = self::storefrontFontStack($typo['body_font']);
            $vars['--font-body'] = self::storefrontFontStack($typo['body_font']);
        }

        if (! empty($typo['heading_font'])) {
            $vars['--font-display'] = self::storefrontFontStack($typo['heading_font']);
            $vars['--font-heading'] = self::storefrontFontStack($typo['heading_font']);
        }

        if (isset($radius['card'])) {
            $vars['--radius-cadi'] = "{$radius['card']}px";
            $vars['--radius-card'] = "{$radius['card']}px";
        }

        if (isset($radius['button'])) {
            $vars['--radius-button'] = "{$radius['button']}px";
        }

        if (isset($layout['content_width'])) {
            $width = max(640, min(1920, (int) $layout['content_width']));
            $vars['--container-content'] = "{$width}px";
        }

        $vars['--space-section-scale'] = SectionRhythm::fromSetting($layout['section_rhythm'] ?? null)->multiplier();

        return collect($vars)->map(fn ($v, $k) => "{$k}: {$v}")->implode(';');
    }

    /**
     * Build a Google Fonts CSS2 URL for the theme's heading + body fonts (when
     * they're hosted on Google Fonts). Returns null when neither font is on
     * Google Fonts so the layout can skip the request.
     *
     * @param  array<string, mixed>  $settings
     */
    public static function googleFontsUrl(array $settings): ?string
    {
        $typo = $settings['typography'] ?? [];
        $heading = $typo['heading_font'] ?? 'Cormorant Garamond';
        $body = $typo['body_font'] ?? 'Inter';

        $knownGoogle = [
            'Cormorant Garamond', 'Inter', 'Playfair Display', 'Lora',
            'Merriweather', 'Montserrat', 'Roboto', 'Open Sans', 'Nunito',
            'Work Sans', 'Poppins', 'Raleway', 'Source Sans 3', 'DM Sans',
            'Tajawal', 'Almarai', 'Cairo', 'Noto Sans Arabic', 'IBM Plex Sans Arabic',
        ];
        $known = collect($knownGoogle)->mapWithKeys(fn ($f) => [strtolower($f) => $f]);

        $families = collect([$heading, $body])
            ->filter()
            ->unique()
            ->map(fn ($f) => $known[strtolower($f)] ?? null)
            ->filter()
            ->unique()
            ->values();

        if ($families->isEmpty()) {
            return null;
        }

        $query = $families
            ->map(fn (string $family) => 'family='.urlencode($family).':wght@400;500;600;700')
            ->implode('&');

        return "https://fonts.googleapis.com/css2?{$query}&display=swap";
    }

    /**
     * Storefront font stack with the Saudi Riyal sign face first so U+20C1 renders.
     */
    private static function storefrontFontStack(string $family): string
    {
        $family = str_replace(['"', "'"], '', $family);

        return "'Saudi Riyal', \"{$family}\", system-ui, sans-serif";
    }

    /**
     * Normalize any hex (#RGB / #RRGGBB) to an uppercase #RRGGBB string.
     * Non-hex input falls back to a sensible default so the CSS never breaks.
     */
    private static function normalizeHex(mixed $hex, string $fallback = '#000000'): string
    {
        if (! is_string($hex)) {
            return $fallback;
        }

        $hex = trim($hex);
        if ($hex === '') {
            return $fallback;
        }
        if (! str_starts_with($hex, '#')) {
            $hex = '#'.$hex;
        }
        if (preg_match('/^#([0-9a-f]{3})$/i', $hex, $m)) {
            $hex = '#'.$m[1][0].$m[1][0].$m[1][1].$m[1][1].$m[1][2].$m[1][2];
        }
        if (! preg_match('/^#[0-9a-f]{6}$/i', $hex)) {
            return $fallback;
        }

        return strtoupper($hex);
    }

    /**
     * A merchant-set color, validated to a `#RRGGBB` literal before it is
     * printed into a `style` attribute or `<style>` block. Theme colors are
     * admin-controlled JSON, so without this a crafted value would be CSS injection.
     */
    public static function cssColor(mixed $color, string $fallback = '#000000'): string
    {
        return self::normalizeHex($color, $fallback);
    }

    /**
     * Linearly mix two hex colors: amount=0 returns $a, amount=1 returns $b.
     */
    private static function mix(string $a, string $b, float $amount): string
    {
        $a = self::normalizeHex($a);
        $b = self::normalizeHex($b);
        $ar = hexdec(substr($a, 1, 2));
        $ag = hexdec(substr($a, 3, 2));
        $ab = hexdec(substr($a, 5, 2));
        $br = hexdec(substr($b, 1, 2));
        $bg = hexdec(substr($b, 3, 2));
        $bb = hexdec(substr($b, 5, 2));

        $r = (int) round($ar + ($br - $ar) * $amount);
        $g = (int) round($ag + ($bg - $ag) * $amount);
        $bl = (int) round($ab + ($bb - $ab) * $amount);

        return sprintf('#%02X%02X%02X', $r, $g, $bl);
    }

    /**
     * Nudge merchant neutrals so text, muted labels, and card surfaces stay
     * legible even when background and surface are picked as the same shade.
     *
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    private static function harmonizeStorefrontNeutrals(
        string $background,
        string $surface,
        string $text,
        string $muted,
    ): array {
        $text = self::ensureContrastAgainst($text, $background, 4.5);
        $muted = self::ensureContrastAgainst($muted, $background, 3.0);

        if (self::contrastRatio($text, $muted) < 2.0) {
            $muted = self::mix($text, $background, 0.58);
        }

        $surface = self::ensureSurfaceContrast($background, $surface, $text);

        return [$background, $surface, $text, $muted];
    }

    private static function ensureSurfaceContrast(string $background, string $surface, string $text): string
    {
        if (self::contrastRatio($surface, $background) >= 1.08) {
            return $surface;
        }

        $lift = self::relativeLuminance($background) > 0.55 ? $text : '#FFFFFF';

        return self::mix($background, $lift, 0.08);
    }

    private static function ensureContrastAgainst(string $foreground, string $background, float $minRatio): string
    {
        if (self::contrastRatio($foreground, $background) >= $minRatio) {
            return $foreground;
        }

        $toward = self::relativeLuminance($background) > 0.5 ? '#000000' : '#FFFFFF';

        for ($step = 1; $step <= 20; $step++) {
            $candidate = self::mix($foreground, $toward, $step / 20);

            if (self::contrastRatio($candidate, $background) >= $minRatio) {
                return $candidate;
            }
        }

        return $toward;
    }

    private static function contrastRatio(string $foreground, string $background): float
    {
        $l1 = self::relativeLuminance($foreground);
        $l2 = self::relativeLuminance($background);
        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    private static function relativeLuminance(string $hex): float
    {
        $hex = self::normalizeHex($hex);
        $channels = [
            hexdec(substr($hex, 1, 2)) / 255,
            hexdec(substr($hex, 3, 2)) / 255,
            hexdec(substr($hex, 5, 2)) / 255,
        ];

        $transform = static function (float $channel): float {
            return $channel <= 0.03928
                ? $channel / 12.92
                : (($channel + 0.055) / 1.055) ** 2.4;
        };

        $r = $transform($channels[0]);
        $g = $transform($channels[1]);
        $b = $transform($channels[2]);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Derive a 50–900 ramp from a single primary hex. 500 is the input color,
     * lighter shades mix toward white, darker shades mix toward black.
     *
     * @return array<int, string>
     */
    private static function deriveRamp(string $primary): array
    {
        $primary = self::normalizeHex($primary, '#458FCC');

        return [
            50 => self::mix($primary, '#FFFFFF', 0.92),
            100 => self::mix($primary, '#FFFFFF', 0.85),
            200 => self::mix($primary, '#FFFFFF', 0.70),
            300 => self::mix($primary, '#FFFFFF', 0.50),
            400 => self::mix($primary, '#FFFFFF', 0.25),
            500 => $primary,
            600 => self::mix($primary, '#000000', 0.12),
            700 => self::mix($primary, '#000000', 0.25),
            800 => self::mix($primary, '#000000', 0.40),
            900 => self::mix($primary, '#000000', 0.55),
        ];
    }

    /**
     * Emit one `.scheme-{key}{ --... }` CSS rule per color scheme. Section
     * wrappers apply the chosen scheme as a class, so merchants get per-section
     * palettes without touching the global tokens.
     *
     * @param  array<string, mixed>  $settings
     */
    public static function colorSchemeRules(array $settings): string
    {
        $schemes = $settings['color_schemes'] ?? [];

        return collect($schemes)
            ->filter(fn ($s) => is_array($s) && ! empty($s['key']) && preg_match('/^[a-z0-9][a-z0-9-]*$/i', (string) $s['key']))
            ->map(function (array $s): string {
                $primary = self::normalizeHex($s['primary'] ?? '#458FCC');
                $background = self::normalizeHex($s['background'] ?? '#FFFFFF');
                $text = self::normalizeHex($s['text'] ?? '#1a1a1f');
                $muted = self::normalizeHex($s['muted'] ?? '#6B7280');
                $surface = self::normalizeHex($s['surface'] ?? '#F9FAFB');

                [$background, $surface, $text, $muted] = self::harmonizeStorefrontNeutrals(
                    $background,
                    $surface,
                    $text,
                    $muted,
                );

                $ramp = self::deriveRamp($primary);

                $vars = collect([
                    '--color-paper' => $background,
                    '--color-paper-deep' => self::mix($background, $text, 0.05),
                    '--color-bg' => $background,
                    '--color-surface' => $surface,
                    '--color-ink' => $text,
                    '--color-text' => $text,
                    '--color-ink-soft' => $muted,
                    '--color-muted' => $muted,
                    '--color-stone' => $muted,
                    '--color-line' => self::mix($background, $text, 0.14),
                    '--color-line-strong' => self::mix($background, $text, 0.22),
                    '--color-primary' => $primary,
                    '--color-clay-50' => $ramp[50],
                    '--color-clay-100' => $ramp[100],
                    '--color-clay-200' => $ramp[200],
                    '--color-clay-300' => $ramp[300],
                    '--color-clay-400' => $ramp[400],
                    '--color-clay-500' => $ramp[500],
                    '--color-clay-600' => self::normalizeHex($s['button'] ?? $primary),
                    '--color-clay-700' => $ramp[700],
                    '--color-clay-800' => $ramp[800],
                    '--color-clay-900' => $ramp[900],
                    '--color-accent' => self::normalizeHex($s['accent'] ?? '#8A30C7'),
                ])
                    ->map(fn ($v, $k) => "{$k}: {$v}")
                    ->implode('; ');

                return ".scheme-{$s['key']} { {$vars} }";
            })
            ->implode("\n");
    }

    /**
     * Body-level classes derived from settings, used to toggle storefront
     * elements (card border, shine, sticky purchase bar, free-shipping bar).
     *
     * @param  array<string, mixed>  $settings
     */
    public static function storefrontBodyClass(array $settings): string
    {
        $classes = [];

        $productCard = $settings['product_card'] ?? [];
        $effects = $settings['effects'] ?? [];
        $productPage = $settings['product_page'] ?? [];
        $cart = $settings['cart'] ?? [];

        if (! empty($productCard['border']) || ! empty($effects['product_border'])) {
            $classes[] = 'has-product-border';
        }

        if (! empty($effects['shine_animation'])) {
            $classes[] = 'with-shine';
        }

        if (! empty($productPage['sticky_purchase_bar'])) {
            $classes[] = 'sticky-purchase-bar';
        }

        if (! empty($cart['free_shipping_bar'])) {
            $classes[] = 'has-free-shipping-bar';
        }

        return implode(' ', $classes);
    }

    /**
     * Publish this theme for its tenant and archive any other published theme
     * for the same tenant, so only one is live at a time.
     */
    public function activateForCurrentStore(): void
    {
        if ($this->hasDraftPayload()) {
            $this->promoteDraftToPublished();
        }

        if ($this->status !== ThemeStatus::Published) {
            $this->status = ThemeStatus::Published;
            $this->published_at = now();
            $this->save();
        }

        static::withoutGlobalScope(new PresetOrTenantScope)
            ->where(self::ownerColumn(), $this->{self::ownerColumn()})
            ->whereKeyNot($this->id)
            ->where('status', ThemeStatus::Published)
            ->update(['status' => ThemeStatus::Archived]);
    }

    public function packageSlug(): ?string
    {
        $slug = $this->preset_slug;

        if (! is_string($slug) || $slug === '') {
            return null;
        }

        return Str::slug($slug);
    }

    public function themeScopeClass(): string
    {
        $slug = $this->packageSlug();

        return $slug !== null && $slug !== '' ? 'theme-'.$slug : '';
    }

    /**
     * Absolute filesystem path to the package stylesheet under public/themes.
     */
    public function themeCssPath(): ?string
    {
        $slug = $this->packageSlug();

        if ($slug === null || $slug === '') {
            return null;
        }

        $publicPath = public_path("themes/{$slug}/theme.css");

        return is_file($publicPath) ? $publicPath : null;
    }

    public function themeCssAssetUrl(): ?string
    {
        if ($this->themeCssPath() === null) {
            return null;
        }

        return asset('themes/'.$this->packageSlug().'/theme.css');
    }

    /**
     * Public URL for a packaged theme asset (images/fonts/etc.) shipped under
     * public/themes/{slug}. Static so block Blade components can resolve a
     * fallback asset without a Theme instance.
     */
    public static function asset(string $slug, string $path): string
    {
        return asset("themes/{$slug}/{$path}");
    }

    public function componentFor(string $type): ?string
    {
        if (array_key_exists($type, $this->componentForCache)) {
            return $this->componentForCache[$type];
        }

        $slug = $this->packageSlug();

        if ($slug === null || $slug === '') {
            return $this->componentForCache[$type] = null;
        }

        $definition = app(BlockRegistry::class)->get($type);
        $kebab = $definition?->bladeComponent
            ? Str::afterLast($definition->bladeComponent, '.')
            : Str::kebab($type);

        $viewPath = $this->overridePath("themes/{$slug}/blocks/{$kebab}.blade.php");

        if (! is_file($viewPath)) {
            return $this->componentForCache[$type] = null;
        }

        return $this->componentForCache[$type] = "themes.{$slug}.blocks.{$kebab}";
    }

    public function headerComponent(): ?string
    {
        return $this->chromeComponent('header');
    }

    public function footerComponent(): ?string
    {
        return $this->chromeComponent('footer');
    }

    private function chromeComponent(string $name): ?string
    {
        $cacheKey = '__chrome_'.$name;

        if (array_key_exists($cacheKey, $this->componentForCache)) {
            return $this->componentForCache[$cacheKey];
        }

        $slug = $this->packageSlug();

        if ($slug === null || $slug === '') {
            return $this->componentForCache[$cacheKey] = null;
        }

        $viewPath = $this->overridePath("themes/{$slug}/{$name}.blade.php");

        if (! is_file($viewPath)) {
            return $this->componentForCache[$cacheKey] = null;
        }

        return $this->componentForCache[$cacheKey] = "themes.{$slug}.{$name}";
    }

    /**
     * Resolve a host-side override view path. Defaults to the host's
     * resources/views/components; configurable via theme-builder.theme_override_path.
     */
    private function overridePath(string $relative): string
    {
        $base = config('theme-builder.theme_override_path') ?? resource_path('views/components');

        return $base.'/'.$relative;
    }

    /**
     * Whether any draft_* column holds a payload (including a deliberate empty
     * array — e.g. the merchant deleted every home block).
     */
    public function hasDraftPayload(): bool
    {
        foreach (['draft_blocks', 'draft_settings', 'draft_header', 'draft_footer', 'draft_templates'] as $column) {
            if ($this->{$column} !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the builder should read/write draft_* instead of the live columns.
     * Published themes always use the overlay; Archived themes keep using it
     * when a draft payload already exists so edits are not lost.
     */
    public function usesDraftOverlay(): bool
    {
        return $this->status === ThemeStatus::Published || $this->hasDraftPayload();
    }

    public function hasUnpublishedDraft(): bool
    {
        return $this->hasDraftPayload();
    }

    public function promoteDraftToPublished(): void
    {
        if ($this->draft_blocks !== null) {
            $this->blocks = $this->draft_blocks;
        }
        if ($this->draft_settings !== null) {
            $this->settings = $this->draft_settings;
        }
        if ($this->draft_header !== null) {
            $this->header = $this->draft_header;
        }
        if ($this->draft_footer !== null) {
            $this->footer = $this->draft_footer;
        }
        if ($this->draft_templates !== null) {
            $this->templates = $this->draft_templates;
        }

        $this->draft_blocks = null;
        $this->draft_settings = null;
        $this->draft_header = null;
        $this->draft_footer = null;
        $this->draft_templates = null;

        if ($this->status === ThemeStatus::Published) {
            $this->published_at = now();
        }

        $this->save();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function builderResolvedBlocks(): array
    {
        if ($this->usesDraftOverlay()) {
            return $this->draft_blocks ?? $this->blocks ?? [];
        }

        return $this->blocks ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function builderResolvedSettings(): array
    {
        if ($this->usesDraftOverlay() && $this->draft_settings !== null) {
            return array_replace_recursive(self::defaultSettings(), $this->draft_settings);
        }

        return $this->resolvedSettings();
    }

    /**
     * @return array<string, mixed>
     */
    public function builderResolvedHeader(): array
    {
        if ($this->usesDraftOverlay() && $this->draft_header !== null) {
            return array_replace_recursive(self::headerDefaults(), $this->draft_header);
        }

        return $this->resolvedHeader();
    }

    /**
     * @return array<string, mixed>
     */
    public function builderResolvedFooter(): array
    {
        if ($this->usesDraftOverlay() && $this->draft_footer !== null) {
            return array_replace_recursive(self::footerDefaults(), $this->draft_footer);
        }

        return $this->resolvedFooter();
    }

    /**
     * @return array<string, array{before: array<int, array<string, mixed>>, after: array<int, array<string, mixed>>}>
     */
    public function builderResolvedTemplates(): array
    {
        if ($this->usesDraftOverlay() && $this->draft_templates !== null) {
            return array_replace_recursive([
                'product' => ['before' => [], 'after' => []],
                'cart' => ['before' => [], 'after' => []],
                'collection' => ['before' => [], 'after' => []],
            ], $this->draft_templates);
        }

        return $this->resolvedTemplates();
    }

    /**
     * @param  array{
     *     blocks: array<int, array<string, mixed>>,
     *     settings?: array<string, mixed>|null,
     *     header?: array<string, mixed>|null,
     *     footer?: array<string, mixed>|null,
     *     templates?: array<string, mixed>|null
     * }  $payload
     */
    public function persistBuilderPayload(array $payload): void
    {
        if ($this->usesDraftOverlay()) {
            $this->draft_blocks = $payload['blocks'];
            if (array_key_exists('settings', $payload) && $payload['settings'] !== null) {
                $this->draft_settings = $payload['settings'];
            }
            if (array_key_exists('header', $payload) && $payload['header'] !== null) {
                $this->draft_header = $payload['header'];
            }
            if (array_key_exists('footer', $payload) && $payload['footer'] !== null) {
                $this->draft_footer = $payload['footer'];
            }
            if (array_key_exists('templates', $payload) && $payload['templates'] !== null) {
                $this->draft_templates = $payload['templates'];
            }
        } else {
            $this->blocks = $payload['blocks'];
            if (array_key_exists('settings', $payload) && $payload['settings'] !== null) {
                $this->settings = $payload['settings'];
            }
            if (array_key_exists('header', $payload) && $payload['header'] !== null) {
                $this->header = $payload['header'];
            }
            if (array_key_exists('footer', $payload) && $payload['footer'] !== null) {
                $this->footer = $payload['footer'];
            }
            if (array_key_exists('templates', $payload) && $payload['templates'] !== null) {
                $this->templates = $payload['templates'];
            }
        }

        $this->save();
    }

    /**
     * Snapshot of the builder's current working state (draft overlay when live).
     *
     * @return array{
     *     blocks: array<int, array<string, mixed>>,
     *     settings: array<string, mixed>,
     *     header: array<string, mixed>,
     *     footer: array<string, mixed>,
     *     templates: array<string, mixed>
     * }
     */
    public function builderWorkingSnapshot(): array
    {
        return [
            'blocks' => $this->builderResolvedBlocks(),
            'settings' => $this->builderResolvedSettings(),
            'header' => $this->builderResolvedHeader(),
            'footer' => $this->builderResolvedFooter(),
            'templates' => $this->builderResolvedTemplates(),
        ];
    }

    /**
     * The block tree for rendering, falling back to empty for legacy themes.
     *
     * @return array<int, array<string, mixed>>
     */
    public function resolvedBlocks(): array
    {
        return $this->blocks ?? [];
    }

    /**
     * Per-template block trees (product/cart/collection pages).
     *
     * @return array<string, array{before: array<int, array<string, mixed>>, after: array<int, array<string, mixed>>}>
     */
    public function resolvedTemplates(): array
    {
        $templates = is_array($this->templates) ? $this->templates : [];

        return array_replace_recursive([
            'product' => ['before' => [], 'after' => []],
            'cart' => ['before' => [], 'after' => []],
            'collection' => ['before' => [], 'after' => []],
        ], $templates);
    }

    /**
     * @return array<string, mixed>
     */
    public static function headerDefaults(): array
    {
        return [
            'logo_text' => '',
            'logo_image' => null,
            'background_color' => '#fbf7f2',
            'text_color' => '#2c2724',
            'accent_color' => '#b06a4a',
            'sticky' => true,
            'transparent_on_hero' => false,
            'category_count' => 5,
            'menu' => [], // array of {label: {ar,en}, url}
            'show_search' => true,
            'show_account' => true,
            'show_cart' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function footerDefaults(): array
    {
        return [
            'tagline' => '',
            'copyright_text' => '',
            'show_links' => true,
            'background_color' => '#f3ede4',
            'text_color' => '#2c2724',
            'muted_color' => '#8a807a',
            'columns' => [], // array of {heading: {ar,en}, links: [{label, url}]}
            'social' => [], // array of {platform: 'instagram'|..., url}
            'payment_icons' => ['mada', 'visa', 'mastercard'],
            'show_logo' => true,
        ];
    }

    /** Curated payment-icon keys (Saudi-relevant). */
    public static function paymentIconOptions(): array
    {
        return [
            ['label' => 'mada', 'value' => 'mada'],
            ['label' => 'Visa', 'value' => 'visa'],
            ['label' => 'Mastercard', 'value' => 'mastercard'],
            ['label' => 'Apple Pay', 'value' => 'applepay'],
            ['label' => 'STC Pay', 'value' => 'stcpay'],
            ['label' => 'Cash on delivery', 'value' => 'cod'],
        ];
    }

    /** Curated social platforms. */
    public static function socialPlatformOptions(): array
    {
        return [
            ['label' => 'Instagram', 'value' => 'instagram'],
            ['label' => 'Twitter / X', 'value' => 'twitter'],
            ['label' => 'Snapchat', 'value' => 'snapchat'],
            ['label' => 'TikTok', 'value' => 'tiktok'],
            ['label' => 'WhatsApp', 'value' => 'whatsapp'],
            ['label' => 'YouTube', 'value' => 'youtube'],
            ['label' => 'Facebook', 'value' => 'facebook'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function resolvedHeader(): array
    {
        return array_replace_recursive(self::headerDefaults(), $this->header ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    public function resolvedFooter(): array
    {
        return array_replace_recursive(self::footerDefaults(), $this->footer ?? []);
    }

    /**
     * @return Collection<int, ThemePreset>
     */
    public static function availablePresets(): Collection
    {
        return static::withoutGlobalScope(new PresetOrTenantScope)
            ->presets()
            ->orderBy('preset_slug')
            ->get()
            ->map(fn (self $theme) => ThemePreset::fromModel($theme));
    }

    /**
     * The active theme for the current request. Uses the configured
     * `active_theme_resolver` (closure returning ?Theme) when set; otherwise
     * resolves the latest published theme for the current tenant id
     * (`theme-builder.tenant_id`, single-tenant = null → no active theme).
     */
    public static function active(): ?self
    {
        $resolver = config('theme-builder.active_theme_resolver');

        if ($resolver !== null) {
            $theme = app()->call($resolver);

            return $theme instanceof self ? $theme : null;
        }

        $tenantId = app()->bound('theme-builder.tenant_id')
            ? app('theme-builder.tenant_id')
            : null;

        if ($tenantId === null) {
            return null;
        }

        if (app()->bound(static::class.'@active')) {
            return resolve(static::class.'@active');
        }

        $theme = static::withoutGlobalScope(new PresetOrTenantScope)
            ->where(self::ownerColumn(), $tenantId)
            ->where('status', ThemeStatus::Published)
            ->latest('published_at')
            ->first();

        app()->instance(static::class.'@active', $theme);

        return $theme;
    }

    /**
     * The latest published preset, used as a storefront fallback when no
     * tenant-specific theme is active (e.g. a single-tenant default install).
     */
    public static function defaultPreset(): ?self
    {
        return static::withoutGlobalScope(new PresetOrTenantScope)
            ->presets()
            ->orderByDesc('id')
            ->first();
    }

    private function tenantId(): int|string|null
    {
        return app()->bound('theme-builder.tenant_id')
            ? app('theme-builder.tenant_id')
            : null;
    }
}
