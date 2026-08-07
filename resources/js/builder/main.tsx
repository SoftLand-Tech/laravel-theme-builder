import React from 'react';
import { createRoot } from 'react-dom/client';
import { PuckBuilder } from './Builder';

const container = document.getElementById('builder-root');

if (container) {
    const props = {
        themeId: Number(container.dataset.themeId),
        themeName: container.dataset.themeName ?? '',
        themeSlug: container.dataset.themeSlug || undefined,
        themeCssUrl: container.dataset.themeCssUrl || undefined,
        usesDraftOverlay: container.dataset.usesDraftOverlay === '1',
        hasUnpublishedDraft: container.dataset.hasUnpublishedDraft === '1',
        storeName: container.dataset.storeName ?? '',
        csrfToken: container.dataset.csrfToken ?? '',
        endpoints: JSON.parse(container.dataset.apiEndpoints ?? '{}'),
        blockRegistry: JSON.parse(container.dataset.blockRegistry ?? '[]'),
        initialBlocks: JSON.parse(container.dataset.initialBlocks ?? '[]'),
        initialSettings: JSON.parse(container.dataset.initialSettings ?? '{}'),
        initialHeader: JSON.parse(container.dataset.initialHeader ?? '{}'),
        initialFooter: JSON.parse(container.dataset.initialFooter ?? '{}'),
        initialTemplates: JSON.parse(container.dataset.initialTemplates ?? '{}'),
        currentVersion: container.dataset.currentVersion ?? '',
        locale: container.dataset.locale ?? 'ar',
    };

    createRoot(container).render(
        <React.StrictMode>
            <PuckBuilder {...props} />
        </React.StrictMode>,
    );
}
