/**
 * Resolve the builder API base path from the `theme-builder-api` <meta> tag
 * (emitted by the editor shell), so the pickers don't hardcode `/builder/...`.
 * The meta holds the route prefix, e.g. "builder"; this normalizes to "/builder".
 */
export function apiBasePath(): string {
    if (typeof document === 'undefined') {
        return '/builder';
    }

    const meta = document.querySelector('meta[name="theme-builder-api"]');
    const raw = meta?.getAttribute('content')?.trim();

    if (!raw) {
        return '/builder';
    }

    return raw.startsWith('/') ? raw : `/${raw}`;
}

export function apiEndpoint(path: string): string {
    return `${apiBasePath()}${path.startsWith('/') ? path : `/${path}`}`;
}

/** Currency code for price previews (overridable via Vite env). */
export const CURRENCY: string =
    (import.meta.env.VITE_THEME_BUILDER_CURRENCY as string | undefined) ?? 'SAR';
