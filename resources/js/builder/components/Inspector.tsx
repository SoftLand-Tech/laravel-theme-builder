import React from 'react';
import { BLOCK_SCHEMAS } from '../config/blocks';
import { schemeOptions } from '../config/settings';
import type { ColorScheme } from '../types/settings';
import type { Block, BlockProps } from '../types/blocks';
import { FieldRenderer } from './FieldRenderer';
import { useT } from '../i18n';

interface InspectorProps {
    block: Block | null;
    onPropsChange: (partial: BlockProps) => void;
    colorSchemes?: ColorScheme[];
}

export function Inspector({ block, onPropsChange, colorSchemes }: InspectorProps) {
    const t = useT();

    if (!block) {
        return (
            <p className="px-1 py-6 text-center text-sm text-neutral-500">
                {t('Select a block on the canvas to edit its settings.')}
            </p>
        );
    }

    const schema = BLOCK_SCHEMAS[block.type];
    if (!schema) {
        return (
            <div className="rounded-lg border border-yellow-400 bg-yellow-50 p-3 text-xs text-yellow-700">
                {t('The block type ":type" is not editable here.', { type: block.type })}
            </div>
        );
    }

    return (
        <div>
            <h3 className="mb-3 text-sm font-semibold text-neutral-900">{t(block.type)}</h3>
            <FieldRenderer
                fields={schema.fields}
                value={block.props}
                onChange={onPropsChange}
                colorSchemeOptions={schemeOptions(colorSchemes)}
            />
        </div>
    );
}
