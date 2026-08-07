import React, { useEffect, useState } from 'react';
import { apiEndpoint, CURRENCY } from '../config/api';

interface Product {
    id: number;
    name: string;
    price: number;
}

interface ProductPickerProps {
    /** When provided, the field behaves as a multi-select and stores an id array. */
    multi?: boolean;
    value: number | number[] | null;
    onChange: (value: number | number[] | null) => void;
    endpoint?: string;
}

const inputClass =
    'w-full rounded-md border border-neutral-300 bg-white px-3 py-1.5 text-sm text-neutral-900 placeholder:text-neutral-400 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20';

/**
 * Searches the store's published products by name. Non-technical: the user
 * types part of a product name and picks from the list; the stored value is an
 * id, but the UI only ever shows the product name + price, never a raw id.
 */
export function ProductPickerField({ multi = false, value, onChange, endpoint = apiEndpoint('/api/products') }: ProductPickerProps) {
    const [products, setProducts] = useState<Product[]>([]);
    const [selected, setSelected] = useState<Product[]>([]);
    const [loading, setLoading] = useState(false);
    const [search, setSearch] = useState('');

    useEffect(() => {
        const timer = setTimeout(() => {
            setLoading(true);
            const url = search ? `${endpoint}?q=${encodeURIComponent(search)}` : endpoint;
            fetch(url, { credentials: 'same-origin' })
                .then((r) => (r.ok ? r.json() : { data: [] }))
                .then((data) => {
                    setProducts(data.data ?? []);
                    setLoading(false);
                })
                .catch(() => setLoading(false));
        }, 250);
        return () => clearTimeout(timer);
    }, [search, endpoint]);

    // Resolve stored ids to names once on mount (and whenever the endpoint list
    // arrives) so the user sees names, not numbers.
    useEffect(() => {
        const ids = Array.isArray(value) ? value : value !== null ? [value] : [];
        if (ids.length === 0) {
            setSelected([]);
            return;
        }
        const known = new Map<number, Product>();
        // Fetch all referenced ids in one pass (the endpoint returns published
        // products; legacy/missing ids are simply not shown).
        Promise.all(
            ids.map((id) =>
                fetch(`${endpoint}?q=&ids=${id}`, { credentials: 'same-origin' })
                    .then((r) => (r.ok ? r.json() : { data: [] }))
                    .then((d) => (d.data ?? [])[0])
                    .catch(() => null),
            ),
        ).then((results) => {
            for (const p of results) {
                if (p) known.set(p.id, p);
            }
            setSelected(ids.map((id) => known.get(id)).filter((p): p is Product => Boolean(p)));
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const toggle = (p: Product) => {
        if (multi) {
            const ids = Array.isArray(value) ? [...value] : value !== null ? [value] : [];
            const next = ids.includes(p.id) ? ids.filter((id) => id !== p.id) : [...ids, p.id];
            onChange(next.length === 0 ? [] : next);
            setSelected((cur) => (cur.find((x) => x.id === p.id) ? cur.filter((x) => x.id !== p.id) : [...cur, p]));
        } else {
            onChange(p.id);
            setSelected([p]);
        }
    };

    const clear = () => {
        onChange(multi ? [] : null);
        setSelected([]);
    };

    return (
        <div className="space-y-2">
            {selected.length > 0 && (
                <div className="space-y-1">
                    {selected.map((p) => (
                        <div
                            key={p.id}
                            className="flex items-center justify-between rounded border border-neutral-200 bg-neutral-50 px-2 py-1 text-xs text-neutral-800"
                        >
                            <span>{p.name}</span>
                            <button type="button" onClick={() => toggle(p)} className="text-red-500 hover:underline">
                                Remove
                            </button>
                        </div>
                    ))}
                    <button type="button" onClick={clear} className="text-[11px] text-neutral-500 hover:underline">
                        Clear all
                    </button>
                </div>
            )}

            <input
                type="text"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Search products by name..."
                className={inputClass}
            />

            {loading && <p className="text-xs text-neutral-500">Loading...</p>}

            {!loading && (
                <div className="max-h-48 space-y-1 overflow-y-auto">
                    {products.map((p) => {
                        const isSelected = Array.isArray(value)
                            ? value.includes(p.id)
                            : value === p.id;
                        return (
                            <button
                                key={p.id}
                                type="button"
                                onClick={() => toggle(p)}
                                className={`block w-full rounded px-2.5 py-1.5 text-left text-sm transition ${
                                    isSelected
                                        ? 'bg-primary-600 text-white'
                                        : 'text-neutral-800 hover:bg-neutral-100'
                                }`}
                            >
                                {p.name} <span className="text-xs opacity-60">{p.price} {CURRENCY}</span>
                            </button>
                        );
                    })}
                    {products.length === 0 && (
                        <p className="px-2 py-2 text-xs text-neutral-500">No products found.</p>
                    )}
                </div>
            )}
        </div>
    );
}
