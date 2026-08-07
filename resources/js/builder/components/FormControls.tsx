import React, { useEffect, useRef } from 'react';
import { apiEndpoint } from '../config/api';
import type { FieldDef } from '../config/fields';
import { ICON_OPTIONS } from '../config/fields';
import type { Bilingual } from '../types/blocks';
import { useT } from '../i18n';
import { MediaPickerField } from '../fields/MediaPickerField';
import { ProductPickerField } from '../fields/ProductPickerField';
import { BlogPostPickerField } from '../fields/BlogPostPickerField';
import { CollectionMultiPickerField } from '../fields/CollectionMultiPickerField';

const inputClass =
    'w-full rounded-md border border-neutral-300 bg-white px-3 py-1.5 text-sm text-neutral-900 placeholder:text-neutral-400 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20';

/** Shell wrapping a control with a label + optional description. */
export function FieldShell({
    label,
    description,
    children,
}: {
    label?: string;
    description?: string;
    children: React.ReactNode;
}) {
    const t = useT();
    return (
        <div className="space-y-1.5">
            {label && <label className="block text-xs font-medium text-neutral-700">{t(label)}</label>}
            {children}
            {description && <p className="text-[11px] leading-snug text-neutral-500">{t(description)}</p>}
        </div>
    );
}

export function Switch({
    value,
    onChange,
}: {
    value: boolean;
    onChange: (v: boolean) => void;
}) {
    // `dir="ltr"` keeps the knob sliding left→right consistently regardless of
    // the surrounding RTL editor chrome.
    return (
        <button
            type="button"
            dir="ltr"
            role="switch"
            aria-checked={value}
            onClick={() => onChange(!value)}
            className={`relative inline-flex h-6 w-11 shrink-0 items-center rounded-full border transition-colors ${
                value ? 'border-primary-500 bg-primary-600' : 'border-neutral-300 bg-neutral-200'
            }`}
        >
            <span
                className={`inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 ${
                    value ? 'translate-x-[1.375rem]' : 'translate-x-1'
                }`}
            />
        </button>
    );
}

/** A switch rendered inline with its label on a single row (the conventional
 *  toggle layout), with optional description below. */
export function SwitchRow({
    label,
    description,
    value,
    onChange,
}: {
    label?: string;
    description?: string;
    value: boolean;
    onChange: (v: boolean) => void;
}) {
    const t = useT();
    return (
        <div>
            <div className="flex items-center justify-between gap-3">
                <span className="text-sm text-neutral-800">{t(label)}</span>
                <Switch value={value} onChange={onChange} />
            </div>
            {description && <p className="mt-1 text-[11px] leading-snug text-neutral-500">{t(description)}</p>}
        </div>
    );
}

export function TextInput({
    value,
    onChange,
    placeholder,
}: {
    value: string;
    onChange: (v: string) => void;
    placeholder?: string;
}) {
    return (
        <input
            type="text"
            value={value}
            placeholder={placeholder}
            onChange={(e) => onChange(e.target.value)}
            className={inputClass}
        />
    );
}

export function TextArea({
    value,
    onChange,
    placeholder,
}: {
    value: string;
    onChange: (v: string) => void;
    placeholder?: string;
}) {
    return (
        <textarea
            value={value}
            placeholder={placeholder}
            onChange={(e) => onChange(e.target.value)}
            rows={3}
            className={inputClass}
        />
    );
}

export function NumberInput({
    value,
    onChange,
    min,
    max,
    unit,
}: {
    value: number | null;
    onChange: (v: number) => void;
    min?: number;
    max?: number;
    unit?: string;
}) {
    return (
        <div className="flex items-center gap-2">
            <input
                type="number"
                value={value ?? ''}
                min={min}
                max={max}
                onChange={(e) => onChange(e.target.value === '' ? 0 : Number(e.target.value))}
                className={inputClass}
            />
            {unit && <span className="text-xs text-neutral-500">{unit}</span>}
        </div>
    );
}

export function SelectInput({
    value,
    onChange,
    options,
}: {
    value: string;
    onChange: (v: string) => void;
    options: { label: string; value: string }[];
}) {
    const t = useT();
    return (
        <select value={value} onChange={(e) => onChange(e.target.value)} className={inputClass}>
            {options.map((o) => (
                <option key={o.value} value={o.value}>
                    {t(o.label)}
                </option>
            ))}
        </select>
    );
}

export function ImageFieldControl({
    value,
    onChange,
    dimensions,
}: {
    value: string;
    onChange: (v: string) => void;
    dimensions?: { width: number; height: number };
}) {
    const t = useT();
    return (
        <div className="space-y-1">
            <MediaPickerField value={value} onChange={onChange} />
            {dimensions && (
                <p className="text-[11px] text-neutral-500">
                    {t('Recommended :w×:h px', { w: dimensions.width, h: dimensions.height })}
                </p>
            )}
        </div>
    );
}

export function ProductFieldControl({
    value,
    onChange,
}: {
    value: number | null;
    onChange: (v: number | null) => void;
}) {
    return <ProductPickerField value={value} onChange={onChange} />;
}

export function ColorInput({
    value,
    onChange,
}: {
    value: string;
    onChange: (v: string) => void;
}) {
    return (
        <div className="flex items-center gap-2">
            <input
                type="color"
                value={value || '#000000'}
                onChange={(e) => onChange(e.target.value)}
                className="h-9 w-11 shrink-0 cursor-pointer rounded-md border border-neutral-300 bg-white p-0.5"
            />
            <input
                type="text"
                value={value}
                onChange={(e) => onChange(e.target.value)}
                className={`${inputClass} font-mono uppercase`}
            />
        </div>
    );
}

export function RangeInput({
    value,
    onChange,
    min = 0,
    max = 24,
    step = 1,
    unit,
}: {
    value: number;
    onChange: (v: number) => void;
    min?: number;
    max?: number;
    step?: number;
    unit?: string;
}) {
    const t = useT();
    return (
        <div className="flex items-center gap-3">
            <input
                type="range"
                min={min}
                max={max}
                step={step}
                value={value}
                onChange={(e) => onChange(Number(e.target.value))}
                className="w-full accent-primary-500"
            />
            <span className="w-14 shrink-0 text-right text-xs tabular-nums text-neutral-700">
                {value}
                {unit ? ` ${unit}` : ''}
            </span>
        </div>
    );
}

/** Native datetime picker (Countdown endsAt, etc.). Stores an ISO-ish string. */
export function DateTimeInput({
    value,
    onChange,
}: {
    value: string;
    onChange: (v: string) => void;
}) {
    // <input type="datetime-local"> expects "YYYY-MM-DDTHH:MM" (no seconds/timezone).
    const toLocal = (iso: string): string => {
        if (!iso) return '';
        const d = new Date(iso);
        if (Number.isNaN(d.getTime())) return '';
        const pad = (n: number) => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    };

    return (
        <input
            type="datetime-local"
            value={toLocal(value)}
            onChange={(e) => onChange(e.target.value ? new Date(e.target.value).toISOString() : '')}
            className={inputClass}
        />
    );
}

const LINK_PRESETS: Array<{ label: string; value: string }> = [
    { label: 'Home', value: '/' },
    { label: 'All products', value: '/collections/all' },
    { label: 'Cart', value: '/cart' },
    { label: 'Blog', value: '/blog' },
    { label: 'Checkout', value: '/checkout' },
    { label: 'External URL', value: '__external__' },
];

interface CategoryNode {
    id: number;
    name: string;
    slug: string;
    children?: Array<{ id: number; name: string; slug: string }>;
}

interface ProductOption {
    id: number;
    name: string;
    slug: string;
}

/** Fetch the store's categories once for category/link pickers. */
function useCategories(endpoint = apiEndpoint('/api/categories')): CategoryNode[] {
    const [cats, setCats] = React.useState<CategoryNode[]>([]);
    React.useEffect(() => {
        let alive = true;
        fetch(endpoint, { credentials: 'same-origin' })
            .then((r) => (r.ok ? r.json() : { data: [] }))
            .then((d) => {
                if (alive) setCats(d.data ?? []);
            })
            .catch(() => {});
        return () => {
            alive = false;
        };
    }, [endpoint]);
    return cats;
}

/** Flatten categories into link options (`/collections/{slug}`). */
function categoryLinkOptions(cats: CategoryNode[]): Array<{ label: string; value: string }> {
    return cats.flatMap((c) => [
        { label: c.name, value: `/collections/${c.slug}` },
        ...(c.children ?? []).map((ch) => ({ label: `— ${ch.name}`, value: `/collections/${ch.slug}` })),
    ]);
}

/**
 * A category picker: a dropdown of the store's categories (no raw ids). Stores
 * the selected category id, or null for "all categories".
 */
export function CategoryField({
    value,
    onChange,
}: {
    value: number | null;
    onChange: (v: number | null) => void;
}) {
    const cats = useCategories();
    const t = useT();

    return (
        <select
            value={value ?? ''}
            onChange={(e) => onChange(e.target.value === '' ? null : Number(e.target.value))}
            className={inputClass}
        >
            <option value="">{t('All categories')}</option>
            {cats.map((c) =>
                (c.children ?? []).length === 0 ? (
                    <option key={c.id} value={c.id}>
                        {c.name}
                    </option>
                ) : (
                    <optgroup key={c.id} label={c.name}>
                        <option value={c.id}>
                            {c.name} — {t('All')}
                        </option>
                        {c.children.map((ch) => (
                            <option key={ch.id} value={ch.id}>
                                {ch.name}
                            </option>
                        ))}
                    </optgroup>
                ),
            )}
        </select>
    );
}

/**
 * A link field rendered as a dropdown of storefront destinations — pages plus
 * the store's real categories, with a free "External URL" input when External
 * is chosen. The stored value is always a URL path string, so it drops straight
 * into an `href`.
 */
export function LinkField({
    value,
    onChange,
}: {
    value: string;
    onChange: (v: string) => void;
}) {
    const cats = useCategories();
    const t = useT();
    const presets = LINK_PRESETS.map((p) => ({ ...p, label: t(p.label) }));
    const categoryOptions = categoryLinkOptions(cats);

    // Detect whether the current value is a known internal destination,
    // an external URL, or a custom path.
    const allInternal = [...LINK_PRESETS.filter((p) => p.value !== '__external__'), ...categoryOptions];
    const matched = allInternal.find((p) => p.value === value);
    const isExternal = !matched && value !== '' && value !== undefined && value !== null;
    const mode = matched ? matched.value : isExternal ? '__external__' : '';

    return (
        <div className="space-y-2">
            <select
                value={mode}
                onChange={(e) => {
                    const v = e.target.value;
                    if (v === '__external__') {
                        onChange(isExternal ? value : '');
                    } else {
                        onChange(v);
                    }
                }}
                className={inputClass}
            >
                <option value="">{t('Select a destination')}</option>
                {presets.map((p) => (
                    <option key={p.value} value={p.value}>
                        {p.label}
                    </option>
                ))}
                {categoryOptions.length > 0 && (
                    <optgroup label={t('Categories')}>
                        {categoryOptions.map((p) => (
                            <option key={p.value} value={p.value}>
                                {p.label}
                            </option>
                        ))}
                    </optgroup>
                )}
            </select>
            {mode === '__external__' && (
                <input
                    type="url"
                    value={value}
                    placeholder="https://instagram.com/yourstore"
                    onChange={(e) => onChange(e.target.value)}
                    className={inputClass}
                />
            )}
        </div>
    );
}

/** Multi-product picker: shows product names, stores an id array. */
export function MultiProductControl({
    value,
    onChange,
}: {
    value: number[] | null;
    onChange: (v: number[] | null) => void;
}) {
    return <ProductPickerField multi value={value} onChange={onChange} />;
}

/** Multi-collection picker: shows category names, stores an id array. */
export function CollectionMultiControl({
    value,
    onChange,
}: {
    value: number[] | null;
    onChange: (v: number[] | null) => void;
}) {
    return <CollectionMultiPickerField value={value} onChange={onChange} />;
}

/** Single blog-post picker: shows post titles, stores an id. */
export function BlogPostFieldControl({
    value,
    onChange,
}: {
    value: number | null;
    onChange: (v: number | null) => void;
}) {
    return <BlogPostPickerField value={value} onChange={onChange} />;
}

/** Multi blog-post picker: shows post titles, stores an id array. */
export function MultiBlogPostControl({
    value,
    onChange,
}: {
    value: number[] | null;
    onChange: (v: number[] | null) => void;
}) {
    return <BlogPostPickerField multi value={value} onChange={onChange} />;
}

/** YouTube/Vimeo URL input with light validation. */
export function VideoInput({
    value,
    onChange,
}: {
    value: string;
    onChange: (v: string) => void;
}) {
    const t = useT();
    const isSupported = /^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be|vimeo\.com)\//.test(value);
    return (
        <div className="space-y-1">
            <input
                type="url"
                value={value}
                placeholder="https://youtube.com/watch?v=..."
                onChange={(e) => onChange(e.target.value)}
                className={inputClass}
            />
            {value && !isSupported && (
                <p className="text-[11px] text-amber-600">{t('Only YouTube and Vimeo links are supported.')}</p>
            )}
        </div>
    );
}

/** Segmented text-align control (start/center/end). */
export function TextAlignControl({
    value,
    onChange,
}: {
    value: string;
    onChange: (v: string) => void;
}) {
    const t = useT();
    const options: Array<{ value: string; label: string; icon: string }> = [
        { value: 'start', label: t('Start'), icon: '⇤' },
        { value: 'center', label: t('Center'), icon: '↔' },
        { value: 'end', label: t('End'), icon: '⇥' },
    ];
    return (
        <div className="inline-flex rounded-md border border-neutral-300 bg-white p-0.5">
            {options.map((o) => (
                <button
                    key={o.value}
                    type="button"
                    onClick={() => onChange(o.value)}
                    title={o.label}
                    className={`rounded px-3 py-1 text-sm transition font-medium ${
                        value === o.value ? 'bg-primary-600 text-white' : 'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900'
                    }`}
                >
                    <span className="hidden sm:inline">{o.label}</span>
                    <span className="sm:hidden">{o.icon}</span>
                </button>
            ))}
        </div>
    );
}

/** Curated icon picker (no free-text icon names). */
export function IconPicker({
    value,
    onChange,
}: {
    value: string;
    onChange: (v: string) => void;
}) {
    return (
        <select value={value || 'check'} onChange={(e) => onChange(e.target.value)} className={inputClass}>
            {ICON_OPTIONS.map((o) => (
                <option key={o.value} value={o.value}>
                    {o.label}
                </option>
            ))}
        </select>
    );
}

/** Color-scheme dropdown: options are passed from the schema (the live list of schemes). */
export function ColorSchemeSelect({
    value,
    onChange,
    options,
}: {
    value: string;
    onChange: (v: string) => void;
    options: Array<{ label: string; value: string }>;
}) {
    return (
        <select value={value || ''} onChange={(e) => onChange(e.target.value)} className={inputClass}>
            {options.map((o) => (
                <option key={o.value} value={o.value}>
                    {o.label}
                </option>
            ))}
        </select>
    );
}

/** Large, monospaced HTML/embed textarea. */
export function HtmlInput({
    value,
    onChange,
}: {
    value: string;
    onChange: (v: string) => void;
}) {
    return (
        <textarea
            value={value}
            onChange={(e) => onChange(e.target.value)}
            rows={6}
            dir="ltr"
            placeholder="<iframe src='...'></iframe>"
            className={`${inputClass} font-mono text-xs`}
        />
    );
}

/** Lightweight rich-text editor (contentEditable + formatting toolbar). The
 *  output is HTML, sanitized server-side on save. */
export function RichTextField({
    value,
    onChange,
    locale,
}: {
    value: string;
    onChange: (v: string) => void;
    locale: string;
}) {
    const ref = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (ref.current && ref.current.innerHTML !== (value ?? '')) {
            ref.current.innerHTML = value ?? '';
        }
    }, [value]);

    const exec = (command: string, arg?: string) => {
        ref.current?.focus();
        document.execCommand(command, false, arg);
        if (ref.current) onChange(ref.current.innerHTML);
    };

    const tools: Array<{ cmd: string; label: string; arg?: string; title: string }> = [
        { cmd: 'bold', label: 'B', title: 'Bold' },
        { cmd: 'italic', label: 'I', title: 'Italic' },
        { cmd: 'underline', label: 'U', title: 'Underline' },
        { cmd: 'insertUnorderedList', label: '•', title: 'Bullet list' },
        { cmd: 'formatBlock', label: 'H', arg: 'H2', title: 'Heading' },
    ];

    return (
        <div className="overflow-hidden rounded-md border border-neutral-300 bg-white">
            <div className="flex flex-wrap gap-1 border-b border-neutral-200 bg-neutral-50 p-1">
                {tools.map((t) => (
                    <button
                        key={t.cmd + (t.arg ?? '')}
                        type="button"
                        title={t.title}
                        onClick={() => exec(t.cmd, t.arg)}
                        className="flex h-7 min-w-7 items-center justify-center rounded px-1.5 text-xs font-semibold text-neutral-500 transition hover:bg-neutral-200 hover:text-neutral-900"
                    >
                        {t.label}
                    </button>
                ))}
            </div>
            <div
                ref={ref}
                contentEditable
                suppressContentEditableWarning
                dir={locale === 'ar' ? 'rtl' : 'ltr'}
                onInput={(e) => onChange((e.target as HTMLDivElement).innerHTML)}
                className="rte-content min-h-[120px] max-w-none px-3 py-2 text-sm text-neutral-900 focus:outline-none"
            />
        </div>
    );
}

/** Non-interactive chrome: headings, dividers, helper text. */
export function StaticChrome({ field }: { field: FieldDef }) {
    const t = useT();
    const format = field.format ?? 'description';
    if (format === 'line') return <hr className="border-neutral-200" />;
    if (format === 'title') {
        return <h4 className="pt-2 text-xs font-semibold uppercase tracking-wide text-neutral-500">{t(field.label ?? '')}</h4>;
    }
    return <p className="text-[11px] text-neutral-500">{t(field.label ?? '')}</p>;
}

/** Multi-select checkbox group: renders a list of checkboxes and stores
 *  the selected values as a string[]. Used for payment-icons footer widget. */
export function CheckboxMultiSelect({
    value,
    onChange,
    options,
}: {
    value: string[];
    onChange: (v: string[]) => void;
    options: { label: string; value: string }[];
}) {
    const t = useT();
    const selected = Array.isArray(value) ? value : [];

    const toggle = (optValue: string) => {
        if (selected.includes(optValue)) {
            onChange(selected.filter((v) => v !== optValue));
        } else {
            onChange([...selected, optValue]);
        }
    };

    return (
        <div className="space-y-1.5">
            {options.map((o) => (
                <label key={o.value} className="flex items-center gap-2 cursor-pointer text-sm text-neutral-700 hover:text-neutral-900">
                    <input
                        type="checkbox"
                        checked={selected.includes(o.value)}
                        onChange={() => toggle(o.value)}
                        className="h-4 w-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500"
                    />
                    {t(o.label)}
                </label>
            ))}
        </div>
    );
}

/** Coerce arbitrary input into a `{ ar, en }` pair with English fallback. */
export function normalizeBilingual(value: unknown): Bilingual {
    if (value && typeof value === 'object') {
        const v = value as Partial<Bilingual>;
        return { ar: v.ar ?? '', en: v.en ?? '' };
    }
    if (typeof value === 'string') return { ar: value, en: value };
    return { ar: '', en: '' };
}
