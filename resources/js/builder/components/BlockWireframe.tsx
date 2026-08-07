import React from 'react';

/** Mini layout wireframes for the section palette (Shopify-style shape previews). */
export function BlockWireframe({ type, className = '' }: { type: string; className?: string }) {
    return (
        <svg
            viewBox="0 0 80 48"
            className={`block w-full text-neutral-400 ${className}`}
            aria-hidden
            fill="none"
        >
            {wireframeFor(type)}
        </svg>
    );
}

function fill(opacity = 0.2): string {
    return `currentColor`;
}

function rect(
    x: number,
    y: number,
    w: number,
    h: number,
    opts: { rx?: number; opacity?: number; stroke?: boolean } = {},
) {
    const { rx = 2, opacity = 0.22, stroke = false } = opts;
    return (
        <rect
            key={`${x}-${y}-${w}-${h}`}
            x={x}
            y={y}
            width={w}
            height={h}
            rx={rx}
            fill={fill()}
            fillOpacity={opacity}
            stroke={stroke ? 'currentColor' : undefined}
            strokeOpacity={stroke ? 0.35 : undefined}
            strokeWidth={stroke ? 0.75 : undefined}
        />
    );
}

function line(x1: number, y1: number, x2: number, y2: number) {
    return (
        <line
            x1={x1}
            y1={y1}
            x2={x2}
            y2={y2}
            stroke="currentColor"
            strokeOpacity={0.3}
            strokeWidth={1}
            strokeLinecap="round"
        />
    );
}

function textLines(y: number, count: number, inset = 8) {
    const lines: React.ReactNode[] = [];
    for (let i = 0; i < count; i++) {
        const w = i === count - 1 && count > 1 ? 28 : 48 - i * 4;
        lines.push(rect(inset, y + i * 5, w, 2.5, { rx: 1, opacity: 0.28 }));
    }
    return lines;
}

function productCards(y: number, count: number, gap: number, cardW: number) {
    const items: React.ReactNode[] = [];
    let x = 6;
    for (let i = 0; i < count; i++) {
        items.push(rect(x, y, cardW, 14, { opacity: 0.18 }));
        items.push(rect(x + 1, y + 15, cardW - 2, 2, { rx: 1, opacity: 0.32 }));
        items.push(rect(x + 1, y + 19, (cardW - 2) * 0.5, 2, { rx: 1, opacity: 0.22 }));
        x += cardW + gap;
    }
    return items;
}

function wireframeFor(type: string): React.ReactNode {
    switch (type) {
        case 'Hero':
            return (
                <>
                    {rect(4, 4, 72, 40, { opacity: 0.12, stroke: true })}
                    {rect(24, 14, 32, 3, { rx: 1, opacity: 0.35 })}
                    {rect(22, 20, 36, 2, { rx: 1, opacity: 0.22 })}
                    {rect(30, 28, 20, 6, { rx: 3, opacity: 0.3 })}
                </>
            );
        case 'Slideshow':
            return (
                <>
                    {rect(4, 6, 72, 32, { opacity: 0.14, stroke: true })}
                    {line(12, 22, 68, 22)}
                    <circle cx="34" cy="42" r="1.5" fill="currentColor" fillOpacity={0.4} />
                    <circle cx="40" cy="42" r="1.5" fill="currentColor" fillOpacity={0.55} />
                    <circle cx="46" cy="42" r="1.5" fill="currentColor" fillOpacity={0.4} />
                </>
            );
        case 'ProductsGrid':
            return (
                <>
                    {rect(6, 4, 24, 2.5, { rx: 1, opacity: 0.3 })}
                    {productCards(12, 4, 3, 15)}
                </>
            );
        case 'ProductsTabs':
            return (
                <>
                    {rect(6, 5, 14, 5, { rx: 1, opacity: 0.28 })}
                    {rect(22, 5, 14, 5, { rx: 1, opacity: 0.14 })}
                    {rect(38, 5, 14, 5, { rx: 1, opacity: 0.14 })}
                    {productCards(14, 3, 4, 18)}
                </>
            );
        case 'ProductsCarousel':
            return (
                <>
                    {rect(6, 4, 28, 2.5, { rx: 1, opacity: 0.3 })}
                    {rect(4, 12, 20, 28, { opacity: 0.16 })}
                    {rect(26, 14, 20, 24, { opacity: 0.22 })}
                    {rect(48, 16, 20, 20, { opacity: 0.14 })}
                    {line(72, 24, 76, 24)}
                    {line(74, 22, 74, 26)}
                </>
            );
        case 'ProductsFeatured':
            return (
                <>
                    {rect(6, 8, 32, 32, { opacity: 0.18 })}
                    {rect(42, 12, 30, 3, { rx: 1, opacity: 0.32 })}
                    {textLines(18, 3, 42)}
                    {rect(42, 34, 18, 6, { rx: 2, opacity: 0.28 })}
                </>
            );
        case 'BannersSingle':
            return (
                <>
                    {rect(4, 10, 72, 28, { opacity: 0.16, stroke: true })}
                    {rect(22, 20, 36, 3, { rx: 1, opacity: 0.35 })}
                    {rect(30, 27, 20, 5, { rx: 2, opacity: 0.28 })}
                </>
            );
        case 'BannersDouble':
            return (
                <>
                    {rect(4, 8, 34, 32, { opacity: 0.2, stroke: true })}
                    {rect(42, 8, 34, 32, { opacity: 0.2, stroke: true })}
                    {rect(10, 30, 22, 2, { rx: 1, opacity: 0.3 })}
                    {rect(48, 30, 22, 2, { rx: 1, opacity: 0.3 })}
                </>
            );
        case 'ParallaxBanner':
            return (
                <>
                    {rect(4, 6, 72, 36, { opacity: 0.1, stroke: true })}
                    {rect(8, 10, 64, 28, { opacity: 0.08 })}
                    {rect(18, 18, 44, 3, { rx: 1, opacity: 0.32 })}
                    {rect(28, 26, 24, 5, { rx: 2, opacity: 0.25 })}
                </>
            );
        case 'Collage':
            return (
                <>
                    {rect(4, 6, 36, 36, { opacity: 0.2 })}
                    {rect(42, 6, 34, 16, { opacity: 0.16 })}
                    {rect(42, 24, 16, 18, { opacity: 0.14 })}
                    {rect(60, 24, 16, 18, { opacity: 0.22 })}
                </>
            );
        case 'CategoryGrid':
            return (
                <>
                    {rect(6, 4, 30, 2.5, { rx: 1, opacity: 0.3 })}
                    {[6, 24, 42, 60].map((x) => (
                        <React.Fragment key={x}>
                            {rect(x, 14, 14, 14, { opacity: 0.18 })}
                            {rect(x + 1, 30, 12, 2, { rx: 1, opacity: 0.28 })}
                        </React.Fragment>
                    ))}
                </>
            );
        case 'CategoryShortcuts':
            return (
                <>
                    {[10, 26, 42, 58].map((cx) => (
                        <circle key={cx} cx={cx} cy={18} r={8} fill="currentColor" fillOpacity={0.16} />
                    ))}
                    {[10, 26, 42, 58].map((cx) => (
                        <rect key={`l-${cx}`} x={cx - 5} y={30} width={10} height={2} rx={1} fill="currentColor" fillOpacity={0.28} />
                    ))}
                </>
            );
        case 'PromoBar':
            return (
                <>
                    {rect(4, 18, 72, 12, { rx: 2, opacity: 0.2, stroke: true })}
                    {rect(20, 23, 40, 2.5, { rx: 1, opacity: 0.35 })}
                </>
            );
        case 'RichText':
            return <>{textLines(10, 5, 10)}</>;
        case 'ImageWithText':
            return (
                <>
                    {rect(6, 8, 28, 32, { opacity: 0.18 })}
                    {rect(40, 12, 32, 3, { rx: 1, opacity: 0.32 })}
                    {textLines(18, 4, 40)}
                </>
            );
        case 'Multicolumn':
            return (
                <>
                    {rect(24, 4, 32, 2.5, { rx: 1, opacity: 0.3 })}
                    {[8, 30, 52].map((x) => (
                        <React.Fragment key={x}>
                            <circle cx={x + 6} cy={22} r={5} fill="currentColor" fillOpacity={0.18} />
                            {rect(x, 30, 20, 2, { rx: 1, opacity: 0.28 })}
                            {rect(x, 34, 16, 2, { rx: 1, opacity: 0.18 })}
                        </React.Fragment>
                    ))}
                </>
            );
        case 'Testimonials':
            return (
                <>
                    {rect(8, 10, 64, 28, { rx: 3, opacity: 0.12, stroke: true })}
                    {rect(14, 16, 4, 3, { rx: 1, opacity: 0.25 })}
                    {textLines(22, 3, 14)}
                    {rect(14, 34, 20, 2, { rx: 1, opacity: 0.22 })}
                </>
            );
        case 'FaqAccordion':
            return (
                <>
                    {rect(8, 6, 64, 2.5, { rx: 1, opacity: 0.3 })}
                    {[10, 20, 30, 38].map((y, i) => (
                        <React.Fragment key={y}>
                            {rect(8, y, 56, 7, { rx: 1, opacity: i === 0 ? 0.2 : 0.1, stroke: true })}
                            {rect(12, y + 2.5, 36, 2, { rx: 1, opacity: 0.28 })}
                            {rect(58, y + 2, 4, 4, { rx: 0.5, opacity: 0.2, stroke: true })}
                        </React.Fragment>
                    ))}
                </>
            );
        case 'CollapsibleContent':
            return (
                <>
                    {rect(8, 5, 40, 2.5, { rx: 1, opacity: 0.3 })}
                    {[12, 22, 32].map((y) => (
                        <React.Fragment key={y}>
                            {rect(8, y, 64, 8, { rx: 1, opacity: 0.12, stroke: true })}
                            {rect(12, y + 3, 44, 2, { rx: 1, opacity: 0.26 })}
                        </React.Fragment>
                    ))}
                </>
            );
        case 'TrustBadges':
            return (
                <>
                    {[8, 24, 40, 56].map((x) => (
                        <React.Fragment key={x}>
                            <circle cx={x + 6} cy={20} r={6} fill="currentColor" fillOpacity={0.16} stroke="currentColor" strokeOpacity={0.25} strokeWidth={0.75} />
                            {rect(x, 30, 12, 2, { rx: 1, opacity: 0.28 })}
                        </React.Fragment>
                    ))}
                </>
            );
        case 'Countdown':
            return (
                <>
                    {rect(20, 8, 40, 2.5, { rx: 1, opacity: 0.3 })}
                    {[14, 28, 42, 56].map((x) => (
                        <React.Fragment key={x}>
                            {rect(x, 18, 10, 14, { rx: 1, opacity: 0.18, stroke: true })}
                            {rect(x + 2, 23, 6, 3, { rx: 1, opacity: 0.32 })}
                        </React.Fragment>
                    ))}
                </>
            );
        case 'Video':
            return (
                <>
                    {rect(10, 8, 60, 32, { rx: 2, opacity: 0.14, stroke: true })}
                    <path d="M36 18 L36 30 L48 24 Z" fill="currentColor" fillOpacity={0.35} />
                </>
            );
        case 'Brands':
            return (
                <>
                    {rect(14, 6, 52, 2.5, { rx: 1, opacity: 0.28 })}
                    {[8, 24, 40, 56].map((x) => (
                        <rect key={x} x={x} y={18} width={14} height={10} rx={1} fill="currentColor" fillOpacity={0.16} stroke="currentColor" strokeOpacity={0.2} strokeWidth={0.75} />
                    ))}
                </>
            );
        case 'ContactForm':
            return (
                <>
                    {rect(10, 6, 36, 2.5, { rx: 1, opacity: 0.3 })}
                    {rect(10, 14, 60, 7, { rx: 1, opacity: 0.12, stroke: true })}
                    {rect(10, 24, 60, 7, { rx: 1, opacity: 0.12, stroke: true })}
                    {rect(10, 34, 60, 10, { rx: 1, opacity: 0.1, stroke: true })}
                    {rect(10, 40, 22, 5, { rx: 2, opacity: 0.28 })}
                </>
            );
        case 'CustomHtml':
            return (
                <>
                    {rect(8, 10, 64, 28, { rx: 2, opacity: 0.1, stroke: true })}
                    <text x="14" y="22" fontSize="8" fill="currentColor" fillOpacity={0.4} fontFamily="ui-monospace, monospace">
                        {'</>'}
                    </text>
                    {textLines(26, 2, 22)}
                </>
            );
        default:
            return (
                <>
                    {rect(12, 12, 56, 24, { opacity: 0.12, stroke: true })}
                    {rect(24, 22, 32, 3, { rx: 1, opacity: 0.25 })}
                </>
            );
    }
}
