import React from 'react';
import { loc } from '../../blocks/_shared';

interface Item { label?: {ar?:string;en?:string}; icon?: string; }
interface P { locale?: string; items?: Item[]; }

const ICONS: Record<string,string> = { truck:'🚚', shield:'🛡', refresh:'↻', badge:'✓', box:'📦', star:'★' };

export function TrustBadges(props: P = {} as P) {
    const p = props ?? ({} as P);
    const locale = p.locale ?? 'ar';
    const items = (p.items && p.items.length) ? p.items : [
        { label: { ar: 'توصيل سريع', en: 'Fast Shipping' }, icon: 'truck' },
        { label: { ar: 'دفع آمن', en: 'Secure Payment' }, icon: 'shield' },
        { label: { ar: 'إرجاع سهل', en: 'Easy Returns' }, icon: 'refresh' },
        { label: { ar: 'ضمان الجودة', en: 'Quality Guarantee' }, icon: 'badge' },
    ];
    return (
        <div className="merrel-trust">
            <div className="merrel-trust__row">
                {items.map((it, i) => (
                    <div className="merrel-trust__item" key={i}>
                        <div className="merrel-trust__icon">{ICONS[it.icon ?? 'box'] ?? '✓'}</div>
                        <div className="merrel-trust__label">{loc(it.label, locale)}</div>
                    </div>
                ))}
            </div>
        </div>
    );
}
