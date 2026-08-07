import React from 'react';
import { loc } from '../../blocks/_shared';
interface Item { author?: {ar?:string;en?:string}; quote?: {ar?:string;en?:string}; }
interface P { locale?: string; title?: {ar?:string;en?:string}; items?: Item[]; }
export function Testimonials(props: P = {} as P) {
    const p = props ?? ({} as P); const locale = p.locale ?? 'ar';
    const items = (p.items && p.items.length) ? p.items : [{author:{ar:'نورة، الرياض',en:'Noura, Riyadh'},quote:{ar:'أفضل خضروات طازجة وتوصيل سريع.',en:'The freshest vegetables and fast delivery.'}}];
    return (
        <div className="picked-testimonials">
            {p.title && <div className="picked-section-head"><h2 className="picked-section-head__title">{loc(p.title,locale)}</h2></div>}
            {items.map((it,i)=>(
                <div className="picked-testimonial" key={i}>
                    <div className="picked-testimonial__author">
                        <div className="picked-testimonial__avatar"><img src="/themes/picked/images/client-img.png" alt=""/></div>
                        <div className="picked-testimonial__name">{loc(it.author,locale)}</div>
                    </div>
                    <p className="picked-testimonial__quote">{loc(it.quote,locale)}</p>
                </div>
            ))}
        </div>
    );
}
