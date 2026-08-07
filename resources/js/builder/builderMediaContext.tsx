import React, { createContext, useContext } from 'react';

export type BuilderMediaApi = {
    mediaUrl: string;
    mediaUploadUrl: string;
    csrfToken: string;
};

const BuilderMediaContext = createContext<BuilderMediaApi | null>(null);

export function BuilderMediaProvider({
    value,
    children,
}: {
    value: BuilderMediaApi;
    children: React.ReactNode;
}) {
    return <BuilderMediaContext.Provider value={value}>{children}</BuilderMediaContext.Provider>;
}

export function useBuilderMedia(): BuilderMediaApi {
    const ctx = useContext(BuilderMediaContext);
    if (!ctx) {
        throw new Error('useBuilderMedia must be used within BuilderMediaProvider');
    }

    return ctx;
}
