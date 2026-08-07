import React from 'react';
import { deriveRamp, normalizeHex, mix } from '../types/settings';
import type { ColorScheme } from '../types/settings';

interface SectionProps {
    children: React.ReactNode;
    sectionPaddingTop?: string;
    sectionPaddingBottom?: string;
    sectionBackground?: string;
    sectionWidth?: string;
    sectionTextAlign?: string;
    colorSchemes: ColorScheme[];
}

// Mirror sections.blade.php verbatim: the rhythm-scaled `--space-section-*`
// tokens (so layout.section_rhythm affects the preview exactly as it does the
// live storefront) rather than fixed `pt-N` values that ignore the scale.
const PADDING_TOP: Record<string, string> = {
    none: '',
    small: 'pt-(--space-section-sm)',
    medium: 'pt-(--space-section-md)',
    large: 'pt-(--space-section-lg)',
};
const PADDING_BOTTOM: Record<string, string> = {
    none: '',
    small: 'pb-(--space-section-sm)',
    medium: 'pb-(--space-section-md)',
    large: 'pb-(--space-section-lg)',
};

/**
 * Mirrors `resources/views/components/theme/sections.blade.php`: wraps each
 * block in the same `block-section` + container + padding + scheme background,
 * so the editor preview matches the live storefront's section rhythm. When a
 * `sectionBackground` scheme is set, the scheme's tokens are emitted inline
 * (the storefront does this via `.scheme-N` rules from colorSchemeRules).
 */
export function Section({
    children,
    sectionPaddingTop = 'medium',
    sectionPaddingBottom = 'medium',
    sectionBackground = '',
    sectionWidth = 'contained',
    sectionTextAlign = '',
    colorSchemes,
}: SectionProps) {
    const padTop = PADDING_TOP[sectionPaddingTop] ?? 'pt-(--space-section-md)';
    const padBottom = PADDING_BOTTOM[sectionPaddingBottom] ?? 'pb-(--space-section-md)';
    const container = sectionWidth === 'full' ? 'w-full' : 'mx-auto w-full max-w-content px-4 sm:px-6 lg:px-8';
    const align =
        sectionTextAlign === 'start'
            ? 'text-start'
            : sectionTextAlign === 'end'
                ? 'text-end'
                : sectionTextAlign === 'center'
                    ? 'text-center'
                    : '';

    const scheme = sectionBackground
        ? colorSchemes.find((s) => String(s.key) === String(sectionBackground))
        : undefined;

    const style: React.CSSProperties = scheme ? schemeVars(scheme) : {};

    return (
        <div className={`block-section ${padTop} ${padBottom}`} style={style}>
            <div className={`${container} ${align}`}>{children}</div>
        </div>
    );
}

/** Inline equivalent of Theme::colorSchemeRules() for one scheme. */
function schemeVars(s: ColorScheme): React.CSSProperties {
    const primary = normalizeHex(s.primary, '#458FCC');
    const background = normalizeHex(s.background, '#FFFFFF');
    const text = normalizeHex(s.text, '#1a1a1f');
    const muted = normalizeHex(s.muted, '#6B7280');
    const ramp = deriveRamp(primary);

    const vars: Record<string, string> = {
        '--color-paper': background,
        '--color-paper-deep': mix(background, text, 0.03),
        '--color-bg': background,
        '--color-surface': normalizeHex(s.surface, '#F9FAFB'),
        '--color-ink': text,
        '--color-text': text,
        '--color-ink-soft': muted,
        '--color-muted': muted,
        '--color-stone': muted,
        '--color-line': mix(background, text, 0.1),
        '--color-line-strong': mix(background, text, 0.18),
        '--color-primary': primary,
        '--color-accent': normalizeHex(s.accent, '#8A30C7'),
    };
    for (const [shade, hex] of Object.entries(ramp)) {
        vars[`--color-clay-${shade}`] = hex;
        vars[`--color-primary-${shade}`] = hex;
    }
    vars['--color-clay-600'] = normalizeHex(s.button, primary);
    vars.backgroundColor = background;
    vars.color = text;

    return vars as React.CSSProperties;
}
