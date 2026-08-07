import React from 'react';
import { loc } from '../../blocks/_shared';
interface Item { label?: {ar?:string;en?:string}; icon?: string; }
interface P { locale?: string; items?: Item[]; }
const ICONS: Record<string,string> = { leaf:'🍃', truck:'🚚', home:'🏠', shield:'🛡', badge:'✓', star:'★' };
export function TrustBadges(props: P = {} as P) {
    const p = props ?? ({} as P); const locale = p.locale ?? 'ar';
    const items = (p.items && p.items.length) ? p.items : [{label:{ar:'طازج يوميًا',en:'Fresh Daily'},icon:'leaf'},{label:{ar:'توصيل سريع',en:'Fast Delivery'},icon:'truck'},{label:{ar:'من المزرعة',en:'From Farm'},icon:'home'},{label:{ar:'دفع آمن',en:'Secure Payment'},icon:'shield'}];
    return (
        <div className="picked-trust">
            <div className="picked-trust__row">
                {items.map((it,i)=>(<div className="picked-trust__item" key={i}><div className="picked-trust__icon">{ICONS[it.icon??'leaf']??'✓'}</div><div className="picked-trust__label">{loc(it.label,locale)}</div></div>))}
            </div>
        </div>
    );
}
