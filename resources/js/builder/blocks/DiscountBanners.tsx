import { loc } from './_shared';

interface DiscountBannersProps {
    locale: string;
    leftMeta?: { ar?: string; en?: string };
    leftTitle?: { ar?: string; en?: string };
    leftPct?: { ar?: string; en?: string };
    leftCta?: { ar?: string; en?: string };
    leftImage?: string;
    leftUrl?: string;
    rightMeta?: { ar?: string; en?: string };
    rightTitle?: { ar?: string; en?: string };
    rightPct?: { ar?: string; en?: string };
    rightCta?: { ar?: string; en?: string };
    rightImage?: string;
    rightUrl?: string;
}

export function DiscountBanners(props: DiscountBannersProps) {
    const panels = [
        {
            meta: loc(props.leftMeta, props.locale),
            title: loc(props.leftTitle, props.locale),
            pct: loc(props.leftPct, props.locale),
            cta: loc(props.leftCta, props.locale),
            image: props.leftImage,
        },
        {
            meta: loc(props.rightMeta, props.locale),
            title: loc(props.rightTitle, props.locale),
            pct: loc(props.rightPct, props.locale),
            cta: loc(props.rightCta, props.locale),
            image: props.rightImage,
        },
    ];

    return (
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            {panels.map((p, i) => (
                <div key={i} className="relative flex min-h-[200px] flex-col justify-between overflow-hidden rounded-2xl bg-neutral-900 p-6 text-white">
                    <div className="relative z-10">
                        {p.meta && <span className="text-[11px] font-semibold uppercase tracking-wide text-indigo-300">{p.meta}</span>}
                        {p.title && (
                            <h3 className="mt-2 text-2xl font-bold leading-tight">
                                {p.title}
                                {p.pct && <><br /><span className="text-3xl text-amber-400">{p.pct}</span></>}
                            </h3>
                        )}
                        {p.cta && <span className="mt-3 inline-block text-sm font-semibold underline-offset-4">{p.cta} →</span>}
                    </div>
                    {p.image && (
                        <img
                            src={p.image}
                            alt=""
                            className="pointer-events-none absolute bottom-3 right-3 z-0 h-24 w-24 rounded-xl object-cover opacity-90"
                        />
                    )}
                </div>
            ))}
        </div>
    );
}
