import React from 'react';
import type { ThemeSettings } from '../types/settings';
import { THEME_TAB_GROUPS } from '../config/settings';
import { SettingsForm } from './SettingsForm';
import { RepeaterField } from './RepeaterField';
import { useT } from '../i18n';

interface SettingsPanelProps {
    settings: ThemeSettings;
    onChange: (settings: ThemeSettings) => void;
}

function slugKey(name: string): string {
    return name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        || 'scheme';
}

function autoFillSchemeKeys(schemes: Record<string, unknown>[]): Record<string, unknown>[] {
    return schemes.map((s, i) => ({
        ...s,
        key: (s['key'] as string) || slugKey((s['name'] as string) || `scheme-${i + 1}`),
    }));
}

export function SettingsPanel({ settings, onChange }: SettingsPanelProps) {
    const t = useT();
    return (
        <div className="space-y-6">
            <h2 className="text-sm font-semibold text-neutral-900">{t('Theme settings')}</h2>
            {THEME_TAB_GROUPS.map((group) => {
                const slice = settings[group.key as keyof ThemeSettings];
                if (Array.isArray(slice)) {
                    const collectionField = group.fields.find((f) => f.type === 'collection');
                    if (!collectionField) return null;
                    return (
                        <div key={group.key} className="space-y-2">
                            <h3 className="text-xs font-semibold uppercase tracking-wide text-neutral-500">{group.title}</h3>
                            <RepeaterField
                                field={collectionField}
                                value={slice}
                                onChange={(next) => {
                                    const schemes = group.key === 'color_schemes'
                                        ? autoFillSchemeKeys(next as Record<string, unknown>[])
                                        : next;
                                    onChange({ ...settings, [group.key]: schemes as never });
                                }}
                            />
                        </div>
                    );
                }
                return (
                    <SettingsForm
                        key={group.key}
                        fields={group.fields}
                        value={slice as Record<string, unknown>}
                        onChange={(next) => onChange({ ...settings, [group.key]: next })}
                    />
                );
            })}
        </div>
    );
}
