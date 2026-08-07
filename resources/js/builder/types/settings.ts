export interface ColorScheme {
    key: string;
    name: string;
    background: string;
    surface: string;
    text: string;
    muted: string;
    primary: string;
    button: string;
    button_text: string;
    accent: string;
}

export interface ThemeSettings {
    colors: {
        primary: string;
        secondary: string;
        accent: string;
        background: string;
        surface: string;
        text: string;
        muted: string;
    };
    color_schemes: ColorScheme[];
    typography: {
        heading_font: string;
        body_font: string;
        base_size: number;
    };
    radius: {
        card: number;
        button: number;
    };
    semantic: {
        price: string | null;
        sale: string;
        instock: string;
        lowstock: string;
        outofstock: string | null;
    };
    layout: {
        content_width: number;
        section_rhythm: 'compact' | 'medium' | 'spacious';
    };
    effects: {
        product_border: boolean;
        shine_animation: boolean;
    };
    product_card: {
        show_image: boolean;
        show_title: boolean;
        show_price: boolean;
        show_compare_price: boolean;
        show_rating: boolean;
        show_add_to_cart: boolean;
        show_sale_badge: boolean;
        show_new_badge: boolean;
        show_out_of_stock_badge: boolean;
        border: boolean;
        hover_zoom: boolean;
    };
    product_page: {
        breadcrumbs: boolean;
        show_rating: boolean;
        show_related: boolean;
        sticky_purchase_bar: boolean;
        show_share: boolean;
        show_quantity_stepper: boolean;
        buy_now_button: boolean;
    };
    cart: {
        free_shipping_bar: boolean;
        free_shipping_threshold: number;
        show_coupon: boolean;
        show_cross_sell: boolean;
        show_order_summary: boolean;
    };
}

export const defaultSettings: ThemeSettings = {
    colors: {
        primary: '#458FCC',
        secondary: '#3A29AB',
        accent: '#8A30C7',
        background: '#FFFFFF',
        surface: '#F9FAFB',
        text: '#1a1a1f',
        muted: '#6B7280',
    },
    color_schemes: [
        {
            key: '1',
            name: 'Default',
            background: '#FFFFFF',
            surface: '#F9FAFB',
            text: '#1a1a1f',
            muted: '#6B7280',
            primary: '#458FCC',
            button: '#2F7AB5',
            button_text: '#FFFFFF',
            accent: '#8A30C7',
        },
        {
            key: '2',
            name: 'Dark',
            background: '#1a1a1f',
            surface: '#26262e',
            text: '#FFFFFF',
            muted: '#a3a3ad',
            primary: '#73FAFD',
            button: '#2F7AB5',
            button_text: '#FFFFFF',
            accent: '#EB3DF7',
        },
    ],
    typography: {
        heading_font: 'IBM Plex Sans Arabic',
        body_font: 'IBM Plex Sans Arabic',
        base_size: 16,
    },
    radius: {
        card: 12,
        button: 8,
    },
    // `price` and `outofstock` are null so they derive from the merchant's
    // text/muted colors, exactly as Theme::defaultSettings() does.
    semantic: {
        price: null,
        sale: '#C0392B',
        instock: '#1E8E5A',
        lowstock: '#D08700',
        outofstock: null,
    },
    layout: {
        content_width: 1152,
        section_rhythm: 'medium',
    },
    effects: {
        product_border: false,
        shine_animation: false,
    },
    product_card: {
        show_image: true,
        show_title: true,
        show_price: true,
        show_compare_price: true,
        show_rating: true,
        show_add_to_cart: true,
        show_sale_badge: true,
        show_new_badge: false,
        show_out_of_stock_badge: true,
        border: false,
        hover_zoom: true,
    },
    product_page: {
        breadcrumbs: true,
        show_rating: true,
        show_related: true,
        sticky_purchase_bar: false,
        show_share: true,
        show_quantity_stepper: true,
        buy_now_button: false,
    },
    cart: {
        free_shipping_bar: true,
        free_shipping_threshold: 200,
        show_coupon: true,
        show_cross_sell: false,
        show_order_summary: true,
    },
};

/**
 * Convert theme settings into CSS custom properties for the storefront.
 * Emits the storefront's real token names (so the canvas preview matches the
 * live site) AND legacy aliases (`--color-primary`, `--font-body`, …) so the
 * editor's own Tailwind utilities (`bg-primary-600`, etc.) still resolve.
 */
export function normalizeHex(hex: string, fallback = '#458FCC'): string {
    hex = hex.trim();
    if (hex === '') return fallback;
    if (!hex.startsWith('#')) hex = `#${hex}`;
    if (/^#([0-9a-f]{3})$/i.test(hex)) {
        hex = `#${hex[1]}${hex[1]}${hex[2]}${hex[2]}${hex[3]}${hex[3]}`;
    }
    if (!/^#[0-9a-f]{6}$/i.test(hex)) return fallback;

    return hex.toUpperCase();
}

export function mix(a: string, b: string, amount: number): string {
    const an = normalizeHex(a);
    const bn = normalizeHex(b);
    const ar = parseInt(an.slice(1, 3), 16);
    const ag = parseInt(an.slice(3, 5), 16);
    const ab = parseInt(an.slice(5, 7), 16);
    const br = parseInt(bn.slice(1, 3), 16);
    const bg = parseInt(bn.slice(3, 5), 16);
    const bb = parseInt(bn.slice(5, 7), 16);
    const r = Math.round(ar + (br - ar) * amount);
    const g = Math.round(ag + (bg - ag) * amount);
    const bl = Math.round(ab + (bb - ab) * amount);

    return `#${[r, g, bl].map((v) => v.toString(16).padStart(2, '0')).join('')}`.toUpperCase();
}

export function deriveRamp(primary: string): Record<string, string> {
    const p = normalizeHex(primary);

    return {
        50: mix(p, '#FFFFFF', 0.92),
        100: mix(p, '#FFFFFF', 0.85),
        200: mix(p, '#FFFFFF', 0.7),
        300: mix(p, '#FFFFFF', 0.5),
        400: mix(p, '#FFFFFF', 0.25),
        500: p,
        600: mix(p, '#000000', 0.12),
        700: mix(p, '#000000', 0.25),
        800: mix(p, '#000000', 0.4),
        900: mix(p, '#000000', 0.55),
    };
}

export function settingsToCssVars(settings: ThemeSettings): Record<string, string> {
    const c = settings.colors;
    const t = settings.typography;
    const r = settings.radius;
    const s = settings.semantic;
    const l = settings.layout;
    // `App\Enums\SectionRhythm::multiplier()` is the source of truth for these
    // multipliers; a test asserts these literals still match it.
    const rhythm =
        l?.section_rhythm === 'compact' ? '0.75' : l?.section_rhythm === 'spacious' ? '1.4' : '1';
    const bodyFont = `"${t.body_font}", system-ui, sans-serif`;
    const headingFont = `"${t.heading_font}", system-ui, sans-serif`;
    const ramp = deriveRamp(c.primary);
    const vars: Record<string, string> = {};

    for (const [shade, hex] of Object.entries(ramp)) {
        vars[`--color-primary-${shade}`] = hex;
        vars[`--color-clay-${shade}`] = hex;
    }

    return {
        ...vars,
        // Storefront tokens (mirror Theme::cssVarsFromSettings 1:1).
        '--color-paper': c.background,
        '--color-paper-deep': mix(c.background, c.text, 0.03),
        '--color-surface': c.surface,
        '--color-ink': c.text,
        '--color-ink-soft': c.muted,
        '--color-stone': c.muted,
        '--color-line': mix(c.background, c.text, 0.1),
        '--color-line-strong': mix(c.background, c.text, 0.18),
        '--color-secondary': c.secondary,
        '--color-accent': c.accent,
        '--font-sans': bodyFont,
        '--font-display': headingFont,
        '--radius-cadi': `${r.card}px`,
        '--radius-button': `${r.button}px`,
        // Fallbacks resolve from defaultSettings, never re-typed literals, so
        // they cannot drift from it (Theme::cssVarsFromSettings does the same).
        '--color-success-600': s?.instock ?? defaultSettings.semantic.instock,
        '--color-warning-600': s?.lowstock ?? defaultSettings.semantic.lowstock,
        '--color-danger-600': s?.sale ?? defaultSettings.semantic.sale,
        // These two are nullable by design — see the PHP defaults. `??` must
        // catch null, so do not switch to `||`, which would also swallow a
        // legitimate empty-string override differently than PHP does.
        //
        // Known divergence: PHP puts these through normalizeHex(), the preview
        // does not. A malformed hex renders as-is here but sanitises to
        // #000000 on the live site. Harmless for injection (React sets inline
        // styles through the CSSOM), but the two can disagree on bad input.
        '--color-price': s?.price ?? c.text,
        '--color-outofstock': s?.outofstock ?? c.muted,
        '--radius-card': `${r.card}px`,
        '--container-content': `${Math.max(640, Math.min(1920, l?.content_width ?? defaultSettings.layout.content_width))}px`,
        '--space-section-scale': rhythm,
        // Legacy aliases (editor Tailwind utilities + forward-compat).
        '--color-primary': c.primary,
        '--color-bg': c.background,
        '--color-text': c.text,
        '--color-muted': c.muted,
        '--font-heading': headingFont,
        '--font-body': bodyFont,
    };
}

/**
 * Families Google Fonts hosts. Must stay in lock-step with the
 * `$knownGoogle` list in App\Models\Theme::googleFontsUrl().
 */
const GOOGLE_FONT_FAMILIES = [
    'Cormorant Garamond', 'Inter', 'Playfair Display', 'Lora',
    'Merriweather', 'Montserrat', 'Roboto', 'Open Sans', 'Nunito',
    'Work Sans', 'Poppins', 'Raleway', 'Source Sans 3', 'DM Sans',
    'Tajawal', 'Almarai', 'Cairo', 'Noto Sans Arabic', 'IBM Plex Sans Arabic',
] as const;

/**
 * Mirror of App\Models\Theme::googleFontsUrl(): build the Google Fonts CSS2
 * URL for the theme's heading + body fonts, or null when neither is a
 * Google-hosted family so the caller can skip the request. This is what
 * makes the canvas render the merchant's chosen fonts instead of falling
 * back to system-ui — the storefront loads the very same <link> in its
 * layout, so keeping the two in sync is what makes the preview match.
 */
export function googleFontsUrl(settings: ThemeSettings): string | null {
    const typo = settings?.typography;
    const requested = [typo?.heading_font, typo?.body_font].filter(
        (f): f is string => Boolean(f),
    );

    const families: string[] = [];
    for (const name of requested) {
        const match = GOOGLE_FONT_FAMILIES.find((g) => g.toLowerCase() === name.toLowerCase());
        if (match && !families.includes(match)) {
            families.push(match);
        }
    }

    if (families.length === 0) {
        return null;
    }

    const query = families
        .map((f) => `family=${encodeURIComponent(f)}:wght@400;500;600;700`)
        .join('&');

    return `https://fonts.googleapis.com/css2?${query}&display=swap`;
}

