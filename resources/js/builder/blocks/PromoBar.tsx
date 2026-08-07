import React from 'react';
import { loc } from './_shared';

interface PromoBarProps {
    locale: string;
    text: { ar?: string; en?: string };
    url: string;
}

export function PromoBar(props: PromoBarProps) {
    const text = loc(props.text, props.locale);
    const content = (
        <div className="bg-primary-700 px-6 py-2.5 text-center text-sm font-medium text-white">
            {text || 'Promo bar'}
        </div>
    );

    if (props.url) {
        return <a href={props.url} className="block">{content}</a>;
    }

    return content;
}
