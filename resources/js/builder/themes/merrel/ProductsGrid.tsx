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
    const title = loc(p.title, locale);
    const sub = locale === 'ar' ? 'أحدث المنتجات بجودة عالية وأسعار منافسة' : 'Latest products with high quality and competitive prices';
    const cols = [2, 3, 5, 6].includes(Number(p.columns ?? 4)) ? Number(p.columns ?? 4) : 4;
    const mobileCols = Math.max(1, Math.min(3, Number(p.mobileColumns ?? 2)));
    const previews = Array.from({ length: 4 }, (_, i) => ({
        name: locale === 'ar' ? `حذاء ${i + 1}` : `Shoe ${i + 1}`,
        price: `${99 + i * 50} ر.س`,
    }));
    const buy = locale === 'ar' ? 'تسوق الآن' : 'Buy Now';

    return (
        <div className="merrel-products">
            {title && (
                <div className="merrel-section-head">
                    <h2 className="merrel-section-head__title">{title}</h2>
                    <p className="merrel-section-head__sub">{sub}</p>
                </div>
            )}
            <div
                className="merrel-products__grid"
                style={{
                    ['--pg-mobile-cols' as string]: mobileCols,
                    gridTemplateColumns: `repeat(${cols}, 1fr)`,
                }}
            >
                {previews.map((c, i) => (
                    <div className="merrel-card" key={i}>
                        <a href="#" onClick={(e) => e.preventDefault()} className="merrel-card__media" />
                        <p className="merrel-card__price">{c.price}</p>
                        <h3 className="merrel-card__name"><a href="#" onClick={(e) => e.preventDefault()}>{c.name}</a></h3>
                        <a href="#" onClick={(e) => e.preventDefault()} className="merrel-btn merrel-btn--white">{buy}</a>
                    </div>
                ))}
            </div>
        </div>
    );
}
