import React from 'react';
import type { FieldDef } from '../config/fields';
import { FieldRenderer } from './FieldRenderer';

interface SettingsFormProps {
    fields: FieldDef[];
    /** The settings slice these fields edit (e.g. `settings.product_card`). */
    value: Record<string, unknown>;
    /** Receives the fully-updated slice. */
    onChange: (next: Record<string, unknown>) => void;
}

/**
 * Schema-driven form for a settings slice. A thin wrapper over `FieldRenderer`
 * that merges each field patch into the slice and emits the whole new object.
 */
export function SettingsForm({ fields, value, onChange }: SettingsFormProps) {
    return <FieldRenderer fields={fields} value={value} onChange={(partial) => onChange({ ...value, ...partial })} />;
}
