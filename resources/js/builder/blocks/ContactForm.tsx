import React from 'react';
import { loc } from './_shared';
import { useT } from '../i18n';

interface ContactFormProps {
    locale: string;
    title: { ar?: string; en?: string };
    subtitle: { ar?: string; en?: string };
    showPhone: boolean;
}

export function ContactForm(props: ContactFormProps) {
    const t = useT();
    const showPhone = props.showPhone ?? true;

    return (
        <div>
            {loc(props.title, props.locale) && <h2 className="text-2xl font-bold text-neutral-900">{loc(props.title, props.locale)}</h2>}
            {loc(props.subtitle, props.locale) && <p className="mb-6 text-sm text-neutral-500">{loc(props.subtitle, props.locale)}</p>}
            <form
                action="#"
                method="POST"
                onSubmit={(e) => e.preventDefault()}
                className="mx-auto max-w-xl space-y-4"
            >
                <div>
                    <label className="mb-1 block text-sm font-medium text-neutral-700" htmlFor="contact-name">{t('Name')}</label>
                    <input id="contact-name" type="text" name="name" className="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none" />
                </div>
                <div>
                    <label className="mb-1 block text-sm font-medium text-neutral-700" htmlFor="contact-email">{t('Email')}</label>
                    <input id="contact-email" type="email" name="email" className="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none" />
                </div>
                {showPhone && (
                    <div>
                        <label className="mb-1 block text-sm font-medium text-neutral-700" htmlFor="contact-phone">{t('Phone')}</label>
                        <input id="contact-phone" type="tel" name="phone" placeholder="+966 5XXXXXXXX" className="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none" />
                    </div>
                )}
                <div>
                    <label className="mb-1 block text-sm font-medium text-neutral-700" htmlFor="contact-message">{t('Message')}</label>
                    <textarea id="contact-message" name="message" rows={4} className="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none" />
                </div>
                <button type="submit" className="rounded-full bg-primary-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700">
                    {t('Send message')}
                </button>
            </form>
        </div>
    );
}
