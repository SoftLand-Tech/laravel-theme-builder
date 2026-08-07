import React, { useEffect, useRef, useState } from 'react';
import type { ColorScheme } from '../types/settings';
import { useT } from '../i18n';

/**
 * The inline toolbar shown on the selected canvas block — Shopify-style: quick
 * section-style controls (padding / width / background scheme / alignment),
 * visibility toggles (desktop / mobile), hide-section, and the usual
 * move / duplicate / delete. All controls patch block props through `onPatch`.
 *
 * Non-technical: every control is a button or curated dropdown — no free text,
 * no raw ids. Color-scheme options are derived live from the theme settings.
 */

interface SectionToolbarProps {
    name: string;
    hidden: boolean;
    hiddenHere: boolean;
    viewport: 'desktop' | 'mobile';
    sectionPaddingTop: string;
    sectionPaddingBottom: string;
    sectionWidth: string;
    sectionBackground: string;
    sectionTextAlign: string;
    visibleOnDesktop: boolean;
    visibleOnMobile: boolean;
    colorSchemes: ColorScheme[];
    dragHandleProps: Record<string, unknown>;
    onPatch: (partial: Record<string, unknown>) => void;
    onMove: (dir: -1 | 1) => void;
    onDuplicate: () => void;
    onDelete: () => void;
    onToggleHide: () => void;
}

const PADDING_CYCLE = ['none', 'small', 'medium', 'large'] as const;

function IconBtn({
    title,
    active,
    danger,
    onClick,
    children,
}: {
    title: string;
    active?: boolean;
    danger?: boolean;
    onClick: () => void;
    children: React.ReactNode;
}) {
    return (
        <button
            type="button"
            title={title}
            onClick={(e) => {
                e.stopPropagation();
                onClick();
            }}
            className={`flex h-7 items-center justify-center rounded px-1.5 text-xs transition ${
                active
                    ? 'bg-primary-100 text-primary-700'
                    : danger
                      ? 'text-danger-600 hover:bg-danger-500/10'
                      : 'text-neutral-600 hover:bg-neutral-200 hover:text-neutral-900'
            }`}
        >
            {children}
        </button>
    );
}

export function SectionToolbar(props: SectionToolbarProps) {
    const t = useT();
    const [schemeOpen, setSchemeOpen] = useState(false);
    const schemeRef = useRef<HTMLDivElement>(null);

    // Close the scheme dropdown on any click outside its container (and on
    // Escape), so it doesn't stay pinned when the merchant clicks another
    // toolbar control.
    useEffect(() => {
        if (!schemeOpen) return;
        const onDown = (e: MouseEvent) => {
            if (schemeRef.current && !schemeRef.current.contains(e.target as Node)) {
                setSchemeOpen(false);
            }
        };
        const onKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') setSchemeOpen(false);
        };
        document.addEventListener('mousedown', onDown);
        document.addEventListener('keydown', onKey);
        return () => {
            document.removeEventListener('mousedown', onDown);
            document.removeEventListener('keydown', onKey);
        };
    }, [schemeOpen]);

    const cyclePadding = () => {
        const cur = props.sectionPaddingTop || 'medium';
        const idx = PADDING_CYCLE.indexOf(cur as (typeof PADDING_CYCLE)[number]);
        const next = PADDING_CYCLE[(idx + 1) % PADDING_CYCLE.length];
        props.onPatch({ sectionPaddingTop: next, sectionPaddingBottom: next });
    };

    const paddingLabel = props.sectionPaddingTop === 'none' ? '0' : props.sectionPaddingTop === 'small' ? 'S' : props.sectionPaddingTop === 'large' ? 'L' : 'M';

    return (
        <div
            className="absolute inset-x-0 top-0 z-20 flex flex-wrap items-center gap-0.5 border-b border-neutral-200 bg-white/95 px-1.5 py-1 shadow-sm backdrop-blur"
            onClick={(e) => e.stopPropagation()}
        >
            {/* Identity + drag */}
            <div className="flex items-center gap-1 pe-1">
                <button
                    type="button"
                    className="canvas-drag-handle flex h-7 w-6 cursor-grab items-center justify-center rounded text-neutral-400 hover:bg-neutral-200 hover:text-neutral-900"
                    title={t('Drag to reorder')}
                    {...props.dragHandleProps}
                >
                    ⠿
                </button>
                <span className="text-[11px] font-semibold text-neutral-700">{t(props.name)}</span>
            </div>

            <span className="mx-1 h-4 w-px bg-neutral-200" />

            {/* Section-style quick controls */}
            <IconBtn title={t('Padding')} onClick={cyclePadding}>
                <span className="block min-w-[14px] text-center font-semibold">{paddingLabel}</span>
            </IconBtn>
            <IconBtn
                title={t('Width')}
                active={props.sectionWidth === 'full'}
                onClick={() => props.onPatch({ sectionWidth: props.sectionWidth === 'full' ? 'contained' : 'full' })}
            >
                ⇿
            </IconBtn>

            {/* Background scheme dropdown */}
            <div className="relative" ref={schemeRef}>
                <IconBtn title={t('Background')} active={props.sectionBackground !== ''} onClick={() => setSchemeOpen((v) => !v)}>
                    ◧
                </IconBtn>
                {schemeOpen && (
                    <div
                        className="absolute left-0 top-8 z-30 min-w-[140px] rounded-md border border-neutral-200 bg-white p-1 shadow-lg"
                    >
                        <button
                            type="button"
                            onClick={() => {
                                props.onPatch({ sectionBackground: '' });
                                setSchemeOpen(false);
                            }}
                            className={`block w-full rounded px-2 py-1 text-left text-xs hover:bg-neutral-100 ${props.sectionBackground === '' ? 'font-semibold text-primary-700' : 'text-neutral-700'}`}
                        >
                            {t('Default')}
                        </button>
                        {props.colorSchemes.map((scheme) => (
                            <button
                                key={scheme.key}
                                type="button"
                                onClick={() => {
                                    props.onPatch({ sectionBackground: scheme.key });
                                    setSchemeOpen(false);
                                }}
                                className={`flex w-full items-center gap-2 rounded px-2 py-1 text-left text-xs hover:bg-neutral-100 ${props.sectionBackground === scheme.key ? 'font-semibold text-primary-700' : 'text-neutral-700'}`}
                            >
                                <span className="h-3 w-3 rounded-full border border-neutral-300" style={{ backgroundColor: scheme.background }} />
                                {scheme.name || scheme.key}
                            </button>
                        ))}
                    </div>
                )}
            </div>

            {/* Alignment segmented */}
            <IconBtn title={t('Align start')} active={props.sectionTextAlign === 'start'} onClick={() => props.onPatch({ sectionTextAlign: props.sectionTextAlign === 'start' ? '' : 'start' })}>⇤</IconBtn>
            <IconBtn title={t('Align center')} active={props.sectionTextAlign === 'center'} onClick={() => props.onPatch({ sectionTextAlign: props.sectionTextAlign === 'center' ? '' : 'center' })}>↔</IconBtn>
            <IconBtn title={t('Align end')} active={props.sectionTextAlign === 'end'} onClick={() => props.onPatch({ sectionTextAlign: props.sectionTextAlign === 'end' ? '' : 'end' })}>⇥</IconBtn>

            <span className="mx-1 h-4 w-px bg-neutral-200" />

            {/* Visibility */}
            <IconBtn
                title={t('Show on desktop')}
                active={props.visibleOnDesktop}
                onClick={() => props.onPatch({ visibleOnDesktop: !props.visibleOnDesktop })}
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="h-3.5 w-3.5">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2" /><line x1="8" y1="21" x2="16" y2="21" /><line x1="12" y1="17" x2="12" y2="21" />
                </svg>
            </IconBtn>
            <IconBtn
                title={t('Show on mobile')}
                active={props.visibleOnMobile}
                onClick={() => props.onPatch({ visibleOnMobile: !props.visibleOnMobile })}
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="h-3.5 w-3.5">
                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2" /><line x1="12" y1="18" x2="12" y2="18" />
                </svg>
            </IconBtn>

            <span className="mx-1 h-4 w-px bg-neutral-200" />

            {/* Hide / move / duplicate / delete */}
            <IconBtn title={props.hidden ? t('Unhide section') : t('Hide section')} active={props.hidden} onClick={props.onToggleHide}>
                {props.hidden ? '◉' : '◯'}
            </IconBtn>
            <IconBtn title={t('Move up')} onClick={() => props.onMove(-1)}>↑</IconBtn>
            <IconBtn title={t('Move down')} onClick={() => props.onMove(1)}>↓</IconBtn>
            <IconBtn title={t('Duplicate')} onClick={props.onDuplicate}>⧉</IconBtn>
            <IconBtn title={t('Delete')} danger onClick={props.onDelete}>✕</IconBtn>

            {props.hiddenHere && !props.hidden && (
                <span className="ms-1 rounded bg-neutral-200 px-1.5 py-0.5 text-[10px] text-neutral-600">
                    {t('hidden on')} {props.viewport}
                </span>
            )}
            {props.hidden && (
                <span className="ms-1 rounded bg-warning-100 px-1.5 py-0.5 text-[10px] font-semibold text-warning-700">
                    {t('hidden')}
                </span>
            )}
        </div>
    );
}
