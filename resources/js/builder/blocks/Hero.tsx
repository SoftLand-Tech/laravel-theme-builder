import React from 'react';
import { loc } from './_shared';

interface HeroProps {
    locale: string;
    title: { ar?: string; en?: string };
    subtitle: { ar?: string; en?: string };
    backgroundImage: string;
    ctaText: { ar?: string; en?: string };
    ctaUrl: string;
    layout: string;
    height?: string;
    overlayOpacity?: number;
}

const MIN_H: Record<string, string> = { small: 'min-h-[320px]', medium: 'min-h-[420px]', large: 'min-h-[560px]' };

export function Hero(props: HeroProps) {
    const title = loc(props.title, props.locale);
    const subtitle = loc(props.subtitle, props.locale);
    const cta = loc(props.ctaText, props.locale);
    const layoutVal = props.layout;
    const align = layoutVal === 'left' || layoutVal === 'start'
        ? 'items-start text-start'
        : layoutVal === 'right' || layoutVal === 'end'
            ? 'items-end text-end'
            : 'items-center text-center';
    const minH = MIN_H[props.height ?? 'medium'] ?? 'min-h-[420px]';
    const bg = props.backgroundImage;
    const overlay = (props.overlayOpacity ?? 30) / 100;

    return (
        <section className={`relative flex ${minH} flex-col justify-center overflow-hidden bg-primary-700 px-6 py-16 text-white`}>
            {bg && (
                <>
                    <img src={bg} alt="" className="absolute inset-0 h-full w-full object-cover" />
                    <div className="absolute inset-0 bg-black" style={{ opacity: overlay }} />
                </>
            )}

            <div className={`relative z-10 mx-auto flex w-full max-w-5xl flex-col gap-4 ${align}`}>
                <h1 className="text-3xl font-extrabold tracking-tight sm:text-4xl md:text-6xl">{title}</h1>
                {subtitle && <p className="max-w-2xl text-base text-white/90 sm:text-lg md:text-xl">{subtitle}</p>}
                {cta && (
                    <a
                        href={props.ctaUrl || '#'}
                        onClick={(e) => e.preventDefault()}
                        className="mt-4 inline-block rounded-full bg-white px-7 py-3 font-semibold text-primary-700 shadow-lg transition hover:bg-neutral-100"
                    >
                        {cta}
                    </a>
                )}
            </div>
        </section>
    );
}
