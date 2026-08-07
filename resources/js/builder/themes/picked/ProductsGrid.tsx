import React from 'react';
import { loc } from '../../blocks/_shared';

interface P {
    locale?: string;
    title?: { ar?: string; en?: string };
    columns?: number;
    mobileColumns?: number;
}

export function ProductsGrid(props: P = {} as P) {
    const p = props ?? ({} as P);
    const locale = p.locale ?? 'ar';
    const cols = [2, 3, 4, 5, 6].includes(Number(p.columns ?? 5)) ? Number(p.columns ?? 5) : 5;
    const mobileCols = Math.max(1, Math.min(3, Number(p.mobileColumns ?? 2)));
    const items = [
        { n: locale === 'ar' ? 'طماطم' : 'Tomato', p: '10 ر.س' },
        { n: locale === 'ar' ? 'خيار' : 'Cucumber', p: '8 ر.س' },
        { n: locale === 'ar' ? 'جزر' : 'Carrot', p: '12 ر.س' },
        { n: locale === 'ar' ? 'فلفل' : 'Pepper', p: '15 ر.س' },
        { n: locale === 'ar' ? 'بصل' : 'Onion', p: '6 ر.س' },
    ];
    const add = locale === 'ar' ? 'أضف للسلة' : 'Add to Cart';

    return (
        <div className="picked-products">
            {p.title && (
                <div className="picked-section-head">
                    <h2 className="picked-section-head__title">{loc(p.title, locale)}</h2>
                    <p className="picked-section-head__sub">{locale === 'ar' ? 'منتجات طازجة يوميًا' : 'Fresh daily produce'}</p>
                </div>
            )}
            <div
                className="picked-products__grid"
                style={{
                    ['--pg-mobile-cols' as string]: mobileCols,
                    gridTemplateColumns: `repeat(${cols}, 1fr)`,
                }}
            >
                {items.map((c, i) => (
                    <div className="picked-card" key={i}>
                        <a href="#" onClick={(e) => e.preventDefault()} className="picked-card__media" />
                        <p className="picked-card__price">{c.p}</p>
                        <h3 className="picked-card__name"><a href="#" onClick={(e) => e.preventDefault()}>{c.n}</a></h3>
                        <a href="#" onClick={(e) => e.preventDefault()} className="picked-btn">{add}</a>
                    </div>
                ))}
            </div>
        </div>
    );
}
