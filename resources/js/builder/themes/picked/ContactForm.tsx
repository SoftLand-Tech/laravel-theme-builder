import React from 'react';
import { loc } from '../../blocks/_shared';
interface P { locale?: string; title?: {ar?:string;en?:string}; subtitle?: {ar?:string;en?:string}; showPhone?: boolean; }
export function ContactForm(props: P = {} as P) {
    const p = props ?? ({} as P); const locale = p.locale ?? 'ar';
    return (
        <div className="picked-contact">
            {p.title && <div className="picked-section-head"><h2 className="picked-section-head__title">{loc(p.title,locale)}</h2><p className="picked-section-head__sub">{loc(p.subtitle,locale)}</p></div>}
            <form className="picked-contact__form" onSubmit={(e)=>e.preventDefault()}>
                <input className="picked-input" type="text" placeholder={locale==='ar'?'الاسم':'Name'} />
                <input className="picked-input" type="email" placeholder={locale==='ar'?'البريد':'Email'} />
                {p.showPhone !== false && <input className="picked-input" type="tel" placeholder={locale==='ar'?'الجوال':'Phone'} />}
                <textarea className="picked-input" placeholder={locale==='ar'?'رسالتك':'Message'} />
                <button type="submit" className="picked-btn">{locale==='ar'?'إرسال':'Send'}</button>
            </form>
        </div>
    );
}
