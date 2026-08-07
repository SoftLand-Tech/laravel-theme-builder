import React, { useState } from 'react';
import type { FieldDef } from '../config/fields';
import { FieldRenderer } from './FieldRenderer';
import { useT } from '../i18n';

interface HeaderFooterPanelProps {
    header: Record<string, unknown>;
    footer: Record<string, unknown>;
    onHeaderChange: (next: Record<string, unknown>) => void;
    onFooterChange: (next: Record<string, unknown>) => void;
    headerFields: FieldDef[];
    footerFields: FieldDef[];
}

export function HeaderFooterPanel({
    header,
    footer,
    onHeaderChange,
    onFooterChange,
    headerFields,
    footerFields,
}: HeaderFooterPanelProps) {
    const t = useT();
    const [tab, setTab] = useState<'header' | 'footer'>('header');

    return (
        <div className="space-y-4">
            <h2 className="text-sm font-semibold text-neutral-900">{t('Header & Footer')}</h2>
            <div className="flex rounded-lg border border-neutral-200 bg-neutral-100">
                {(['header', 'footer'] as const).map((p) => (
                    <button
                        key={p}
                        type="button"
                        onClick={() => setTab(p)}
                        className={`flex-1 px-3 py-1.5 text-xs font-medium transition ${
                            tab === p ? 'bg-primary-600 text-white' : 'text-neutral-500 hover:text-neutral-900'
                        }`}
                    >
                        {t(p === 'header' ? 'Header' : 'Footer')}
                    </button>
                ))}
            </div>

            {tab === 'header' ? (
                <FieldRenderer
                    fields={headerFields}
                    value={header}
                    onChange={(partial) => onHeaderChange({ ...header, ...partial })}
                />
            ) : (
                <FieldRenderer
                    fields={footerFields}
                    value={footer}
                    onChange={(partial) => onFooterChange({ ...footer, ...partial })}
                />
            )}
        </div>
    );
}
