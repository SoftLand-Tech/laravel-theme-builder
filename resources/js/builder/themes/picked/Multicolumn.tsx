import React from 'react';
import { loc } from '../../blocks/_shared';

interface Bi { ar?: string; en?: string }
interface Item {
    label?: Bi;
    text?: Bi;
    title?: Bi;
    content?: Bi;
    image?: string;
    icon?: string;
    ctaText?: Bi;
    url?: string;
}
interface P {
    locale?: string;
    title?: Bi;
    columns?: number;
    items?: Item[];
}

export function Multicolumn(props: P = {} as P) {
    const p = props ?? ({} as P);
    const locale = p.locale ?? 'ar';
    const columns = Math.max(1, Math.min(5, Number(p.columns ?? 4)));
    const items = (p.items && p.items.length) ? p.items : [
        { label: { ar: 'طازج يوميًا', en: 'Fresh Daily' }, text: { ar: 'من المزرعة يوميًا', en: 'From farm daily' }, icon: 'star' },
        { label: { ar: 'توصيل سريع', en: 'Fast Delivery' }, text: { ar: '٢٤-٤٨ ساعة', en: '24-48 hours' }, icon: 'truck' },
        { label: { ar: 'أسعار منافسة', en: 'Best Prices' }, text: { ar: 'أفضل الأسعار', en: 'Best market prices' }, icon: 'badge' },
        { label: { ar: 'دفع آمن', en: 'Secure Payment' }, text: { ar: 'مدى وأبل باي', en: 'mada, Apple Pay' }, icon: 'shield' },
    ];

    return (
        <div className="picked-features">
            {p.title && <div className="picked-section-head"><h2 className="picked-section-head__title">{loc(p.title, locale)}</h2></div>}
            <div className="picked-features__grid" style={{ gridTemplateColumns: `repeat(${columns}, 1fr)` }}>
                {items.map((it, i) => {
                    const label = loc(it.label ?? it.title, locale);
                    const text = loc(it.text ?? it.content, locale);
                    const ctaText = loc(it.ctaText, locale);
                    return (
                        <div className="picked-feature" key={i}>
                            <div className="picked-feature__media">
                                {it.image
                                    ? <img src={it.image} alt="" />
                                    : <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', height: '100%', fontSize: 28 }}>◆</div>}
                            </div>
                            {label && <h3 className="picked-feature__title">{label}</h3>}
                            {text && <p className="picked-feature__text">{text}</p>}
                            {ctaText && (
                                <a href={it.url || '#'} onClick={(e) => e.preventDefault()} className="picked-btn">{ctaText}</a>
                            )}
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
