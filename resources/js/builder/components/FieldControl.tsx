import React from 'react';
import type { FieldDef } from '../config/fields';
import { resolveFormat } from '../config/fields';
import { useLocale } from '../localeContext';
import { type Bilingual } from '../types/blocks';
import {
    BlogPostFieldControl,
    CategoryField,
    CheckboxMultiSelect,
    CollectionMultiControl,
    ColorInput,
    ColorSchemeSelect,
    DateTimeInput,
    HtmlInput,
    IconPicker,
    ImageFieldControl,
    LinkField,
    MultiBlogPostControl,
    MultiProductControl,
    NumberInput,
    ProductFieldControl,
    RangeInput,
    RichTextField,
    SelectInput,
    Switch,
    TextAlignControl,
    TextArea,
    TextInput,
    VideoInput,
    normalizeBilingual,
} from './FormControls';

interface FieldControlProps {
    field: FieldDef;
    value: unknown;
    onChange: (v: unknown) => void;
    /** Live color-scheme options, used by the `color-scheme` format. */
    colorSchemeOptions?: Array<{ label: string; value: string }>;
}

/**
 * Renders the right control for a field definition, routing by `type`+`format`
 * (Salla model). Returns the raw control only — the caller wraps it with a
 * label/shell. Bilingual fields (`multilanguage: true`) render stacked AR/EN
 * inputs writing a `{ ar, en }` value.
 */
export function FieldControl({ field, value, onChange, colorSchemeOptions }: FieldControlProps) {
    if (field.multilanguage) {
        return <BilingualControl field={field} value={value} onChange={onChange} />;
    }
    return <ScalarControl field={field} value={value} onChange={onChange} colorSchemeOptions={colorSchemeOptions} />;
}

function ScalarControl({ field, value, onChange, colorSchemeOptions }: FieldControlProps) {
    const format = resolveFormat(field);
    const locale = useLocale();

    switch (format) {
        case 'switch':
            return <Switch value={Boolean(value)} onChange={onChange} />;
        case 'datetime':
            return (
                <DateTimeInput
                    value={typeof value === 'string' ? value : ''}
                    onChange={onChange}
                />
            );
        case 'link':
            return (
                <LinkField
                    value={typeof value === 'string' ? value : ''}
                    onChange={onChange}
                />
            );
        case 'category':
            return (
                <CategoryField
                    value={typeof value === 'number' ? value : null}
                    onChange={onChange}
                />
            );
        case 'product':
            return (
                <ProductFieldControl
                    value={typeof value === 'number' ? value : null}
                    onChange={onChange}
                />
            );
        case 'product-list':
            return (
                <MultiProductControl
                    value={Array.isArray(value) ? (value as number[]) : null}
                    onChange={onChange}
                />
            );
        case 'blog-post':
            return (
                <BlogPostFieldControl
                    value={typeof value === 'number' ? value : null}
                    onChange={onChange}
                />
            );
        case 'blog-post-list':
            return (
                <MultiBlogPostControl
                    value={Array.isArray(value) ? (value as number[]) : null}
                    onChange={onChange}
                />
            );
        case 'collection-list':
            return (
                <CollectionMultiControl
                    value={Array.isArray(value) ? (value as number[]) : null}
                    onChange={onChange}
                />
            );
        case 'video':
            return (
                <VideoInput value={typeof value === 'string' ? value : ''} onChange={onChange} />
            );
        case 'text-align':
            return (
                <TextAlignControl
                    value={typeof value === 'string' ? value : 'start'}
                    onChange={onChange}
                />
            );
        case 'icon':
            return <IconPicker value={typeof value === 'string' ? value : ''} onChange={onChange} />;
        case 'color-scheme':
            return (
                <ColorSchemeSelect
                    value={typeof value === 'string' ? value : ''}
                    onChange={onChange}
                    options={colorSchemeOptions ?? field.options ?? []}
                />
            );
        case 'html':
            return <HtmlInput value={typeof value === 'string' ? value : ''} onChange={onChange} />;
        case 'richtext':
            return (
                <RichTextField
                    value={typeof value === 'string' ? value : ''}
                    onChange={onChange}
                    locale={locale}
                />
            );
        case 'textarea':
            return (
                <TextArea
                    value={typeof value === 'string' ? value : ''}
                    placeholder={field.placeholder}
                    onChange={onChange}
                />
            );
        case 'image':
            return (
                <ImageFieldControl
                    value={typeof value === 'string' ? value : ''}
                    onChange={onChange}
                    dimensions={field.dimensions}
                />
            );
        case 'dropdown-list':
            return (
                <SelectInput
                    value={typeof value === 'string' ? value : ''}
                    options={field.options ?? []}
                    onChange={onChange}
                />
            );
        case 'multi-select':
            return (
                <CheckboxMultiSelect
                    value={Array.isArray(value) ? (value as string[]) : []}
                    onChange={onChange}
                    options={field.options ?? []}
                />
            );
        case 'color':
            return (
                <ColorInput
                    value={typeof value === 'string' ? value : ''}
                    onChange={onChange}
                />
            );
        case 'range':
            return (
                <RangeInput
                    value={typeof value === 'number' ? value : 0}
                    onChange={onChange}
                    min={field.min}
                    max={field.max}
                />
            );
        case 'text':
        default:
            // number type falls through to a text input here; NumberInput is
            // used when the field type is explicitly `number`.
            if (field.type === 'number') {
                return (
                    <NumberInput
                        value={typeof value === 'number' ? value : null}
                        onChange={onChange}
                        min={field.min}
                        max={field.max}
                    />
                );
            }
            return (
                <TextInput
                    value={typeof value === 'string' ? value : ''}
                    placeholder={field.placeholder}
                    onChange={onChange}
                />
            );
    }
}

function BilingualControl({ field, value, onChange }: FieldControlProps) {
    const bi: Bilingual = normalizeBilingual(value);
    const set = (locale: 'ar' | 'en') => (v: unknown) =>
        onChange({ ...bi, [locale]: typeof v === 'string' ? v : String(v ?? '') });

    const format = resolveFormat(field);

    return (
        <div className="space-y-1.5">
            <Labeled locale="AR">
                {renderBilingualInput(format, 'ar', bi.ar, set('ar'), field)}
            </Labeled>
            <Labeled locale="EN">
                {renderBilingualInput(format, 'en', bi.en, set('en'), field)}
            </Labeled>
        </div>
    );
}

function renderBilingualInput(
    format: ReturnType<typeof resolveFormat>,
    locale: 'ar' | 'en',
    val: string,
    onChange: (v: string) => void,
    field: FieldDef,
) {
    if (format === 'richtext') {
        return <RichTextField value={val} onChange={onChange} locale={locale} />;
    }
    if (format === 'textarea') {
        return <TextArea value={val} onChange={onChange} placeholder={field.placeholder} />;
    }
    return <TextInput value={val} onChange={onChange} placeholder={field.placeholder} />;
}

function Labeled({ locale, children }: { locale: string; children: React.ReactNode }) {
    return (
        <div className="space-y-0.5">
            <span className="text-[10px] font-medium uppercase tracking-wide text-neutral-500">{locale}</span>
            {children}
        </div>
    );
}
