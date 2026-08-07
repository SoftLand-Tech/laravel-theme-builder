import React, { useCallback, useEffect, useRef } from 'react';
import { loc } from './_shared';
import { ProductCardPreview, SAMPLE_PRODUCTS } from './ProductCardPreview';
import { useViewport } from '../viewportContext';

interface ProductsCarouselProps {
    locale: string;
    title: { ar?: string; en?: string };
    source: string;
    limit: number;
    autoplay?: boolean;
}

export function ProductsCarousel(props: ProductsCarouselProps) {
    const viewport = useViewport();
    const title = loc(props.title, props.locale);
    const count = Math.min(props.limit || 8, SAMPLE_PRODUCTS.length);
    const trackRef = useRef<HTMLDivElement>(null);
    const cardWidth = viewport === 'mobile' ? 'w-[160px]' : 'w-[220px] sm:w-[260px]';

    const scrollBy = useCallback((dir: 1 | -1) => {
        const el = trackRef.current;
        if (!el) return;
        const isRtl = props.locale === 'ar';
        el.scrollBy({ left: dir * 300 * (isRtl ? -1 : 1), behavior: 'smooth' });
    }, [props.locale]);

    useEffect(() => {
        if (!props.autoplay) return;
        const id = setInterval(() => scrollBy(1), 4000);
        return () => clearInterval(id);
    }, [props.autoplay, scrollBy]);

    const isRtl = props.locale === 'ar';

    return (
        <section>
            {title && (
                <div className="mb-6 flex items-center justify-between gap-3">
                    <h2 className="truncate text-xl font-bold text-neutral-900 sm:text-2xl">{title}</h2>
                    <div className="flex gap-2">
                        <button
                            type="button"
                            onClick={() => scrollBy(-1)}
                            className="flex h-9 w-9 items-center justify-center rounded-full border border-neutral-300 text-neutral-600 hover:bg-neutral-100"
                        >
                            {isRtl ? '→' : '←'}
                        </button>
                        <button
                            type="button"
                            onClick={() => scrollBy(1)}
                            className="flex h-9 w-9 items-center justify-center rounded-full border border-neutral-300 text-neutral-600 hover:bg-neutral-100"
                        >
                            {isRtl ? '←' : '→'}
                        </button>
                    </div>
                </div>
            )}

            <div className="relative">
                <div
                    ref={trackRef}
                    className={`flex snap-x snap-mandatory gap-4 overflow-x-auto pb-4 ${props.autoplay ? 'scroll-smooth' : ''}`}
                    style={{ scrollbarWidth: 'none' }}
                >
                    {SAMPLE_PRODUCTS.slice(0, count).map((p, i) => (
                        <div key={i} className={`${cardWidth} shrink-0 snap-start`}>
                            <ProductCardPreview product={p} locale={props.locale} />
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
