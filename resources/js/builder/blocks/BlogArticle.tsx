import React from 'react';
import { loc } from './_shared';

const SAMPLE = {
    ar: { title: 'كيف تختار هديتك المثالية', category: 'أدلة الشراء', excerpt: 'دليلك الكامل لاختيار هدية لا تُنسى لكل مناسبة.' },
    en: { title: 'How to pick the perfect gift', category: 'Buying guides', excerpt: 'Your complete guide to choosing an unforgettable gift for any occasion.' },
};

interface BlogArticleProps {
    locale: string;
    title: { ar?: string; en?: string };
    showExcerpt?: boolean;
    ctaText: { ar?: string; en?: string };
}

export function BlogArticle(props: BlogArticleProps) {
    const title = loc(props.title, props.locale);
    const cta = loc(props.ctaText, props.locale) || 'Read article';
    const s = props.locale === 'ar' ? SAMPLE.ar : SAMPLE.en;

    return (
        <div className="grid items-center gap-8 lg:grid-cols-2">
            <div className="aspect-[4/3] rounded-lg bg-neutral-200" />
            <div className="space-y-3">
                {title && <h2 className="text-xl font-bold text-neutral-900 sm:text-2xl">{title}</h2>}
                <span className="text-xs font-medium text-primary-600">{s.category}</span>
                <h3 className="text-lg font-semibold text-neutral-900">{s.title}</h3>
                {props.showExcerpt && <p className="text-sm text-neutral-600">{s.excerpt}</p>}
                <span className="inline-block rounded-full bg-neutral-900 px-5 py-2 text-xs font-medium text-white">{cta}</span>
            </div>
        </div>
    );
}
