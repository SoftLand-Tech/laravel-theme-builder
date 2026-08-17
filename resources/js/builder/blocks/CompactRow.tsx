import { loc } from './_shared';
import { SAMPLE_PRODUCTS } from './ProductCardPreview';

interface CompactRowProps {
    locale: string;
    title?: { ar?: string; en?: string };
    ctaText?: { ar?: string; en?: string };
    source?: string;
    limit?: number;
}

export function CompactRow(props: CompactRowProps) {
    const title = loc(props.title, props.locale);
    const cta = loc(props.ctaText, props.locale);
    const count = Math.min(props.limit || 4, SAMPLE_PRODUCTS.length);

    return (
        <div>
            {title && <h2 className="mb-4 text-xl font-bold text-neutral-900">{title}</h2>}
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                {SAMPLE_PRODUCTS.slice(0, count).map((p, i) => (
                    <div key={i} className="flex gap-3 rounded-xl border border-neutral-200 bg-neutral-50 p-3">
                        <div className="h-16 w-16 shrink-0 rounded-lg bg-neutral-200" />
                        <div className="flex flex-col">
                            <span className="font-semibold text-neutral-900">{loc(p.name, props.locale)}</span>
                            <span className="mt-1 text-sm font-bold text-indigo-600">{p.price}</span>
                            {cta && <span className="mt-auto pt-1 text-xs font-semibold text-neutral-500">{cta} →</span>}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
