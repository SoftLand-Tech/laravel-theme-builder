import React from 'react';
import { loc } from './_shared';

interface RichTextProps {
    locale: string;
    title: { ar?: string; en?: string };
    content: { ar?: string; en?: string };
}

export function RichText(props: RichTextProps) {
    const title = loc(props.title, props.locale);
    const html = loc(props.content, props.locale);

    return (
        <div className="mx-auto max-w-3xl text-center">
            {title && <h2 className="mb-4 text-2xl font-bold text-neutral-900">{title}</h2>}
            {html ? (
                <div
                    className="rte-content prose prose-neutral max-w-none text-neutral-600"
                    dangerouslySetInnerHTML={{ __html: html }}
                />
            ) : (
                <p className="text-neutral-500">
                    {props.locale === 'ar' ? 'اكتب المحتوى هنا...' : 'Write your content here...'}
                </p>
            )}
        </div>
    );
}
