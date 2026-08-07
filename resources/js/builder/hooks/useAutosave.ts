import { useCallback, useEffect, useRef, useState } from 'react';
import type { ThemeSettings } from '../types/settings';
import type { WireBlock } from '../types/blocks';

export type SaveStatus = 'idle' | 'saving' | 'saved' | 'error';

export interface Snapshot {
    blocks: WireBlock[];
    settings: ThemeSettings;
    header?: Record<string, unknown>;
    footer?: Record<string, unknown>;
    templates?: Record<string, unknown>;
}

interface UseAutosaveOptions {
    endpoints: { saveUrl: string; publishUrl: string; promoteUrl: string; exitUrl: string };
    csrfToken: string;
    onThemeMeta?: (meta: { hasUnpublishedDraft?: boolean }) => void;
    getSnapshot: () => Snapshot;
    debounceMs?: number;
}

export interface UseAutosave {
    status: SaveStatus;
    dirty: boolean;
    lastPublished: Date | null;
    lastVersion: string | null;
    /** Debounced draft save. Safe to call on every change. */
    save: () => void;
    /** Flush any pending debounced save immediately and await it. */
    flush: () => Promise<void>;
    /** Publish a new versioned revision (does not update the live storefront). */
    publish: (opts?: { major?: boolean; changeSummary?: string }) => Promise<void>;
    /** Copy draft builder state to the live storefront. */
    promote: (opts?: { major?: boolean; changeSummary?: string }) => Promise<void>;
    /** Navigate back to the store panel. */
    exit: () => void;
    /** Flush then exit. */
    saveAndExit: () => Promise<void>;
}

/**
 * Draft autosave + publish orchestration. Requests are serialized through a
 * single promise chain so promote / publish / save-and-exit always wait for
 * any in-flight autosave. Snapshot and metadata callbacks live in refs so
 * inline parent callbacks do not rebuild the request pipeline every render.
 */
export function useAutosave({
    endpoints,
    csrfToken,
    onThemeMeta,
    getSnapshot,
    debounceMs = 1500,
}: UseAutosaveOptions): UseAutosave {
    const [status, setStatus] = useState<SaveStatus>('idle');
    const [dirty, setDirty] = useState(false);
    const [lastPublished, setLastPublished] = useState<Date | null>(null);
    const [lastVersion, setLastVersion] = useState<string | null>(null);

    const snapshotRef = useRef<Snapshot>(getSnapshot());
    const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const chainRef = useRef<Promise<void>>(Promise.resolve());
    const onThemeMetaRef = useRef(onThemeMeta);
    const csrfTokenRef = useRef(csrfToken);
    const endpointsRef = useRef(endpoints);

    useEffect(() => {
        snapshotRef.current = getSnapshot();
    });

    useEffect(() => {
        onThemeMetaRef.current = onThemeMeta;
    }, [onThemeMeta]);

    useEffect(() => {
        csrfTokenRef.current = csrfToken;
    }, [csrfToken]);

    useEffect(() => {
        endpointsRef.current = endpoints;
    }, [endpoints]);

    const enqueue = useCallback((task: () => Promise<void>): Promise<void> => {
        const next = chainRef.current.catch(() => undefined).then(task);
        chainRef.current = next;
        return next;
    }, []);

    const send = useCallback(
        async (url: string, mode: 'save' | 'publish' | 'promote', extra?: Record<string, unknown>) => {
            const { blocks, settings, header, footer, templates } = snapshotRef.current;
            setStatus('saving');

            // Read the CSRF token live from the <meta name="csrf-token"> tag on
            // every send. The builder is a long-lived single-page shell, and the
            // token captured once into a dataset attribute at mount can go stale
            // (session expiry / regeneration) — which surfaces as HTTP 419.
            const liveToken =
                (typeof document !== 'undefined'
                    && document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content)
                || csrfTokenRef.current;

            const post = (token: string) =>
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        Accept: 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        blocks,
                        settings,
                        ...(header ? { header } : {}),
                        ...(footer ? { footer } : {}),
                        ...(templates ? { templates } : {}),
                        ...(extra ?? {}),
                    }),
                });

            try {
                const res = await post(liveToken);
                // 419 = CSRF token mismatch. The page's meta token may be stale;
                // reload once to pick up a fresh token, then retry the same send.
                if (res.status === 419 && typeof window !== 'undefined') {
                    window.location.reload();
                    return;
                }
                if (!res.ok) throw new Error(`Server responded ${res.status}`);
                const json = await res.json().catch(() => ({}));
                if (mode === 'publish' || mode === 'promote') {
                    if (typeof json.version === 'string') setLastVersion(json.version);
                    setLastPublished(new Date());
                }
                if (json.theme && onThemeMetaRef.current) {
                    onThemeMetaRef.current({
                        hasUnpublishedDraft: Boolean(json.theme.hasUnpublishedDraft),
                    });
                }
                setDirty(false);
                setStatus('saved');
            } catch (err) {
                console.error('Save failed', err);
                setStatus('error');
                throw err;
            }
        },
        [],
    );

    const flush = useCallback(async () => {
        if (timerRef.current) {
            clearTimeout(timerRef.current);
            timerRef.current = null;
        }
        await enqueue(() => send(endpointsRef.current.saveUrl, 'save'));
    }, [enqueue, send]);

    const save = useCallback(() => {
        setDirty(true);
        if (timerRef.current) clearTimeout(timerRef.current);
        timerRef.current = setTimeout(() => {
            timerRef.current = null;
            void enqueue(() => send(endpointsRef.current.saveUrl, 'save'));
        }, debounceMs);
    }, [debounceMs, enqueue, send]);

    const publish = useCallback(
        async (opts?: { major?: boolean; changeSummary?: string }) => {
            if (timerRef.current) {
                clearTimeout(timerRef.current);
                timerRef.current = null;
            }
            await enqueue(() =>
                send(endpointsRef.current.publishUrl, 'publish', {
                    major: opts?.major === true,
                    change_summary: opts?.changeSummary ?? '',
                }),
            );
        },
        [enqueue, send],
    );

    const promote = useCallback(
        async (opts?: { major?: boolean; changeSummary?: string }) => {
            if (timerRef.current) {
                clearTimeout(timerRef.current);
                timerRef.current = null;
            }
            await enqueue(() =>
                send(endpointsRef.current.promoteUrl, 'promote', {
                    major: opts?.major === true,
                    change_summary: opts?.changeSummary ?? '',
                }),
            );
        },
        [enqueue, send],
    );

    const exit = useCallback(() => {
        if (timerRef.current) clearTimeout(timerRef.current);
        window.location.href = endpointsRef.current.exitUrl;
    }, []);

    const saveAndExit = useCallback(async () => {
        await flush();
        exit();
    }, [flush, exit]);

    useEffect(() => () => {
        if (timerRef.current) clearTimeout(timerRef.current);
    }, []);

    return { status, dirty, lastPublished, lastVersion, save, flush, publish, promote, exit, saveAndExit };
}
