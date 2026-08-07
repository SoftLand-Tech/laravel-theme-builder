import React from 'react';
import { loc } from './_shared';
import { useT } from '../i18n';

interface VideoProps {
    locale: string;
    title: { ar?: string; en?: string };
    videoUrl: string;
}

function embedUrl(url: string): string | null {
    if (!url) return null;
    let m = url.match(/(?:youtube\.com\/(?:watch\?(?:[^#]*&)?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]+)/);
    if (m) return `https://www.youtube.com/embed/${m[1]}`;
    m = url.match(/vimeo\.com\/(?:video\/)?(\d+)/);
    if (m) return `https://player.vimeo.com/video/${m[1]}`;
    return null;
}

export function Video(props: VideoProps) {
    const t = useT();
    const embed = embedUrl(props.videoUrl);
    const title = loc(props.title, props.locale);

    return (
        <div>
            {title && <h2 className="mb-6 text-center text-2xl font-bold text-neutral-900">{title}</h2>}
            {embed ? (
                <div className="overflow-hidden rounded-lg bg-black" style={{ aspectRatio: '16 / 9' }}>
                    <iframe
                        src={embed}
                        className="h-full w-full"
                        frameBorder={0}
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowFullScreen
                        loading="lazy"
                        title={title}
                    />
                </div>
            ) : (
                <div className="flex aspect-video w-full items-center justify-center rounded-lg border border-dashed border-neutral-300 bg-neutral-50 text-sm text-neutral-400">
                    {t('Add a YouTube or Vimeo link')}
                </div>
            )}
        </div>
    );
}
