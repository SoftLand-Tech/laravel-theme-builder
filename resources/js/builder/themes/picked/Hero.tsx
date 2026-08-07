import React from 'react';
import { loc } from '../../blocks/_shared';

interface P {
    locale?: string;
    title?: { ar?: string; en?: string };
    subtitle?: { ar?: string; en?: string };
    ctaText?: { ar?: string; en?: string };
    ctaUrl?: string;
    backgroundImage?: string;
    overlayOpacity?: number;
    height?: string;
    layout?: string;
}

export function Hero(props: P = {} as P) {
    const p = props ?? ({} as P);
    const locale = p.locale ?? 'ar';
    const height = p.height ?? 'medium';
    const layout = p.layout ?? 'center';
    const minH = height === 'small' ? '320px' : height === 'large' ? '560px' : '480px';
    const alignItems = layout === 'start' || layout === 'left' ? 'flex-start' : layout === 'end' || layout === 'right' ? 'flex-end' : 'center';
    const textAlign = layout === 'start' || layout === 'left' ? 'start' : layout === 'end' || layout === 'right' ? 'end' : 'center';
    const eyebrow = locale === 'ar' ? 'طازج · من المزرعة إلى باب منزلك' : 'Fresh · Farm to your door';

    return (
        <div className="picked-hero" style={{ minHeight: minH, alignItems, textAlign }}>
            {p.backgroundImage && (
                <div className="picked-hero__bg" style={{ backgroundImage: `url(${p.backgroundImage})`, opacity: (p.overlayOpacity ?? 20) / 100 }} />
            )}
            <div className="picked-hero__inner" style={{ marginInline: layout === 'center' ? 'auto' : 0, textAlign }}>
                <p className="picked-hero__eyebrow">{eyebrow}</p>
                <h1 className="picked-hero__title">{loc(p.title, locale)}</h1>
                {p.subtitle && <p className="picked-hero__subtitle" style={{ marginInline: layout === 'center' ? 'auto' : 0 }}>{loc(p.subtitle, locale)}</p>}
                {p.ctaText && (
                    <a href={p.ctaUrl || '#'} onClick={(e) => e.preventDefault()} className="picked-btn">{loc(p.ctaText, locale)} →</a>
                )}
            </div>
        </div>
    );
}
