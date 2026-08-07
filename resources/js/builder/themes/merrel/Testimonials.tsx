import React from 'react';
import { loc } from '../../blocks/_shared';

interface Item { author?: {ar?:string;en?:string}; quote?: {ar?:string;en?:string}; }
interface P { locale?: string; title?: {ar?:string;en?:string}; items?: Item[]; }

export function Testimonials(props: P = {} as P) {
    const p = props ?? ({} as P);
    const locale = p.locale ?? 'ar';
    const title = loc(p.title, locale);
    const items = (p.items && p.items.length) ? p.items : [{ author: { ar: 'محمد، الرياض', en: 'Mohammed, Riyadh' }, quote: { ar: 'أحذية مريحة وجودة ممتازة وتوصيل سريع.', en: 'Comfortable shoes, excellent quality, fast delivery.' } }];
    return (
        <div className="merrel-testimonials">
            {title && <div className="merrel-section-head"><h2 className="merrel-section-head__title">{title}</h2></div>}
            {items.map((it, i) => (
                <div className="merrel-testimonial" key={i}>
                    <div className="merrel-testimonial__author">
                        <div className="merrel-testimonial__avatar"><img src="/themes/merrel/images/client-img.png" alt="" /></div>
                        <div className="merrel-testimonial__name">{loc(it.author, locale)}</div>
                    </div>
                    <p className="merrel-testimonial__quote">{loc(it.quote, locale)}</p>
                </div>
            ))}
        </div>
    );
}
