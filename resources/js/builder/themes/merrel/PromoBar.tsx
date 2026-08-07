import React from 'react';
import { loc } from '../../blocks/_shared';

interface P { locale?: string; text?: {ar?:string;en?:string}; url?: string; }

export function PromoBar(props: P = {} as P) {
    const p = props ?? ({} as P);
    const text = loc(p.text, p.locale ?? 'ar');
    return (
        <div className="merrel-promo">
            {p.url ? <a href={p.url} onClick={(e) => e.preventDefault()}>{text}</a> : text}
        </div>
    );
}
