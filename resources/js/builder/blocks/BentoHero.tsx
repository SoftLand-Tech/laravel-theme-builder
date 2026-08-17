import { loc } from './_shared';
import { SAMPLE_PRODUCTS } from './ProductCardPreview';

interface BentoHeroProps {
    locale: string;
    eyebrow?: { ar?: string; en?: string };
    ctaText?: { ar?: string; en?: string };
    source?: string;
    limit?: number;
}

const CARD_COLORS = [
    'bg-indigo-600',
    'bg-fuchsia-600',
    'bg-teal-600',
    'bg-orange-500',
    'bg-green-600',
    'bg-neutral-900',
];

export function BentoHero(props: BentoHeroProps) {
    const eyebrow = loc(props.eyebrow, props.locale);
    const cta = loc(props.ctaText, props.locale);
    const count = Math.min(props.limit || 6, SAMPLE_PRODUCTS.length);
    const items = SAMPLE_PRODUCTS.slice(0, count);

    return (
        <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
            {items.map((p, i) => {
                const isLead = i === 0;
                return (
                    <div
                        key={i}
                        className={`relative flex flex-col justify-between overflow-hidden rounded-2xl p-4 text-white ${CARD_COLORS[i % CARD_COLORS.length]} ${isLead ? 'col-span-2 row-span-2 min-h-[220px]' : 'min-h-[120px]'}`}
                    >
                        <div className="relative z-10">
                            {isLead && eyebrow && (
                                <span className="text-[11px] font-semibold uppercase tracking-wide opacity-80">⚡ {eyebrow}</span>
                            )}
                            <h3 className={`font-bold ${isLead ? 'text-2xl' : 'text-sm'}`}>{loc(p.name, props.locale)}</h3>
                            {isLead && <p className="mt-1 text-sm opacity-90">{p.price}</p>}
                            {cta && (
                                <span className="mt-2 inline-block text-xs font-semibold underline-offset-2">{cta} →</span>
                            )}
                        </div>
                    </div>
                );
            })}
        </div>
    );
}
