import React from 'react';
import { loc } from '../../blocks/_shared';

interface P { locale?: string; title?: {ar?:string;en?:string}; subtitle?: {ar?:string;en?:string}; showPhone?: boolean; }

export function ContactForm(props: P = {} as P) {
    const p = props ?? ({} as P);
    const locale = p.locale ?? 'ar';
    const title = loc(p.title, locale);
    const subtitle = loc(p.subtitle, locale);
    const send = locale === 'ar' ? 'إرسال' : 'SEND';
    return (
        <div className="merrel-contact">
            {title && <div className="merrel-section-head"><h2 className="merrel-section-head__title">{title}</h2><p className="merrel-section-head__sub">{subtitle}</p></div>}
            <form className="merrel-contact__form" onSubmit={(e) => e.preventDefault()}>
                <input className="merrel-input" type="text" placeholder={locale === 'ar' ? 'الاسم' : 'Name'} />
                <input className="merrel-input" type="email" placeholder={locale === 'ar' ? 'البريد الإلكتروني' : 'Email'} />
                {p.showPhone !== false && <input className="merrel-input" type="tel" placeholder={locale === 'ar' ? 'الجوال' : 'Phone'} />}
                <textarea className="merrel-input" placeholder={locale === 'ar' ? 'رسالتك' : 'Your message'} />
                <button type="submit" className="merrel-btn">{send}</button>
            </form>
        </div>
    );
}
