import React, { useEffect, useState } from 'react';
import { apiEndpoint } from '../config/api';

interface CategoryNode {
    id: number;
    name: string;
    slug: string;
    children?: Array<{ id: number; name: string; slug: string }>;
}

interface CollectionMultiPickerProps {
    value: number[] | null;
    onChange: (value: number[] | null) => void;
    endpoint?: string;
}

const inputClass =
    'w-full rounded-md border border-neutral-300 bg-white px-3 py-1.5 text-sm text-neutral-900 placeholder:text-neutral-400 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20';

/**
 * Multi-select of the store's categories. Non-technical: shows category names
 * only (grouped by parent); stores an array of ids.
 */
export function CollectionMultiPickerField({
    value,
    onChange,
    endpoint = apiEndpoint('/api/categories'),
}: CollectionMultiPickerProps) {
    const [roots, setRoots] = useState<CategoryNode[]>([]);

    useEffect(() => {
        fetch(endpoint, { credentials: 'same-origin' })
            .then((r) => (r.ok ? r.json() : { data: [] }))
            .then((d) => setRoots(d.data ?? []))
            .catch(() => {});
    }, [endpoint]);

    const ids = Array.isArray(value) ? value : value !== null ? [value] : [];
    const selected = new Set(ids);

    const toggle = (id: number) => {
        const next = selected.has(id) ? ids.filter((x) => x !== id) : [...ids, id];
        onChange(next);
    };

    return (
        <div className="space-y-1">
            {selected.size > 0 && (
                <button type="button" onClick={() => onChange([])} className="text-[11px] text-neutral-500 hover:underline">
                    Clear all ({selected.size})
                </button>
            )}
            <select
                multiple
                value={ids.map(String)}
                onChange={(e) => {
                    const picked = Array.from(e.target.selectedOptions).map((o) => Number(o.value));
                    onChange(picked);
                }}
                className={`${inputClass} h-40`}
            >
                {roots.map((c) =>
                    (c.children ?? []).length === 0 ? (
                        <option key={c.id} value={c.id}>
                            {c.name}
                        </option>
                    ) : (
                        <optgroup key={c.id} label={c.name}>
                            {c.children!.map((ch) => (
                                <option key={ch.id} value={ch.id}>
                                    {ch.name}
                                </option>
                            ))}
                        </optgroup>
                    ),
                )}
            </select>
            <p className="text-[11px] text-neutral-500">Hold Ctrl/Cmd to select multiple categories.</p>
        </div>
    );
}
