import React, { useEffect, useState } from 'react';
import { useT } from '../i18n';

interface RevisionsPanelProps {
    endpoints: {
        revisionsUrl: string;
        restoreUrl: (revisionId: number) => string;
    };
    csrfToken: string;
    onRestored: () => void;
}

interface Revision {
    id: number;
    revision_number: number;
    version: string | null;
    change_summary: string | null;
    published_at: string | null;
    created_at: string;
    blocks_count: number;
}

export function RevisionsPanel({ endpoints, csrfToken, onRestored }: RevisionsPanelProps) {
    const t = useT();
    const [revisions, setRevisions] = useState<Revision[]>([]);
    const [loading, setLoading] = useState(true);
    const [restoring, setRestoring] = useState<number | null>(null);

    useEffect(() => {
        fetch(endpoints.revisionsUrl, { credentials: 'same-origin' })
            .then((r) => (r.ok ? r.json() : { data: [] }))
            .then((data) => {
                setRevisions(data.data ?? []);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, [endpoints.revisionsUrl]);

    const restore = async (revision: Revision) => {
        if (!confirm(`Restore version ${revision.version ?? '#' + revision.revision_number}? This will replace the current draft.`)) return;
        setRestoring(revision.id);
        try {
            const res = await fetch(endpoints.restoreUrl(revision.id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            });
            if (res.ok) {
                onRestored();
            }
        } finally {
            setRestoring(null);
        }
    };

    return (
        <div className="space-y-3">
            <h2 className="text-sm font-semibold text-neutral-900">{t("Revisions")}</h2>

            {loading && <p className="text-sm text-neutral-500">{t("Loading...")}</p>}

            {!loading && revisions.length === 0 && (
                <p className="text-sm text-neutral-500">{t("No published revisions yet. Publish your theme to create one.")}</p>
            )}

            <div className="space-y-2">
                {revisions.map((r) => (
                    <div key={r.id} className="rounded-lg border border-neutral-200 bg-neutral-100 p-3">
                        <div className="flex items-center justify-between">
                            <div>
                                <div className="text-sm font-medium text-neutral-900">
                                    {r.version ? t('v:version', { version: r.version }) : t('Revision #:n', { n: r.revision_number })}
                                </div>
                                <div className="text-xs text-neutral-500">
                                    {new Date(r.created_at).toLocaleString()}
                                </div>
                                {r.change_summary && (
                                    <div className="text-xs text-neutral-500">{r.change_summary}</div>
                                )}
                                <div className="text-xs text-neutral-500">{t(':n blocks', { n: r.blocks_count })}</div>
                            </div>
                            <button
                                type="button"
                                disabled={restoring === r.id}
                                onClick={() => restore(r)}
                                className="rounded border border-neutral-200 px-3 py-1 text-xs text-neutral-300 transition hover:border-neutral-400 disabled:opacity-50"
                            >
                                {restoring === r.id ? t("Restoring...") : t("Restore")}
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
