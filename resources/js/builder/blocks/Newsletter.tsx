import { loc } from './_shared';

interface NewsletterProps {
    locale: string;
    title?: { ar?: string; en?: string };
    subtitle?: { ar?: string; en?: string };
    placeholder?: { ar?: string; en?: string };
    ctaText?: { ar?: string; en?: string };
    layout?: string;
}

export function Newsletter(props: NewsletterProps) {
    const title = loc(props.title, props.locale);
    const subtitle = loc(props.subtitle, props.locale);
    const placeholder = loc(props.placeholder, props.locale);
    const cta = loc(props.ctaText, props.locale);
    const align =
        props.layout === 'start' || props.layout === 'left'
            ? 'items-start text-left'
            : props.layout === 'end' || props.layout === 'right'
                ? 'items-end text-right'
                : 'items-center text-center';

    return (
        <div className={`flex w-full flex-col gap-4 ${align}`}>
            {title && <h2 className="text-2xl font-bold text-ink">{title}</h2>}
            {subtitle && <p className="max-w-xl text-base text-neutral-600">{subtitle}</p>}
            <div className={`flex w-full max-w-md flex-col gap-2 sm:flex-row ${align === 'items-center text-center' ? 'mx-auto' : ''}`}>
                <input
                    type="email"
                    placeholder={placeholder}
                    className="w-full rounded-lg border border-neutral-300 bg-white px-4 py-2.5 text-neutral-900"
                />
                <button
                    type="button"
                    className="shrink-0 rounded-lg bg-primary-600 px-5 py-2.5 font-semibold text-white"
                >
                    {cta}
                </button>
            </div>
        </div>
    );
}
