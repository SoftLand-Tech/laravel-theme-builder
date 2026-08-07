import React, { useMemo, useEffect, useState, Component } from 'react';
import { BLOCK_SCHEMAS } from '../config/blocks';
import type { BlockProps } from '../blocks/_shared';

/**
 * Theme-aware block preview resolver.
 *
 * For a compiled theme that ships its own component package, the editor renders
 * the *theme's* preview so the canvas matches the storefront. Uses a plain
 * dynamic import (not React.lazy) so missing previews fail silently. Any render
 * error in a theme preview is caught by an ErrorBoundary and falls back to the
 * core `BLOCK_SCHEMAS` preview — theme previews are best-effort.
 */

interface ThemePreviewProps {
    type: string;
    themeSlug?: string;
    locale: string;
}

const successCache = new Map<string, React.ComponentType<BlockProps>>();
const failedCache = new Set<string>();

function loadThemeComponent(
    slug: string,
    type: string,
): Promise<React.ComponentType<BlockProps>> {
    const key = `${slug}:${type}`;

    if (successCache.has(key)) {
        return Promise.resolve(successCache.get(key)!);
    }

    if (failedCache.has(key)) {
        return Promise.reject(new Error(`theme preview missing: ${slug}/${type}`));
    }

    return import(`./${slug}/${type}.tsx`)
        .then((mod) => {
            const C = resolveComponent(mod, type);
            if (typeof C !== 'function') {
                throw new Error(`theme preview not a component: ${slug}/${type}`);
            }
            successCache.set(key, C);
            return C;
        })
        .catch(() => {
            failedCache.add(key);
            throw new Error(`theme preview missing: ${slug}/${type}`);
        });
}

/**
 * Extract a React component (a function) from a dynamically-imported module.
 * The module shape varies by bundler output, so never trust `default`/named
 * exports blindly — only a function is a valid component (React #130 fires on
 * anything else). Prefer the named export matching the block type, then a
 * function-typed default, then the first function export.
 */
function resolveComponent(
    mod: Record<string, unknown>,
    type: string,
): React.ComponentType<BlockProps> | null {
    const named = mod[type];
    if (typeof named === 'function') {
        return named as React.ComponentType<BlockProps>;
    }
    if (typeof mod.default === 'function') {
        return mod.default as React.ComponentType<BlockProps>;
    }
    for (const value of Object.values(mod)) {
        if (typeof value === 'function') {
            return value as React.ComponentType<BlockProps>;
        }
    }

    return null;
}

class ThemePreviewBoundary extends Component<
    { fallback: React.ReactNode; resetKey: string },
    { hasError: boolean }
> {
    state = { hasError: false };

    static getDerivedStateFromError() {
        return { hasError: true };
    }

    componentDidUpdate(prev: { resetKey: string }) {
        if (prev.resetKey !== this.props.resetKey && this.state.hasError) {
            this.setState({ hasError: false });
        }
    }

    render() {
        return this.state.hasError ? this.props.fallback : this.props.children;
    }
}

export function ThemePreview({ type, themeSlug, locale, ...props }: ThemePreviewProps) {
    const coreSchema = BLOCK_SCHEMAS[type];
    const CoreComponent = coreSchema?.Component;

    const coreFallback = CoreComponent ? (
        <CoreComponent locale={locale} {...props} />
    ) : (
        <MissingPreview type={type} />
    );

    const cacheKey = useMemo(() => (themeSlug ? `${themeSlug}:${type}` : null), [themeSlug, type]);

    const [themeC, setThemeC] = useState<React.ComponentType<BlockProps> | null>(
        () => (cacheKey && successCache.has(cacheKey) ? successCache.get(cacheKey)! : null),
    );
    const [errored, setErrored] = useState(false);

    useEffect(() => {
        if (!cacheKey) return;

        let cancelled = false;
        setErrored(false);

        if (successCache.has(cacheKey)) {
            setThemeC(successCache.get(cacheKey)!);
            return;
        }
        if (failedCache.has(cacheKey)) {
            setErrored(true);
            return;
        }

        loadThemeComponent(themeSlug!, type)
            .then((c) => { if (!cancelled) setThemeC(c); })
            .catch(() => { if (!cancelled) setErrored(true); });

        return () => { cancelled = true; };
    }, [cacheKey, themeSlug, type]);

    // No theme slug → render core directly.
    if (!themeSlug) {
        return coreFallback;
    }

    // Theme preview import failed (no override for this block type) → core.
    if (errored) {
        return coreFallback;
    }

    // Theme preview loaded — render it, but guard against any render-time
    // error so a faulty theme preview never crashes the canvas.
    if (themeC) {
        const T = themeC;
        return (
            <ThemePreviewBoundary fallback={coreFallback} resetKey={cacheKey ?? ''}>
                <T locale={locale} {...props} />
            </ThemePreviewBoundary>
        );
    }

    // Still loading.
    return <div className="animate-pulse rounded bg-neutral-100 p-8" />;
}

function MissingPreview({ type }: { type: string }) {
    return <div className="p-6 text-center text-sm text-neutral-500">Unsupported block: {type}</div>;
}
