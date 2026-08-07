import React, { useEffect, useState } from 'react';
import { loc } from './_shared';
import { useT } from '../i18n';

interface CountdownProps {
    locale: string;
    title: { ar?: string; en?: string };
    endsAt: string;
    ctaText: { ar?: string; en?: string };
    ctaUrl: string;
}

function useRemaining(endsAt: string) {
    const target = endsAt ? new Date(endsAt).getTime() : null;
    const compute = () => {
        if (!target) return { days: '00', hours: '00', minutes: '00', seconds: '00' };
        const diff = Math.max(0, target - Date.now());
        const s = Math.floor(diff / 1000);
        return {
            days: String(Math.floor(s / 86400)).padStart(2, '0'),
            hours: String(Math.floor((s % 86400) / 3600)).padStart(2, '0'),
            minutes: String(Math.floor((s % 3600) / 60)).padStart(2, '0'),
            seconds: String(s % 60).padStart(2, '0'),
        };
    };
    const [units, setUnits] = useState(compute);
    useEffect(() => {
        // Recompute immediately on every target change so the preview reflects a
        // new end date (or a cleared one → all zeros) without waiting up to a
        // second for the first interval tick.
        setUnits(compute());
        if (!target) return;
        const id = setInterval(() => setUnits(compute()), 1000);
        return () => clearInterval(id);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [target]);
    return units;
}

const UNIT_LABELS = {
    ar: { days: 'أيام', hours: 'ساعات', minutes: 'دقائق', seconds: 'ثواني' },
    en: { days: 'Days', hours: 'Hours', minutes: 'Minutes', seconds: 'Seconds' },
};

export function Countdown(props: CountdownProps) {
    const t = useT();
    const units = useRemaining(props.endsAt);
    const lang = props.locale === 'ar' ? 'ar' : 'en';

    return (
        <div className="rounded-lg border border-warning-200 bg-warning-50 p-8 text-center">
            {loc(props.title, props.locale) && <h2 className="mb-4 text-2xl font-bold text-neutral-900">{loc(props.title, props.locale)}</h2>}
            <div className="flex flex-wrap justify-center gap-2 sm:gap-4">
                {(['days', 'hours', 'minutes', 'seconds'] as const).map((unit) => (
                    <div key={unit} className="rounded-lg bg-white px-3 py-2 shadow-sm sm:px-4 sm:py-3">
                        <div className="text-xl font-bold text-neutral-900 sm:text-2xl">{units[unit]}</div>
                        <div className="text-xs text-neutral-500">{UNIT_LABELS[lang][unit]}</div>
                    </div>
                ))}
            </div>
            {loc(props.ctaText, props.locale) && (
                <a
                    href={props.ctaUrl || '#'}
                    onClick={(e) => e.preventDefault()}
                    className="mt-5 inline-block rounded-full bg-warning-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-warning-700"
                >
                    {loc(props.ctaText, props.locale)}
                </a>
            )}
        </div>
    );
}
