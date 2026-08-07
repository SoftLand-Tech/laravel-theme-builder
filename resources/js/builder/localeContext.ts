import { createContext, useContext } from 'react';

/**
 * The active editor locale (ar/en), provided once by `PuckBuilder` so leaf
 * controls (e.g. the rich-text editor's direction) can read it without prop
 * drilling through every layer.
 */
export const LocaleContext = createContext<string>('ar');

export function useLocale(): string {
    return useContext(LocaleContext);
}
