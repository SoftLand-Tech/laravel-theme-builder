import React from 'react';
import { useT } from '../i18n';

const LOCALES = [
    { code: 'ar', label: 'العربية', lang: 'ar' },
    { code: 'en', label: 'English', lang: 'en' },
] as const;

function GlobeIcon({ className }: { className?: string }) {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" className={className} aria-hidden>
            <circle cx="12" cy="12" r="10" />
            <path strokeLinecap="round" d="M2 12h20" />
            <path
                strokeLinecap="round"
                d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"
            />
        </svg>
    );
}

function ChevronDownIcon({ className }: { className?: string }) {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className={className} aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M6 9l6 6 6-6" />
        </svg>
    );
}

export function LocaleSelect({
    value,
    onChange,
}: {
    value: string;
    onChange: (locale: string) => void;
}) {
    const t = useT();

    return (
        <div className="relative inline-flex shrink-0 items-center">
            <span className="pointer-events-none absolute start-2.5 top-1/2 z-10 flex -translate-y-1/2 items-center text-neutral-500" aria-hidden>
                <GlobeIcon className="size-4" />
            </span>
            <select
                value={value}
                onChange={(e) => onChange(e.target.value)}
                className="max-w-[10.5rem] min-w-[8.5rem] cursor-pointer appearance-none rounded-lg border border-neutral-300 bg-white py-1.5 ps-8 pe-8 text-sm font-medium text-neutral-900 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                aria-label={t('Language')}
            >
                {LOCALES.map((loc) => (
                    <option key={loc.code} value={loc.code} lang={loc.lang}>
                        {loc.label}
                    </option>
                ))}
            </select>
            <span className="pointer-events-none absolute end-2.5 top-1/2 flex -translate-y-1/2 items-center text-neutral-400" aria-hidden>
                <ChevronDownIcon className="size-3.5" />
            </span>
        </div>
    );
}
