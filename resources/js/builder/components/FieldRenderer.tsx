import React from 'react';
import type { FieldDef } from '../config/fields';
import { meetsConditions, resolveFormat } from '../config/fields';
import { FieldControl } from './FieldControl';
import { RepeaterField } from './RepeaterField';
import { FieldShell, StaticChrome, SwitchRow } from './FormControls';
import { useT } from '../i18n';

interface FieldRendererProps {
    fields: FieldDef[];
    /** The object the fields read from / write to (block props or a settings slice). */
    value: Record<string, unknown>;
    /** Receives a partial patch to merge into `value`. */
    onChange: (partial: Record<string, unknown>) => void;
    /** Live color-scheme options, used by the `color-scheme` format. */
    colorSchemeOptions?: Array<{ label: string; value: string }>;
}

interface FieldGroup {
    title?: FieldDef;
    fields: FieldDef[];
}

/** Whether a field is a `group()` heading marker (starts a new section). */
function isGroupTitle(field: FieldDef): boolean {
    return field.type === 'static' && (field.format ?? 'description') === 'title';
}

/**
 * Split a flat field list into sections delimited by `group()` title markers.
 * Fields before the first title fall into a leading, header-less section.
 */
function groupFields(fields: FieldDef[]): FieldGroup[] {
    const groups: FieldGroup[] = [];
    let current: FieldGroup = { fields: [] };
    let started = false;

    for (const field of fields) {
        if (isGroupTitle(field)) {
            if (started || current.fields.length > 0) {
                groups.push(current);
            }
            current = { title: field, fields: [] };
            started = true;
        } else {
            current.fields.push(field);
        }
    }
    groups.push(current);

    return groups.filter((group) => group.title || group.fields.length > 0);
}

/**
 * Renders a list of field definitions against a value object, honoring
 * `conditions`, `static` chrome, `collection` repeaters, and scalar/bilingual
 * controls. `group()` markers split the fields into bordered section cards.
 * Shared by the block Inspector and the schema-driven SettingsForm.
 */
export function FieldRenderer({ fields, value, onChange, colorSchemeOptions }: FieldRendererProps) {
    const t = useT();
    const groups = groupFields(fields);

    return (
        <div className="space-y-3">
            {groups.map((group, index) => {
                const hasVisibleField = group.fields.some(
                    (field) => field.type === 'static' || meetsConditions(field, value),
                );

                // Skip a section whose fields are all hidden by conditions.
                if (group.title && ! hasVisibleField) return null;

                return (
                    <section
                        key={group.title?.id ?? `group-${index}`}
                        className="rounded-lg border border-neutral-200 bg-neutral-50 p-3.5"
                    >
                        {group.title && (
                            <h3 className="mb-3 text-[11px] font-semibold uppercase tracking-wide text-neutral-500">
                                {t(group.title.label ?? '')}
                            </h3>
                        )}
                        <div className="space-y-3.5">
                            {group.fields.map((field) => (
                                <FieldRow
                                    key={field.id}
                                    field={field}
                                    value={value}
                                    onChange={onChange}
                                    colorSchemeOptions={colorSchemeOptions}
                                    groupTitle={group.title?.label}
                                />
                            ))}
                        </div>
                    </section>
                );
            })}
        </div>
    );
}

function FieldRow({
    field,
    value,
    onChange,
    colorSchemeOptions,
    groupTitle,
}: {
    field: FieldDef;
    value: Record<string, unknown>;
    onChange: (partial: Record<string, unknown>) => void;
    colorSchemeOptions?: Array<{ label: string; value: string }>;
    groupTitle?: string;
}) {
    if (!meetsConditions(field, value)) return null;

    if (field.type === 'static') {
        return <StaticChrome field={field} />;
    }

    const fieldValue = value[field.id];
    const update = (next: unknown) => onChange({ [field.id]: next });
    const shellLabel = field.label && field.label !== groupTitle ? field.label : undefined;

    // Switches render as an inline label + toggle row (not a stacked shell).
    if (resolveFormat(field) === 'switch') {
        return (
            <SwitchRow
                label={field.label}
                description={field.description}
                value={Boolean(fieldValue)}
                onChange={update}
            />
        );
    }

    if (field.type === 'collection') {
        return (
            <FieldShell label={shellLabel} description={field.description}>
                <RepeaterField
                    field={field}
                    value={fieldValue}
                    onChange={update}
                    colorSchemeOptions={colorSchemeOptions}
                />
            </FieldShell>
        );
    }

    return (
        <FieldShell label={shellLabel} description={field.description}>
            <FieldControl
                field={field}
                value={fieldValue}
                onChange={update}
                colorSchemeOptions={colorSchemeOptions}
            />
        </FieldShell>
    );
}
