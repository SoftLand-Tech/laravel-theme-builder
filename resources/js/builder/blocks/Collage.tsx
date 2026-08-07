import React from 'react';
import { useViewport } from '../viewportContext';

interface CollageProps {
    items: Array<{ image?: string; url?: string }>;
}

export function Collage(props: CollageProps) {
    const viewport = useViewport();
    const raw = (props.items ?? []).slice(0, 4);
    if (raw.length < 2) return null;
    const isMobile = viewport === 'mobile';

    const Tile = ({ item, i, aspect }: { item: { image?: string; url?: string }; i: number; aspect: string }) => (
        <a
            key={i}
            href={item.url || '#'}
            onClick={(e) => e.preventDefault()}
            className="relative block overflow-hidden rounded-lg bg-neutral-200"
            style={{ aspectRatio: aspect }}
        >
            {item.image ? (
                <img src={item.image} alt="" className="absolute inset-0 h-full w-full object-cover transition hover:scale-105" />
            ) : (
                <div className="absolute inset-0 flex items-center justify-center text-xs text-neutral-400">
                    Image
                </div>
            )}
        </a>
    );

    const items = raw;
    const count = items.length;

    if (count === 2) {
        return (
            <div className={`grid grid-cols-1 gap-4 ${isMobile ? '' : 'md:grid-cols-2'}`}>
                {items.map((item, i) => (
                    <Tile key={i} item={item} i={i} aspect="4 / 5" />
                ))}
            </div>
        );
    }

    if (count === 3) {
        return (
            <div className={`grid grid-cols-2 gap-4 ${isMobile ? '' : 'md:grid-cols-4'}`}>
                <div className={isMobile ? '' : 'col-span-2 row-span-2'}>
                    <Tile item={items[0]} i={0} aspect="1 / 1" />
                </div>
                {items.slice(1).map((item, i) => (
                    <Tile key={i + 1} item={item} i={i + 1} aspect="1 / 1" />
                ))}
            </div>
        );
    }

    return (
        <div className={`grid grid-cols-2 gap-4 ${isMobile ? '' : 'md:grid-cols-4'}`}>
            {items.map((item, i) => (
                <Tile key={i} item={item} i={i} aspect="1 / 1" />
            ))}
        </div>
    );
}
