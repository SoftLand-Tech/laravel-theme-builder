import React from 'react';
import { loc } from './_shared';

interface ImageWithTextProps {
    locale: string;
    image: string;
    imagePosition: string;
    title: { ar?: string; en?: string };
    content: { ar?: string; en?: string };
    ctaText: { ar?: string; en?: string };
    ctaUrl: string;
}

export function ImageWithText(props: ImageWithTextProps) {
    const reverse = props.imagePosition === 'end';

    return (
        <div className="grid grid-cols-1 items-center gap-8 md:grid-cols-2">
            <div className={`overflow-hidden rounded-lg bg-neutral-100 ${reverse ? 'md:order-2' : ''}`}>
                {props.image ? (
                    <img src={props.image} alt={loc(props.title, props.locale)} className="aspect-[4/3] w-full object-cover" />
                ) : (
                    <div className="flex aspect-[4/3] w-full items-center justify-center text-sm text-neutral-400">
                        {props.locale === 'ar' ? 'صورة' : 'Image'}
                    </div>
                )}
            </div>
            <div className={reverse ? 'md:order-1' : ''}>
                {loc(props.title, props.locale) && <h2 className="mb-3 text-2xl font-bold text-neutral-900 sm:text-3xl">{loc(props.title, props.locale)}</h2>}
                <div
                    className="rte-content prose prose-neutral max-w-none text-neutral-600"
                    dangerouslySetInnerHTML={{ __html: loc(props.content, props.locale) }}
                />
                {loc(props.ctaText, props.locale) && (
                    <a
                        href={props.ctaUrl || '#'}
                        onClick={(e) => e.preventDefault()}
                        className="mt-5 inline-block rounded-full bg-primary-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700"
                    >
                        {loc(props.ctaText, props.locale)}
                    </a>
                )}
            </div>
        </div>
    );
}
