import React from 'react';
import { loc } from '../../blocks/_shared';

interface P {
    locale?: string;
    title?: { ar?: string; en?: string };
    content?: { ar?: string; en?: string };
    ctaText?: { ar?: string; en?: string };
    ctaUrl?: string;
    image?: string;
    imagePosition?: string;
}

export function ImageWithText(props: P = {} as P) {
    const p = props ?? ({} as P);
    const locale = p.locale ?? 'ar';
    const title = loc(p.title, locale);
    const content = loc(p.content, locale);
    const cta = loc(p.ctaText, locale);
    const hasImage = Boolean(p.image);

    return (
        <div className="merrel-about">
            <div className={`merrel-about__row${hasImage ? ' merrel-about__row--has-image' : ''}`}>
                {hasImage && (
                    <div className="merrel-about__media" style={{ order: p.imagePosition === 'end' ? 2 : 0 }}>
                        <img src={p.image} alt="" />
                    </div>
                )}
                <div className="merrel-about__copy" style={{ order: p.imagePosition === 'end' ? 1 : 0 }}>
                    {title && <h2 className="merrel-about__title">{title}</h2>}
                    {content && <div className="merrel-about__text" dangerouslySetInnerHTML={{ __html: content }} />}
                    {cta && (
                        <a href={p.ctaUrl || '#'} onClick={(e) => e.preventDefault()} className="merrel-btn merrel-btn--dark">{cta}</a>
                    )}
                </div>
            </div>
        </div>
    );
}
