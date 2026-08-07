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
    const title = loc(p.title, locale);
    const columns = Math.max(1, Math.min(5, Number(p.columns ?? 3)));
    const items = (p.items && p.items.length) ? p.items : [
        { label: { ar: 'تشكيلة جديدة', en: 'New Arrivals' }, text: { ar: '', en: '' }, icon: 'box' },
        { label: { ar: 'عروض حصرية', en: 'Exclusive Offers' }, text: { ar: '', en: '' }, icon: 'star' },
        { label: { ar: 'نصائح العناية', en: 'Care Tips' }, text: { ar: '', en: '' }, icon: 'badge' },
    ];

    return (
        <div className="merrel-news">
            {title && <div className="merrel-section-head"><h2 className="merrel-section-head__title">{title}</h2></div>}
            <div className="merrel-news__grid" style={{ gridTemplateColumns: `repeat(${columns}, 1fr)` }}>
                {items.map((it, i) => {
                    const label = loc(it.label ?? it.title, locale);
                    const text = loc(it.text ?? it.content, locale);
                    const ctaText = loc(it.ctaText, locale);
                    return (
                        <article className="merrel-news__card" key={i}>
                            <div className="merrel-news__media">
                                {it.image
                                    ? <img src={it.image} alt="" />
                                    : <div className="merrel-news__media--icon" style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', height: '100%', fontSize: 28 }}>◆</div>}
                            </div>
                            {label && <h3 className="merrel-news__title">{label}</h3>}
                            {text && <p className="merrel-news__text">{text}</p>}
                            {ctaText && (
                                <a href={it.url || '#'} onClick={(e) => e.preventDefault()} className="merrel-btn merrel-btn--dark">{ctaText}</a>
                            )}
                        </article>
                    );
                })}
            </div>
        </div>
    );
}
