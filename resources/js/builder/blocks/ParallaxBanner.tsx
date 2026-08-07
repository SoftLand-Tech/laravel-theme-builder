import React from 'react';
import { loc } from './_shared';

interface ParallaxBannerProps {
    locale: string;
    image: string;
    title: { ar?: string; en?: string };
    subtitle: { ar?: string; en?: string };
    ctaText: { ar?: string; en?: string };
    ctaUrl: string;
    overlayOpacity?: number;
}

export function ParallaxBanner(props: ParallaxBannerProps) {
    const overlay = (props.overlayOpacity ?? 40) / 100;
    const bg = props.image;

    return (
        <section
            className="relative flex min-h-[400px] flex-col items-center justify-center overflow-hidden px-6 py-20 text-center text-white"
            style={
                bg
                    ? { backgroundImage: `url(${bg})`, backgroundSize: 'cover', backgroundPosition: 'center' }
                    : { backgroundColor: '#1a1a1f' }
            }
        >
            {bg && <div className="absolute inset-0 bg-black" style={{ opacity: overlay }} />}
            <div className="relative z-10 max-w-2xl">
                {loc(props.title, props.locale) && <h2 className="mb-3 text-2xl font-extrabold sm:text-3xl md:text-5xl">{loc(props.title, props.locale)}</h2>}
                {loc(props.subtitle, props.locale) && <p className="mb-6 text-base text-white/85 sm:text-lg">{loc(props.subtitle, props.locale)}</p>}
                {loc(props.ctaText, props.locale) && (
                    <a
                        href={props.ctaUrl || '#'}
                        onClick={(e) => e.preventDefault()}
                        className="inline-block rounded-full bg-white px-7 py-3 font-semibold text-neutral-900 transition hover:bg-neutral-100"
                    >
                        {loc(props.ctaText, props.locale)}
                    </a>
                )}
            </div>
        </section>
    );
}
