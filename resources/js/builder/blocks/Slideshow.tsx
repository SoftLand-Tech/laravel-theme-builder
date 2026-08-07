import React, { useEffect, useState } from 'react';
import { loc } from './_shared';

interface Slide {
    image?: string;
    title?: { ar?: string; en?: string };
    subtitle?: { ar?: string; en?: string };
    ctaText?: { ar?: string; en?: string };
    ctaUrl?: string;
    overlayOpacity?: number;
}

interface SlideshowProps {
    locale: string;
    slides: Slide[];
    autoplay?: boolean;
    interval?: number;
    height?: string;
}

const MIN_H: Record<string, string> = { small: 'min-h-[320px]', medium: 'min-h-[440px]', large: 'min-h-[560px]' };

export function Slideshow(props: SlideshowProps) {
    const slides = props.slides ?? [];
    const [i, setI] = useState(0);

    useEffect(() => {
        if (props.autoplay !== false && slides.length > 1) {
            const ms = (props.interval ?? 5) * 1000;
            const id = setInterval(() => setI((p) => (p + 1) % slides.length), ms);
            return () => clearInterval(id);
        }
    }, [props.autoplay, props.interval, slides.length]);

    if (slides.length === 0) return null;
    const minH = MIN_H[props.height ?? 'medium'] ?? 'min-h-[440px]';

    return (
        <section className={`relative overflow-hidden ${minH}`}>
            {slides.map((slide, idx) => {
                const title = loc(slide.title, props.locale);
                const subtitle = loc(slide.subtitle, props.locale);
                const cta = loc(slide.ctaText, props.locale);
                const overlay = (slide.overlayOpacity ?? 30) / 100;
                const active = idx === i;
                return (
                    <div
                        key={idx}
                        className={`absolute inset-0 transition-opacity duration-700 ${active ? 'opacity-100' : 'opacity-0'}`}
                        style={{ display: active ? 'block' : 'none' }}
                    >
                        {slide.image ? (
                            <img src={slide.image} alt={title} className="h-full w-full object-cover" />
                        ) : (
                            <div className="h-full w-full bg-neutral-800" />
                        )}
                        <div className="absolute inset-0 bg-black" style={{ opacity: overlay }} />
                        <div className="relative z-10 flex h-full items-center justify-center px-6 text-center text-white">
                            <div className="max-w-2xl">
                                {title && <h2 className="mb-3 text-2xl font-extrabold sm:text-3xl md:text-5xl">{title}</h2>}
                                {subtitle && <p className="mb-5 text-base text-white/90 sm:text-lg">{subtitle}</p>}
                                {cta && (
                                    <a
                                        href={slide.ctaUrl || '#'}
                                        onClick={(e) => e.preventDefault()}
                                        className="inline-block rounded-full bg-white px-7 py-3 font-semibold text-neutral-900 transition hover:bg-neutral-100"
                                    >
                                        {cta}
                                    </a>
                                )}
                            </div>
                        </div>
                    </div>
                );
            })}

            {slides.length > 1 && (
                <div className="absolute inset-x-0 bottom-4 z-20 flex justify-center gap-2">
                    {slides.map((_, idx) => (
                        <button
                            key={idx}
                            type="button"
                            onClick={() => setI(idx)}
                            className={`h-2 w-8 rounded-full transition ${idx === i ? 'bg-white' : 'bg-white/50'}`}
                            aria-label={`Slide ${idx + 1}`}
                        />
                    ))}
                </div>
            )}
        </section>
    );
}
