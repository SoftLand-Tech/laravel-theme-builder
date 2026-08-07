import React from 'react';
import { loc } from '../../blocks/_shared';
interface P { locale?: string; text?: {ar?:string;en?:string}; url?: string; }
export function PromoBar(props: P = {} as P) { const p = props ?? ({} as P); const t = loc(p.text, p.locale ?? 'ar'); return (<div className="picked-promo">{p.url ? <a href={p.url} onClick={(e)=>e.preventDefault()}>{t}</a> : t}</div>); }
