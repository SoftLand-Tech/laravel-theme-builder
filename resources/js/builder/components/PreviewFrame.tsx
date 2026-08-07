import React from 'react';
import { googleFontsUrl, settingsToCssVars, type ThemeSettings } from '../types/settings';
import { ViewportContext } from '../viewportContext';

interface PreviewFrameProps {
    settings: ThemeSettings;
    locale: string;
    viewport: 'desktop' | 'mobile';
    themeCssUrl?: string;
    themeScopeClass?: string;
    /** Optional click handler for the area outside the page (e.g. deselect). */
    onBackgroundClick?: () => void;
    children: React.ReactNode;
}

/**
 * The scrollable, viewport-sized white "device" frame that hosts every editor
 * preview (home blocks, product page, cart page). Injects the theme's CSS
 * variables, flips `dir` for Arabic, constrains mobile width, and neutralizes
 * link clicks from the live preview components.
 */
export function PreviewFrame({ settings, locale, viewport, themeCssUrl, themeScopeClass, onBackgroundClick, children }: PreviewFrameProps) {
    const dir = locale === 'ar' ? 'rtl' : 'ltr';
    const fontsUrl = googleFontsUrl(settings);

    return (
        <div className="flex-1 overflow-auto bg-neutral-100 p-6" onClick={onBackgroundClick}>
            {themeCssUrl ? <link rel="stylesheet" href={themeCssUrl} /> : null}
            {fontsUrl ? <link rel="stylesheet" href={fontsUrl} /> : null}
            <div
                dir={dir}
                className={`mx-auto rounded-xl shadow-2xl ${themeScopeClass ?? ''}`}
                style={{
                    ...(settingsToCssVars(settings) as React.CSSProperties),
                    maxWidth: viewport === 'mobile' ? 420 : 1280,
                    minHeight: '100%',
                    background: 'var(--color-bg, #ffffff)',
                    color: 'var(--color-text, #1a1a1f)',
                    fontFamily: 'var(--font-body)',
                }}
                onClickCapture={(e) => {
                    const el = e.target as HTMLElement;
                    if (el.closest('a')) e.preventDefault();
                }}
            >
                <ViewportContext.Provider value={viewport}>
                    {children}
                </ViewportContext.Provider>
            </div>
        </div>
    );
}
