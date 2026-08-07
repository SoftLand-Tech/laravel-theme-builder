import React, { useState } from 'react';
import { useT } from '../i18n';
import { LocaleSelect } from './LocaleSelect';

interface BuilderHeaderProps {
    themeName: string;
    storeName: string;
    saveStatus: 'idle' | 'saving' | 'saved' | 'error';
    usesDraftOverlay?: boolean;
    hasUnpublishedDraft?: boolean;
    lastPublished: Date | null;
    lastVersion: string | null;
    /** Next version label this publish would create (minor). */
    nextMinorVersion: string;
    onPublish: (opts: { major: boolean; changeSummary: string }) => void;
    onPromote: () => void;
    onSaveAndExit: () => void;
    onExit: () => void;
    locale: string;
    onLocaleChange: (locale: string) => void;
    viewport: 'desktop' | 'mobile';
    onViewportChange: (v: 'desktop' | 'mobile') => void;
    sidebarTab: 'sections' | 'properties' | 'theme' | 'header-footer' | 'pages' | 'revisions';
    onSidebarTabChange: (t: 'sections' | 'properties' | 'theme' | 'header-footer' | 'pages' | 'revisions') => void;
}

const tabGroupClass = 'flex rounded-lg border border-neutral-200 bg-neutral-100';
const tabBtn = (active: boolean) =>
    `px-3 py-1.5 text-xs font-medium transition ${
        active ? 'bg-primary-600 text-white' : 'text-neutral-500 hover:text-neutral-900'
    }`;

export function BuilderHeader({
    themeName,
    storeName,
    saveStatus,
    usesDraftOverlay = false,
    hasUnpublishedDraft = false,
    lastPublished,
    lastVersion,
    nextMinorVersion,
    onPublish,
    onPromote,
    onSaveAndExit,
    onExit,
    locale,
    onLocaleChange,
    viewport,
    onViewportChange,
    sidebarTab,
    onSidebarTabChange,
}: BuilderHeaderProps) {
    const t = useT();
    const [publishOpen, setPublishOpen] = useState(false);
    const [major, setMajor] = useState(false);
    const [summary, setSummary] = useState('');
    const statusText = {
        idle: '',
        saving: t('Saving'),
        saved: usesDraftOverlay ? t('Saved as draft') : t('Saved'),
        error: t('Save failed'),
    }[saveStatus];

    const majorNumber = major ? bumpMajor(nextMinorVersion) : nextMinorVersion;

    return (
        <>
            {usesDraftOverlay && hasUnpublishedDraft && (
                <div className="border-b border-warning-200 bg-warning-50 px-4 py-2 text-center text-sm text-warning-900">
                    {t('You have unpublished changes. Update the live store when you are ready.')}
                </div>
            )}
        <header className="flex flex-wrap items-center justify-between gap-2 border-b border-neutral-200 bg-white px-3 py-2 sm:gap-3 sm:px-5 sm:py-3">
            <div className="flex min-w-0 items-center gap-2 sm:gap-3">
                <div className="truncate font-display text-base font-bold text-neutral-900 sm:text-lg">{themeName}</div>
                <span className="hidden text-neutral-300 sm:inline">|</span>
                <div className="hidden text-sm text-neutral-500 sm:block">{storeName}</div>
                {statusText && <span className="ml-1 text-xs text-neutral-400 sm:ml-3">{statusText}</span>}
                {lastVersion && (
                    <span className="rounded-full bg-success-500/15 px-2.5 py-0.5 text-xs font-medium text-success-700">
                        {t('v:version', { version: lastVersion })}
                    </span>
                )}
            </div>

            <div className="flex flex-wrap items-center gap-2 sm:gap-3">
                <div className={`${tabGroupClass} max-w-full overflow-x-auto`}>
                    {(['sections', 'properties', 'theme', 'header-footer', 'pages', 'revisions'] as const).map((tab) => (
                        <button
                            key={tab}
                            type="button"
                            onClick={() => onSidebarTabChange(tab)}
                            className={`${tabBtn(sidebarTab === tab)} whitespace-nowrap`}
                        >
                            {t(
                                tab === 'header-footer' ? 'Header & Footer'
                                    : tab === 'pages' ? 'Pages'
                                    : tab.charAt(0).toUpperCase() + tab.slice(1),
                            )}
                        </button>
                    ))}
                </div>

                {/* Viewport */}
                <div className={tabGroupClass}>
                    <button type="button" onClick={() => onViewportChange('desktop')} className={tabBtn(viewport === 'desktop')} title={t('Desktop')}>
                        <span className="hidden sm:inline">{t('Desktop')}</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" className="h-4 w-4 sm:hidden"><rect x="2" y="3" width="20" height="14" rx="2" /><line x1="8" y1="21" x2="16" y2="21" /><line x1="12" y1="17" x2="12" y2="21" /></svg>
                    </button>
                    <button type="button" onClick={() => onViewportChange('mobile')} className={tabBtn(viewport === 'mobile')} title={t('Mobile')}>
                        <span className="hidden sm:inline">{t('Mobile')}</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" className="h-4 w-4 sm:hidden"><rect x="5" y="2" width="14" height="20" rx="2" /><line x1="12" y1="18" x2="12" y2="18" /></svg>
                    </button>
                </div>

                <LocaleSelect value={locale} onChange={onLocaleChange} />
            </div>

            <div className="flex items-center gap-2 sm:gap-2.5">
                <button
                    type="button"
                    onClick={onExit}
                    className="rounded-full border border-neutral-300 px-3 py-2 text-sm font-medium text-neutral-700 transition hover:border-neutral-400 sm:px-4"
                >
                    {t('Exit')}
                </button>
                <button
                    type="button"
                    onClick={onSaveAndExit}
                    className="rounded-full border border-neutral-300 px-3 py-2 text-sm font-medium text-neutral-700 transition hover:border-neutral-400 sm:px-4"
                >
                    <span className="hidden sm:inline">{t('Save & Exit')}</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" className="h-4 w-4 sm:hidden"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 19.5l-15-15m0 0V8m0-3.5H8" /></svg>
                </button>
                <button
                    type="button"
                    onClick={() => setPublishOpen(true)}
                    className="rounded-full border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-800 transition hover:border-neutral-400 sm:px-5"
                >
                    <span className="hidden sm:inline">{t('Save version')}</span>
                    <span className="sm:hidden">{t('Version')}</span>
                </button>
                {usesDraftOverlay && (
                    <button
                        type="button"
                        onClick={onPromote}
                        className="rounded-full bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-500 sm:px-5"
                    >
                        <span className="hidden sm:inline">{t('Update live store')}</span>
                        <span className="sm:hidden">{t('Go live')}</span>
                    </button>
                )}
            </div>

            {publishOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40" onClick={() => setPublishOpen(false)}>
                    <div
                        className="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
                        onClick={(e) => e.stopPropagation()}
                    >
                        <h2 className="font-display text-lg font-bold text-neutral-900">{t('Save a new version')}</h2>
                        <p className="mt-1 text-sm text-neutral-500">
                            {t('This saves a versioned snapshot for your history. It does not update the live storefront.')}
                        </p>

                        <div className="mt-5">
                            <label className="text-xs font-semibold uppercase tracking-wide text-neutral-500">{t('Version bump')}</label>
                            <div className="mt-2 flex gap-2">
                                <button
                                    type="button"
                                    onClick={() => setMajor(false)}
                                    className={tabBtn(!major) + ' flex-1 rounded-md border px-3 py-2'}
                                >
                                    {t('Minor')} · {nextMinorVersion}
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setMajor(true)}
                                    className={tabBtn(major) + ' flex-1 rounded-md border px-3 py-2'}
                                >
                                    {t('Major')} · {majorNumber}
                                </button>
                            </div>
                            <p className="mt-2 text-xs text-neutral-400">
                                {major
                                    ? t('Use major for a substantial redesign or rebuild.')
                                    : t('Use minor for everyday edits and content changes.')}
                            </p>
                        </div>

                        <div className="mt-4">
                            <label className="text-xs font-semibold uppercase tracking-wide text-neutral-500">{t('Summary (optional)')}</label>
                            <input
                                type="text"
                                value={summary}
                                onChange={(e) => setSummary(e.target.value)}
                                placeholder={t('e.g. Updated hero copy')}
                                className="mt-2 w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
                            />
                        </div>

                        <div className="mt-6 flex justify-end gap-2.5">
                            <button
                                type="button"
                                onClick={() => setPublishOpen(false)}
                                className="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:border-neutral-400"
                            >
                                {t('Cancel')}
                            </button>
                            <button
                                type="button"
                                onClick={() => {
                                    onPublish({ major, changeSummary: summary });
                                    setPublishOpen(false);
                                    setSummary('');
                                    setMajor(false);
                                }}
                                className="rounded-full bg-primary-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-primary-500"
                            >
                                {t('Publish :version', { version: majorNumber })}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </header>
        </>
    );
}

/** Bump the major segment of a "x.y" version string. */
function bumpMajor(version: string): string {
    const m = version.match(/^(\d+)/);
    return m ? `${Number(m[1]) + 1}.0` : '2.0';
}
