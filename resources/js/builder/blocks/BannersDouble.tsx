import React from 'react';
import { loc } from './_shared';

interface BannersDoubleProps {
    locale: string;
    leftImage: string;
    leftTitle: { ar?: string; en?: string };
    leftUrl: string;
    rightImage: string;
    rightTitle: { ar?: string; en?: string };
    rightUrl: string;
}

export function BannersDouble(props: BannersDoubleProps) {
    return (
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <Banner image={props.leftImage} title={loc(props.leftTitle, props.locale)} url={props.leftUrl} />
            <Banner image={props.rightImage} title={loc(props.rightTitle, props.locale)} url={props.rightUrl} />
        </div>
    );
}

function Banner({ image, title, url }: { image: string; title: string; url: string }) {
    return (
        <a
            href={url || '#'}
            onClick={(e) => e.preventDefault()}
            className="relative flex min-h-[220px] items-end overflow-hidden rounded-lg bg-neutral-800 p-6"
        >
            {image && (
                <>
                    <img src={image} alt="" className="absolute inset-0 h-full w-full object-cover" />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
                </>
            )}
            {title && <h3 className="relative z-10 text-xl font-bold text-white">{title}</h3>}
        </a>
    );
}
