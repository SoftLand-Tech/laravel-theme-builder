import React, { useState } from 'react';
import type { FieldDef } from '../config/fields';
import { meetsConditions } from '../config/fields';
import { defaultCollectionItem } from '../config/blocks';
import { FieldControl } from './FieldControl';
import { FieldShell } from './FormControls';

interface RepeaterFieldProps {
    field: FieldDef;
    value: unknown;
    onChange: (v: unknown) => void;
    colorSchemeOptions?: Array<{ label: string; value: string }>;
}

type Item = Record<string, unknown>;

/**
 * Editor for `collection` fields (Salla's blocks-in-section model). Each item is
 * a collapsible card rendering its `itemFields` via `FieldControl`. Items can be
 * added (up to `maxLength`), removed (down to `minLength`), and reordered.
 */
export function RepeaterField({ field, value, onChange, colorSchemeOptions }: RepeaterFieldProps) {
    const items: Item[] = Array.isArray(value) ? (value as Item[]) : [];
    const [openIndex, setOpenIndex] = useState<number | null>(null);

    const min = field.minLength ?? 0;
    const max = field.maxLength ?? 50;
    const itemLabel = field.itemLabel ?? 'Item';

    const update = (next: Item[]) => onChange(next);

    const addItem = () => {
        if (items.length >= max) return;
        const next = [...items, defaultCollectionItem(field)];
        update(next);
        setOpenIndex(next.length - 1);
    };

    const removeItem = (index: number) => {
        if (items.length <= min) return;
        update(items.filter((_, i) => i !== index));
        setOpenIndex(null);
    };

    const moveItem = (index: number, dir: -1 | 1) => {
        const target = index + dir;
        if (target < 0 || target >= items.length) return;
        const next = [...items];
        [next[index], next[target]] = [next[target], next[index]];
        update(next);
    };

    const setItemField = (index: number, key: string, v: unknown) => {
        update(items.map((it, i) => (i === index ? { ...it, [key]: v } : it)));
    };

    return (
        <div className="space-y-2">
            {items.map((item, index) => {
                const open = openIndex === index;
                return (
                    <div key={index} className="rounded-lg border border-neutral-200 bg-neutral-50">
                        <div className="flex items-center justify-between px-3 py-2">
                            <button
                                type="button"
                                onClick={() => setOpenIndex(open ? null : index)}
                                className="flex-1 text-left text-sm font-medium text-neutral-800 hover:text-neutral-900"
                            >
                                {itemLabel} {index + 1}
                            </button>
                            <div className="flex items-center gap-1">
                                <IconBtn title="Move up" disabled={index === 0} onClick={() => moveItem(index, -1)}>↑</IconBtn>
                                <IconBtn title="Move down" disabled={index === items.length - 1} onClick={() => moveItem(index, 1)}>↓</IconBtn>
                                <IconBtn title="Remove" disabled={items.length <= min} danger onClick={() => removeItem(index)}>✕</IconBtn>
                            </div>
                        </div>
                        {open && (
                            <div className="space-y-3 border-t border-neutral-200 px-3 py-3">
                                {(field.itemFields ?? [])
                                    .filter((itemField) => meetsConditions(itemField, item))
                                    .map((itemField) => (
                                        <FieldShell
                                            key={itemField.id}
                                            label={itemField.label}
                                            description={itemField.description}
                                        >
                                            <FieldControl
                                                field={itemField}
                                                value={item[itemField.id]}
                                                onChange={(v) => setItemField(index, itemField.id, v)}
                                                colorSchemeOptions={colorSchemeOptions}
                                            />
                                        </FieldShell>
                                    ))}
                            </div>
                        )}
                    </div>
                );
            })}

            <button
                type="button"
                onClick={addItem}
                disabled={items.length >= max}
                className="w-full rounded-lg border border-dashed border-neutral-300 px-3 py-2 text-xs text-neutral-400 transition hover:border-neutral-400 hover:text-neutral-900 disabled:opacity-40"
            >
                + Add {itemLabel.toLowerCase()}
            </button>
        </div>
    );
}

function IconBtn({
    children,
    onClick,
    disabled,
    danger,
    title,
}: {
    children: React.ReactNode;
    onClick: () => void;
    disabled?: boolean;
    danger?: boolean;
    title: string;
}) {
    return (
        <button
            type="button"
            title={title}
            disabled={disabled}
            onClick={onClick}
            className={`flex h-6 w-6 items-center justify-center rounded text-xs transition disabled:opacity-30 ${
                danger ? 'text-red-400 hover:bg-red-500/10' : 'text-neutral-400 hover:bg-neutral-100 hover:text-neutral-900'
            }`}
        >
            {children}
        </button>
    );
}
