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
    const title = loc(p.title, locale);
    const subtitle = loc(p.subtitle, locale);
    const cta = loc(p.ctaText, locale);
    const bg = p.backgroundImage;
    const overlay = (p.overlayOpacity ?? 40) / 100;
    const height = p.height ?? 'medium';
    const layout = p.layout ?? 'center';
    const minH = height === 'small' ? '320px' : height === 'large' ? '560px' : '520px';
    const alignItems = layout === 'start' || layout === 'left' ? 'flex-start' : layout === 'end' || layout === 'right' ? 'flex-end' : 'center';
    const textAlign = layout === 'start' || layout === 'left' ? 'start' : layout === 'end' || layout === 'right' ? 'end' : 'center';
    const eyebrow = locale === 'ar' ? 'أحذية أصلية · جودة عالية' : 'Genuine Shoes · High Quality';

    return (
        <div className="merrel-hero" style={{ minHeight: minH, alignItems, textAlign }}>
            {bg && (<>
                <div className="merrel-hero__bg" style={{ backgroundImage: `url(${bg})` }} />
                <div className="merrel-hero__overlay" style={{ opacity: overlay }} />
            </>)}
            <div className="merrel-hero__inner" style={{ marginInline: layout === 'center' ? 'auto' : 0, textAlign }}>
                <p className="merrel-hero__eyebrow">{eyebrow}</p>
                <h1 className="merrel-hero__title">{title}</h1>
                {subtitle && <p className="merrel-hero__subtitle" style={{ marginInline: layout === 'center' ? 'auto' : 0 }}>{subtitle}</p>}
                {cta && <a href={p.ctaUrl || '#'} onClick={(e) => e.preventDefault()} className="merrel-btn">{cta}</a>}
            </div>
        </div>
    );
}
