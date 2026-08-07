interface BlockProps {
    locale: string;
    [key: string]: unknown;
}

function loc(val: { ar?: string; en?: string } | undefined, locale: string): string {
    if (!val) return '';
    return val[locale] ?? val.ar ?? val.en ?? '';
}

export type { BlockProps };
export { loc };
