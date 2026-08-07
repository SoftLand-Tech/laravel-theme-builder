import React from 'react';
import { loc } from './_shared';

interface BannersSingleProps {
    locale: string;
    image: string;
    title: { ar?: string; en?: string };
    subtitle: { ar?: string; en?: string };
    ctaText: { ar?: string; en?: string };
    ctaUrl: string;
    align: string;
    height?: string;
    overlayOpacity?: number;
}

const MIN_H: Record<string, string> = { small: 'min-h-[220px]', medium: 'min-h-[280px]', large: 'min-h-[380px]' };

export function BannersSingle(props: BannersSingleProps) {
    const align = props.align === 'start'
        ? 'items-start text-start'
        : props.align === 'end'
            ? 'items-end text-end'
            : 'items-center text-center';
    const minH = MIN_H[props.height ?? 'medium'] ?? 'min-h-[280px]';
    const overlay = (props.overlayOpacity ?? 40) / 100;

    return (
        <div className={`relative flex ${minH} flex-col justify-center overflow-hidden rounded-lg bg-neutral-800 px-8 py-12 text-white ${align}`}>
            {props.image && (
                <>
                    <img src={props.image} alt="" className="absolute inset-0 h-full w-full object-cover" />
                    <div className="absolute inset-0 bg-black" style={{ opacity: overlay }} />
                </>
            )}
            <div className="relative z-10 max-w-xl">
                {loc(props.title, props.locale) && <h3 className="mb-2 text-3xl font-bold">{loc(props.title, props.locale)}</h3>}
                {loc(props.subtitle, props.locale) && <p className="text-lg text-white/85">{loc(props.subtitle, props.locale)}</p>}
                {loc(props.ctaText, props.locale) && (
                    <a
                        href={props.ctaUrl || '#'}
                        onClick={(e) => e.preventDefault()}
                        className="mt-4 inline-block rounded-full bg-white px-6 py-2.5 text-sm font-semibold text-neutral-900"
                    >
                        {loc(props.ctaText, props.locale)}
                    </a>
                )}
            </div>
        </div>
    );
}
